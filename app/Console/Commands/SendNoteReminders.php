<?php

namespace App\Console\Commands;

use App\Mail\NoteReminder;
use App\Models\Note;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendNoteReminders extends Command
{
    protected $signature   = 'notes:send-reminders';
    protected $description = 'Send email and in-app reminders for due notes';

    public function handle(): void
    {
        // 1. Send reminders for notes due now OR overdue today (not yet sent)
        $due = Note::with('user')
            ->whereNotNull('note_date')
            ->whereNotNull('reminder_time')
            ->where('reminder_sent', false)
            ->whereNull('completed_at')
            ->whereDate('note_date', '<=', now()->toDateString())
            ->get()
            ->filter(fn($note) => $note->reminder_at && now()->greaterThanOrEqualTo($note->reminder_at));

        foreach ($due as $note) {
            try {
                Mail::to($note->user->email)->send(new NoteReminder($note));
                \App\Models\SystemNotification::notify(
                    $note->user_id,
                    'note_reminder',
                    'Note Reminder: ' . $note->title,
                    ($note->body ? \Illuminate\Support\Str::limit($note->body, 80) : 'You have a scheduled note.') .
                    ($note->reminder_time ? ' — ' . \Carbon\Carbon::parse($note->reminder_time)->format('g:i A') : '')
                );
                $note->update(['reminder_sent' => true]);
                $this->info("Sent reminder for note #{$note->id}: {$note->title}");
            } catch (\Exception $e) {
                $this->error("Failed for note #{$note->id}: " . $e->getMessage());
            }
        }

        // 2. Send reminders for notes due tomorrow — at 6AM and 5PM
        $currentTime = now()->format('H:i');
        $isMorning = ($currentTime >= '06:00' && $currentTime <= '06:01');
        $isEvening = ($currentTime >= '17:00' && $currentTime <= '17:01');

        if ($isMorning || $isEvening) {
            $tomorrow = now()->addDay()->format('Y-m-d');
            $dayBefore = Note::with('user')
                ->whereNotNull('note_date')
                ->whereNull('completed_at')
                ->whereDate('note_date', $tomorrow)
                ->get();

            foreach ($dayBefore as $note) {
                try {
                    Mail::to($note->user->email)->send(new NoteReminder($note, true));
                    \App\Models\SystemNotification::notify(
                        $note->user_id,
                        'note_reminder',
                        'Upcoming Note Tomorrow: ' . $note->title,
                        'You have a note scheduled tomorrow' .
                        ($note->reminder_time ? ' at ' . \Carbon\Carbon::parse($note->reminder_time)->format('g:i A') : '') . '.'
                    );
                    $this->info("Sent tomorrow reminder for note #{$note->id}");
                } catch (\Exception $e) {
                    $this->error("Failed tomorrow reminder for note #{$note->id}: " . $e->getMessage());
                }
            }
        }

        // 3. Send reminders for notes due today — at 6AM only
        if ($isMorning) {
            $today = now()->format('Y-m-d');
            $todayNotes = Note::with('user')
                ->whereNotNull('note_date')
                ->whereNull('completed_at')
                ->whereDate('note_date', $today)
                ->get();

            foreach ($todayNotes as $note) {
                try {
                    Mail::to($note->user->email)->send(new NoteReminder($note, false, true));
                    \App\Models\SystemNotification::notify(
                        $note->user_id,
                        'note_reminder',
                        'Note Today: ' . $note->title,
                        'You have a note scheduled today' .
                        ($note->reminder_time ? ' at ' . \Carbon\Carbon::parse($note->reminder_time)->format('g:i A') : '') . '.'
                    );
                    $this->info("Sent today reminder for note #{$note->id}");
                } catch (\Exception $e) {
                    $this->error("Failed today reminder for note #{$note->id}: " . $e->getMessage());
                }
            }
        }

        $this->info("Done.");
    }
}
