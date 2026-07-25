<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prevent deployment failure when the table already exists.
        if (Schema::hasTable('agent_cash_advances')) {
            return;
        }

        Schema::create('agent_cash_advances', function (Blueprint $table) {
            $table->id();
            $table->string('control_number')->unique();

            // Sourced from the real sales_agents table (not free text), so
            // requests are always tied to an actual agent record. Nullable +
            // nullOnDelete so a removed agent doesn't take historical cash
            // advance records down with it.
            $table->foreignId('agent_id')
                ->nullable()
                ->constrained('sales_agents')
                ->nullOnDelete();
            $table->string('agent_name');

            // Snapshot of the agent's sales team name at request time —
            // mirrors how Cash Advance stores a plain "department" string
            // rather than a live foreign key.
            $table->string('team')->nullable();

            $table->decimal('amount', 12, 2);
            $table->text('purpose')->nullable();
            $table->date('date_requested')->nullable();
            $table->date('date_needed')->nullable();

            // INSTALLMENT | OTHERS
            $table->string('repayment_type')->default('INSTALLMENT');
            // Number of salary/commission-deduction terms, 1-6, only used when repayment_type = INSTALLMENT
            $table->unsignedTinyInteger('installment_terms')->nullable();
            // One-time payment date, only used when repayment_type = OTHERS
            $table->date('repayment_date')->nullable();

            $table->string('status')->default('PENDING');
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_cash_advances');
    }
};
