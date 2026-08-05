<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('news_post_media')) {
            return;
        }

        Schema::create('news_post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_post_id')
                ->constrained('news_posts')
                ->cascadeOnDelete();
            $table->string('disk', 40)->default('public');
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 120);
            $table->string('media_type', 20);
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['news_post_id', 'sort_order']);
            $table->index('media_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_post_media');
    }
};
