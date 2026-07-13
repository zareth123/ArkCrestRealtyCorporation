<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('commission_requests', 'source_client_record_id')) {
                $table->unsignedBigInteger('source_client_record_id')->nullable()->after('id');
                $table->index('source_client_record_id', 'commission_requests_source_client_idx');
            }
            if (!Schema::hasColumn('commission_requests', 'commission_stage')) {
                $table->unsignedTinyInteger('commission_stage')->nullable()->after('source_client_record_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commission_requests', function (Blueprint $table) {
            if (Schema::hasColumn('commission_requests', 'commission_stage')) {
                $table->dropColumn('commission_stage');
            }
            if (Schema::hasColumn('commission_requests', 'source_client_record_id')) {
                $table->dropIndex('commission_requests_source_client_idx');
                $table->dropColumn('source_client_record_id');
            }
        });
    }
};
