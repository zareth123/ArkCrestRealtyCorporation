<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionRequestSales;
use App\Models\ActivityLog;
use App\Models\SalesTeam;

class SalesMarketingController extends Controller
{
    public function index(Request $request)
    {
        // Date range filter (default: current month)
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo   = $request->input('date_to',   date('Y-m-t'));

        $totalNetTcp  = CommissionRequestSales::whereNotNull('date_of_downpayment')
            ->whereBetween('date_of_downpayment', [$dateFrom, $dateTo])
            ->where('client_status', '!=', 'Cancelled')
            ->sum('net_tcp');
        $totalClients = CommissionRequestSales::whereBetween('date_requested', [$dateFrom, $dateTo])->distinct('client_name')->count('client_name');
        $totalRecords = CommissionRequestSales::whereBetween('date_requested', [$dateFrom, $dateTo])->count();

        // Units, Pending, Cancelled, Total Reservation for the date range (based on date_of_downpayment)
        $units = CommissionRequestSales::whereNotNull('date_of_downpayment')
            ->whereBetween('date_of_downpayment', [$dateFrom, $dateTo])
            ->where('client_status', '!=', 'Cancelled')
            ->whereNotNull('block_lot_number')
            ->distinct('block_lot_number')->count('block_lot_number');

        $grossSalesFromClient = CommissionRequestSales::whereNotNull('date_of_downpayment')
            ->whereBetween('date_of_downpayment', [$dateFrom, $dateTo])
            ->where('client_status', '!=', 'Cancelled')->sum('net_tcp');

        $pendingReservation = CommissionRequestSales::whereBetween('reservation_date', [$dateFrom, $dateTo])
            ->where(function($q) { $q->whereNull('downpayment_status')->orWhereNotIn('downpayment_status', ['Paid','Spot Paid']); })
            ->where(function($q) { $q->whereNull('client_status')->orWhere('client_status','!=','Cancelled'); })->count();

        $cancelledReservation = CommissionRequestSales::whereBetween('reservation_date', [$dateFrom, $dateTo])
            ->where('client_status','Cancelled')->count();

        $totalReservation = $units + $pendingReservation - $cancelledReservation;

        // Get all teams with agents and quotas
        $teams = SalesTeam::with(['agents', 'quotas'])->orderBy('leader_name')->get();

        // Build team performance from commission_requests_sales
        $teamPerformance = $teams->map(function ($team) use ($dateFrom, $dateTo) {
            // All members = leader + agents
            $memberNames = $team->agents->pluck('name')->push($team->leader_name)->toArray();

            // Per-agent sales from client database — only records with downpayment
            $rawAgentSales = CommissionRequestSales::selectRaw('agent_name, SUM(net_tcp) as total_sales, SUM(commission) as total_commission, COUNT(*) as deals')
                ->whereIn('agent_name', $memberNames)
                ->whereNotNull('date_of_downpayment')
                ->whereBetween('date_of_downpayment', [$dateFrom, $dateTo])
                ->where('client_status', '!=', 'Cancelled')
                ->groupBy('agent_name')
                ->get();

            // Normalize and merge typo variants
            $mergedAgents = [];
            foreach ($rawAgentSales as $row) {
                $key = preg_replace('/[^A-Z0-9 ]/', '', strtoupper(preg_replace('/\s+/', ' ', trim($row->agent_name))));
                if (!isset($mergedAgents[$key])) {
                    $mergedAgents[$key] = ['agent_name' => trim($row->agent_name), 'total_sales' => 0, 'total_commission' => 0, 'deals' => 0];
                }
                $mergedAgents[$key]['total_sales']      += $row->total_sales;
                $mergedAgents[$key]['total_commission']  += $row->total_commission;
                $mergedAgents[$key]['deals']             += $row->deals;
            }
            usort($mergedAgents, fn($a, $b) => $b['total_sales'] <=> $a['total_sales']);
            $agentSales = collect($mergedAgents)->map(fn($r) => (object)$r);

            // Find applicable quota for this date range
            $quota = $team->quotas
                ->filter(fn($q) => $q->date_from <= $dateTo && $q->date_to >= $dateFrom)
                ->sortByDesc('date_from')
                ->first();

            return [
                'team'             => $team,
                'agentSales'       => $agentSales,
                'teamTotal'        => $agentSales->sum('total_sales'),
                'teamCommission'   => $agentSales->sum('total_commission'),
                'teamDeals'        => $agentSales->sum('deals'),
                'quota'            => $quota,
            ];
        })->sortByDesc('teamTotal')->values();

        // Fallback flat list if no teams configured
        // Fetch raw then normalize agent names in PHP to merge typo variants
        $rawPerformers = CommissionRequestSales::selectRaw('agent_name, SUM(net_tcp) as total_sales, SUM(commission) as total_commission, COUNT(*) as deals')
            ->whereNotNull('agent_name')
            ->whereNotNull('date_of_downpayment')
            ->whereBetween('date_of_downpayment', [$dateFrom, $dateTo])
            ->where('client_status', '!=', 'Cancelled')
            ->groupBy('agent_name')
            ->orderByDesc('total_sales')
            ->get();

        // Normalize: uppercase + collapse spaces + normalize punctuation
        $merged = [];
        foreach ($rawPerformers as $row) {
            $key = preg_replace('/[^A-Z0-9 ]/', '', strtoupper(preg_replace('/\s+/', ' ', trim($row->agent_name))));
            if (!isset($merged[$key])) {
                $merged[$key] = ['agent_name' => trim($row->agent_name), 'total_sales' => 0, 'total_commission' => 0, 'deals' => 0, 'position' => null];
            }
            $merged[$key]['total_sales']       += $row->total_sales;
            $merged[$key]['total_commission']   += $row->total_commission;
            $merged[$key]['deals']              += $row->deals;
        }

        // Look up position from users table AND team management roles
        $agentNames = collect($merged)->pluck('agent_name');
        $userPositions = \App\Models\User::whereIn('name', $agentNames)->pluck('position', 'name');

        // Build role map from Team Management (leader_name → Team Leader, sales_manager → Sales Manager)
        $allTeams = \App\Models\SalesTeam::all();
        $teamRoleMap = [];
        foreach ($allTeams as $t) {
            if ($t->leader_name) {
                $teamRoleMap[strtolower(trim($t->leader_name))] = 'Team Leader';
            }
            if ($t->sales_manager) {
                // Sales Manager overrides Team Leader if same person
                $teamRoleMap[strtolower(trim($t->sales_manager))] = 'Sales Manager';
            }
        }

        foreach ($merged as $key => &$agent) {
            $nameKey = strtolower(trim($agent['agent_name']));
            // Team Management role takes priority over users.position
            if (isset($teamRoleMap[$nameKey])) {
                $agent['position'] = $teamRoleMap[$nameKey];
            } else {
                $agent['position'] = $userPositions[$agent['agent_name']] ?? null;
            }
        }
        unset($agent);

        usort($merged, fn($a, $b) => $b['total_sales'] <=> $a['total_sales']);
        $topPerformers = collect($merged)->map(fn($r) => (object)$r);

        // Today's summary for banner
        $today = \Carbon\Carbon::today()->toDateString();
        $todayTrips    = \App\Models\TripSchedule::whereDate('tripping_date', $today)->whereIn('status', ['confirmed', 'pending'])->count();
        $todayReleases = \App\Models\CommissionRequestSales::whereDate('date_released', $today)->where('status', 'Not Yet Released')->count();
        $todayEvents   = \App\Models\CommissionRequestSales::where(function($q) use ($today) {
            $q->whereDate('reservation_date', $today)->orWhereDate('date_of_downpayment', $today);
        })->count();

        // Chart data for team performance
        $chartTeamData = $teamPerformance->map(function($t) use ($topPerformers) {
            // Try to match members from topPerformers by name (loose match)
            $memberSales = collect($t['agentSales']);

            // If no agent sales found via team membership, try matching from topPerformers
            if ($memberSales->isEmpty() || $memberSales->sum('total_sales') == 0) {
                $allMemberNames = $t['team']->agents->pluck('name')
                    ->push($t['team']->leader_name)
                    ->filter()
                    ->map(fn($n) => strtolower(trim($n)))
                    ->toArray();

                $memberSales = $topPerformers->filter(function($p) use ($allMemberNames) {
                    $normalized = strtolower(trim($p->agent_name));
                    foreach ($allMemberNames as $m) {
                        if ($normalized === $m || str_contains($normalized, $m) || str_contains($m, $normalized)) {
                            return true;
                        }
                    }
                    return false;
                })->map(function($p) {
                    return (object)['agent_name' => $p->agent_name, 'total_sales' => $p->total_sales];
                })->values();
            }

            return [
                'team'    => $t['team']->team_name,
                'total'   => (float) $memberSales->sum('total_sales') ?: (float) $t['teamTotal'],
                'members' => $memberSales->map(function($a) {
                    return ['name' => $a->agent_name, 'sales' => (float) $a->total_sales];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        return view('sales-marketing', compact(
            'totalNetTcp', 'totalClients', 'totalRecords',
            'topPerformers', 'teamPerformance', 'dateFrom', 'dateTo', 'teams',
            'todayTrips', 'todayReleases', 'todayEvents',
            'units', 'grossSalesFromClient', 'pendingReservation', 'cancelledReservation', 'totalReservation',
            'chartTeamData'
        ));
    }
    

    public function storeReservedClient(Request $request)
    {
        \App\Models\ReservedClient::create($request->only([
            'trip_id', 'client_name', 'client_email', 'client_phone', 'client_phone_code',
            'address', 'source', 'property_name', 'company_name', 'agent_name',
        ]));
        return redirect()->route('reserved-clients')->with('success', 'Client added to list.');
    }

    public function reservedClients()
    {
        $clients = CommissionRequestSales::select(
                'id', 'client_name', 'agent_name', 'project_name',
                'date_requested', 'status', 'client_status'
            )->orderBy('client_name')->get()
            ->groupBy('client_name')->map(fn($g) => $g->first())->values();

        $tripData = \App\Models\TripSchedule::select('client_name', 'client_email', 'client_phone', 'client_phone_code')
            ->whereNotNull('client_name')->get()->groupBy('client_name');

        $contactMap = \App\Models\Client::all()->keyBy('name');

        return view('reserved-clients', compact('clients', 'tripData', 'contactMap'));
    }

    public function storeClient(Request $request)
    {
        \App\Models\Client::create([
            'name'    => $request->name,
            'address' => $request->address,
            'emails'  => array_filter($request->input('emails', [])),
            'phones'  => array_filter($request->input('phones', [])),
            'notes'   => $request->notes,
        ]);
        return redirect()->route('reserved-clients')->with('success', 'Client added.');
    }

    public function updateClient(Request $request, $id)
    {
        $client = \App\Models\Client::findOrFail($id);
        $client->update([
            'name'    => $request->name,
            'address' => $request->address,
            'emails'  => array_filter($request->input('emails', [])),
            'phones'  => array_filter($request->input('phones', [])),
            'notes'   => $request->notes,
        ]);
        return redirect()->route('reserved-clients')->with('success', 'Client updated.');
    }

    public function destroyClient($id)
    {
        \App\Models\Client::findOrFail($id)->delete();
        return redirect()->route('reserved-clients')->with('success', 'Client deleted.');
    }

    public function getClient($id)
    {
        return response()->json(\App\Models\Client::findOrFail($id));
    }

    public function getReservedClient($id)
    {
        return response()->json(\App\Models\ReservedClient::findOrFail($id));
    }

    public function updateReservedClient(Request $request, $id)
    {
        $client = \App\Models\ReservedClient::findOrFail($id);
        $client->update($request->only([
            'client_name', 'client_email', 'client_phone', 'client_phone_code',
            'address', 'source', 'property_name', 'company_name', 'agent_name',
        ]));
        return redirect()->route('reserved-clients')->with('success', 'Client updated.');
    }

    public function destroyReservedClient($id)
    {
        \App\Models\ReservedClient::findOrFail($id)->delete();
        return redirect()->route('reserved-clients')->with('success', 'Client deleted.');
    }

    public function prefillCommission($id)
    {
        $r = CommissionRequestSales::findOrFail($id);
        return response()->json([
            'client_name'       => $r->client_name ?? '',
            'project_name'      => $r->project_name ?? '',
            'agent_name'        => $r->agent_name ?? '',
            'net_tcp'           => $r->net_tcp ?? '',
            'reservation_date'  => $r->reservation_date ? $r->reservation_date->format('Y-m-d') : '',
            'terms_of_payment'  => $r->terms_of_payment ?? '',
            'number_of_units'   => $r->number_of_units ?? 1,
            'commission_percent'=> $r->commission_percent ?? '',
            'date_requested'    => $r->date_requested ? $r->date_requested->format('Y-m-d') : '',
            'developer_name'    => $r->developer_name ?? '',
            'block_lot_number'  => $r->block_lot_number ?? '',
            'price_sqm'         => $r->price_sqm ?? '',
            'lot_area'          => $r->lot_area ?? '',
            'discount'          => $r->discount ?? '',
            'mode_of_payment'   => $r->mode_of_payment ?? '',
        ]);
    }

    public function clientDatabase()
    {
        $commissionRequests = CommissionRequestSales::orderBy('date_requested', 'asc')->get();
        return view('client-database', compact('commissionRequests'));
    }

    public function propertyList()
    {
        $properties = CommissionRequestSales::whereNotNull('project_name')
            ->orderBy('created_at', 'asc')
            ->get();
        return view('property-list', compact('properties'));
    }

    private function validationRules(): array
    {
        return [
            'developer_name'      => 'nullable|string|max:255',
            'date_requested'      => 'nullable|date',
            'reservation_date'    => 'required|date',
            'date_of_downpayment' => 'required|date',
            'project_name'        => 'required|string|max:255',
            'property_details'    => 'nullable|string|max:255',
            'block_lot_number'    => 'required|string|max:255',
            'client_name'         => 'required|string|max:255',
            'lot_area'            => 'required|numeric',
            'price_sqm'           => 'nullable|numeric',
            'tcp'                 => 'nullable|numeric',
            'discount'            => 'nullable|numeric',
            'net_tcp'             => 'nullable|numeric',
            'terms_of_payment'    => 'required|string|max:255',
            'agent_name'          => 'required|string|max:255',
            'number_of_units'     => 'required|integer|min:1',
            'commission_percent'  => 'nullable|numeric|min:0|max:100',
            'commission'          => 'nullable|numeric',
            'mode_of_payment'     => 'nullable|string|max:255',
            'remarks'             => 'nullable|string',
            'date_released'       => 'nullable|date',
            'status'              => 'nullable|string|max:50',
            // downpayment_status, downpayment_amount, downpayment_terms,
            // downpayment_per_term, downpayment_date are managed separately
            // via the downpayment modal — never overwritten by the edit form
        ];
    }

    public function checkDuplicate(Request $request)
{
    $clientName    = trim($request->query('client_name', ''));
    $projectName   = trim($request->query('project_name', ''));
    $developerName = trim($request->query('developer_name', ''));
    $blockLot      = trim($request->query('block_lot_number', ''));

    if ($clientName === '' || $projectName === '') {
        return response()->json(['duplicate' => false]);
    }

    $query = CommissionRequestSales::whereRaw('LOWER(client_name) = ?', [strtolower($clientName)])
        ->whereRaw('LOWER(project_name) = ?', [strtolower($projectName)]);

    if ($developerName !== '') {
        $query->whereRaw('LOWER(developer_name) = ?', [strtolower($developerName)]);
    }

    if ($blockLot !== '') {
        $query->whereRaw('LOWER(block_lot_number) = ?', [strtolower($blockLot)]);
    }

    $existing = $query->first();

    return response()->json([
        'duplicate' => (bool) $existing,
        'id' => $existing->id ?? null,
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());
        if (empty($validated['status'])) {
            $validated['status'] = 'Not Yet Released';
        }
        $record = CommissionRequestSales::create($validated);
        ActivityLog::log('create', 'Sales & Marketing', "Added sale entry for client '{$validated['client_name']}' (Agent: {$validated['agent_name']})");

        // Email admins about new commission entry
        $body = "<b>Client:</b> {$validated['client_name']}<br>
                 <b>Project:</b> {$validated['project_name']}<br>
                 <b>Agent:</b> {$validated['agent_name']}<br>
                 <b>Net TCP:</b> ₱" . number_format($validated['net_tcp'] ?? 0, 2) . "<br>" .
                 (!empty($validated['date_released']) ? "<b>Release Date:</b> " . \Carbon\Carbon::parse($validated['date_released'])->format('F j, Y') . "<br>" : '') .
                 (!empty($validated['date_of_downpayment']) ? "<b>Downpayment Date:</b> " . \Carbon\Carbon::parse($validated['date_of_downpayment'])->format('F j, Y') . "<br>" : '');
        \App\Services\AdminEmailNotifier::send(
            'New Commission Entry — ' . $validated['client_name'],
            'New Commission Entry Added',
            $body
        );

        return redirect()->route('client-database')->with('success', 'Commission request added successfully!');
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        // Non-admins can edit directly. Downpayment-paid records remain locked as a business rule.
        if (!$user->isAdmin()) {
            $record = CommissionRequestSales::findOrFail($id);
            $lockedStatuses = ['Paid', 'Spot Paid'];
            if (in_array($record->downpayment_status, $lockedStatuses) && $record->downpayment_amount > 0) {
                return response()->json(['error' => 'This record is locked. Downpayment has already been marked as paid and cannot be edited by staff.'], 403);
            }
        }

        $commissionRequest = CommissionRequestSales::findOrFail($id);
        $oldStatus = $commissionRequest->status;

        try {
            $validated = $request->validate($this->validationRules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first();
            return response()->json(['error' => $msg], 422);
        }

        // Preserve downpayment fields — never overwrite from the edit form
        unset(
            $validated['downpayment_status'],
            $validated['downpayment_amount'],
            $validated['downpayment_terms'],
            $validated['downpayment_per_term'],
            $validated['downpayment_date']
        );

        $commissionRequest->update($validated);
        ActivityLog::log('update', 'Sales & Marketing', "Updated sale entry for client '{$validated['client_name']}' (ID: {$id})");

        // Email admins when commission is marked as Released
        if (!empty($validated['status']) && $validated['status'] === 'Released' && $oldStatus !== 'Released') {
            $body = "<b>Client:</b> {$validated['client_name']}<br>
                     <b>Project:</b> {$validated['project_name']}<br>
                     <b>Agent:</b> {$validated['agent_name']}<br>
                     <b>Commission:</b> ₱" . number_format($validated['commission'] ?? 0, 2) . "<br>
                     <b>Release Date:</b> " . (!empty($validated['date_released']) ? \Carbon\Carbon::parse($validated['date_released'])->format('F j, Y') : 'N/A');
            \App\Services\AdminEmailNotifier::send(
                'Commission Released — ' . $validated['client_name'],
                '✅ Commission Marked as Released',
                $body
            );
        }

        if ($request->expectsJson() || $request->isJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Commission request updated successfully!');
    }

    public function show($id)
    {
        $commissionRequest = CommissionRequestSales::findOrFail($id);
        return response()->json($commissionRequest);
    }

    public function updateClientStatus(Request $request, $id)
    {
        $record = CommissionRequestSales::findOrFail($id);
        $oldStatus = $record->client_status;

        // Block Done only if there is absolutely no downpayment activity yet
        if ($request->client_status === 'Done') {
            $dpStatus = $record->downpayment_status;

            // Check installments — at least 1 paid is enough
            $installments = \App\Models\DownpaymentInstallment::where('commission_request_sales_id', $id)->get();
            $hasAnyInstallmentPaid = $installments->contains(fn($i) => $i->is_paid);

            // Check if downpayment_status is set to something meaningful (Spot, Paid, Partial, etc.)
            $hasDownpaymentStatus = !empty($dpStatus) && !in_array($dpStatus, ['— Set —', null]);

            if (!$hasAnyInstallmentPaid && !$hasDownpaymentStatus) {
                return back()->with('error', 'Cannot set to Done — client must have at least one downpayment recorded first.');
            }
        }

        $record->update(['client_status' => $request->client_status ?: null]);

        // Fire notification when status is set to Done (downpayment received)
        if ($request->client_status === 'Done' && $oldStatus !== 'Done') {
            $clientName  = $record->client_name ?? 'Unknown Client';
            $projectName = $record->project_name ?? 'Unknown Project';

            // Finance positions to notify
            $financePositions = ['chief in finance', 'finance secretary', 'finance officer', 'finance'];

            $recipients = \App\Models\User::where('status', 'active')
                ->where(function($q) use ($financePositions) {
                    $q->where('role', 'admin');
                    foreach ($financePositions as $pos) {
                        $q->orWhereRaw('LOWER(TRIM(position)) LIKE ?', ["%{$pos}%"]);
                    }
                })
                ->get();

            foreach ($recipients as $user) {
                \App\Models\SystemNotification::create([
                    'user_id'     => $user->id,
                    'type'        => 'client_done',
                    'title'       => 'Client Marked as Done',
                    'message'     => "{$clientName} — {$projectName} is marked Done. Please encode in Commission Monitoring.",
                    'is_read'     => false,
                    'notified_at' => now(),
                    'note_id'     => $record->id,
                ]);
            }
        }

        return back()->with('success', 'Status updated.');
    }

    public function getInstallments($id)
    {
        $installments = \App\Models\DownpaymentInstallment::where('commission_request_sales_id', $id)
            ->orderBy('term_number')->get();
        return response()->json($installments->map(function($inst) {
            return [
                'id'          => $inst->id,
                'term_number' => $inst->term_number,
                'amount'      => $inst->amount,
                'is_paid'     => $inst->is_paid,
                'paid_at'     => $inst->paid_at,
                'paid_date'   => $inst->paid_date ?? ($inst->paid_at ? \Carbon\Carbon::parse($inst->paid_at)->format('Y-m-d') : null),
            ];
        }));
    }

    public function setupInstallments(Request $request, $id)
    {
        $terms = (int) $request->terms;
        if ($terms < 1 || $terms > 120) return response()->json(['error' => 'Invalid terms'], 422);

        // Delete existing unpaid installments only
        \App\Models\DownpaymentInstallment::where('commission_request_sales_id', $id)
            ->where('is_paid', false)->delete();

        // Create new installments
        $existing = \App\Models\DownpaymentInstallment::where('commission_request_sales_id', $id)
            ->pluck('term_number')->toArray();

        for ($i = 1; $i <= $terms; $i++) {
            if (!in_array($i, $existing)) {
                \App\Models\DownpaymentInstallment::create([
                    'commission_request_sales_id' => $id,
                    'term_number' => $i,
                    'amount' => null,
                    'is_paid' => false,
                ]);
            }
        }

        // Update terms count and total amount on parent record
        $updates = ['downpayment_terms' => $terms, 'downpayment_status' => $terms . ' month' . ($terms > 1 ? 's' : '')];
        if ($request->total_amount) {
            $updates['downpayment_amount'] = $request->total_amount;
            $updates['downpayment_per_term'] = round($request->total_amount / $terms, 2);
        }

        // NEW: setting up an installment plan means the client is now
        // actively paying — auto-set client_status to Pending, unless the
        // client has already been marked Cancelled.
        $dpRecord = CommissionRequestSales::findOrFail($id);
        if ($dpRecord->client_status !== 'Cancelled') {
            $updates['client_status'] = 'Pending';
        }
        $dpRecord->update($updates);

        return response()->json(\App\Models\DownpaymentInstallment::where('commission_request_sales_id', $id)
            ->orderBy('term_number')->get());
    }

    public function updateInstallmentAmount(Request $request, $id)
    {
        $inst = \App\Models\DownpaymentInstallment::findOrFail($id);
        if ($inst->is_paid) return response()->json(['error' => 'Already paid'], 422);
        $inst->update(['amount' => $request->amount]);
        return response()->json(['success' => true]);
    }

    public function markInstallmentPaid(Request $request, $id)
    {
        $inst = \App\Models\DownpaymentInstallment::findOrFail($id);
        $updates = ['is_paid' => true, 'paid_at' => now()];
        if ($request->filled('paid_date')) {
            $updates['paid_date'] = $request->paid_date;
        }
        // Auto-create paid_date column if missing
        if (!empty($updates['paid_date']) && !\Schema::hasColumn('downpayment_installments', 'paid_date')) {
            try { \Schema::table('downpayment_installments', fn($t) => $t->date('paid_date')->nullable()->after('paid_at')); } catch (\Exception $e) {}
        }
        $inst->update($updates);

        // Update parent downpayment_status
        $parentId = $inst->commission_request_sales_id;
        $all   = \App\Models\DownpaymentInstallment::where('commission_request_sales_id', $parentId)->count();
        $paid  = \App\Models\DownpaymentInstallment::where('commission_request_sales_id', $parentId)->where('is_paid', true)->count();
        $status = $paid === $all ? 'Paid' : 'Partial';

        // NEW: once every term is paid, the client is fully Done too.
        $parentUpdates = ['downpayment_status' => $status];
        if ($status === 'Paid') {
            $parentUpdates['client_status'] = 'Done';
        }
        CommissionRequestSales::findOrFail($parentId)->update($parentUpdates);

        return response()->json(['success' => true, 'status' => $status]);
    }

    public function unmarkInstallmentPaid(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $inst = \App\Models\DownpaymentInstallment::findOrFail($id);
        $inst->update(['is_paid' => false, 'paid_at' => null]);

        // Recalculate parent downpayment_status
        $parentId = $inst->commission_request_sales_id;
        $all  = \App\Models\DownpaymentInstallment::where('commission_request_sales_id', $parentId)->count();
        $paid = \App\Models\DownpaymentInstallment::where('commission_request_sales_id', $parentId)->where('is_paid', true)->count();
        $status = $paid === 0 ? null : ($paid === $all ? 'Paid' : 'Partial');
        CommissionRequestSales::findOrFail($parentId)->update(['downpayment_status' => $status]);

        return response()->json(['success' => true, 'status' => $status]);
    }

    public function updateDownpaymentInstallment(Request $request, $id)
    {
        $record = CommissionRequestSales::findOrFail($id);
        $amount = (float) $request->downpayment_amount;
        $terms  = (int) $request->downpayment_terms;
        $perTerm = $terms > 0 ? round($amount / $terms, 2) : 0;
        $record->update([
            'downpayment_amount'   => $amount,
            'downpayment_terms'    => $terms,
            'downpayment_per_term' => $perTerm,
        ]);
        return response()->json(['success' => true, 'per_term' => $perTerm]);
    }

    public function updateDownpaymentStatus(Request $request, $id)
    {
        $record = CommissionRequestSales::findOrFail($id);

        // Non-admin cannot change a finalized downpayment status
        $finalStatuses = ['Spot Paid', 'Paid'];
        if (!auth()->user()->isAdmin() && in_array($record->downpayment_status, $finalStatuses)) {
            if ($request->expectsJson() || $request->isJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Only admin can modify a finalized downpayment.'], 403);
            }
            return back()->with('error', 'Only admin can modify a finalized downpayment.');
        }

        $updates = ['downpayment_status' => $request->input('downpayment_status') ?: null];
        if ($request->filled('downpayment_amount')) {
            $updates['downpayment_amount'] = $request->input('downpayment_amount');
        }
        // Save downpayment_date if column exists
        try {
            if (\Schema::hasColumn('commission_requests_sales', 'downpayment_date') && $request->filled('downpayment_date')) {
                $updates['downpayment_date'] = $request->input('downpayment_date');
            }
        } catch (\Exception $e) {}

        $record->update($updates);

        // NEW: Spot Paid (or fully Paid) downpayment automatically marks
        // the client as Done.
        if (in_array($updates['downpayment_status'], ['Spot Paid', 'Paid'])) {
            $record->update(['client_status' => 'Done']);
        }

        // Always return JSON for PATCH/AJAX requests
        if ($request->expectsJson() || $request->isJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Downpayment status updated.');
    }

    public function destroy($id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $record = CommissionRequestSales::findOrFail($id);
        $clientName = $record->client_name ?? '';
        $projectName = $record->project_name ?? '';
        ActivityLog::log('delete', 'Sales & Marketing', "Deleted sale entry ID: {$id} ({$clientName} - {$projectName})", [
            'id'                  => $record->id,
            'developer_name'      => $record->developer_name,
            'date_requested'      => $record->date_requested ? (string)$record->date_requested : null,
            'reservation_date'    => $record->reservation_date ? (string)$record->reservation_date : null,
            'date_of_downpayment' => $record->date_of_downpayment ? (string)$record->date_of_downpayment : null,
            'project_name'        => $record->project_name,
            'property_details'    => $record->property_details,
            'block_lot_number'    => $record->block_lot_number,
            'price_sqm'           => $record->price_sqm,
            'lot_area'            => $record->lot_area,
            'tcp'                 => $record->tcp,
            'discount'            => $record->discount,
            'client_name'         => $record->client_name,
            'terms_of_payment'    => $record->terms_of_payment,
            'agent_name'          => $record->agent_name,
            'number_of_units'     => $record->number_of_units,
            'net_tcp'             => $record->net_tcp,
            'commission_percent'  => $record->commission_percent,
            'commission'          => $record->commission,
            'mode_of_payment'     => $record->mode_of_payment,
            'remarks'             => $record->remarks,
            'date_released'       => $record->date_released ? (string)$record->date_released : null,
            'status'              => $record->status,
            'client_status'       => $record->client_status,
        ]);
        $record->delete();

        return redirect()->route('client-database')->with('success', 'Commission request deleted successfully!');
    }
}