<?php

namespace App\Services;

use App\Models\CommissionRequest;
use App\Models\CommissionRequestSales;
use App\Models\CommissionStageRequest;
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
        $netTcp = max(0, (float) ($record->net_tcp ?? 0));

        if ($label !== '' && str_contains($label, 'STRAIGHT PAYMENT')) {
            return round($netTcp, 2);
        }

        if ($label !== '' && preg_match('/(\d+(?:\.\d+)?)\s*%\s*DP/i', $label, $matches)) {
            return round($netTcp * ((float) $matches[1] / 100), 2);
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
     * Official records entered by Finance. Soft-deleted rows remain filed so a
     * stage cannot be submitted twice while that record is recoverable.
     */
    public function getOfficialStageRequests(CommissionRequestSales $record): Collection
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

    /**
     * Pending requests sent by Sales. These exist before Finance creates the
     * official commission_requests record.
     */
    public function getPendingStageRequests(CommissionRequestSales $record): Collection
    {
        return CommissionStageRequest::where('source_client_record_id', $record->id)
            ->orderByDesc('id')
            ->get()
            ->unique(fn (CommissionStageRequest $request) => (int) $request->commission_stage)
            ->keyBy(fn (CommissionStageRequest $request) => (int) $request->commission_stage);
    }

    public function getFiledStages(CommissionRequestSales $record): array
    {
        return $this->getOfficialStageRequests($record)
            ->keys()
            ->merge($this->getPendingStageRequests($record)->keys())
            ->map(fn ($stage) => (int) $stage)
            ->unique()
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

    private function normalizeStatus(?string $status, string $fallback = 'Requested'): string
    {
        if ($status === 'Not Released') {
            return 'Not Yet Released';
        }

        return in_array($status, ['Requested', 'Not Yet Released', 'Released'], true)
            ? $status
            : $fallback;
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

        $officialRequests = $this->getOfficialStageRequests($record);
        $pendingRequests = $this->getPendingStageRequests($record);

        $filedStages = $officialRequests
            ->keys()
            ->merge($pendingRequests->keys())
            ->map(fn ($stage) => (int) $stage)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $nextPendingStage = $this->getNextPendingStage($stageTotal, $filedStages);
        $commissionReady = $nextPendingStage !== null
            && $paymentStage >= $nextPendingStage;
        $nextRequestableStage = $commissionReady ? $nextPendingStage : null;
        $nextThresholdAmount = $nextPendingStage === null
            ? null
            : round($totalDownpayment * ($nextPendingStage / $stageTotal), 2);

        $commissionStages = [];

        for ($stage = 1; $stage <= $stageTotal; $stage++) {
            /** @var CommissionRequest|null $officialRequest */
            $officialRequest = $officialRequests->get($stage);
            /** @var CommissionStageRequest|null $pendingRequest */
            $pendingRequest = $pendingRequests->get($stage);

            $threshold = round($totalDownpayment * ($stage / $stageTotal), 2);
            $isEligible = $paymentStage >= $stage;
            $isRequested = $officialRequest !== null || $pendingRequest !== null;

            if ($officialRequest) {
                $requestStatus = $this->normalizeStatus($officialRequest->status, 'Not Yet Released');
            } elseif ($pendingRequest) {
                // Until Finance submits the Add form, Sales must continue seeing
                // this exact stage as Requested.
                $requestStatus = 'Requested';
            } else {
                $requestStatus = null;
            }

            if ($requestStatus === 'Released') {
                $status = 'released';
                $statusLabel = 'Released';
            } elseif ($requestStatus === 'Not Yet Released') {
                $status = 'not_yet_released';
                $statusLabel = 'Not Yet Released';
            } elseif ($requestStatus === 'Requested') {
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

            $requestedDate = $pendingRequest?->requested_at?->format('Y-m-d')
                ?? $officialRequest?->date_requested?->format('Y-m-d');

            $commissionStages[] = [
                'stage' => $stage,
                'total' => $stageTotal,
                'label' => $stage . '/' . $stageTotal,
                'threshold_amount' => $threshold,
                'is_eligible' => $isEligible,
                'is_requested' => $isRequested,
                'status' => $status,
                'status_label' => $statusLabel,
                'request_id' => $officialRequest?->id,
                'stage_request_id' => $pendingRequest?->id,
                'request_status' => $requestStatus,
                'date_requested' => $requestedDate,
                'is_deleted' => $officialRequest?->trashed() ?? false,
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
        $preferredRequestStatus = $this->normalizeStatus($preferredRequestStatus, '');

        if (!empty($summary['filed_stages'])) {
            for ($index = count($summary['commission_stages']) - 1; $index >= 0; $index--) {
                $stage = $summary['commission_stages'][$index];
                if ($stage['is_requested']) {
                    return $stage['status_label'];
                }
            }
        }

        if ($summary['commission_ready']) {
            return 'For Request';
        }

        if (in_array($preferredRequestStatus, ['Requested', 'Not Yet Released', 'Released'], true)) {
            return $preferredRequestStatus;
        }

        $currentStatus = $record->status === 'Not Released'
            ? 'Not Yet Released'
            : $record->status;

        return in_array($currentStatus, ['Requested', 'Not Yet Released', 'Released'], true)
            ? $currentStatus
            : 'Not Yet Released';
    }
}
