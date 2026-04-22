<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            if (!Schema::hasColumn('notes', 'note_time'))
                $table->time('note_time')->nullable()->after('note_date');
            if (!Schema::hasColumn('notes', 'notif_sent'))
                $table->boolean('notif_sent')->default(false)->after('note_time');
            if (!Schema::hasColumn('notes', 'reminder_time'))
                $table->time('reminder_time')->nullable()->after('notif_sent');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            foreach (['note_time', 'notif_sent', 'reminder_time'] as $col) {
                if (Schema::hasColumn('notes', $col))
                    $table->dropColumn($col);
            }
        });
    }
};
