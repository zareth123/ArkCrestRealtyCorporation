<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CommissionRequestSales;
use App\Models\User;
use App\Mail\CommissionReleaseReminder;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendCommissionReleaseReminders extends Command
{
    protected $signature   = 'commissions:send-reminders';
    protected $description = 'Send email reminders for commission releases scheduled for tomorrow';

    public function handle(): void
    {
        $tomorrow    = Carbon::tomorrow()->toDateString();
        $displayDate = Carbon::tomorrow()->format('F j, Y');

        $releases = CommissionRequestSales::whereDate('date_released', $tomorrow)
            ->where('status', 'Not Released')
            ->orderBy('agent_name')
            ->get();

        if ($releases->isEmpty()) {
            $this->info("No commission releases scheduled for {$displayDate}.");
            return;
        }

        $this->info("Found {$releases->count()} release(s) for {$displayDate}. Sending reminders...");

        $s = \DB::table('app_settings')->pluck('value', 'key');
        $smtpHost     = $s['smtp_host']     ?? config('mail.mailers.smtp.host');
        $smtpPort     = $s['smtp_port']     ?? config('mail.mailers.smtp.port');
        $smtpUser     = $s['smtp_username'] ?? config('mail.mailers.smtp.username');
        $smtpPass     = $s['smtp_password'] ?? config('mail.mailers.smtp.password');
        $smtpFromName = $s['smtp_from_name'] ?? config('app.name');

        if (empty($smtpHost) || empty($smtpUser) || empty($smtpPass)) {
            $this->error('SMTP not configured. Go to Settings > Notifications to set up email.');
            return;
        }

        config([
            'mail.mailers.smtp.host'       => $smtpHost,
            'mail.mailers.smtp.port'       => $smtpPort,
            'mail.mailers.smtp.username'   => $smtpUser,
            'mail.mailers.smtp.password'   => $smtpPass,
            'mail.mailers.smtp.encryption' => 'tls',
            'mail.from.address'            => $smtpUser,
            'mail.from.name'               => $smtpFromName,
            'mail.default'                 => 'smtp',
        ]);

        $rawEmails  = $s['notification_email'] ?? '';
        $recipients = array_filter(array_map('trim', explode(',', $rawEmails)));

        if (empty($recipients)) {
            $recipients = User::where('role', 'admin')->pluck('email')->toArray() ?: [$smtpUser];
        }

        foreach ($recipients as $email) {
            Mail::to($email)->send(new CommissionReleaseReminder($releases, $displayDate));
            $this->info("Sent to: {$email}");
        }

        $this->info('Done.');
    }
}
