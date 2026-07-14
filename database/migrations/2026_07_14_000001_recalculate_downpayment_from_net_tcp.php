<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commission_requests_sales')) {
            return;
        }

        DB::table('commission_requests_sales')
            ->orderBy('id')
            ->chunkById(200, function ($records) {
                foreach ($records as $record) {
                    $label = (string) ($record->terms_of_payment ?? '');
                    $upperLabel = strtoupper(trim($label));
                    $netTcp = max(0, (float) ($record->net_tcp ?? 0));
                    $totalDownpayment = max(0, (float) ($record->downpayment_amount ?? 0));

                    if (str_contains($upperLabel, 'STRAIGHT PAYMENT')) {
                        $totalDownpayment = $netTcp;
                    } elseif (preg_match('/(\d+(?:\.\d+)?)\s*%\s*DP/i', $label, $matches)) {
                        $totalDownpayment = round($netTcp * ((float) $matches[1] / 100), 2);
                    }

                    $terms = (int) ($record->downpayment_terms ?? 0);
                    if ($terms <= 0 && preg_match('/\((\d+)\s*MOS?\)/i', $label, $matches)) {
                        $terms = (int) $matches[1];
                    }

                    $stageTotal = $terms === 2 ? 2 : ($terms >= 3 ? 3 : 1);
                    $paidTotal = Schema::hasTable('downpayment_installments')
                        ? (float) DB::table('downpayment_installments')
                            ->where('commission_request_sales_id', $record->id)
                            ->where('is_paid', true)
                            ->sum('amount')
                        : 0;

                    if ($paidTotal <= 0
                        && in_array($record->downpayment_status ?? null, ['Paid', 'Spot Paid'], true)) {
                        $paidTotal = $totalDownpayment;
                    }

                    $stage = 0;
                    if ($totalDownpayment > 0) {
                        for ($current = 1; $current <= $stageTotal; $current++) {
                            $threshold = round($totalDownpayment * ($current / $stageTotal), 2);
                            if ($paidTotal + 0.01 >= $threshold) {
                                $stage = $current;
                            }
                        }
                    }

                    DB::table('commission_requests_sales')
                        ->where('id', $record->id)
                        ->update([
                            'downpayment_amount' => $totalDownpayment,
                            'downpayment_stage' => $stage,
                            'downpayment_stage_total' => $stageTotal,
                        ]);

                    if (Schema::hasTable('commission_requests')) {
                        $requests = DB::table('commission_requests')
                            ->where('source_client_record_id', $record->id)
                            ->whereNotNull('commission_stage')
                            ->get(['id', 'commission_stage']);

                        foreach ($requests as $request) {
                            DB::table('commission_requests')
                                ->where('id', $request->id)
                                ->update([
                                    'commission_stage_total' => $stageTotal,
                                    'stage_threshold_amount' => round(
                                        $totalDownpayment * ((int) $request->commission_stage / $stageTotal),
                                        2
                                    ),
                                ]);
                        }
                    }
                }
            });

        if (Schema::hasTable('commission_requests')) {
            DB::table('commission_requests')
                ->where('status', 'Not Released')
                ->update(['status' => 'Not Yet Released']);
        }

        DB::table('commission_requests_sales')
            ->where('status', 'Not Released')
            ->update(['status' => 'Not Yet Released']);
    }

    public function down(): void
    {
        // The previous TCP-based values cannot be safely reconstructed because
        // payments may have changed after this migration ran.
    }
};
