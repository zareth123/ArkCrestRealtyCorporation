<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Auto-create the cash_advances table if it doesn't exist yet, so no
        // collaborator ever needs to run `php artisan migrate` manually.
        // Uses the schema builder (not raw SQL) so it works the same on
        // MySQL, SQLite, or Postgres.
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('cash_advances')) {
                \Illuminate\Support\Facades\Schema::create('cash_advances', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('control_number')->unique();
                    $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->string('employee_name');
                    $table->decimal('amount', 12, 2);
                    $table->text('reason')->nullable();
                    $table->date('repayment_date');
                    $table->string('status')->default('PENDING');
                    $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamp('reviewed_at')->nullable();
                    $table->softDeletes();
                    $table->timestamps();
                });
            }
        } catch (\Exception $e) {
            // Table may already be mid-creation or DB not reachable yet.
        }
        // Auto-create the training_module_progress table if it doesn't exist yet
        // (Real Estate Agent Training course — per-user module/quiz progress).
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('training_module_progress')) {
                \Illuminate\Support\Facades\Schema::create('training_module_progress', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                    $table->unsignedTinyInteger('module_number');
                    $table->unsignedInteger('attempts')->default(0);
                    $table->unsignedTinyInteger('last_score')->nullable();
                    $table->unsignedTinyInteger('best_score')->nullable();
                    $table->boolean('passed')->default(false);
                    $table->timestamp('last_attempted_at')->nullable();
                    $table->timestamp('completed_at')->nullable();
                    $table->timestamps();
                    $table->unique(['user_id', 'module_number']);
                });
            }
        } catch (\Exception $e) {
            // Table may already be mid-creation or DB not reachable yet.
        }
        // Auto-seed departments if empty
        try {
            if (\App\Models\Department::count() === 0) {
                $departments = [
                    ['name' => 'Administrative',    'slug' => 'admin'],
                    ['name' => 'Sales & Marketing', 'slug' => 'sales_and_marketing'],
                    ['name' => 'Human Resource',    'slug' => 'hr'],
                    ['name' => 'Finance',           'slug' => 'finance'],
                    ['name' => 'Executive',         'slug' => 'executive'],
                ];
                foreach ($departments as $dept) {
                    \App\Models\Department::firstOrCreate(
                        ['slug' => $dept['slug']],
                        ['name' => $dept['name'], 'allowable_budget' => 0]
                    );
                }
            }
        } catch (\Exception $e) {
            // Table may not exist yet during migration
        }
        View::composer('*', function ($view) {
            // Skip for public pages
            if (in_array($view->getName(), ['tripping', 'auth.login', 'auth.registered', 'auth.verify'])) {
                $view->with('hiddenSections', []);
                $view->with('userNotes', collect());
                $view->with('dueNotesCount', 0);
                $view->with('sysNotifs', collect());
                $view->with('unreadNotifCount', 0);
                $view->with('trainingUser', null);
                $view->with('trainingName', '');
                $view->with('trainingInitial', 'A');
                return;
            }
            $user = auth()->user();
            if ($user) {
                // Hidden sections — admin sees all
                if ($user->isAdmin()) {
                    $view->with('hiddenSections', []);
                } else {
                    $hidden = array_values($user->hidden_pages ?? []);
                    $view->with('hiddenSections', $hidden);
                }
                // Notes & notifications — all users get their own
                $notes = \App\Models\Note::where('user_id', $user->id)->whereNull('completed_at')->orderBy('created_at','desc')->get();
                $view->with('userNotes', $notes);
                $view->with('dueNotesCount', $notes->filter(fn($n) => $n->isDueNow())->count());
                $sysNotifs = \App\Models\SystemNotification::where('user_id', $user->id)
                    ->orderBy('notified_at', 'desc')->limit(50)->get();
                $view->with('sysNotifs', $sysNotifs);
                $view->with('unreadNotifCount', $sysNotifs->where('is_read', false)->count());
                // Agent Training / Sales Academy header display — computed here (not in the
                // academy layout's own @php block) because a child view's @section content
                // renders BEFORE the parent layout it @extends, so variables set only inside
                // the layout aren't defined yet when the child references them.
                $view->with('trainingUser', $user);
                $view->with('trainingName', $user->preferred_address
                    ? $user->preferred_address . ' ' . $user->name
                    : $user->name);
                $view->with('trainingInitial', strtoupper(substr($user->name ?: 'A', 0, 1)));

                // Real per-user course progress (Real Estate Agent Training) —
                // feeds the always-visible academy sidebar's module list and
                // progress bar, not just the training-course page itself.
                try {
                    $sidebarProgress = \App\Services\AgentTrainingCourseService::progressFor($user);
                    $view->with('academyProgress', $sidebarProgress);
                    $view->with('academyOverallPercent', \App\Services\AgentTrainingCourseService::overallPercent($sidebarProgress));
                } catch (\Exception $e) {
                    // Table may not exist yet (fresh install before migration runs).
                    $view->with('academyProgress', []);
                    $view->with('academyOverallPercent', 0);
                }
            } else {
                $view->with('hiddenSections', []);
                $view->with('userNotes', collect());
                $view->with('dueNotesCount', 0);
                $view->with('sysNotifs', collect());
                $view->with('unreadNotifCount', 0);
                $view->with('trainingUser', null);
                $view->with('trainingName', '');
                $view->with('trainingInitial', 'A');
            }
        });
    }
}