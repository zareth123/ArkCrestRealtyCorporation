<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Same fix as the awards table: stop hard-deleting testimonials and wiping
// the avatar file immediately, so restoring one from the "Deleted Records"
// panel actually has an intact row + file to restore instead of a
// recreated row pointing at a photo that no longer exists.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('testimonials', 'deleted_at')) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (Schema::hasColumn('testimonials', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};