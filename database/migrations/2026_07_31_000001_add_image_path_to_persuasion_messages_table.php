<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persuasion_messages', function (Blueprint $table) {
            // Nullable path (on the 'public' disk) to an image the AGENT
            // attached to this message — e.g. a listing photo or floor
            // plan they want the buyer persona to react to. BUYER messages
            // never populate this.
            $table->string('image_path')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('persuasion_messages', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};