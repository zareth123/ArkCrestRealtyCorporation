<?php

namespace App\Services;

use App\Models\CommissionRequest;
use App\Models\CommissionRequestSales;
use App\Models\DownpaymentInstallment;
use Illuminate\Support\Collection;

class CommissionStageService
{
    public function getPlanTerms(CommissionRequestSales $record): int
    {
        $terms = (int) ($record->downpayment_terms ?? 0);

        if ($terms <= 0 && preg_match('/\((\d+)\s*MOS?\)/i', (string) $record->terms_of_payment, $matches)) {
            $terms = (int) $matches[1];
        }

        return max(1, $terms);
    }

    public function getStageTotal(CommissionRequestSales $record): int
    {
        $terms = $this->getPlanTerms($record);

        if ($terms === 2) {
            return 2;
        }

        if ($terms >= 3) {
            return 3;
        }

        return 1;
    }

    public function getTotalDownpayment(CommissionRequestSales $record): float
    {
        $label = strtoupper(trim((string) $record->terms_of_payment));
        $tcp = max(0, (float) ($record->tcp ?? 0));

        if ($label !== '' && str_contains($label, 'STRAIGHT PAYMENT')) {
            return round($tcp, 2);
        }

        if ($label !== '' && preg_match('/(\d+(?:\.\d+)?)\s*%\s*DP/i', $label, $matches)) {
            return round($tcp * ((float) $matches[1] / 100), 2);
        }

        return round(max(0, (float) ($record->downpayment_amount ?? 0)), 2);
    }

    public function getPaidTotal(CommissionRequestSales $record, ?float $totalDownpayment = null): float
    {
        $totalDownpayment ??= $this->getTotalDownpayment($record);

        $installments = DownpaymentInstallment::where(
            'commission_request_sales_id',
            $record->id
        )->get(['amount', 'is_paid']);

        $paidTotal = round((float) $installments
            ->where('is_paid', true)
            ->sum(fn ($installment) => (float) ($installment->amount ?? 0)), 2);

        // Supports spot/full payments and legacy records without installment rows.
        if ($paidTotal <= 0
            && in_array($record->downpayment_status, ['Spot Paid', 'Paid'], true)) {
            return round($totalDownpayment, 2);
        }

        return min(round($paidTotal, 2), round($totalDownpayment, 2));
    }

    public function getPaymentStage(float $paidTotal, float $totalDownpayment, int $stageTotal): int
    {
        if ($totalDownpayment <= 0 || $paidTotal <= 0 || $stageTotal <= 0) {
            return 0;
        }

        $stage = 0;

        for ($current = 1; $current <= $stageTotal; $current++) {
            $threshold = round($totalDownpayment * ($current / $stageTotal), 2);

            if ($paidTotal + 0.01 >= $threshold) {
                $stage = $current;
            }
        }

        return min($stage, $stageTotal);
    }

    /**
     * Commission request rows are the source of truth for requested stages.
     *
     * Soft-deleted requests are intentionally included because a stage that was
     * already filed must not be filed a second time while the deleted request is
     * still recoverable by an administrator.
     */
    public function getStageRequests(CommissionRequestSales $record): Collection
    {
        return CommissionRequest::withTrashed()
            ->where('source_client_record_id', $record->id)
            ->whereNotNull('commission_stage')
            ->orderByDesc('id')
            ->get([
                'id',
                'commission_stage',
                'commission_stage_total',
                'stage_threshold_amount',
                'status',
                'date_requested',
                'deleted_at',
            ])
            ->unique(fn (CommissionRequest $request) => (int) $request->commission_stage)
            ->keyBy(fn (CommissionRequest $request) => (int) $request->commission_stage);
    }

    public function getFiledStages(CommissionRequestSales $record): array
    {
        return $this->getStageRequests($record)
            ->keys()
            ->map(fn ($stage) => (int) $stage)
            ->sort()
            ->values()
            ->all();
    }

    public function getNextPendingStage(int $stageTotal, array $filedStages): ?int
    {
        for ($stage = 1; $stage <= $stageTotal; $stage++) {
            if (!in_array($stage, $filedStages, true)) {
                return $stage;
            }
        }

        return null;
    }

    public function summarize(CommissionRequestSales $record): array
    {
        $stageTotal = $this->getStageTotal($record);
        $totalDownpayment = $this->getTotalDownpayment($record);
        $paidTotal = $this->getPaidTotal($record, $totalDownpayment);
        $remainingBalance = max(0, round($totalDownpayment - $paidTotal, 2));

        $paymentStage = $this->getPaymentStage(
            $paidTotal,
            $totalDownpayment,
            $stageTotal
        );

        $stageRequests = $this->getStageRequests($record);
        $filedStages = $stageRequests
            ->keys()
            ->map(fn ($stage) => (int) $stage)
            ->sort()
            ->values()
            ->all();

        // The first stage without a commission_requests row is always the next
        // stage in sequence, even when its payment threshold is not reached yet.
        $nextPendingStage = $this->getNextPendingStage($stageTotal, $filedStages);
        $commissionReady = $nextPendingStage !== null
            && $paymentStage >= $nextPendingStage;
        $nextRequestableStage = $commissionReady ? $nextPendingStage : null;
        $nextThresholdAmount = $nextPendingStage === null
            ? null
            : round($totalDownpayment * ($nextPendingStage / $stageTotal), 2);

        $commissionStages = [];

        for ($stage = 1; $stage <= $stageTotal; $stage++) {
            /** @var CommissionRequest|null $request */
            $request = $stageRequests->get($stage);
            $threshold = round($totalDownpayment * ($stage / $stageTotal), 2);
            $isEligible = $paymentStage >= $stage;
            $isRequested = $request !== null;

            if ($isRequested) {
                $status = 'requested';
                $statusLabel = 'Requested';
            } elseif ($stage === $nextPendingStage && $isEligible) {
                $status = 'ready';
                $statusLabel = 'Ready to request';
            } elseif ($isEligible) {
                $status = 'eligible_waiting';
                $statusLabel = 'Eligible after previous stage';
            } else {
                $status = 'waiting_payment';
                $statusLabel = 'Waiting for payment';
            }

            $commissionStages[] = [
                'stage' => $stage,
                'total' => $stageTotal,
                'label' => $stage . '/' . $stageTotal,
                'threshold_amount' => $threshold,
                'is_eligible' => $isEligible,
                'is_requested' => $isRequested,
                'status' => $status,
                'status_label' => $statusLabel,
                'request_id' => $request?->id,
                'request_status' => $request?->status,
                'date_requested' => $request?->date_requested?->format('Y-m-d'),
                'is_deleted' => $request?->trashed() ?? false,
            ];
        }

        return [
            'total_downpayment' => $totalDownpayment,
            'paid_total' => $paidTotal,
            'remaining_balance' => $remainingBalance,
            'downpayment_stage' => $paymentStage,
            'downpayment_stage_total' => $stageTotal,
            'stage_display' => $paymentStage . '/' . $stageTotal,
            'filed_stages' => $filedStages,
            'requested_stages' => $filedStages,
            'commission_stages' => $commissionStages,
            'next_commission_stage' => $nextPendingStage,
            'next_requestable_stage' => $nextRequestableStage,
            'next_threshold_amount' => $nextThresholdAmount,
            'threshold_amount' => $nextThresholdAmount,
            'threshold_basis' => $nextPendingStage === null
                ? 'All commission stages have already been requested'
                : $nextPendingStage . '/' . $stageTotal . ' of total downpayment',
            'commission_ready' => $commissionReady,
            'all_commission_stages_requested' => $nextPendingStage === null,
        ];
    }

    public function getSourceCommissionStatus(
        CommissionRequestSales $record,
        ?string $preferredRequestStatus = null
    ): string {
        $summary = $this->summarize($record);

        if ($summary['commission_ready']) {
            return 'For Request';
        }

        if (in_array($preferredRequestStatus, ['Released', 'Not Released'], true)) {
            return $preferredRequestStatus;
        }

        $latestRequestStatus = CommissionRequest::where(
            'source_client_record_id',
            $record->id
        )
            ->orderByDesc('commission_stage')
            ->orderByDesc('id')
            ->value('status');

        return in_array($latestRequestStatus, ['Released', 'Not Released'], true)
            ? $latestRequestStatus
            : 'Not Released';
    }
}
