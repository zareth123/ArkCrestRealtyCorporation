<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_requests_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('commission_requests_sales', 'downpayment_stage')) {
                $table->unsignedTinyInteger('downpayment_stage')
                    ->default(0)
                    ->after('downpayment_status');
            }

            if (!Schema::hasColumn('commission_requests_sales', 'downpayment_stage_total')) {
                $table->unsignedTinyInteger('downpayment_stage_total')
                    ->default(1)
                    ->after('downpayment_stage');
            }
        });

        Schema::table('commission_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('commission_requests', 'source_client_record_id')) {
                $table->unsignedBigInteger('source_client_record_id')->nullable()->after('id');
                $table->index('source_client_record_id', 'commission_requests_source_client_idx');
            }

            if (!Schema::hasColumn('commission_requests', 'commission_stage')) {
                $table->unsignedTinyInteger('commission_stage')->nullable()->after('source_client_record_id');
            }

            if (!Schema::hasColumn('commission_requests', 'commission_stage_total')) {
                $table->unsignedTinyInteger('commission_stage_total')->nullable()->after('commission_stage');
            }

            if (!Schema::hasColumn('commission_requests', 'stage_threshold_amount')) {
                $table->decimal('stage_threshold_amount', 15, 2)
                    ->nullable()
                    ->after('commission_stage_total');
            }
        });

        DB::table('commission_requests_sales')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '')
                    ->orWhere('status', 'Not Yet Released');
            })
            ->update(['status' => 'Not Released']);

        DB::table('commission_requests')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '')
                    ->orWhere('status', 'Not Yet Released');
            })
            ->update(['status' => 'Not Released']);

        $this->backfillStages();
        $this->addUniqueStageIndex();
    }

    private function backfillStages(): void
    {
        DB::table('commission_requests_sales')
            ->orderBy('id')
            ->chunkById(200, function ($records) {
                foreach ($records as $record) {
                    $terms = (int) ($record->downpayment_terms ?? 0);
                    $label = (string) ($record->terms_of_payment ?? '');

                    if ($terms <= 0 && preg_match('/\((\d+)\s*MOS?\)/i', $label, $matches)) {
                        $terms = (int) $matches[1];
                    }

                    $stageTotal = $terms === 2 ? 2 : ($terms >= 3 ? 3 : 1);
                    $tcp = max(0, (float) ($record->tcp ?? 0));
                    $totalDownpayment = max(0, (float) ($record->downpayment_amount ?? 0));
                    $upperLabel = strtoupper($label);

                    if (str_contains($upperLabel, 'STRAIGHT PAYMENT')) {
                        $totalDownpayment = $tcp;
                    } elseif (preg_match('/(\d+(?:\.\d+)?)\s*%\s*DP/i', $label, $matches)) {
                        $totalDownpayment = round($tcp * ((float) $matches[1] / 100), 2);
                    }

                    $paidTotal = (float) DB::table('downpayment_installments')
                        ->where('commission_request_sales_id', $record->id)
                        ->where('is_paid', true)
                        ->sum('amount');

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
                            'downpayment_stage' => $stage,
                            'downpayment_stage_total' => $stageTotal,
                        ]);

                    DB::table('commission_requests')
                        ->where('source_client_record_id', $record->id)
                        ->whereNotNull('commission_stage')
                        ->update([
                            'commission_stage_total' => $stageTotal,
                        ]);

                    $linkedRequests = DB::table('commission_requests')
                        ->where('source_client_record_id', $record->id)
                        ->whereNotNull('commission_stage')
                        ->get(['id', 'commission_stage']);

                    foreach ($linkedRequests as $request) {
                        DB::table('commission_requests')
                            ->where('id', $request->id)
                            ->update([
                                'stage_threshold_amount' => round(
                                    $totalDownpayment * ((int) $request->commission_stage / $stageTotal),
                                    2
                                ),
                            ]);
                    }
                }
            });
    }

    private function addUniqueStageIndex(): void
    {
        $duplicates = DB::table('commission_requests')
            ->select('source_client_record_id', 'commission_stage', DB::raw('COUNT(*) as duplicate_count'))
            ->whereNotNull('source_client_record_id')
            ->whereNotNull('commission_stage')
            ->groupBy('source_client_record_id', 'commission_stage')
            ->having('duplicate_count', '>', 1)
            ->exists();

        if ($duplicates) {
            return;
        }

        try {
            Schema::table('commission_requests', function (Blueprint $table) {
                $table->unique(
                    ['source_client_record_id', 'commission_stage'],
                    'commission_request_source_stage_unique'
                );
            });
        } catch (\Throwable $exception) {
            // The application still performs a locked duplicate check.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('commission_requests', function (Blueprint $table) {
                $table->dropUnique('commission_request_source_stage_unique');
            });
        } catch (\Throwable $exception) {
        }

        Schema::table('commission_requests', function (Blueprint $table) {
            foreach (['commission_stage_total', 'stage_threshold_amount'] as $column) {
                if (Schema::hasColumn('commission_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('commission_requests_sales', function (Blueprint $table) {
            foreach (['downpayment_stage', 'downpayment_stage_total'] as $column) {
                if (Schema::hasColumn('commission_requests_sales', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
