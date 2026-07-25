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

    /**
     * Backward-compatible alias for older call sites. The existing `status`
     * column now represents liquidation status only.
     */
    public const STATUSES = self::LIQUIDATION_STATUSES;
}
