<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prevent deployment failure when the table already exists.
        if (Schema::hasTable('persuasion_messages')) {
            return;
        }

        Schema::create('persuasion_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')
                ->constrained('persuasion_sessions')
                ->cascadeOnDelete();

            // AGENT | BUYER — who "said" this message.
            $table->string('sender');

            $table->text('message');

            // Position within the conversation, so ordering never depends
            // on timestamp precision alone.
            $table->unsignedInteger('turn_number');

            $table->timestamps();

            $table->index(['session_id', 'turn_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persuasion_messages');
    }
};