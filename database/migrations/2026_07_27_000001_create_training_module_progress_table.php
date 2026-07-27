<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prevent deployment failure when the table already exists.
        if (Schema::hasTable('training_module_progress')) {
            return;
        }

        Schema::create('training_module_progress', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Which of the 6 "Real Estate Agent Training" modules this row
            // tracks (1 = Property Knowledge lesson set... matches the
            // module order rendered on the training-course page).
            $table->unsignedTinyInteger('module_number');

            // How many times the user has submitted the "Check Your
            // Understanding" quiz for this module.
            $table->unsignedInteger('attempts')->default(0);

            // Most recent and best-ever quiz scores, stored as a whole
            // percentage (0-100) so the UI never needs to recompute from
            // raw correct/total counts.
            $table->unsignedTinyInteger('last_score')->nullable();
            $table->unsignedTinyInteger('best_score')->nullable();

            // A module is "completed" the first time the user passes the
            // quiz (score >= passing threshold, enforced server-side in
            // the controller). Once true this never flips back to false,
            // even on a later lower-scoring retake.
            $table->boolean('passed')->default(false);

            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // One progress row per user per module — attempts/scores are
            // tracked by updating this row, not by inserting new ones.
            $table->unique(['user_id', 'module_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_module_progress');
    }
};
