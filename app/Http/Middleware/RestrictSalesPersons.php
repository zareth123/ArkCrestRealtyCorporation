<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictSalesPersons
{
    // Positions that should only access the site visit form
    const SALES_POSITIONS = ['sales agent', 'sales manager', 'sales person', 'salesperson', 'sales team leader'];

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) return $next($request);
        if ($user->isAdmin()) return $next($request);

        $pos = strtolower(trim($user->position ?? ''));
        $isSales = str_contains($pos, 'sales');

        if ($isSales) {
            // Allow only tripping form and logout
            $allowed = [
                route('tripping', [], false),
                route('tripping.store', [], false),
                route('logout', [], false),
                '/api/tripping',
            ];

            $path = '/' . ltrim($request->path(), '/');
            $isAllowed = collect($allowed)->contains(fn($a) => str_starts_with($path, $a))
                || str_starts_with($path, '/api/tripping');

            if (!$isAllowed) {
                return redirect()->route('tripping');
            }
        }

        return $next($request);
    }
}
