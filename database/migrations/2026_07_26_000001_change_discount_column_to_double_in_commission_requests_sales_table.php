<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The `discount` column on commission_requests_sales was originally
     * decimal(15,2), which forces MySQL to round any value stored here to
     * exactly 2 decimal places. This changes it to DOUBLE, which has no
     * fixed scale, so the discount percentage entered on the Add New
     * Client Record / Edit forms is stored at full precision instead of
     * being rounded off.
     *
     * Uses a raw ALTER TABLE (same approach already used in
     * 2026_04_25_000002_force_add_commission_fields.php) instead of
     * Schema::table()->change(), since the latter requires the
     * doctrine/dbal package to be installed.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE commission_requests_sales MODIFY discount DOUBLE NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE commission_requests_sales MODIFY discount DECIMAL(15,2) NULL');
    }
};
