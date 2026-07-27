<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersuasionScenario extends Model
{
    use SoftDeletes;

    protected $table = 'persuasion_scenarios';

    protected $fillable = [
        'name',
        'tagline',
        'difficulty',
        'buyer_name',
        'buyer_avatar',
        'buyer_backstory',
        'buyer_budget',
        'personality_traits',
        'common_objections',
        'win_conditions',
        'walkaway_triggers',
        'property_id',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'buyer_budget' => 'decimal:2',
        'is_active'    => 'boolean',
    ];

    /** The allowed difficulty levels for a scenario. */
    public const DIFFICULTIES = ['EASY', 'MEDIUM', 'HARD'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault(['name' => 'System']);
    }

    public function sessions()
    {
        return $this->hasMany(PersuasionSession::class, 'scenario_id');
    }

    /**
     * Splits a newline-separated text field (e.g. common_objections) into a
     * clean array, dropping blank lines. Used when building the AI system
     * prompt so each line becomes one bullet point of persona instruction.
     */
    public function linesOf(string $field): array
    {
        $value = $this->{$field};

        if (!$value) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}