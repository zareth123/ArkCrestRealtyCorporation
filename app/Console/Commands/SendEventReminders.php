<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CommissionRequestSales;
use App\Models\TripSchedule;
use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature   = 'events:send-reminders';
    protected $description = 'Send daily email reminders (day before events) to admins and note owners';

    public function handle(): void
    {
        $currentTime = now()->format('H:i');
        $isMorning = ($currentTime >= '06:00' && $currentTime <= '06:05');
        $isEvening = ($currentTime >= '17:00' && $currentTime <= '17:05');
        $isDayBeforeMorning = ($currentTime >= '08:00' && $currentTime <= '08:05');

        // For testing — force run if TEST_REMINDERS env is set
        if (app()->environment('production') && request()->server('TEST_REMINDERS')) {
            $isMorning = true;
            $isDayBeforeMorning = true;
        }

        // Get admin and sales admin emails
        $recipients = User::where(function($q) {
            $q->where('role', 'admin')
              ->orWhereRaw('LOWER(position) LIKE ?', ['%sales admin%']);
        })->where('status', 'active')
          ->whereNotNull('email')
          ->where('email', 'not like', 'pending_%')
          ->pluck('email')
          ->unique()
          ->toArray();

        if (empty($recipients)) {
            $this->error('No admin/sales admin emails found.');
            return;
        }

        // ── DAY BEFORE REMINDER (8 AM and 5 PM) ────────────────────────
        if ($isEvening || $isDayBeforeMorning) {
            $this->sendDayBeforeReminders($recipients);
        }

        // ── SAME DAY REMINDER (6 AM) ────────────────────────────────────
        if ($isMorning) {
            $this->sendSameDayReminders($recipients);
        }

        $this->info('Done.');
    }

    private function sendDayBeforeReminders(array $recipients): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();
        $displayDate = Carbon::tomorrow()->format('F j, Y');

        $events = [];

        // Commission releases tomorrow
        CommissionRequestSales::whereDate('date_released', $tomorrow)
            ->where('status', 'Not Yet Released')->get()
            ->each(fn($c) => $events[] = [
                'type'   => '💰 Commission Release Tomorrow',
                'detail' => "{$c->client_name} — {$c->project_name} | Agent: {$c->agent_name} | ₱" . number_format($c->commission ?? 0, 2),
            ]);

        // Downpayment due tomorrow (not Done)
        CommissionRequestSales::whereDate('date_of_downpayment', $tomorrow)
            ->where('client_status', '!=', 'Done')->get()
            ->each(fn($c) => $events[] = [
                'type'   => '📋 Downpayment Due Tomorrow',
                'detail' => "{$c->client_name} — {$c->project_name} | Agent: {$c->agent_name}",
            ]);

        // Site visits tomorrow
        TripSchedule::whereDate('tripping_date', $tomorrow)
            ->whereIn('status', ['confirmed', 'pending'])->get()
            ->each(function($t) use (&$events) {
                $time = $t->tripping_time ? Carbon::parse($t->tripping_time)->format('g:i A') : 'Time TBD';
                $events[] = [
                    'type'   => '🏠 Site Visit Tomorrow',
                    'detail' => "{$t->client_name} — {$t->property_name} | Agent: {$t->agent_name} | {$time}",
                ];
            });

        if (!empty($events)) {
            $subject = "ArkCrest Reminder: Events on {$displayDate}";
            $html = $this->buildEmailHtml($events, $displayDate, 'Tomorrow\'s Important Events');
            foreach ($recipients as $email) {
                try {
                    Mail::html($html, fn($m) => $m->to($email)->subject($subject));
                    $this->info("Day-before reminder sent to: {$email}");
                } catch (\Exception $e) {
                    $this->error("Failed to send to {$email}: " . $e->getMessage());
                }
            }
        } else {
            $this->info('No events for tomorrow.');
        }
    }

    private function sendSameDayReminders(array $recipients): void
    {
        $today = Carbon::today()->toDateString();
        $displayDate = Carbon::today()->format('F j, Y');

        $events = [];

        // Commission releases today
        CommissionRequestSales::whereDate('date_released', $today)
            ->where('status', 'Not Yet Released')->get()
            ->each(fn($c) => $events[] = [
                'type'   => '💰 Commission Release Today',
                'detail' => "{$c->client_name} — {$c->project_name} | Agent: {$c->agent_name} | ₱" . number_format($c->commission ?? 0, 2),
            ]);

        // Downpayment due today (not Done)
        CommissionRequestSales::whereDate('date_of_downpayment', $today)
            ->where('client_status', '!=', 'Done')->get()
            ->each(fn($c) => $events[] = [
                'type'   => '📋 Downpayment Due Today',
                'detail' => "{$c->client_name} — {$c->project_name} | Agent: {$c->agent_name}",
            ]);

        // Site visits today
        TripSchedule::whereDate('tripping_date', $today)
            ->whereIn('status', ['confirmed', 'pending'])->get()
            ->each(function($t) use (&$events) {
                $time = $t->tripping_time ? Carbon::parse($t->tripping_time)->format('g:i A') : 'Time TBD';
                $events[] = [
                    'type'   => '🏠 Site Visit Today',
                    'detail' => "{$t->client_name} — {$t->property_name} | Agent: {$t->agent_name} | {$time}",
                ];
            });

        if (!empty($events)) {
            $subject = "ArkCrest Reminder: Events TODAY — {$displayDate}";
            $html = $this->buildEmailHtml($events, $displayDate, 'Today\'s Important Events');
            foreach ($recipients as $email) {
                try {
                    Mail::html($html, fn($m) => $m->to($email)->subject($subject));
                    $this->info("Same-day reminder sent to: {$email}");
                } catch (\Exception $e) {
                    $this->error("Failed to send to {$email}: " . $e->getMessage());
                }
            }
        } else {
            $this->info('No events for today.');
        }
    }

    private function buildEmailHtml(array $events, string $date, string $intro): string
    {
        $rows = '';
        foreach ($events as $e) {
            $rows .= "<tr><td style='padding:12px 20px;border-bottom:1px solid #f1f5f9;'>
                <div style='font-size:13px;font-weight:700;color:#1e4575;margin-bottom:3px;'>{$e['type']}</div>
                <div style='font-size:12px;color:#374151;'>{$e['detail']}</div>
            </td></tr>";
        }

        return "<!DOCTYPE html><html><body style='font-family:Segoe UI,sans-serif;background:#f0f2f5;padding:24px;margin:0;'>
<div style='max-width:600px;margin:0 auto;background:white;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.1);'>
    <div style='background:linear-gradient(135deg,#1e4575,#2563eb);padding:24px 28px;'>
        <h2 style='color:white;margin:0;font-size:18px;'>ArkCrest Realty Corporation</h2>
        <p style='color:rgba(255,255,255,.75);margin:6px 0 0;font-size:13px;'>Event Reminder for {$date}</p>
    </div>
    <div style='padding:20px 0;'>
        <p style='padding:0 20px;font-size:13px;color:#64748b;margin-bottom:12px;'>{$intro}:</p>
        <table style='width:100%;border-collapse:collapse;'>{$rows}</table>
    </div>
    <div style='background:#f8fafc;padding:14px 20px;border-top:1px solid #f1f5f9;'>
        <p style='font-size:11px;color:#94a3b8;margin:0;'>Automated reminder from ArkCrest Realty System. Do not reply.</p>
    </div>
</div></body></html>";
    }
}
