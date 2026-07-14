<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
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
