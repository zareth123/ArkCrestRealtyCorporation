<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PersuasionMessage extends Model
{
    protected $table = 'persuasion_messages';

    protected $fillable = [
        'session_id',
        'sender',
        'message',
        'image_path',
        'turn_number',
        'is_error',
    ];

    protected $casts = [
        'turn_number' => 'integer',
        'is_error'    => 'boolean',
    ];

    protected $appends = ['image_url'];

    /** Who a message can be attributed to. */
    public const SENDERS = ['AGENT', 'BUYER'];

    public function session()
    {
        return $this->belongsTo(PersuasionSession::class, 'session_id');
    }

    /** Public URL for the attached image, if any, for the frontend to render. */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}