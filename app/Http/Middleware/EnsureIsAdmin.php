<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Hard-blocks any route it's attached to unless the authenticated user is an Administrator.
 * Unlike CheckPageVisibility (which staff visibility toggles can override), this middleware
 * cannot be bypassed by page-visibility settings — it is a strict permission gate.
 */
class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'You do not have permission to access this page. Administrator access is required.');
        }

        return $next($request);
    }
}
