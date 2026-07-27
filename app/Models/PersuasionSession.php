<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersuasionSession extends Model
{
    use SoftDeletes;

    protected $table = 'persuasion_sessions';

    protected $fillable = [
        'user_id',
        'scenario_id',
        'difficulty',
        'status',
        'started_at',
        'ended_at',
        'overall_score',
        'scorecard',
    ];

    protected $casts = [
        'started_at'    => 'datetime',
        'ended_at'      => 'datetime',
        'overall_score' => 'integer',
        'scorecard'     => 'array',
    ];

    // Prevents date-only... n/a here, but keep JSON/datetime fields from
    // being converted to UTC on serialization (see DepartmentalExpense for
    // the same fix applied to a date-only field). started_at/ended_at carry
    // real time-of-day, so UTC conversion is actually correct for those —
    // no override needed here.

    /** The allowed statuses for a practice session. */
    public const STATUSES = ['IN_PROGRESS', 'SOLD', 'NOT_SOLD', 'ABANDONED'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scenario()
    {
        return $this->belongsTo(PersuasionScenario::class, 'scenario_id');
    }

    public function messages()
    {
        return $this->hasMany(PersuasionMessage::class, 'session_id')->orderBy('turn_number');
    }

    public function getIsFinishedAttribute(): bool
    {
        return $this->status !== 'IN_PROGRESS';
    }
}