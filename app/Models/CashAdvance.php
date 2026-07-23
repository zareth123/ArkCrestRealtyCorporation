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
        'department',
        'amount',
        'purpose',
        'reason',
        'date_requested',
        'date_needed',
        'repayment_type',
        'installment_terms',
        'repayment_date',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'amount'             => 'decimal:2',
        'date_requested'     => 'date',
        'date_needed'        => 'date',
        'repayment_date'     => 'date',
        'installment_terms'  => 'integer',
        'reviewed_at'        => 'datetime',
    ];

    /** The allowed statuses for a cash advance record. */
    public const STATUSES = ['PENDING', 'APPROVED', 'REJECTED', 'COMPLETED'];

    /** The allowed repayment types for a cash advance request. */
    public const REPAYMENT_TYPES = ['INSTALLMENT', 'OTHERS'];

    /** Maximum number of salary-deduction terms allowed for an installment repayment. */
    public const MAX_INSTALLMENT_TERMS = 6;

    /**
     * The amount deducted per term when repayment_type is INSTALLMENT.
     * Returns null when the request isn't an installment plan.
     */
    public function getAmountPerTermAttribute(): ?float
    {
        if ($this->repayment_type !== 'INSTALLMENT' || !$this->installment_terms) {
            return null;
        }

        return round(((float) $this->amount) / $this->installment_terms, 2);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withDefault(['name' => 'System']);
    }

    public function repayments()
    {
        return $this->hasMany(CashAdvanceRepayment::class)->orderBy('term_number');
    }

    /** Total number of repayment terms expected (installment terms, or 1 for Others). */
    public function getTotalTermsAttribute(): int
    {
        return $this->repayment_type === 'INSTALLMENT'
            ? (int) ($this->installment_terms ?? 0)
            : 1;
    }

    /** Number of repayment terms marked PAID so far. */
    public function getPaidTermsAttribute(): int
    {
        return $this->relationLoaded('repayments')
            ? $this->repayments->where('status', 'PAID')->count()
            : $this->repayments()->where('status', 'PAID')->count();
    }

    /** "x/y" label used in the Records table's Payment Stage column. */
    public function getPaymentStageLabelAttribute(): string
    {
        return $this->paid_terms . '/' . $this->total_terms;
    }

    /**
     * Display-only status: Pending / Rejected pass through unchanged.
     * Once Approved, the record reads as Active / Completed / Overdue
     * depending on repayment progress, without altering the stored
     * approval status (except the automatic switch to COMPLETED — see
     * CashAdvanceController::markRepaymentPaid()).
     */
    public function getDisplayStatusAttribute(): string
    {
        if (in_array($this->status, ['PENDING', 'REJECTED', 'COMPLETED'], true)) {
            return ucfirst(strtolower($this->status));
        }

        // APPROVED
        if ($this->total_terms > 0 && $this->paid_terms >= $this->total_terms) {
            return 'Completed';
        }

        if ($this->repayment_type === 'OTHERS'
            && $this->repayment_date
            && $this->repayment_date->isPast()
            && $this->paid_terms < $this->total_terms) {
            return 'Overdue';
        }

        return 'Active';
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