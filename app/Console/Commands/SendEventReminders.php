<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CommissionRequest;
use App\Models\CommissionRequestSales;
use App\Models\TripSchedule;
use Carbon\Carbon;

class SendEventReminders extends Command
{
    protected $signature   = 'events:send-reminders {--trigger=day_before : day_before or same_day}';
    protected $description = 'Send email reminders for upcoming events (commission releases, downpayments, site visits)';

    public function handle(): void
    {
        $trigger = $this->option('trigger');

        if ($trigger === 'same_day') {
            $this->sendSameDayReminders();
        } else {
            $this->sendDayBeforeReminders();
        }

        $this->info('Done.');
    }

    private function sendDayBeforeReminders(): void
    {
        $tomorrow    = Carbon::tomorrow()->toDateString();
        $displayDate = Carbon::tomorrow()->format('F j, Y');
        $events      = $this->collectEvents($tomorrow, 'tomorrow');

        if (!empty($events)) {
            $rows = implode('', array_map(fn($e) => "<b>{$e['type']}</b><br>{$e['detail']}<br><br>", $events));
            $body = $rows;
        } else {
            $body = "<i style='color:#94a3b8;'>No scheduled events for tomorrow.</i>";
        }

        \App\Services\AdminEmailNotifier::send(
            "ArkCrest Reminder: Events on {$displayDate}",
            "Tomorrow's Events — {$displayDate}",
            $body
        );
        $this->info("Day-before reminder sent for {$displayDate}.");
    }

    private function sendSameDayReminders(): void
    {
        $today       = Carbon::today()->toDateString();
        $displayDate = Carbon::today()->format('F j, Y');
        $events      = $this->collectEvents($today, 'today');

        if (!empty($events)) {
            $rows = implode('', array_map(fn($e) => "<b>{$e['type']}</b><br>{$e['detail']}<br><br>", $events));
            $body = $rows;
        } else {
            $body = "<i style='color:#94a3b8;'>No scheduled events for today.</i>";
        }

        \App\Services\AdminEmailNotifier::send(
            "ArkCrest Reminder: Events TODAY — {$displayDate}",
            "Today's Events — {$displayDate}",
            $body
        );
        $this->info("Same-day reminder sent for {$displayDate}.");
    }

    private function collectEvents(string $date, string $when): array
    {
        $label   = $when === 'today' ? 'Today' : 'Tomorrow';
        $events  = [];

        // Commission releases (Client Database)
        CommissionRequestSales::whereDate('date_released', $date)
            ->where('status', 'Not Yet Released')->get()
            ->each(fn($c) => $events[] = [
                'type'   => "💰 Commission Release {$label}",
                'detail' => "{$c->client_name} — {$c->project_name} | Agent: {$c->agent_name} | ₱" . number_format($c->commission ?? 0, 2),
            ]);

        // Commission releases (Commission Monitoring)
        CommissionRequest::whereDate('date_released', $date)
            ->where('status', 'Not Yet Released')->get()
            ->each(fn($c) => $events[] = [
                'type'   => "💰 Commission Release {$label}",
                'detail' => ($c->client_name ?? '—') . ' — ' . ($c->project_name ?? '—') . ' | Agent: ' . ($c->agent_name ?? '—') . ' | ₱' . number_format($c->commission ?? 0, 2),
            ]);

        // Downpayments due
        CommissionRequestSales::whereDate('date_of_downpayment', $date)
            ->where('client_status', '!=', 'Done')->get()
            ->each(fn($c) => $events[] = [
                'type'   => "📋 Downpayment Due {$label}",
                'detail' => "{$c->client_name} — {$c->project_name} | Agent: {$c->agent_name}",
            ]);

        // Site visits / trippings
        TripSchedule::whereDate('tripping_date', $date)
            ->whereIn('status', ['confirmed', 'pending'])->get()
            ->each(function($t) use (&$events, $label) {
                $time = $t->tripping_time ? \Carbon\Carbon::parse($t->tripping_time)->format('g:i A') : 'Time TBD';
                $events[] = [
                    'type'   => "🏠 Site Visit {$label}",
                    'detail' => "{$t->client_name} — {$t->property_name} | Agent: {$t->agent_name} | {$time}",
                ];
            });

        return $events;
    }
}
