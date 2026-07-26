<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentalExpense extends Model
{
    use SoftDeletes;

    protected $table = 'departmental_expenses';

    protected $fillable = [
        'control_number',
        'requestor_name',
        'department',
        'release_status',
        'status',
        'category',
        'date_requested',
        'requested_amount',
        'date_released',
        'total_expenses',
        'amount_returned',
        'date_of_amount_returned',
    ];

    protected $casts = [
        'date_requested'          => 'date',
        'date_released'           => 'date',
        'date_of_amount_returned' => 'date',
        'requested_amount'        => 'decimal:2',
        'total_expenses'          => 'decimal:2',
        'amount_returned'         => 'decimal:2',
    ];

    public const RELEASE_STATUSES = [
        'NOT YET RELEASED',
        'RELEASED',
        'REJECTED',
    ];

    public const LIQUIDATION_STATUSES = [
        'NOT YET LIQUIDATED',
        'LIQUIDATED',
    ];

    // Prevents date-only casts from being converted to UTC when serialized to
    // JSON (Laravel's default). Without this, a date stored as "2026-07-25"
    // gets serialized as "2026-07-24T16:00:00.000000Z" for any timezone ahead
    // of UTC (like Asia/Manila), which the frontend then reads as one day
    // earlier — silently corrupting date_released whenever the record is
    // returned as JSON (e.g. after updating Release Status or Liquidation
    // Status).
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    /**
     * Backward-compatible alias for older call sites. The existing `status`
     * column now represents liquidation status only.
     */
    public const STATUSES = self::LIQUIDATION_STATUSES;
}