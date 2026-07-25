<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentCashAdvanceRepayment extends Model
{
    protected $table = 'agent_cash_advance_repayments';

    protected $fillable = [
        'agent_cash_advance_id',
        'term_number',
        'status',
        'date_paid',
    ];

    protected $casts = [
        'term_number' => 'integer',
        'date_paid'   => 'date',
    ];

    public const STATUSES = ['PENDING', 'PAID'];

    public function agentCashAdvance()
    {
        return $this->belongsTo(AgentCashAdvance::class);
    }
}
