<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TripSchedule;
use App\Models\CommissionRequestSales;

class TripScheduleController extends Controller
{
    public function saveTeam(\Illuminate\Http\Request $request)
    {
        try {
            $request->validate(['team_name' => 'required|string|max:255']);
            $user = auth()->user();

            // Only save if user doesn't already have a team
            if ($user->team_name) {
                return response()->json(['success' => true]);
            }

            $user->update(['team_name' => $request->team_name]);

            // Auto-add user as agent in the selected team (if not already there)
            $team = \App\Models\SalesTeam::where('team_name', $request->team_name)->first();
            if ($team) {
                $alreadyAgent = \App\Models\SalesAgent::where('team_id', $team->id)
                    ->where(function($q) use ($user) {
                        if (\Schema::hasColumn('sales_agents', 'user_id')) {
                            $q->where('user_id', $user->id)
                              ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($user->name))]);
                        } else {
                            $q->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($user->name))]);
                        }
                    })->exists();

                if (!$alreadyAgent) {
                    $data = [
                        'team_id'   => $team->id,
                        'name'      => $user->name,
                        'is_active' => true,
                    ];
                    if (\Schema::hasColumn('sales_agents', 'user_id'))     $data['user_id']     = $user->id;
                    if (\Schema::hasColumn('sales_agents', 'employee_id')) $data['employee_id'] = $user->employee_id;
                    \App\Models\SalesAgent::create($data);
                } else {
                    // Update existing agent record with user_id/employee_id if missing
                    if (\Schema::hasColumn('sales_agents', 'user_id')) {
                        \App\Models\SalesAgent::where('team_id', $team->id)
                            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($user->name))])
                            ->whereNull('user_id')
                            ->update(['user_id' => $user->id, 'employee_id' => $user->employee_id]);
                    }
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            // Never fail the form submission due to team linking issues
            return response()->json(['success' => true]);
        }
    }

    public function show()
    {
        $user = auth()->user();

        // Sync team_name from sales_agents if user doesn't have one yet
        if ($user && !$user->team_name) {
            try {
                $agentRecord = \App\Models\SalesAgent::where(function($q) use ($user) {
                        if (\Schema::hasColumn('sales_agents', 'user_id')) {
                            $q->where('user_id', $user->id)
                              ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($user->name))]);
                        } else {
                            $q->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($user->name))]);
                        }
                    })->with('team')->first();

                if ($agentRecord && $agentRecord->team) {
                    $user->update(['team_name' => $agentRecord->team->team_name]);
                    if (\Schema::hasColumn('sales_agents', 'user_id') && !$agentRecord->user_id) {
                        $agentRecord->update(['user_id' => $user->id, 'employee_id' => $user->employee_id]);
                    }
                }
            } catch (\Exception $e) {}
        } elseif ($user && $user->team_name) {
            // Clear team_name if user is no longer in any team agent record
            try {
                $stillInTeam = \App\Models\SalesAgent::whereHas('team', function($q) use ($user) {
                        $q->where('team_name', $user->team_name);
                    })
                    ->where(function($q) use ($user) {
                        if (\Schema::hasColumn('sales_agents', 'user_id')) {
                            $q->where('user_id', $user->id)
                              ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($user->name))]);
                        } else {
                            $q->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($user->name))]);
                        }
                    })->exists();

                if (!$stillInTeam) {
                    $user->update(['team_name' => null]);
                }
            } catch (\Exception $e) {}
        }

        try {
            $teams = \App\Models\SalesTeam::orderBy('team_name')->pluck('team_name');
        } catch (\Exception $e) {
            $teams = collect();
        }
        try {
            $properties = \Schema::hasTable('properties') ? \App\Models\Property::orderBy('name')->get() : collect();
        } catch (\Exception $e) {
            $properties = collect();
        }
        return view('tripping', compact('teams', 'properties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'agent_name'    => 'required|string|max:255',
            'team_name'     => 'nullable|string|max:255',
            'client_name'   => 'required|string|max:255',
            'client_email'  => 'nullable|email|max:255',
            'client_phone'  => 'nullable|string|max:50',
            'client_address' => 'nullable|string|max:500',
            'property_name' => 'required|string|max:255',
            'company_name'  => 'nullable|string|max:255',
            'tripping_date' => 'required|date|after_or_equal:today',
            'tripping_time' => 'required',
            'tripping_type' => 'required|string|max:100',
        ], [
            'agent_name.required'    => 'Agent ID is required.',
            'client_name.required'   => 'Client name is required.',
            'client_phone.required'  => 'Client phone number is required.',
            'property_name.required' => 'Property name is required.',
            'tripping_date.required' => 'Visit date is required.',
            'tripping_date.after_or_equal' => 'Visit date must be today or a future date.',
            'tripping_time.required' => 'Visit time is required.',
            'tripping_type.required' => 'Mode of visit is required.',
        ]);

        // Server-side duplicate check — block if same client + same property with active or done tripping within 30 days
        $duplicate = TripSchedule::whereRaw('LOWER(TRIM(client_name)) = ?', [strtolower(trim($request->client_name))])
            ->whereRaw('LOWER(TRIM(property_name)) = ?', [strtolower(trim($request->property_name))])
            ->whereIn('status', ['pending', 'confirmed', 'done'])
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['client_name' => 'This client already has a scheduled or completed site visit for the same property within the last 30 days.'])->withInput();
        }

        $fields = ['agent_name', 'client_name', 'client_email', 'client_phone', 'client_phone_code', 'client_address',
            'property_name', 'company_name', 'tripping_date', 'tripping_time', 'tripping_type'];

        // Include team_name only if column exists
        if (\Schema::hasColumn('tripping_schedules', 'team_name')) {
            $fields[] = 'team_name';
        }

        TripSchedule::create(array_merge($request->only($fields), ['status' => 'confirmed']));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('trip_success', true);
    }

    public function searchClients(Request $request)
    {
        $q = $request->input('q', '');
        $clients = CommissionRequestSales::select('client_name')
            ->when($q, fn($q2) => $q2->where('client_name', 'like', "%{$q}%"))
            ->distinct()->orderBy('client_name')->limit(10)->pluck('client_name');

        return response()->json($clients);
    }

    public function clientDetails(Request $request)
    {
        $name = $request->input('name', '');
        $record = TripSchedule::where('client_name', $name)
            ->whereNotNull('client_email')
            ->latest()->first();

        return response()->json([
            'email' => $record?->client_email ?? '',
            'phone' => $record?->client_phone ?? '',
        ]);
    }

    public function agentDetails(Request $request)
    {
        $empId = trim($request->input('employee_id', ''));
        $user = \App\Models\User::where('employee_id', $empId)->first();
        if (!$user) {
            return response()->json(['found' => false]);
        }
        return response()->json([
            'found'    => true,
            'name'     => $user->name,
            'salutation' => $user->preferred_address ?? '',
        ]);
    }

    public function searchProperties(Request $request)
    {
        $q = $request->input('q', '');
        $props = CommissionRequestSales::select('project_name')
            ->when($q, fn($q2) => $q2->where('project_name', 'like', "%{$q}%"))
            ->distinct()->orderBy('project_name')->limit(10)->pluck('project_name');

        return response()->json($props);
    }

    public function propertyDetails(Request $request)
    {
        $name = $request->input('name', '');
        $record = CommissionRequestSales::where('project_name', $name)->latest()->first();

        return response()->json([
            'company' => $record?->developer_name ?? '',
        ]);
    }

    public function pendingJson()
    {
        $records = TripSchedule::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $userMap = \App\Models\User::whereNotNull('employee_id')->pluck('name', 'employee_id');

        return response()->json($records->map(function ($r) use ($userMap) {
            $agentName = $r->agent_name ?? '';
            if (isset($userMap[$agentName])) $agentName = $userMap[$agentName];
            return [
                'id'            => $r->id,
                'client_name'   => $r->client_name,
                'property_name' => $r->property_name ?? '—',
                'company_name'  => $r->company_name ?: '—',
                'agent_name'    => $agentName ?: '—',
                'client_email'  => $r->client_email ?: '—',
                'client_phone'  => $r->client_phone ? (($r->client_phone_code ?? '+63') . ' ' . ltrim($r->client_phone, '0')) : '—',
                'tripping_date' => $r->tripping_date ? $r->tripping_date->format('M j, Y') : '—',
                'tripping_time' => $r->tripping_time ? \Carbon\Carbon::parse($r->tripping_time)->format('g:i A') : '—',
                'approve_url'   => route('site-visit-database.approve', $r->id),
                'reject_url'    => route('site-visit-database.reject', $r->id),
            ];
        }));
    }

    public function database()
    {
        $records = TripSchedule::orderBy('tripping_date', 'asc')->orderBy('tripping_time', 'asc')->get();

        $userMap = \App\Models\User::whereNotNull('employee_id')->pluck('name', 'employee_id');
        $records->each(function ($r) use ($userMap) {
            if ($r->agent_name && isset($userMap[$r->agent_name])) {
                $r->agent_name = $userMap[$r->agent_name];
            }
        });

        $cancelled = $records->where('status', 'cancelled')->count();
        $rejected  = $records->where('status', 'rejected')->count();

        // Toggle panel data
        $clientList = CommissionRequestSales::select('client_name', 'agent_name', 'project_name', 'date_requested', 'net_tcp', 'status')
            ->orderBy('client_name')->get();

        $propertyList = CommissionRequestSales::select('project_name', 'developer_name', 'property_details', 'net_tcp', 'terms_of_payment')
            ->distinct('project_name')->orderBy('project_name')->get();

        $existingTransactions = CommissionRequestSales::orderBy('date_requested', 'desc')
            ->limit(100)->get();

        return view('site-visit-database', compact(
            'records', 'cancelled', 'rejected',
            'clientList', 'propertyList', 'existingTransactions'
        ));
    }

    public function updateStatus(Request $request, $id)
    {
        $record = TripSchedule::findOrFail($id);
        $request->validate(['status' => 'required|in:pending,confirmed,done,cancelled,rejected']);
        $record->update(['status' => $request->status]);
        return back()->with('success', 'Status updated.');
    }

    public function approve($id)
    {
        TripSchedule::findOrFail($id)->update(['status' => 'confirmed']);
        return back()->with('success', 'Tripping approved.');
    }

    public function reject($id)
    {
        TripSchedule::findOrFail($id)->update(['status' => 'rejected']);
        return back()->with('success', 'Tripping rejected.');
    }

    public function cancel($id)
    {
        TripSchedule::findOrFail($id)->update(['status' => 'cancelled']);
        return back()->with('success', 'Tripping cancelled.');
    }

    public function markDone($id)
    {
        TripSchedule::findOrFail($id)->update(['status' => 'done']);
        return back()->with('success', 'Tripping marked as done.');
    }

    public function reserve($id)
    {
        $trip = TripSchedule::findOrFail($id);
        $trip->update(['status' => 'reserved']);

        $clientName   = $trip->client_name ?? 'Unknown Client';
        $propertyName = $trip->property_name ?? 'Unknown Property';
        $tripDate     = $trip->tripping_date ? $trip->tripping_date->format('F j, Y') : '—';

        // Resolve agent name
        $agentName = $trip->agent_name ?? '';
        $agentUser = \App\Models\User::where('employee_id', $agentName)->first();
        if ($agentUser) $agentName = $agentUser->name;

        // Resolve developer name
        $developerName = $trip->company_name ?? '';
        if (!$developerName && $trip->property_name) {
            $sale = CommissionRequestSales::where('project_name', $trip->property_name)
                ->whereNotNull('developer_name')->latest()->first();
            if ($sale) $developerName = $sale->developer_name;
        }

        // Save to reserved_clients (avoid duplicates)
        \App\Models\ReservedClient::firstOrCreate(
            ['trip_id' => $trip->id],
            [
                'client_name'      => $clientName,
                'client_email'     => $trip->client_email ?? null,
                'client_phone'     => $trip->client_phone ?? null,
                'client_phone_code'=> $trip->client_phone_code ?? '+63',
                'property_name'    => $propertyName,
                'company_name'     => $developerName,
                'agent_name'       => $agentName,
                'tripping_date'    => $trip->tripping_date,
            ]
        );

        // Notify admins and sales admin
        $recipients = \App\Models\User::where('role', 'admin')
            ->orWhere(fn($q) => $q->whereRaw('LOWER(position) LIKE ?', ['%sales admin%']))
            ->get();

        foreach ($recipients as $user) {
            \App\Models\SystemNotification::create([
                'user_id'     => $user->id,
                'type'        => 'trip_done',
                'title'       => 'Ready to Reserve',
                'message'     => "{$clientName} — {$propertyName} on {$tripDate}. Please encode in Client Database.",
                'is_read'     => false,
                'notified_at' => now(),
                'note_id'     => $trip->id,
            ]);
        }

        return back()->with('success', 'Notification sent. Admin will encode in Client Database.');
    }

    public function reschedule(Request $request, $id)
    {
        $request->validate([
            'tripping_date' => 'required|date|after_or_equal:today',
            'tripping_time' => 'nullable',
        ]);
        TripSchedule::findOrFail($id)->update([
            'tripping_date' => $request->tripping_date,
            'tripping_time' => $request->tripping_time,
        ]);
        return back()->with('success', 'Tripping rescheduled.');
    }

    public function destroy($id)
    {
        $trip = TripSchedule::findOrFail($id);
        \App\Models\ActivityLog::log('delete', 'Site Visit Form', "Deleted site visit record for client '{$trip->client_name}' (ID: {$id})", [
            'model_class'        => TripSchedule::class,
            'record_id'          => $trip->id,
            'id'                 => $trip->id,
            'agent_name'         => $trip->agent_name,
            'team_name'          => $trip->team_name,
            'client_name'        => $trip->client_name,
            'client_email'       => $trip->client_email,
            'client_phone'       => $trip->client_phone,
            'client_phone_code'  => $trip->client_phone_code,
            'client_address'     => $trip->client_address,
            'property_name'      => $trip->property_name,
            'company_name'       => $trip->company_name,
            'tripping_date'      => $trip->tripping_date ? (string) $trip->tripping_date : null,
            'tripping_time'      => $trip->tripping_time,
            'tripping_type'      => $trip->tripping_type,
            'status'             => $trip->status,
        ]);
        $trip->delete();
        return back()->with('success', 'Record deleted.');
    }

    public function prefillData($id)
    {
        $trip = TripSchedule::findOrFail($id);

        // Resolve agent name from employee_id if needed
        $agentName = $trip->agent_name ?? '';
        $user = \App\Models\User::where('employee_id', $agentName)->first();
        if ($user) $agentName = $user->name;

        // If company_name is empty, look up developer from CommissionRequestSales by property/project name
        $developerName = $trip->company_name ?? '';
        if (!$developerName && $trip->property_name) {
            $sale = CommissionRequestSales::where('project_name', $trip->property_name)
                ->whereNotNull('developer_name')
                ->latest()->first();
            if ($sale) $developerName = $sale->developer_name;
        }

        return response()->json([
            'client_name'    => $trip->client_name ?? '',
            'client_email'   => $trip->client_email ?? '',
            'client_phone'   => $trip->client_phone ?? '',
            'client_phone_code' => $trip->client_phone_code ?? '+63',
            'project_name'   => $trip->property_name ?? '',
            'agent_name'     => $agentName,
            'date_requested' => $trip->tripping_date ? $trip->tripping_date->format('Y-m-d') : '',
            'developer_name' => $developerName,
        ]);
    }

    public function checkDuplicate(Request $request)
    {
        $client   = trim($request->input('client_name', ''));
        $property = trim($request->input('property_name', ''));

        if (!$client || !$property) {
            return response()->json(['duplicate' => false]);
        }

        $existing = TripSchedule::whereRaw('LOWER(TRIM(client_name)) = ?', [strtolower($client)])
            ->whereRaw('LOWER(TRIM(property_name)) = ?', [strtolower($property)])
            ->whereIn('status', ['pending', 'confirmed', 'done'])
            ->where('created_at', '>=', now()->subDays(30))
            ->select('tripping_date', 'tripping_time', 'status', 'agent_name')
            ->latest()->first();

        if ($existing) {
            // Try to resolve agent name from employee_id
            $agentDisplay = $existing->agent_name ?? '';
            $user = \App\Models\User::where('employee_id', $agentDisplay)->first();
            if ($user) $agentDisplay = $user->name;

            return response()->json([
                'duplicate' => true,
                'date'      => $existing->tripping_date?->format('F j, Y') ?? '',
                'time'      => $existing->tripping_time ?? '',
                'status'    => ucfirst($existing->status),
                'agent'     => $agentDisplay,
            ]);
        }

        return response()->json(['duplicate' => false]);
    }
}