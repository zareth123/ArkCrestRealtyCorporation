<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CommissionRequest;
use App\Models\CommissionRequestSales;
use App\Models\TripSchedule;
use App\Models\SystemNotification;
use App\Services\AdminEmailNotifier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature   = 'events:send-reminders {--trigger=day_before : day_before or same_day}';
    protected $description = 'Send email + in-app reminders for upcoming events';

    public function handle(): void
    {
        $isToday     = $this->option('trigger') === 'same_day';
        $date        = $isToday ? Carbon::today()->toDateString()   : Carbon::tomorrow()->toDateString();
        $displayDate = $isToday ? Carbon::today()->format('F j, Y') : Carbon::tomorrow()->format('F j, Y');
        $when        = $isToday ? 'Today'                           : 'Tomorrow';

        $this->handleCommissionReleases($date, $displayDate, $when);
        $this->handleDownpayments($date, $displayDate, $when);
        $this->handleTrippings($date, $displayDate, $when);

        $this->info('Done.');
    }

    private function handleCommissionReleases(string $date, string $displayDate, string $when): void
    {
        $releases = collect();
        CommissionRequestSales::whereDate('date_released', $date)
            ->where('status', 'Not Released')->get()->each(fn($r) => $releases->push($r));
        CommissionRequest::whereDate('date_released', $date)
            ->where('status', 'Not Released')->get()->each(fn($r) => $releases->push($r));

        if ($releases->isEmpty()) return;

        $admins = $this->getAdminUsers();
        if ($admins->isEmpty()) return;

        $rows = $releases->map(fn($r) =>
            "<b>💰 Commission Release {$when}</b><br>" .
            ($r->client_name ?? '—') . " — " . ($r->project_name ?? '—') .
            " | Agent: " . ($r->agent_name ?? '—') .
            " | ₱" . number_format($r->commission ?? 0, 2) . "<br><br>"
        )->implode('');

        $subject = "ArkCrest: Commission Release {$when} — {$displayDate}";
        $title   = "Commission Release {$when} — {$displayDate}";
        $notifMsg = $releases->count() . " commission release(s) scheduled for {$when}.";

        foreach ($admins as $admin) {
            $this->sendEmail([$admin->email], $subject, $title, $rows, $admin->name);
            SystemNotification::notify($admin->id, 'commission_reminder', "💰 Commission Release {$when}", $notifMsg);
        }

        $this->info("Commission release reminder sent ({$releases->count()} record/s).");
    }

    private function handleDownpayments(string $date, string $displayDate, string $when): void
    {
        $downpayments = CommissionRequestSales::whereDate('date_of_downpayment', $date)
            ->where('client_status', '!=', 'Done')->get();

        if ($downpayments->isEmpty()) return;

        $recipients = $this->getAdminAndSalesAdminUsers();
        if ($recipients->isEmpty()) return;

        $rows = $downpayments->map(fn($d) =>
            "<b>📋 Downpayment Due {$when}</b><br>" .
            "{$d->client_name} — {$d->project_name} | Agent: {$d->agent_name}<br><br>"
        )->implode('');

        $subject  = "ArkCrest: Downpayment Due {$when} — {$displayDate}";
        $title    = "Downpayment Due {$when} — {$displayDate}";
        $notifMsg = $downpayments->count() . " downpayment(s) due {$when}.";

        foreach ($recipients as $user) {
            $this->sendEmail([$user->email], $subject, $title, $rows, $user->name);
            SystemNotification::notify($user->id, 'downpayment_reminder', "📋 Downpayment Due {$when}", $notifMsg);
        }

        $this->info("Downpayment reminder sent ({$downpayments->count()} record/s).");
    }

    private function handleTrippings(string $date, string $displayDate, string $when): void
    {
        $trips = TripSchedule::whereDate('tripping_date', $date)
            ->whereIn('status', ['confirmed', 'pending'])->get();

        if ($trips->isEmpty()) return;

        $adminSalesUsers = $this->getAdminAndSalesAdminUsers();
        $adminSalesIds   = $adminSalesUsers->pluck('id')->toArray();

        $adminRows = $trips->map(function($t) use ($when) {
            $time = $t->tripping_time ? Carbon::parse($t->tripping_time)->format('g:i A') : 'Time TBD';
            return "<b>🏠 Site Visit {$when}</b><br>" .
                "{$t->client_name} — {$t->property_name} | Agent: {$t->agent_name} | {$time}<br><br>";
        })->implode('');

        $notifMsg = $trips->count() . " site visit(s) scheduled for {$when}.";

        foreach ($adminSalesUsers as $user) {
            $this->sendEmail([$user->email], "ArkCrest: Site Visit {$when} — {$displayDate}", "Site Visit {$when} — {$displayDate}", $adminRows, $user->name);
            SystemNotification::notify($user->id, 'tripping_reminder', "🏠 Site Visit {$when}", $notifMsg);
        }

        $tripsByAgent = $trips->groupBy('agent_name');
        foreach ($tripsByAgent as $agentName => $agentTrips) {
            $agentUser = User::where('name', $agentName)
                ->where('status', 'active')
                ->whereNotNull('email')
                ->where('email', 'not like', 'pending_%')
                ->first();

            if (!$agentUser || in_array($agentUser->id, $adminSalesIds)) continue;

            $agentRows = $agentTrips->map(function($t) use ($when) {
                $time = $t->tripping_time ? Carbon::parse($t->tripping_time)->format('g:i A') : 'Time TBD';
                return "<b>🏠 Your Site Visit {$when}</b><br>" .
                    "{$t->client_name} — {$t->property_name} | {$time}<br><br>";
            })->implode('');

            $agentNotifMsg = $agentTrips->count() . " site visit(s) assigned to you for {$when}.";
            $this->sendEmail([$agentUser->email], "ArkCrest: Your Site Visit {$when} — {$displayDate}", "Your Site Visit {$when} — {$displayDate}", $agentRows, $agentUser->name);
            SystemNotification::notify($agentUser->id, 'tripping_reminder', "🏠 Your Site Visit {$when}", $agentNotifMsg);
        }

        $this->info("Site visit reminder sent ({$trips->count()} trip/s).");
    }

    private function getAdminUsers()
    {
        return User::where('role', 'admin')
            ->where('status', 'active')
            ->whereNotNull('email')
            ->where('email', 'not like', 'pending_%')
            ->get();
    }

    private function getAdminAndSalesAdminUsers()
    {
        return User::where(function($q) {
                $q->where('role', 'admin')
                  ->orWhereRaw("LOWER(position) LIKE '%admin sales%'")
                  ->orWhereRaw("LOWER(position) LIKE '%sales admin%'");
            })
            ->where('status', 'active')
            ->whereNotNull('email')
            ->where('email', 'not like', 'pending_%')
            ->get()
            ->unique('id');
    }

    private function sendEmail(array $emails, string $subject, string $title, string $body, string $recipientName = ''): void
    {
        if (empty($emails)) return;

        $s            = \DB::table('app_settings')->pluck('value', 'key');
        $smtpHost     = $s['smtp_host']     ?? config('mail.mailers.smtp.host');
        $smtpPort     = $s['smtp_port']     ?? config('mail.mailers.smtp.port', '587');
        $smtpUser     = $s['smtp_username'] ?? config('mail.from.address');
        $smtpPass     = $s['smtp_password'] ?? config('mail.mailers.smtp.password');
        $smtpFromName = $s['smtp_from_name'] ?? config('app.name');

        if (empty($smtpHost) || empty($smtpUser) || empty($smtpPass)) {
            $this->error('SMTP not configured.');
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

        $html = AdminEmailNotifier::buildPublicHtml($title, $body, $recipientName);

        foreach ($emails as $email) {
            try {
                Mail::html($html, fn($msg) => $msg->to($email)->subject($subject)->from($smtpUser, $smtpFromName));
                $this->info("  → Sent to {$email}");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed {$email}: " . $e->getMessage());
            }
        }
    }
}
