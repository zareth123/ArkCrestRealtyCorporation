<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('departmental_expenses', 'release_status')) {
            Schema::table('departmental_expenses', function (Blueprint $table) {
                $table->string('release_status')
                    ->default('NOT YET RELEASED')
                    ->after('department');
            });
        }

        // Convert the former combined status into separate release and
        // liquidation statuses without losing existing liquidation records.
        DB::table('departmental_expenses')
            ->where('status', 'LIQUIDATED')
            ->update(['release_status' => 'RELEASED']);

        DB::table('departmental_expenses')
            ->whereIn('status', ['NOT YET LIQUIDATED', 'NOT LIQUIDATED'])
            ->whereNotNull('date_released')
            ->update([
                'release_status' => 'RELEASED',
                'status' => 'NOT YET LIQUIDATED',
            ]);

        DB::table('departmental_expenses')
            ->where('status', 'PENDING')
            ->whereNotNull('date_released')
            ->update([
                'release_status' => 'RELEASED',
                'status' => 'NOT YET LIQUIDATED',
            ]);

        DB::table('departmental_expenses')
            ->whereIn('status', ['PENDING', 'NOT LIQUIDATED'])
            ->whereNull('date_released')
            ->update([
                'release_status' => 'NOT YET RELEASED',
                'status' => 'NOT YET LIQUIDATED',
            ]);

        DB::table('departmental_expenses')
            ->where('status', 'REJECTED')
            ->update([
                'release_status' => 'REJECTED',
                'status' => 'NOT YET LIQUIDATED',
                'date_released' => null,
                'total_expenses' => null,
                'amount_returned' => null,
                'date_of_amount_returned' => null,
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('departmental_expenses', 'release_status')) {
            Schema::table('departmental_expenses', function (Blueprint $table) {
                $table->dropColumn('release_status');
            });
        }
    }
};
