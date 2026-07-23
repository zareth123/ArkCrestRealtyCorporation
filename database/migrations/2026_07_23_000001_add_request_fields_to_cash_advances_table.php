<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // New Request form fields (Part 1 revamp): Department, Purpose,
        // Date Requested, Date Needed, Repayment Type, Installment Terms.
        // Each addition is guarded so re-running this migration (or a
        // deploy that already has the columns) never fails.
        Schema::table('cash_advances', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_advances', 'department')) {
                $table->string('department')->nullable()->after('employee_name');
            }
            if (!Schema::hasColumn('cash_advances', 'purpose')) {
                $table->text('purpose')->nullable()->after('department');
            }
            if (!Schema::hasColumn('cash_advances', 'date_requested')) {
                $table->date('date_requested')->nullable()->after('purpose');
            }
            if (!Schema::hasColumn('cash_advances', 'date_needed')) {
                $table->date('date_needed')->nullable()->after('date_requested');
            }
            if (!Schema::hasColumn('cash_advances', 'repayment_type')) {
                // INSTALLMENT | OTHERS
                $table->string('repayment_type')->default('INSTALLMENT')->after('date_needed');
            }
            if (!Schema::hasColumn('cash_advances', 'installment_terms')) {
                // Number of salary-deduction terms, 1-6, only used when repayment_type = INSTALLMENT
                $table->unsignedTinyInteger('installment_terms')->nullable()->after('repayment_type');
            }
        });

        // Backfill: carry any legacy "reason" text into the new "purpose" field
        // so existing records still display correctly under the new label.
        if (Schema::hasColumn('cash_advances', 'reason') && Schema::hasColumn('cash_advances', 'purpose')) {
            DB::table('cash_advances')
                ->whereNull('purpose')
                ->whereNotNull('reason')
                ->update(['purpose' => DB::raw('reason')]);
        }

        // repayment_date is now only required for the "Others" (one-time
        // payment) repayment type, so it can no longer be a blanket
        // not-null column.
        Schema::table('cash_advances', function (Blueprint $table) {
            $table->date('repayment_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cash_advances', function (Blueprint $table) {
            foreach (['department', 'purpose', 'date_requested', 'date_needed', 'repayment_type', 'installment_terms'] as $col) {
                if (Schema::hasColumn('cash_advances', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
