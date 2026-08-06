<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Same fix as awards/testimonials: news posts were hard-deleted with every
// attached media file wiped from disk immediately, so restoring a post from
// the "Deleted Records" panel recreated the row but the pictures/videos it
// pointed at were already gone. Soft deletes let destroy() just trash the
// post (media rows + files left untouched) so restore() brings it back with
// working attachments.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('news_posts', 'deleted_at')) {
            Schema::table('news_posts', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('news_posts', function (Blueprint $table) {
            if (Schema::hasColumn('news_posts', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};