<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservedClient extends Model
{
    protected $table = 'reserved_clients';

    protected $fillable = [
        'trip_id', 'client_name', 'client_email', 'client_phone',
        'client_phone_code', 'address', 'source',
        'property_name', 'company_name', 'agent_name', 'tripping_date',
    ];

    protected $casts = ['tripping_date' => 'date'];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }
}
