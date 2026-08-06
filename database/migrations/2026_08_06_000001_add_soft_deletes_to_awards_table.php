<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Awards were being hard-deleted with the image file wiped from disk
// immediately, so the "Deleted Records" restore flow had nothing to bring
// back — it could only recreate the DB row from the audit snapshot, and by
// then the image was already gone permanently. Making the table soft-delete
// aware lets destroy() just trash the row (image file left untouched) so a
// genuine Eloquent restore() brings the award AND its image back intact.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('awards', 'deleted_at')) {
            Schema::table('awards', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('awards', function (Blueprint $table) {
            if (Schema::hasColumn('awards', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};