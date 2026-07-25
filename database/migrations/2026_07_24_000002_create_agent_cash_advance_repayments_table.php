<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agent_cash_advance_repayments')) {
            return;
        }

        Schema::create('agent_cash_advance_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_cash_advance_id')
                ->constrained('agent_cash_advances')
                ->cascadeOnDelete();
            // Sequential term number. INSTALLMENT plans use 1..N (N = installment_terms).
            // OTHERS (one-time) plans always have exactly one row, term_number = 1.
            $table->unsignedTinyInteger('term_number');
            // PENDING | PAID
            $table->string('status')->default('PENDING');
            $table->date('date_paid')->nullable();
            $table->timestamps();

            $table->unique(['agent_cash_advance_id', 'term_number'], 'aca_repayments_advance_term_unique');        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_cash_advance_repayments');
    }
};
