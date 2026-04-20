<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \DB::table('departments')->where('slug', 'admin')->update(['name' => 'Administrative']);
        \DB::table('departments')->where('slug', 'hr')->update(['name' => 'Human Resource']);
    }

    public function down(): void
    {
        \DB::table('departments')->where('slug', 'admin')->update(['name' => 'Admin']);
        \DB::table('departments')->where('slug', 'hr')->update(['name' => 'HR']);
    }
};
