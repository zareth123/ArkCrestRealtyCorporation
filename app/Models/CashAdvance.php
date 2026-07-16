<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashAdvance extends Model
{
    use SoftDeletes;

    protected $table = 'cash_advances';

    protected $fillable = [
        'control_number',
        'employee_id',
        'employee_name',
        'amount',
        'reason',
        'repayment_date',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'repayment_date' => 'date',
        'reviewed_at'    => 'datetime',
    ];

    /** The allowed statuses for a cash advance record. */
    public const STATUSES = ['PENDING', 'APPROVED', 'REJECTED'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withDefault(['name' => 'System']);
    }

    /**
     * Generate the next sequential control number, e.g. CA-1001, CA-1002...
     * Looks at trashed records too so a deleted record's number is never reused.
     */
    public static function nextControlNumber(): string
    {
        $last = static::withTrashed()
            ->selectRaw('MAX(CAST(SUBSTRING(control_number, 4) AS UNSIGNED)) as max_num')
            ->value('max_num');

        $next = $last ? ((int) $last + 1) : 1001;

        return 'CA-' . $next;
    }
}