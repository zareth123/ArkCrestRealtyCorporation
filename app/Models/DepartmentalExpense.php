<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentalExpense extends Model
{
    use SoftDeletes;

    protected $table = 'departmental_expenses';

    protected $fillable = [
        'control_number', 'requestor_name', 'department', 'category',
        'date_requested', 'requested_amount', 'status', 'date_released',
        'total_expenses', 'amount_returned', 'date_of_amount_returned',
    ];

    protected $casts = [
        'date_requested'          => 'date',
        'date_released'           => 'date',
        'date_of_amount_returned' => 'date',
        'requested_amount'        => 'decimal:2',
        'total_expenses'          => 'decimal:2',
        'amount_returned'         => 'decimal:2',
    ];

    /** The allowed statuses for a departmental expense / budget request record. */
    public const STATUSES = ['PENDING', 'NOT LIQUIDATED', 'LIQUIDATED', 'REJECTED'];
}
