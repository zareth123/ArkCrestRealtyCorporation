<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArkcrestCommissionRate extends Model
{
    protected $table = 'arkcrest_commission_rates';
    protected $fillable = ['commission_request_id', 'arkcrest_percent', 'arkcrest_commission'];

    public function commissionRequest()
    {
        return $this->belongsTo(CommissionRequest::class);
    }
}
