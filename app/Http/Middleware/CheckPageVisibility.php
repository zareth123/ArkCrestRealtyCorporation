<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPageVisibility
{
    // Map route names to their setting key
    const PAGE_MAP = [
        'dashboard'                    => 'dashboard',
        'summary-report'               => 'summary-report',
        'summary-report.yearly'        => 'summary-report',
        'departments.admin'            => 'departments',
        'commission-monitoring'        => 'commission-monitoring',
        'commission-dashboard'         => 'commission-monitoring.dashboard',
        'arkcrest-sales'               => 'arkcrest-sales',
        'cash-advance'                  => 'cash-advance',
        'agent-cash-advance'            => 'agent-cash-advance',
        'calendar'                     => 'calendar',
        'sales-marketing'              => 'sales-marketing',
        'client-database'              => 'client-database',
        'reserved-clients'             => 'client-database.list',
        'property-list'                => 'client-database.property',
        'site-visit-database'          => 'site-visit-database',
        'sales-calendar'               => 'sales-calendar',
        'forms'                        => 'forms',
        'human-resource'               => 'human-resource',
        'hr.employee-data'             => 'human-resource.employee-data',
        'hr.contact-list'              => 'human-resource.contact-list',

        // Content management (News & Updates, Feedback, Awards) — every
        // action route is mapped, not just the index, so a staff member
        // without visibility can't bypass the check by hitting the
        // store/update/destroy endpoints directly.
        'admin.news-updates.index'          => 'admin.news-updates',
        'admin.news-updates.store'          => 'admin.news-updates',
        'admin.news-updates.update'         => 'admin.news-updates',
        'admin.news-updates.destroy'        => 'admin.news-updates',
        'admin.news-updates.media.destroy'  => 'admin.news-updates',

        'admin.testimonials.index'          => 'admin.testimonials',
        'admin.testimonials.store'          => 'admin.testimonials',
        'admin.testimonials.update'         => 'admin.testimonials',
        'admin.testimonials.destroy'        => 'admin.testimonials',
        'admin.testimonials.avatar.destroy' => 'admin.testimonials',

        'admin.awards.index'                => 'admin.awards',
        'admin.awards.store'                => 'admin.awards',
        'admin.awards.update'               => 'admin.awards',
        'admin.awards.destroy'              => 'admin.awards',
        'admin.awards.image.destroy'        => 'admin.awards',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Admins always have full access
        if (!$user || $user->isAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $pageKey   = self::PAGE_MAP[$routeName] ?? null;

        if ($pageKey) {
            $hidden = array_values($user->hidden_pages ?? []);

            if (in_array($pageKey, $hidden)) {
                // Find first visible page to redirect to
                $fallbacks = [
                    'sales-marketing' => 'sales-marketing',
                    'client-database' => 'client-database',
                    'site-visit-database' => 'site-visit-database',
                    'forms' => 'forms',
                    'settings' => 'settings',
                ];
                foreach ($fallbacks as $key => $route) {
                    if (!in_array($key, $hidden)) {
                        return redirect()->route($route);
                    }
                }
                return redirect()->route('settings');
            }
        }

        return $next($request);
    }
}