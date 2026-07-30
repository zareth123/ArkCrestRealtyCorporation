<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArkcrestCommissionRate extends Model
{
    protected $table = 'arkcrest_commission_rates';
    protected $fillable = ['commission_request_id', 'arkcrest_percent', 'arkcrest_commission'];


    protected $casts = [
        'arkcrest_percent' => 'decimal:30',
        'arkcrest_commission' => 'decimal:2',
    ];

    public function commissionRequest()
    {
        return $this->belongsTo(CommissionRequest::class);
    }
}
