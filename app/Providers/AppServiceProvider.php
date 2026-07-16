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
            } else {
                $view->with('hiddenSections', []);
                $view->with('userNotes', collect());
                $view->with('dueNotesCount', 0);
                $view->with('sysNotifs', collect());
                $view->with('unreadNotifCount', 0);
            }
        });
    }
}
