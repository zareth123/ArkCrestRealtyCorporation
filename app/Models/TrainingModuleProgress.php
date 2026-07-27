<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingModuleProgress extends Model
{
    use HasFactory;

    protected $table = 'training_module_progress';

    protected $fillable = [
        'user_id',
        'module_number',
        'attempts',
        'last_score',
        'best_score',
        'passed',
        'last_attempted_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
            'last_attempted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
