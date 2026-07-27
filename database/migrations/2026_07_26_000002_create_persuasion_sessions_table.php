<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prevent deployment failure when the table already exists.
        if (Schema::hasTable('persuasion_sessions')) {
            return;
        }

        Schema::create('persuasion_sessions', function (Blueprint $table) {
            $table->id();

            // Tied to users (not sales_agents) since any logged-in employee
            // can practice, not only sales agents.
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('scenario_id')
                ->constrained('persuasion_scenarios')
                ->cascadeOnDelete();

            // Snapshot of difficulty at the time the session was played, so
            // historical results stay accurate even if the scenario's
            // difficulty is edited later.
            $table->string('difficulty');

            // IN_PROGRESS | SOLD | NOT_SOLD | ABANDONED
            $table->string('status')->default('IN_PROGRESS');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            // Overall score (0-100) once the session is scored by the AI.
            $table->unsignedTinyInteger('overall_score')->nullable();

            // Full rubric breakdown + written feedback from the scoring
            // pass, e.g. {"rapport":80,"objection_handling":60,...,
            // "summary":"...", "suggestions":["...","..."]}.
            // Stored as JSON rather than separate scorecard columns/table
            // since the rubric is likely to be tuned frequently early on.
            $table->json('scorecard')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persuasion_sessions');
    }
};