<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commission_requests_sales') ||
            !Schema::hasColumn('commission_requests_sales', 'status')) {
            return;
        }

        DB::table('commission_requests_sales')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '')
                    ->orWhere('status', 'Not Yet Released');
            })
            ->update(['status' => 'Not Released']);
    }

    public function down(): void
    {
        // Status normalization is intentionally not reversed.
    }
};
