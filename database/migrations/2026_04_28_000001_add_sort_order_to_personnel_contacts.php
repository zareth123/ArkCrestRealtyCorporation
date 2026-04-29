<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try { DB::statement("ALTER TABLE personnel_contacts ADD COLUMN sort_order INT DEFAULT 0"); }
        catch (\Exception $e) {}
    }

    public function down(): void {}
};
