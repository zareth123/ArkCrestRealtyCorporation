<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersuasionMessage extends Model
{
    protected $table = 'persuasion_messages';

    protected $fillable = [
        'session_id',
        'sender',
        'message',
        'turn_number',
        'is_error',
    ];

    protected $casts = [
        'turn_number' => 'integer',
        'is_error'    => 'boolean',
    ];

    /** Who a message can be attributed to. */
    public const SENDERS = ['AGENT', 'BUYER'];

    public function session()
    {
        return $this->belongsTo(PersuasionSession::class, 'session_id');
    }
}