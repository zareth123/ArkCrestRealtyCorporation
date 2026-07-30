<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commission_requests')
            && !Schema::hasColumn('commission_requests', 'discount_value')) {
            Schema::table('commission_requests', function (Blueprint $table) {
                $table->decimal('discount_value', 18, 2)->nullable()->after('discount');
            });
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $this->prepareCommissionRequests();
        $this->prepareClientCommissionRequests();
    }

    /**
     * Convert the Finance commission-request table without failing when an old
     * record stored the monetary discount inside the percentage column.
     */
    private function prepareCommissionRequests(): void
    {
        $table = 'commission_requests';

        if (!Schema::hasTable($table)) {
            return;
        }

        // Widen percentage columns first. This prevents existing legacy values
        // such as 100000.00 from failing before they can be repaired.
        $this->widenPercentageColumns($table);

        $columns = [
            'price_sqm' => 'DECIMAL(18,4) NULL',
            'lot_area' => 'DECIMAL(18,4) NULL',
            'discount_value' => 'DECIMAL(18,2) NULL',
            'net_tcp' => 'DECIMAL(18,2) NULL',
            'commission' => 'DECIMAL(18,2) NULL',
            'value_of_payment_terms' => 'DECIMAL(18,2) NULL',
        ];

        $this->alterExistingColumns($table, $columns);

        if (Schema::hasColumn($table, 'discount')) {
            // Older rows may contain the discount amount in `discount`.
            // Preserve that amount before rebuilding the exact percentage.
            if (Schema::hasColumn($table, 'discount_value')) {
                DB::statement(<<<'SQL'
                    UPDATE commission_requests
                    SET discount_value = CAST(discount AS DECIMAL(18,2))
                    WHERE discount_value IS NULL
                      AND discount IS NOT NULL
                      AND (discount > 100 OR discount < 0)
                SQL);
            }

            if (Schema::hasColumn($table, 'discount_value')
                && Schema::hasColumn($table, 'net_tcp')) {
                DB::statement(<<<'SQL'
                    UPDATE commission_requests
                    SET discount = TRUNCATE(
                        (CAST(discount_value AS DECIMAL(65,30)) * 100) /
                        NULLIF(
                            CAST(net_tcp AS DECIMAL(65,30)) +
                            CAST(discount_value AS DECIMAL(65,30)),
                            0
                        ),
                        30
                    )
                    WHERE discount_value IS NOT NULL
                      AND net_tcp IS NOT NULL
                      AND (net_tcp + discount_value) <> 0
                SQL);
            }

            // A percentage outside 0–100 that could not be reconstructed is
            // invalid. Clear only the percentage; any recovered money value is
            // retained in discount_value.
            DB::statement(<<<'SQL'
                UPDATE commission_requests
                SET discount = NULL
                WHERE discount IS NOT NULL
                  AND (discount < 0 OR discount > 100)
            SQL);
        }

        if (Schema::hasColumn($table, 'commission_percent')) {
            if (Schema::hasColumn($table, 'commission')
                && Schema::hasColumn($table, 'net_tcp')) {
                DB::statement(<<<'SQL'
                    UPDATE commission_requests
                    SET commission_percent = TRUNCATE(
                        (CAST(commission AS DECIMAL(65,30)) * 100) /
                        NULLIF(CAST(net_tcp AS DECIMAL(65,30)), 0),
                        30
                    )
                    WHERE commission IS NOT NULL
                      AND net_tcp IS NOT NULL
                      AND net_tcp <> 0
                SQL);
            }

            DB::statement(<<<'SQL'
                UPDATE commission_requests
                SET commission_percent = NULL
                WHERE commission_percent IS NOT NULL
                  AND (commission_percent < 0 OR commission_percent > 100)
            SQL);
        }

        $this->finalizePercentageColumns($table);
    }

    /**
     * Convert the Client Database commission-request source table safely.
     */
    private function prepareClientCommissionRequests(): void
    {
        $table = 'commission_requests_sales';

        if (!Schema::hasTable($table)) {
            return;
        }

        $this->widenPercentageColumns($table);

        $columns = [
            'price_sqm' => 'DECIMAL(18,4) NULL',
            'lot_area' => 'DECIMAL(18,4) NULL',
            'tcp' => 'DECIMAL(18,2) NULL',
            'discount_value' => 'DECIMAL(18,2) NULL',
            'net_tcp' => 'DECIMAL(18,2) NULL',
            'commission' => 'DECIMAL(18,2) NULL',
        ];

        $this->alterExistingColumns($table, $columns);

        if (Schema::hasColumn($table, 'discount')) {
            if (Schema::hasColumn($table, 'discount_value')) {
                DB::statement(<<<'SQL'
                    UPDATE commission_requests_sales
                    SET discount_value = CAST(discount AS DECIMAL(18,2))
                    WHERE discount_value IS NULL
                      AND discount IS NOT NULL
                      AND (discount > 100 OR discount < 0)
                SQL);
            }

            if (Schema::hasColumn($table, 'discount_value')
                && Schema::hasColumn($table, 'tcp')) {
                DB::statement(<<<'SQL'
                    UPDATE commission_requests_sales
                    SET discount = TRUNCATE(
                        (CAST(discount_value AS DECIMAL(65,30)) * 100) /
                        NULLIF(CAST(tcp AS DECIMAL(65,30)), 0),
                        30
                    )
                    WHERE discount_value IS NOT NULL
                      AND tcp IS NOT NULL
                      AND tcp <> 0
                SQL);
            }

            DB::statement(<<<'SQL'
                UPDATE commission_requests_sales
                SET discount = NULL
                WHERE discount IS NOT NULL
                  AND (discount < 0 OR discount > 100)
            SQL);
        }

        if (Schema::hasColumn($table, 'commission_percent')) {
            if (Schema::hasColumn($table, 'commission')
                && Schema::hasColumn($table, 'net_tcp')) {
                DB::statement(<<<'SQL'
                    UPDATE commission_requests_sales
                    SET commission_percent = TRUNCATE(
                        (CAST(commission AS DECIMAL(65,30)) * 100) /
                        NULLIF(CAST(net_tcp AS DECIMAL(65,30)), 0),
                        30
                    )
                    WHERE commission IS NOT NULL
                      AND net_tcp IS NOT NULL
                      AND net_tcp <> 0
                SQL);
            }

            DB::statement(<<<'SQL'
                UPDATE commission_requests_sales
                SET commission_percent = NULL
                WHERE commission_percent IS NOT NULL
                  AND (commission_percent < 0 OR commission_percent > 100)
            SQL);
        }

        $this->finalizePercentageColumns($table);
    }

    private function widenPercentageColumns(string $table): void
    {
        foreach (['discount', 'commission_percent'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                DB::statement(
                    "ALTER TABLE {$table} MODIFY COLUMN {$column} DECIMAL(65,30) NULL"
                );
            }
        }
    }

    private function finalizePercentageColumns(string $table): void
    {
        // DECIMAL(33,30) stores percentages from 0–100 with MySQL's maximum
        // supported 30 decimal places and no FLOAT/DOUBLE approximation.
        foreach (['discount', 'commission_percent'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                DB::statement(
                    "ALTER TABLE {$table} MODIFY COLUMN {$column} DECIMAL(33,30) NULL"
                );
            }
        }
    }

    private function alterExistingColumns(string $table, array $columns): void
    {
        foreach ($columns as $column => $definition) {
            if (Schema::hasColumn($table, $column)) {
                DB::statement(
                    "ALTER TABLE {$table} MODIFY COLUMN {$column} {$definition}"
                );
            }
        }
    }

    public function down(): void
    {
        // This migration repairs legacy financial data. Reverting the schema
        // would reduce precision and may reintroduce inaccurate percentages.
    }
};
