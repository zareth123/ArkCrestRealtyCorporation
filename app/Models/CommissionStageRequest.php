<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionStageRequest extends Model
{
    protected $fillable = [
        'source_client_record_id',
        'commission_request_id',
        'commission_stage',
        'commission_stage_total',
        'stage_threshold_amount',
        'requested_by_user_id',
        'requested_by_name',
        'requested_at',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'source_client_record_id' => 'integer',
        'commission_request_id' => 'integer',
        'commission_stage' => 'integer',
        'commission_stage_total' => 'integer',
        'stage_threshold_amount' => 'decimal:2',
        'requested_by_user_id' => 'integer',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function sourceClientRecord()
    {
        return $this->belongsTo(CommissionRequestSales::class, 'source_client_record_id');
    }

    public function commissionRequest()
    {
        return $this->belongsTo(CommissionRequest::class, 'commission_request_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
