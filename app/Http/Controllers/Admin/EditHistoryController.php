<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Dedicated, Administrator-only controller for the Edit History (Audit Trail) page.
 * Route access is additionally hard-gated by the `admin` middleware (see routes/web.php),
 * and this controller double-checks so it is safe even if ever wired up without it.
 */
class EditHistoryController extends Controller
{
    // Only CUD-type actions belong in the Edit History audit trail
    // (login/logout/approve/reject etc. remain in the general Activity Log panel).
    const CUD_ACTIONS = ['create', 'update', 'delete', 'restore'];

    // Modules excluded from self-service Undo even though their logs carry enough
    // data to technically restore/revert — these touch accounts and system config,
    // so changes there should go through their normal admin flows, not a one-click undo.
    const UNDO_EXCLUDED_MODULES = ['Settings'];

    public function index(Request $request)
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'You do not have permission to view Edit History.');
        }

        $query = ActivityLog::with('user')->whereIn('action', self::CUD_ACTIONS);

        if ($request->filled('module')) {
            $query->where('module', $request->string('module'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        if ($request->filled('search')) {
            $term = $request->string('search');
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                  ->orWhere('meta', 'like', "%{$term}%")
                  ->orWhereHas('user', function ($uq) use ($term) {
                      $uq->where('name', 'like', "%{$term}%")
                         ->orWhere('email', 'like', "%{$term}%");
                  });
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        $logs->getCollection()->transform(function ($log) {
            $meta = is_array($log->meta) ? $log->meta : [];
            $log->can_undo = in_array($log->action, ['delete', 'update'], true)
                && !empty($meta)
                && !empty($meta['record_id'] ?? null)
                && !in_array($log->module, self::UNDO_EXCLUDED_MODULES, true);
            return $log;
        });

        $modules = ActivityLog::whereIn('action', self::CUD_ACTIONS)
            ->whereNotNull('module')
            ->select('module')->distinct()->orderBy('module')->pluck('module');

        $editors = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('settings.edit-history', [
            'logs'    => $logs,
            'modules' => $modules,
            'editors' => $editors,
            'filters' => $request->only(['module', 'user_id', 'date_from', 'date_to', 'search']),
        ]);
    }
}