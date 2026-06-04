<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermissionRequest;
use App\Models\SystemNotification;
use App\Models\User;

class PermissionRequestController extends Controller
{
    // Staff submits a permission request
    public function store(Request $request)
    {
        $request->validate([
            'action'       => 'required|in:edit,delete',
            'module'       => 'required|string',
            'record_id'    => 'nullable|integer',
            'record_label' => 'nullable|string',
            'reason'       => 'required|string|min:5|max:500',
        ]);

        $perm = PermissionRequest::create([
            'user_id'      => auth()->id(),
            'action'       => $request->action,
            'module'       => $request->module,
            'record_id'    => $request->record_id,
            'record_label' => $request->record_label,
            'reason'       => $request->reason,
            'status'       => 'pending',
        ]);

        // Notify all admins
        $admins = User::where('role', 'admin')->where('status', 'active')->get();
        foreach ($admins as $admin) {
            SystemNotification::notify(
                $admin->id,
                'permission_request',
                'Permission Request',
                auth()->user()->name . ' is requesting to ' . $request->action . ' a record in ' . $request->module . '.',
            );
        }

        // Notify the staff themselves (confirmation)
        SystemNotification::notify(
            auth()->id(),
            'permission_sent',
            'Request Sent',
            'Your request to ' . $request->action . ' a record in ' . $request->module . ' has been sent to admin for approval.',
        );

        return response()->json([
            'success' => true,
            'message' => 'Permission request sent to admin.',
            'id'      => $perm->id,
        ]);
    }

    // Admin approves or rejects
    public function review(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        $request->validate([
            'status'     => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:300',
        ]);

        $perm = PermissionRequest::findOrFail($id);
        $perm->update([
            'status'      => $request->status,
            'admin_note'  => $request->admin_note,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $action = $request->status === 'approved' ? 'approved' : 'rejected';
        $msg = 'Your request to ' . $perm->action . ' a record in ' . $perm->module . ' has been ' . $action . '.';
        if ($request->admin_note) {
            $msg .= ' Note: ' . $request->admin_note;
        }

        SystemNotification::notify(
            $perm->user_id,
            'permission_' . $action,
            'Request ' . ucfirst($action),
            $msg,
            $perm->id  // store perm_id in note_id field for redirect
        );

        return response()->json([
            'success' => true,
            'status'  => $request->status,
            'perm_id' => $perm->id,
        ]);
    }

    // Check if a specific permission is approved for the current user
    public function check(Request $request)
    {
        $recordId = $request->record_id !== null && $request->record_id !== '' ? (int) $request->record_id : null;

        if (!$recordId) {
            return response()->json(['approved' => false, 'perm_id' => null]);
        }

        // First try exact record_id match (new requests)
        $perm = PermissionRequest::where('user_id', auth()->id())
            ->where('action', $request->action)
            ->where('record_id', $recordId)
            ->where('status', 'approved')
            ->latest()
            ->first();

        // Fallback: match old approved requests where record_id was not saved (null)
        if (!$perm) {
            $perm = PermissionRequest::where('user_id', auth()->id())
                ->where('action', $request->action)
                ->whereNull('record_id')
                ->where('status', 'approved')
                ->latest()
                ->first();
        }

        return response()->json(['approved' => (bool) $perm, 'perm_id' => $perm?->id]);
    }

    // Get permission request by notification ID for redirect
    public function byNotif($notifId)
    {
        $notif = \App\Models\SystemNotification::where('id', $notifId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$notif) return response()->json(['url' => null]);

        // note_id stores the perm_id (set when admin reviews)
        $perm = null;
        if ($notif->note_id) {
            $perm = PermissionRequest::find($notif->note_id);
        }

        // Fallback: find most recent reviewed permission for this user
        if (!$perm) {
            $status = str_contains($notif->type, 'approved') ? 'approved' : 
                     (str_contains($notif->type, 'rejected') ? 'rejected' : null);
            $query = PermissionRequest::where('user_id', auth()->id());
            if ($status) $query->where('status', $status);
            $perm = $query->latest()->first();
        }

        if (!$perm) return response()->json(['url' => null]);

        $moduleUrls = [
            'Departmental Expenses' => '/departments',
            'Commission Monitoring' => '/commission-monitoring',
            'Sales & Marketing'     => '/client-database',
            'Client Database'       => '/client-database',
        ];

        $base = $moduleUrls[$perm->module] ?? '/dashboard';
        $url = $base . '?highlight=' . ($perm->record_id ?? '') . '&status=' . $perm->status . '&action=' . $perm->action;
        if ($perm->record_id) {
            $url .= '&record_id=' . $perm->record_id;
        }

        return response()->json(['url' => $url]);
    }

    // Get pending requests for admin
    public function pending()
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $requests = PermissionRequest::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($requests);
    }

    // Consume (delete) an approved permission after it has been used
    public static function consume(int $userId, string $action, int $recordId): void
    {
        // Only consume permissions that were specifically approved for this record
        // (don't consume null-record_id permissions — those are general approvals)
        PermissionRequest::where('user_id', $userId)
            ->where('action', $action)
            ->where('record_id', $recordId)
            ->where('status', 'approved')
            ->delete();
    }
}
