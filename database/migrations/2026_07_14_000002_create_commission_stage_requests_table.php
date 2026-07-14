<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commission_stage_requests')) {
            Schema::create('commission_stage_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_client_record_id');
                $table->unsignedBigInteger('commission_request_id')->nullable();
                $table->unsignedTinyInteger('commission_stage');
                $table->unsignedTinyInteger('commission_stage_total')->default(1);
                $table->decimal('stage_threshold_amount', 15, 2)->nullable();
                $table->unsignedBigInteger('requested_by_user_id')->nullable();
                $table->string('requested_by_name')->nullable();
                $table->timestamp('requested_at')->nullable();
                $table->string('status', 50)->default('Requested');
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['source_client_record_id', 'commission_stage'],
                    'commission_stage_request_source_stage_unique'
                );
                $table->index('commission_request_id', 'commission_stage_request_commission_idx');
                $table->index('status', 'commission_stage_request_status_idx');
            });
        }

        $this->moveLegacyRequestedRows();
    }

    /**
     * Older builds created an incomplete commission_requests row as soon as
     * Sales clicked Request. Convert those placeholders into pending stage
     * requests so Finance opens the Add form instead of the Edit modal.
     */
    private function moveLegacyRequestedRows(): void
    {
        if (!Schema::hasTable('commission_requests')
            || !Schema::hasTable('commission_stage_requests')) {
            return;
        }

        DB::table('commission_requests')
            ->where('status', 'Requested')
            ->whereNotNull('source_client_record_id')
            ->whereNotNull('commission_stage')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $existing = DB::table('commission_stage_requests')
                        ->where('source_client_record_id', $row->source_client_record_id)
                        ->where('commission_stage', $row->commission_stage)
                        ->first();

                    if ($existing) {
                        $stageRequestId = $existing->id;
                    } else {
                        $stageRequestId = DB::table('commission_stage_requests')->insertGetId([
                            'source_client_record_id' => $row->source_client_record_id,
                            'commission_request_id' => null,
                            'commission_stage' => $row->commission_stage,
                            'commission_stage_total' => $row->commission_stage_total ?: 1,
                            'stage_threshold_amount' => $row->stage_threshold_amount,
                            'requested_by_user_id' => null,
                            'requested_by_name' => $row->requestor_name,
                            'requested_at' => $row->date_requested ?: $row->created_at,
                            'status' => 'Requested',
                            'processed_at' => null,
                            'created_at' => $row->created_at ?: now(),
                            'updated_at' => now(),
                        ]);
                    }

                    if (Schema::hasTable('system_notifications')) {
                        DB::table('system_notifications')
                            ->where('type', 'commission_request_submitted')
                            ->where('note_id', $row->id)
                            ->update([
                                'note_id' => $stageRequestId,
                                'updated_at' => now(),
                            ]);
                    }

                    DB::table('commission_requests')->where('id', $row->id)->delete();
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_stage_requests');
    }
};
