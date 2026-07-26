<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_cash_advance_repayments', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_cash_advance_repayments', 'amount')) {
                // Amount actually paid for this term. Defaults to the equal
                // split (amount / installment_terms) on the frontend but can
                // be edited before the payment is recorded, since a row here
                // now only ever exists once a payment has actually been made.
                $table->decimal('amount', 12, 2)->nullable()->after('term_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agent_cash_advance_repayments', function (Blueprint $table) {
            if (Schema::hasColumn('agent_cash_advance_repayments', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }
};
