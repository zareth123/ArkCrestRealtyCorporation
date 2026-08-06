<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('awards', 'image_disk')) {
            Schema::table('awards', function (Blueprint $table) {
                $table->string('image_disk', 40)->nullable()->after('title');
                $table->string('image_path', 500)->nullable()->after('image_disk');
            });
        }
    }

    public function down(): void
    {
        Schema::table('awards', function (Blueprint $table) {
            if (Schema::hasColumn('awards', 'image_disk')) {
                $table->dropColumn(['image_disk', 'image_path']);
            }
        });
    }
};