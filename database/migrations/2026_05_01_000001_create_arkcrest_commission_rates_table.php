<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arkcrest_commission_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commission_request_id');
            $table->decimal('arkcrest_percent', 8, 4)->default(0);
            $table->decimal('arkcrest_commission', 15, 2)->default(0);
            $table->timestamps();
            $table->foreign('commission_request_id')->references('id')->on('commission_requests')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arkcrest_commission_rates');
    }
};
