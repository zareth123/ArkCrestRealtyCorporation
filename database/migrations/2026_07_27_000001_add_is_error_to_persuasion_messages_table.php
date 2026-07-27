<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persuasion_messages', function (Blueprint $table) {
            // Marks a BUYER message as a system fallback (the AI call
            // failed) rather than a genuine persona reply, so the UI can
            // show a "buyer didn't get that" state instead of pretending
            // it was a real conversational beat.
            $table->boolean('is_error')->default(false)->after('turn_number');
        });
    }

    public function down(): void
    {
        Schema::table('persuasion_messages', function (Blueprint $table) {
            $table->dropColumn('is_error');
        });
    }
};