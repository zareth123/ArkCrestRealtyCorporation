<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // MySQL DECIMAL supports a maximum scale of 30. DECIMAL(33,30)
        // preserves 0–100 percentages without FLOAT/DOUBLE approximation.
        foreach (['commission_requests', 'commission_requests_sales'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'discount')) {
                DB::statement("ALTER TABLE {$table} MODIFY COLUMN discount DECIMAL(33,30) NULL");
            }

            if (Schema::hasColumn($table, 'commission_percent')) {
                DB::statement("ALTER TABLE {$table} MODIFY COLUMN commission_percent DECIMAL(33,30) NULL");
            }
        }

        if (Schema::hasTable('arkcrest_commission_rates')
            && Schema::hasColumn('arkcrest_commission_rates', 'arkcrest_percent')) {
            DB::statement('ALTER TABLE arkcrest_commission_rates MODIFY COLUMN arkcrest_percent DECIMAL(33,30) NOT NULL DEFAULT 0');
        }


        // Recover the full percentage from the authoritative stored money
        // values. Existing rows may previously have been limited to 2, 6, or
        // 10 decimal places. TRUNCATE deliberately avoids percentage rounding.
        if (Schema::hasTable('commission_requests_sales')
            && Schema::hasColumn('commission_requests_sales', 'discount_value')
            && Schema::hasColumn('commission_requests_sales', 'tcp')) {
            DB::statement(<<<'SQL'
                UPDATE commission_requests_sales
                SET discount = TRUNCATE(
                    (CAST(discount_value AS DECIMAL(65,30)) * 100) /
                    NULLIF(CAST(tcp AS DECIMAL(65,30)), 0),
                    30
                )
                WHERE discount_value IS NOT NULL AND tcp IS NOT NULL AND tcp <> 0
            SQL);
        }

        if (Schema::hasTable('commission_requests')) {
            if (Schema::hasColumn('commission_requests', 'discount_value')
                && Schema::hasColumn('commission_requests', 'net_tcp')) {
                DB::statement(<<<'SQL'
                    UPDATE commission_requests
                    SET discount = TRUNCATE(
                        (CAST(discount_value AS DECIMAL(65,30)) * 100) /
                        NULLIF(
                            CAST(net_tcp AS DECIMAL(65,30)) + CAST(discount_value AS DECIMAL(65,30)),
                            0
                        ),
                        30
                    )
                    WHERE discount_value IS NOT NULL
                      AND net_tcp IS NOT NULL
                      AND (net_tcp + discount_value) <> 0
                SQL);
            }

            if (Schema::hasColumn('commission_requests', 'commission_percent')
                && Schema::hasColumn('commission_requests', 'commission')
                && Schema::hasColumn('commission_requests', 'net_tcp')) {
                DB::statement(<<<'SQL'
                    UPDATE commission_requests
                    SET commission_percent = TRUNCATE(
                        (CAST(commission AS DECIMAL(65,30)) * 100) /
                        NULLIF(CAST(net_tcp AS DECIMAL(65,30)), 0),
                        30
                    )
                    WHERE commission IS NOT NULL AND net_tcp IS NOT NULL AND net_tcp <> 0
                SQL);
            }
        }

        if (Schema::hasTable('arkcrest_commission_rates')
            && Schema::hasTable('commission_requests')) {
            DB::statement(<<<'SQL'
                UPDATE arkcrest_commission_rates rates
                INNER JOIN commission_requests requests
                    ON requests.id = rates.commission_request_id
                SET rates.arkcrest_percent = TRUNCATE(
                    (
                        CAST(rates.arkcrest_commission AS DECIMAL(65,30)) *
                        CASE
                            WHEN requests.payment_type = '2 Months Commission' THEN 2
                            WHEN requests.payment_type = '3 Months Commission' THEN 3
                            ELSE 1
                        END * 100
                    ) /
                    NULLIF(CAST(requests.net_tcp AS DECIMAL(65,30)), 0),
                    30
                )
                WHERE rates.arkcrest_commission IS NOT NULL
                  AND requests.net_tcp IS NOT NULL
                  AND requests.net_tcp <> 0
            SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (['commission_requests', 'commission_requests_sales'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'discount')) {
                DB::statement("ALTER TABLE {$table} MODIFY COLUMN discount DECIMAL(15,10) NULL");
            }

            if (Schema::hasColumn($table, 'commission_percent')) {
                DB::statement("ALTER TABLE {$table} MODIFY COLUMN commission_percent DECIMAL(10,6) NULL");
            }
        }

        if (Schema::hasTable('arkcrest_commission_rates')
            && Schema::hasColumn('arkcrest_commission_rates', 'arkcrest_percent')) {
            DB::statement('ALTER TABLE arkcrest_commission_rates MODIFY COLUMN arkcrest_percent DECIMAL(8,4) NOT NULL DEFAULT 0');
        }
    }
};
