<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Note extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'note_date', 'reminder_time', 'reminder_sent', 'completed_at'];

    protected $casts = [
        'note_date'     => 'date',
        'reminder_sent' => 'boolean',
        'completed_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Returns full datetime of reminder
    public function getReminderAtAttribute(): ?Carbon
    {
        if (!$this->note_date || !$this->reminder_time) return null;
        return Carbon::parse($this->note_date->format('Y-m-d') . ' ' . $this->reminder_time);
    }

    // Is this note due for reminder right now (within the last 5 minutes, not yet sent)?
    public function isDueNow(): bool
    {
        $at = $this->reminder_at;
        if (!$at || $this->reminder_sent) return false;
        return now()->greaterThanOrEqualTo($at) && now()->lessThanOrEqualTo($at->copy()->addMinutes(5));
    }
}
