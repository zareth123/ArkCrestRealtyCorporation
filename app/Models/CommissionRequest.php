<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommissionRequest extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'control_number',
        'requestor_name',
        'department',
        'category',
        'date_requested',
        'requested_amount',
        'status',
        'date_released',
        'total_expenses',
        'amount_returned',
        'date_of_amount_returned',
        // Commission fields
        'project_name',
        'property_details',
        'client_name',
        'terms_of_payment',
        'agent_name',
        'number_of_units',
        'net_tcp',
        'commission',
        'commission_percent',
        'mode_of_payment',
        'reservation_date',
        'price_sqm',
        'lot_area',
        'discount',
        'remarks',
        'status',
        'payment_type',
        'value_of_payment_terms',
        'source_client_record_id',
        'commission_stage',
        'commission_stage_total',
        'stage_threshold_amount',
    ];

    public function sourceClientRecord()
    {
        return $this->belongsTo(CommissionRequestSales::class, 'source_client_record_id');
    }

    public function stageRequest()
    {
        return $this->hasOne(CommissionStageRequest::class, 'commission_request_id');
    }

    protected $casts = [
        'date_requested' => 'date',
        'date_released' => 'date',
        'date_of_amount_returned' => 'date',
        'reservation_date' => 'date',
        'requested_amount' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'amount_returned' => 'decimal:2',
        'price_sqm' => 'decimal:2',
        'lot_area' => 'decimal:4',
        'discount' => 'decimal:2',
        'net_tcp' => 'decimal:2',
        'commission' => 'decimal:2',
        'commission_percent' => 'decimal:4',
        'source_client_record_id' => 'integer',
        'commission_stage' => 'integer',
        'commission_stage_total' => 'integer',
        'stage_threshold_amount' => 'decimal:2',
    ];

    // Prevents date-only casts from being converted to UTC when serialized to
    // JSON (Laravel's default). Without this, a date stored as "2026-07-05"
    // gets serialized as "2026-07-04T16:00:00.000000Z" for any timezone ahead
    // of UTC (like Asia/Manila), which the frontend then reads as one day
    // earlier — silently corrupting the date if the Edit form is saved as-is.
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }
}
