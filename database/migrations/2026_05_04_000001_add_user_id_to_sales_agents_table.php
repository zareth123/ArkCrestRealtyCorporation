<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_agents', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_agents', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('team_id');
            }
            if (!Schema::hasColumn('sales_agents', 'employee_id')) {
                $table->string('employee_id')->nullable()->after('user_id');
            }
        });

        // Back-fill: match existing agents to users by name
        try {
            $agents = \DB::table('sales_agents')->get();
            foreach ($agents as $agent) {
                $user = \DB::table('users')
                    ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($agent->name))])
                    ->first();
                if ($user) {
                    \DB::table('sales_agents')->where('id', $agent->id)->update([
                        'user_id'     => $user->id,
                        'employee_id' => $user->employee_id,
                    ]);
                    // Also sync team_name on the user
                    $team = \DB::table('sales_teams')->where('id', $agent->team_id)->first();
                    if ($team && !$user->team_name) {
                        \DB::table('users')->where('id', $user->id)->update(['team_name' => $team->team_name]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Back-fill is best-effort
        }
    }

    public function down(): void
    {
        Schema::table('sales_agents', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'employee_id']);
        });
    }
};
