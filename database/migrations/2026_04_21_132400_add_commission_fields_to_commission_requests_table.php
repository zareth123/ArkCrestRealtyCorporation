<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('commission_requests', 'project_name'))
                $table->string('project_name')->nullable()->after('category');
            if (!Schema::hasColumn('commission_requests', 'property_details'))
                $table->string('property_details')->nullable()->after('project_name');
            if (!Schema::hasColumn('commission_requests', 'client_name'))
                $table->string('client_name')->nullable()->after('property_details');
            if (!Schema::hasColumn('commission_requests', 'terms_of_payment'))
                $table->string('terms_of_payment')->nullable()->after('client_name');
            if (!Schema::hasColumn('commission_requests', 'agent_name'))
                $table->string('agent_name')->nullable()->after('terms_of_payment');
            if (!Schema::hasColumn('commission_requests', 'number_of_units'))
                $table->integer('number_of_units')->nullable()->after('agent_name');
            if (!Schema::hasColumn('commission_requests', 'net_tcp'))
                $table->decimal('net_tcp', 15, 2)->nullable()->after('number_of_units');
            if (!Schema::hasColumn('commission_requests', 'commission'))
                $table->decimal('commission', 15, 2)->nullable()->after('net_tcp');
            if (!Schema::hasColumn('commission_requests', 'mode_of_payment'))
                $table->string('mode_of_payment')->nullable()->after('commission');
            if (!Schema::hasColumn('commission_requests', 'reservation_date'))
                $table->date('reservation_date')->nullable()->after('mode_of_payment');
            if (!Schema::hasColumn('commission_requests', 'remarks'))
                $table->text('remarks')->nullable()->after('reservation_date');
        });
    }

    public function down(): void
    {
        Schema::table('commission_requests', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('commission_requests', 'project_name') ? 'project_name' : null,
                Schema::hasColumn('commission_requests', 'property_details') ? 'property_details' : null,
                Schema::hasColumn('commission_requests', 'client_name') ? 'client_name' : null,
                Schema::hasColumn('commission_requests', 'terms_of_payment') ? 'terms_of_payment' : null,
                Schema::hasColumn('commission_requests', 'agent_name') ? 'agent_name' : null,
                Schema::hasColumn('commission_requests', 'number_of_units') ? 'number_of_units' : null,
                Schema::hasColumn('commission_requests', 'net_tcp') ? 'net_tcp' : null,
                Schema::hasColumn('commission_requests', 'commission') ? 'commission' : null,
                Schema::hasColumn('commission_requests', 'mode_of_payment') ? 'mode_of_payment' : null,
                Schema::hasColumn('commission_requests', 'reservation_date') ? 'reservation_date' : null,
                Schema::hasColumn('commission_requests', 'remarks') ? 'remarks' : null,
            ]));
        });
    }
};
