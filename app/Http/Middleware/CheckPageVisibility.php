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
        'cash-advance'                  => 'cash-advance',
        'calendar'                     => 'calendar',
        'sales-marketing'              => 'sales-marketing',
        'forms'                        => 'forms',
        'human-resource'               => 'human-resource',
        'hr.employee-data'             => 'human-resource.employee-data',
        'hr.contact-list'              => 'human-resource.contact-list',
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
