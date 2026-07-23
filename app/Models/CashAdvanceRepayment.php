<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAdvanceRepayment extends Model
{
    protected $table = 'cash_advance_repayments';

    protected $fillable = [
        'cash_advance_id',
        'term_number',
        'status',
        'date_paid',
    ];

    protected $casts = [
        'term_number' => 'integer',
        'date_paid'   => 'date',
    ];

    public const STATUSES = ['PENDING', 'PAID'];

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }
}
