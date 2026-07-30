<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || !Schema::hasTable('commission_requests')) {
            return;
        }

        // Keep the columns exact even when the earlier precision migration was
        // skipped or the database was imported after that migration ran.
        if (Schema::hasColumn('commission_requests', 'discount')) {
            DB::statement('ALTER TABLE commission_requests MODIFY COLUMN discount DECIMAL(33,30) NULL');
        }

        if (Schema::hasColumn('commission_requests', 'commission_percent')) {
            DB::statement('ALTER TABLE commission_requests MODIFY COLUMN commission_percent DECIMAL(33,30) NULL');
        }

        // A commission request created from a client record must use the
        // client's authoritative financial values. This also repairs rows that
        // were changed after an Edit/Save cycle used a shortened percentage.
        if (Schema::hasTable('commission_requests_sales')
            && Schema::hasColumn('commission_requests', 'source_client_record_id')) {
            DB::statement(<<<'SQL'
                UPDATE commission_requests requests
                INNER JOIN commission_requests_sales clients
                    ON clients.id = requests.source_client_record_id
                SET requests.price_sqm = clients.price_sqm,
                    requests.lot_area = clients.lot_area,
                    requests.discount_value = clients.discount_value,
                    requests.net_tcp = clients.net_tcp,
                    requests.discount = TRUNCATE(
                        (CAST(clients.discount_value AS DECIMAL(65,30)) * 100) /
                        NULLIF(CAST(clients.tcp AS DECIMAL(65,30)), 0),
                        30
                    )
                WHERE requests.source_client_record_id IS NOT NULL
            SQL);
        }

        // Repair manual/older commission rows from the stored money amounts.
        // TRUNCATE is intentional: no percentage digit is rounded upward.
        if (Schema::hasColumn('commission_requests', 'discount_value')
            && Schema::hasColumn('commission_requests', 'net_tcp')) {
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
                WHERE source_client_record_id IS NULL
                  AND discount_value IS NOT NULL
                  AND net_tcp IS NOT NULL
                  AND (net_tcp + discount_value) <> 0
            SQL);
        }

        if (Schema::hasColumn('commission_requests', 'commission')
            && Schema::hasColumn('commission_requests', 'commission_percent')
            && Schema::hasColumn('commission_requests', 'net_tcp')) {
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
    }

    public function down(): void
    {
        // Data-repair migration: reverting would reintroduce inaccurate values.
    }
};
