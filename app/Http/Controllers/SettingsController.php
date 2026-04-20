<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\TeamMonthlyQuota;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings', $this->getSettingsData());
    }

    public function saveSmtp(Request $request)
    {
        $request->validate([
            'smtp_host'      => 'required|string',
            'smtp_port'      => 'required|integer',
            'smtp_username'  => 'required|email',
            'smtp_password'  => 'nullable|string',
            'smtp_from_name' => 'nullable|string',
        ]);

        $keys = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_from_name'];
        foreach ($keys as $key) {
            \DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $request->$key, 'created_at' => now(), 'updated_at' => now()]
            );
        }
        // Only update password if provided
        if ($request->filled('smtp_password')) {
            \DB::table('app_settings')->updateOrInsert(
                ['key' => 'smtp_password'],
                ['value' => $request->smtp_password, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        return redirect()->route('settings')->with('success', 'Email (SMTP) settings saved!')->with('open_section', 'notifications');
    }

    public function saveNotifications(Request $request)
    {
        $request->validate([
            'notification_emails'   => 'nullable|array',
            'notification_emails.*' => 'nullable|email',
            'notification_time'     => 'required|date_format:H:i',
        ]);

        $emails = implode(',', array_filter(array_map('trim', $request->notification_emails)));

        \DB::table('app_settings')->updateOrInsert(
            ['key' => 'notification_email'],
            ['value' => $emails, 'created_at' => now(), 'updated_at' => now()]
        );
        \DB::table('app_settings')->updateOrInsert(
            ['key' => 'notification_time'],
            ['value' => $request->notification_time, 'created_at' => now(), 'updated_at' => now()]
        );

        return redirect()->route('settings')->with('success', 'Notification settings saved!')->with('open_section', 'notifications');
    }
    
    public function users()
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $pendingUsers = User::where('status', 'pending')->where('email', 'not like', 'pending_%')->orderBy('created_at', 'desc')->get();
        $activeUsers  = User::whereIn('status', ['active', 'pre_registered'])->orderBy('employee_id')->get();
        return view('settings', array_merge(
            $this->getSettingsData(),
            compact('pendingUsers', 'activeUsers')
        ));
    }

    public function approveUser($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $request = request();
        $role = in_array($request->input('role'), ['admin', 'staff']) ? $request->input('role') : 'staff';
        $user = User::findOrFail($id);
        $user->update(['status' => 'active', 'role' => $role]);
        \App\Models\SystemNotification::where('type', 'user_pending')->where('is_read', false)->where('message', 'like', '%'.$user->name.'%')->update(['is_read' => true]);
        ActivityLog::log('approve', 'Settings', "Approved user ID: {$id} with role '{$role}'");
        return redirect()->route('settings')->with('success', 'User approved successfully.')->with('open_section', 'users');
    }

    public function rejectUser($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $user = User::findOrFail($id);
        ActivityLog::log('reject', 'Settings', "Rejected and removed user '{$user->name}' ({$user->email})");
        $user->delete();
        return redirect()->route('settings')->with('success', 'User rejected and removed.')->with('open_section', 'users');
    }

    public function updateRole($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $role = in_array(request('role'), ['admin', 'staff']) ? request('role') : 'staff';
        $u = User::findOrFail($id);
        $u->update(['role' => $role]);
        ActivityLog::log('update', 'Settings', "Changed role of '{$u->name}' to '{$role}'");
        return redirect()->route('settings')->with('success', 'User role updated.')->with('open_section', 'users');
    }

    public function removeUser($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        if ($id == auth()->id()) return redirect()->back()->with('error', 'You cannot remove yourself.');
        $u = User::findOrFail($id);
        ActivityLog::log('delete', 'Settings', "Removed user '{$u->name}' ({$u->email})");
        $u->delete();
        return redirect()->route('settings')->with('success', 'User removed.')->with('open_section', 'users');
    }

    private function getSettingsData(): array
    {
        $settings = \DB::table('app_settings')->pluck('value', 'key');
        $rawEmails          = $settings['notification_email'] ?? '';
        $notificationEmails = array_values(array_filter(array_map('trim', explode(',', $rawEmails))));
        return [
            'notificationEmails' => $notificationEmails,
            'notificationTime'   => $settings['notification_time'] ?? '08:00',
            'smtpHost'           => $settings['smtp_host'] ?? '',
            'smtpPort'           => $settings['smtp_port'] ?? '587',
            'smtpUsername'       => $settings['smtp_username'] ?? '',
            'smtpPassword'       => $settings['smtp_password'] ?? '',
            'smtpFromName'       => $settings['smtp_from_name'] ?? config('app.name'),
            'pendingUsers'       => User::where('status', 'pending')->where('email', 'not like', 'pending_%')->orderBy('created_at', 'desc')->get(),
            'activeUsers'        => User::whereIn('status', ['active', 'pre_registered', 'pending'])->orderBy('employee_id')->get(),
            'activityLogs'       => ActivityLog::with('user')->orderBy('created_at', 'desc')->limit(200)->get(),
            'hiddenSections'     => array_values(json_decode(\DB::table('app_settings')->where('key', 'hidden_pages')->value('value') ?? '[]', true) ?: []),
            'salesTeams'         => \App\Models\SalesTeam::with(['agents', 'quotas' => fn($q) => $q->orderBy('date_from', 'desc')])->orderBy('leader_name')->get(),
            'privacyContent'     => \DB::table('app_settings')->where('key', 'privacy_policy')->value('value') ?? "Data Privacy Notice\n\nArckrest Realty Corporation is committed to protecting the privacy and confidentiality of all personal information collected through this system.\n\nInformation We Collect\n\nWe collect your full name, email address, employee ID, position, and date hired for account management and system access purposes.\n\nHow We Use Your Information\n\n- To manage and authenticate your system account\n- To track activity logs for security and audit purposes\n- To send email notifications related to your account\n- To generate internal reports and analytics\n\nSystem Usage Policy\n\n- Keep your login credentials confidential at all times.\n- Unauthorized access or sharing of credentials is strictly prohibited.\n- All data entered must be accurate and truthful.\n- Misuse may result in account suspension or termination.\n- This system is for authorized Arckrest Realty Corporation employees only.",
            'periodLocks'        => \App\Models\PeriodLock::getLocked(),
            'rejectedTrippings'  => \App\Models\TripSchedule::where('status', 'rejected')->orderBy('updated_at', 'desc')->get()->each(function($r) {
                $user = \App\Models\User::where('employee_id', $r->agent_name)->first();
                if ($user) $r->agent_name = $user->name;
            }),
            'personnelContacts'  => \App\Models\PersonnelContact::orderBy('id')->get(),
        ];
    }

    public function storeTeam(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $request->validate([
            'team_name'     => 'required|string|max:255',
            'sales_manager' => 'required|string|max:255',
            'leader_name'   => 'nullable|string|max:255',
        ]);
        \App\Models\SalesTeam::create([
            'team_name'     => $request->team_name,
            'sales_manager' => $request->sales_manager,
            'leader_name'   => $request->leader_name ?? $request->sales_manager,
        ]);
        return redirect()->route('settings')->with('success', 'Team added.')->with('open_section', 'teams');
    }

    public function destroyTeam($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        \App\Models\SalesTeam::findOrFail($id)->delete();
        return redirect()->route('settings')->with('success', 'Team deleted.')->with('open_section', 'teams');
    }

    public function setTeamQuota(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $request->validate([
            'date_from'    => 'required|date',
            'date_to'      => 'required|date|after_or_equal:date_from',
            'quota_amount' => 'required|numeric|min:0',
        ]);
        \App\Models\TeamMonthlyQuota::create([
            'team_id'      => $id,
            'date_from'    => $request->date_from,
            'date_to'      => $request->date_to,
            'quota_amount' => $request->quota_amount,
        ]);
        return redirect()->route('settings')->with('success', 'Quota set.')->with('open_section', 'teams');
    }

    public function destroyQuota($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        \App\Models\TeamMonthlyQuota::findOrFail($id)->delete();
        return redirect()->route('settings')->with('success', 'Quota removed.')->with('open_section', 'teams');
    }

    public function storeAgent(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $request->validate(['team_id' => 'required|exists:sales_teams,id', 'name' => 'required|string|max:255']);
        \App\Models\SalesAgent::create(['team_id' => $request->team_id, 'name' => $request->name]);
        return redirect()->route('settings')->with('success', 'Agent added.')->with('open_section', 'teams');
    }

    public function destroyAgent($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        \App\Models\SalesAgent::findOrFail($id)->delete();
        return redirect()->route('settings')->with('success', 'Agent removed.')->with('open_section', 'teams');
    }

    public function updateEmployeeInfo(Request $request)
    {
        $request->validate([
            'position'    => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:100',
            'date_hired'  => 'nullable|date',
        ]);
        auth()->user()->update($request->only('position', 'employee_id', 'date_hired'));
        return redirect()->route('settings')->with('success', 'Employee info updated.')->with('open_section', 'employee-info');
    }

    public function updateUserEmployeeInfo(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $request->validate([
            'position'    => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:100|unique:users,employee_id,' . $id,
            'date_hired'  => 'nullable|date',
        ], [
            'employee_id.unique' => 'This Employee ID is already assigned to another employee.',
        ]);
        User::findOrFail($id)->update($request->only('position', 'employee_id', 'date_hired'));
        return redirect()->route('settings')->with('emp_success', 'Employee info updated.')->with('open_section', 'employee-directory');
    }

    public function addEmployeeRecord(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $request->validate([
            'name'        => 'required|string|max:255',
            'employee_id' => 'required|string|max:100|unique:users,employee_id',
            'position'    => 'required|string|max:255',
            'date_hired'  => 'required|date',
        ]);
        User::create([
            'name'        => $request->name,
            'employee_id' => $request->employee_id,
            'position'    => $request->position,
            'date_hired'  => $request->date_hired,
            'email'       => 'pending_' . strtolower(str_replace([' ', '/'], '_', $request->employee_id)) . '@arckrest.local',
            'password'    => bcrypt(\Illuminate\Support\Str::random(32)),
            'role'        => 'staff',
            'status'      => 'pre_registered',
        ]);
        return redirect()->route('settings')->with('emp_success', "Employee '{$request->name}' added successfully.")->with('open_section', $request->has('redirect_to_visibility') ? 'visibility' : 'employee-directory');
    }

    public function updateSecurityQuestion(Request $request)
    {
        $request->validate([
            'security_question' => 'required|string',
            'security_answer'   => 'nullable|string|min:2',
        ]);

        $user = auth()->user();
        $user->security_question = $request->security_question;
        if ($request->filled('security_answer')) {
            $user->security_answer = \Hash::make(strtolower(trim($request->security_answer)));
        }
        $user->save();

        ActivityLog::log('update', 'Settings', "Updated security question for '{$user->name}'");
        return redirect()->route('settings')->with('success', 'Security question saved successfully.')->with('open_section', 'profile');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name'     => 'required|string|max:255',
            'password' => ['nullable', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()->symbols()],
            'avatar'   => 'nullable|image|max:2048',
        ]);

        $data = ['name' => $request->name];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }
            $file = $request->file('avatar');
            $filename = 'avatars/'.time().'_'.$user->id.'.'.$file->getClientOriginalExtension();
            $file->move(public_path('avatars'), basename($filename));
            $data['avatar'] = $filename;
        }

        $user->update($data);
        return redirect()->route('settings')->with('success', 'Profile updated successfully.')->with('open_section', 'profile');
    }

    public function savePrivacyPolicy(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $request->validate(['privacy_content' => 'required|string']);
        \DB::table('app_settings')->updateOrInsert(
            ['key' => 'privacy_policy'],
            ['value' => $request->privacy_content, 'created_at' => now(), 'updated_at' => now()]
        );
        ActivityLog::log('update', 'Settings', 'Updated Privacy Policy content');
        return redirect()->route('settings')->with('success', 'Privacy Policy updated.')->with('open_section', 'privacy');
    }

    // Restore a soft-deleted record from activity log meta
    public function restoreRecord(Request $request, $logId)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        $log = ActivityLog::findOrFail($logId);
        $meta = $log->meta;

        if (!$meta || empty($meta)) {
            return response()->json(['success' => false, 'message' => 'No record data available to restore.'], 422);
        }

        try {
            $module = $log->module;
            $restoreMeta = $meta;
            unset($restoreMeta['id']);

            // Departmental Expenses
            if (in_array($module, ['Departmental Expenses', 'Commission Monitoring'])) {
                // Try soft-delete restore first
                if (!empty($meta['id'])) {
                    $existing = \App\Models\CommissionRequest::withTrashed()->find($meta['id']);
                    if ($existing && $existing->trashed()) {
                        $existing->restore();
                        $log->delete();
                        return response()->json(['success' => true, 'message' => 'Record restored successfully.']);
                    }
                }
                $fillable = ['control_number','requestor_name','department','category','date_requested',
                    'requested_amount','status','date_released','total_expenses','amount_returned','date_of_amount_returned'];
                $data = array_filter(array_intersect_key($restoreMeta, array_flip($fillable)), fn($v) => $v !== null);
                \App\Models\CommissionRequest::create($data);
                $log->delete();
                return response()->json(['success' => true, 'message' => 'Record restored to Departmental Expenses.']);
            }

            // Sales & Marketing / Client Database
            if ($module === 'Sales & Marketing') {
                $fillable = ['developer_name','date_requested','reservation_date','date_of_downpayment',
                    'project_name','property_details','block_lot_number','price_sqm','lot_area','tcp',
                    'discount','client_name','terms_of_payment','agent_name','number_of_units','net_tcp',
                    'commission_percent','commission','mode_of_payment','remarks','date_released','status','client_status'];
                $data = array_filter(array_intersect_key($restoreMeta, array_flip($fillable)), fn($v) => $v !== null);
                \App\Models\CommissionRequestSales::create($data);
                $log->delete();
                return response()->json(['success' => true, 'message' => 'Record restored to Client Database.']);
            }

            return response()->json(['success' => false, 'message' => "Restore not supported for module: {$module}"], 422);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to restore: ' . $e->getMessage()], 500);
        }
    }

    // Permanently delete a log entry (removes from deleted records list)
    public function permanentDeleteRecord($logId)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $log = ActivityLog::findOrFail($logId);
        $log->delete();
        return response()->json(['success' => true, 'message' => 'Record permanently removed from history.']);
    }

    public function lockPeriod(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2020|max:2099',
            'reason' => 'nullable|string|max:255',
        ]);
        \App\Models\PeriodLock::firstOrCreate(
            ['month' => $request->month, 'year' => $request->year],
            ['locked_by' => auth()->user()->name, 'reason' => $request->reason]
        );
        ActivityLog::log('update', 'Settings', "Locked period: " . date('F', mktime(0,0,0,$request->month,1)) . " {$request->year}");
        return redirect()->route('settings')->with('success', 'Period locked.')->with('open_section', 'period-lock');
    }

    public function unlockPeriod($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $lock = \App\Models\PeriodLock::findOrFail($id);
        ActivityLog::log('update', 'Settings', "Unlocked period: " . date('F', mktime(0,0,0,$lock->month,1)) . " {$lock->year}");
        $lock->delete();
        return redirect()->route('settings')->with('success', 'Period unlocked.')->with('open_section', 'period-lock');
    }

    public function getUserVisibility($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $user = User::findOrFail($id);
        return response()->json(['hidden_pages' => $user->hidden_pages ?? []]);
    }

    public function savePageVisibility(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        if (!$request->has('visibility_submitted')) {
            return redirect()->route('settings')->with('open_section', 'visibility');
        }
        $userId = $request->input('visibility_user_id');
        $user = User::findOrFail($userId);

        $allPages = [
            'dashboard','departments','summary-report','commission-monitoring','commission-monitoring.dashboard',
            'calendar','sales-marketing','client-database','client-database.list',
            'client-database.property','site-visit-database','sales-calendar','forms',
            'settings.users','settings.employee','settings.personnel','settings.teams',
            'settings.period-lock','settings.visibility','settings.activity','settings.deleted','settings.permissions',
        ];

        $visiblePages = $request->input('visible_pages', []);
        $hidden = array_values(array_diff($allPages, $visiblePages));

        $user->update(['hidden_pages' => $hidden]);
        ActivityLog::log('update', 'Settings', "Updated page visibility for user '{$user->name}'.");
        return redirect()->route('settings', ['vis_user' => $userId])->with('success', "Visibility updated for {$user->name}.")->with('open_section', 'visibility');
    }

    public function updatePeriodLock(Request $request)
    {
        $validated = $request->validate([
            'lock_period_months' => 'required|integer|min:0|max:12'
        ]);
        
        // Store in session or database
        session(['lock_period_months' => $validated['lock_period_months']]);
        
        return response()->json([
            'success' => true,
            'message' => 'Period lock settings updated successfully'
        ]);
    }

    public function storePersonnelContact(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'company'  => 'nullable|string|max:255',
            'email'    => 'nullable|email|max:255',
            'phone'    => 'nullable|string|max:50',
            'facebook' => 'nullable|string|max:255',
            'address'  => 'nullable|string|max:500',
        ]);
        \App\Models\PersonnelContact::create($data);
        return back()->with('success', 'Contact added.')->with('open_section', 'personnel-contacts');
    }

    public function updatePersonnelContact(Request $request, $id)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'company'  => 'nullable|string|max:255',
            'email'    => 'nullable|email|max:255',
            'phone'    => 'nullable|string|max:50',
            'facebook' => 'nullable|string|max:255',
            'address'  => 'nullable|string|max:500',
        ]);
        \App\Models\PersonnelContact::findOrFail($id)->update($data);
        return back()->with('success', 'Contact updated.')->with('open_section', 'personnel-contacts');
    }

    public function destroyPersonnelContact($id)
    {
        \App\Models\PersonnelContact::findOrFail($id)->delete();
        return back()->with('success', 'Contact removed.')->with('open_section', 'personnel-contacts');
    }
}
