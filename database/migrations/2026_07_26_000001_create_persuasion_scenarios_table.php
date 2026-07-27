<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prevent deployment failure when the table already exists.
        if (Schema::hasTable('persuasion_scenarios')) {
            return;
        }

        Schema::create('persuasion_scenarios', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            // Short line shown on the scenario picker card, e.g.
            // "First-time buyer, cautious about budget"
            $table->string('tagline')->nullable();

            // EASY | MEDIUM | HARD — kept as a plain string (not an FK)
            // since it's a small fixed set, matching how status/type fields
            // are handled elsewhere in this app (e.g. cash advance status).
            $table->string('difficulty');

            // Buyer persona details shown to the agent before starting and
            // used to build the AI system prompt.
            $table->string('buyer_name');
            $table->string('buyer_avatar')->nullable();
            $table->text('buyer_backstory')->nullable();
            $table->decimal('buyer_budget', 12, 2)->nullable();

            // Free-text list-style fields (one item per line) describing the
            // persona for prompt-building. Kept simple as text rather than
            // normalized child tables since these are authored/edited as a
            // block of writing, not queried individually.
            $table->text('personality_traits')->nullable();
            $table->text('common_objections')->nullable();
            $table->text('win_conditions')->nullable();     // what convinces this buyer to say yes
            $table->text('walkaway_triggers')->nullable();  // what makes this buyer end the chat

            // Optional link to a real listing/lot the scenario is themed
            // around, so the pitch can reference actual project details.
            $table->foreignId('property_id')
                ->nullable()
                ->constrained('properties')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persuasion_scenarios');
    }
};