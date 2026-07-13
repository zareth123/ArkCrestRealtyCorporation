<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Observers\ActivityLogObserver;

/**
 * Wires up the centralized Edit History / Audit Trail feature.
 *
 * 1. Registers ActivityLogObserver on every module model so Create/Update/Delete
 *    actions are captured automatically — no manual logging calls in controllers.
 * 2. Self-heals the `activity_logs` table/columns at boot time so collaborators
 *    never need to run `php artisan migrate` for this feature to work. (A real
 *    migration file also exists for environments that do migrate normally —
 *    this is purely a safety net.)
 */
class ActivityLogServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->ensureSchema();
        $this->registerObservers();
    }

    protected function ensureSchema(): void
    {
        try {
            if (!Schema::hasTable('activity_logs')) {
                Schema::create('activity_logs', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                    $table->string('action');
                    $table->string('module');
                    $table->text('description');
                    $table->json('meta')->nullable();
                    $table->string('ip')->nullable();
                    $table->timestamps();
                });
                return;
            }

            if (!Schema::hasColumn('activity_logs', 'meta')) {
                Schema::table('activity_logs', function (Blueprint $table) {
                    $table->json('meta')->nullable()->after('description');
                });
            }
        } catch (\Throwable $e) {
            // DB may not be ready yet (e.g. during `migrate` itself running via console). Ignore.
        }
    }

    protected function registerObservers(): void
    {
        foreach (array_keys(ActivityLogObserver::$moduleMap) as $modelClass) {
            if (class_exists($modelClass)) {
                $modelClass::observe(ActivityLogObserver::class);
            }
        }
    }
}
