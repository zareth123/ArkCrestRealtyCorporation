<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyInquiry extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'property_interest',
        'message',
        'source',
        'email_sent',
        'ip_address',
    ];

    protected $casts = [
        'email_sent' => 'boolean',
    ];
}