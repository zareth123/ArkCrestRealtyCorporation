<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictSalesPersons
{
    // Positions that should only access the site visit form
    const SALES_POSITIONS = ['sales agent', 'sales manager', 'sales person', 'salesperson', 'sales team leader', 'sales personnel'];

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) return $next($request);
        if ($user->isAdmin()) return $next($request);

        $pos = strtolower(trim($user->position ?? ''));
        $isSales = in_array($pos, self::SALES_POSITIONS);

        if ($isSales) {
            $path = '/' . ltrim($request->path(), '/');

            // Allow tripping form, logout, login, and ALL api routes
            $isAllowed = $path === '/tripping'
                || $path === '/logout'
                || $path === '/login'
                || str_starts_with($path, '/api/')
                || str_starts_with($path, '/api');

            if (!$isAllowed) {
                return redirect()->route('tripping');
            }
        }

        return $next($request);
    }
}
