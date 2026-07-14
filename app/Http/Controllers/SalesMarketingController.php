<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionRequest;
use App\Models\CommissionRequestSales;
use App\Models\CommissionStageRequest;
use App\Models\ActivityLog;
use App\Models\SalesTeam;
use App\Models\CommissionThreshold;
use App\Models\DownpaymentInstallment;
use App\Models\Property;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\CommissionStageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SalesMarketingController extends Controller
{
    public function __construct(
        private readonly CommissionStageService $stageService
    ) {
    }

    /**
     * Extract the downpayment percentage from the Terms of Payment label.
     * Examples:
     *   "30% DP (6 MOS) - 70% BAL 54 MOS" => 30
     *   "STRAIGHT PAYMENT"                => 100
     */
    private function parseDownpaymentPercentage(?string $termsLabel): float
    {
        $label = strtoupper(trim((string) $termsLabel));

        if ($label === '') {
            return 0;
        }

        if (str_contains($label, 'STRAIGHT PAYMENT')) {
            return 100;
        }

        if (preg_match('/(\d+(?:\.\d+)?)\s*%\s*DP/i', $label, $matches)) {
            return (float) $matches[1];
        }

        return 0;
    }

    /**
     * Calculate the required total downpayment from Net TCP and payment terms.
     */
    private function calculateTotalDownpayment(CommissionRequestSales $record): float
    {
        return $this->stageService->getTotalDownpayment($record);
    }

    /**
     * Calculate payment totals, balance and commission-request threshold.
     *
     * Payment progress determines eligibility, while commission_requests rows
     * determine which sequential DP stage has already been requested.
     */
    private function getDownpaymentSummary(CommissionRequestSales $record): array
    {
        return $this->stageService->summarize($record);
    }

    /**
     * Keep the parent record synchronized with the actual payment totals.
     */
    private function syncDownpaymentAndClientStatus(
        CommissionRequestSales $record,
        ?string $zeroPaymentStatus = null
    ): array {
        $summary = $this->getDownpaymentSummary($record);

        if ($summary['total_downpayment'] > 0 && $summary['remaining_balance'] <= 0.01) {
            $downpaymentStatus = $record->downpayment_status === 'Spot Paid'
                ? 'Spot Paid'
                : 'Paid';
            $clientStatus = 'Done';
        } elseif ($summary['paid_total'] > 0) {
            $downpaymentStatus = 'Partial';
            $clientStatus = 'Pending';
        } else {
            $downpaymentStatus = $zeroPaymentStatus;
            $clientStatus = 'Pending';
        }

        if ($record->client_status === 'Cancelled') {
            $clientStatus = 'Cancelled';
        }

        $updates = [
            'downpayment_amount' => $summary['total_downpayment'],
            'downpayment_status' => $downpaymentStatus,
            'client_status' => $clientStatus,
            'downpayment_stage' => $summary['downpayment_stage'],
            'downpayment_stage_total' => $summary['downpayment_stage_total'],
            'status' => $this->stageService->getSourceCommissionStatus($record, $record->status),
        ];

        $record->update($updates);
        $record->refresh();

        return array_merge($summary, [
            'status' => $record->downpayment_status,
            'client_status' => $record->client_status,
            'commission_status' => $record->status,
        ]);
    }

    /**
     * Create an in-app notification for active finance users and admins.
     */
    private function notifyFinanceUsers(
        CommissionRequestSales $record,
        string $type,
        string $title,
        string $message,
        ?int $referenceId = null
    ): void {
        $financePositions = [
            'finance',
            'accounting',
            'accountant',
            'treasury',
            'audit',
            'bookkeep',
        ];

        $recipients = User::where('status', 'active')
            ->where(function ($query) use ($financePositions) {
                $query->where('role', 'admin');

                foreach ($financePositions as $position) {
                    $query->orWhereRaw(
                        'LOWER(TRIM(position)) LIKE ?',
                        ['%' . $position . '%']
                    );
                }
            })
            ->get();

        foreach ($recipients as $recipient) {
            SystemNotification::create([
                'user_id' => $recipient->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'is_read' => false,
                'notified_at' => now(),
                'note_id' => $referenceId ?? $record->id,
            ]);
        }
    }

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
        $todayReleases = \App\Models\CommissionRequestSales::whereDate('date_released', $today)->whereIn('status', ['Not Yet Released', 'Not Released'])->count();
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

    private function nextCommissionControlNumber(): string
    {
        $month = now()->format('m');
        $year = now()->format('y');
        $count = 1;

        do {
            $controlNumber = sprintf('CM-%s-%03d-%s', $month, $count, $year);
            $count++;
        } while (CommissionRequest::withTrashed()
            ->where('control_number', $controlNumber)
            ->exists());

        return $controlNumber;
    }

    /**
     * Submit the next eligible DP stage to Finance without creating the final
     * commission_requests row. Finance creates that record only after completing
     * the Add New Commission Request form.
     */
    public function requestCommissionStage(Request $request, $id)
    {
        return DB::transaction(function () use ($id) {
            $record = CommissionRequestSales::lockForUpdate()->findOrFail($id);
            $summary = $this->stageService->summarize($record);

            if (!$summary['commission_ready'] || !$summary['next_requestable_stage']) {
                return response()->json([
                    'success' => false,
                    'message' => $summary['all_commission_stages_requested']
                        ? 'All eligible commission stages have already been requested.'
                        : 'The next commission stage is not ready yet.',
                    'commission_stages' => $summary['commission_stages'],
                ], 422);
            }

            $stage = (int) $summary['next_requestable_stage'];
            $stageTotal = (int) $summary['downpayment_stage_total'];

            $alreadyRequested = CommissionStageRequest::where(
                'source_client_record_id',
                $record->id
            )
                ->where('commission_stage', $stage)
                ->lockForUpdate()
                ->exists();

            $alreadyRecorded = CommissionRequest::withTrashed()
                ->where('source_client_record_id', $record->id)
                ->where('commission_stage', $stage)
                ->lockForUpdate()
                ->exists();

            if ($alreadyRequested || $alreadyRecorded) {
                return response()->json([
                    'success' => false,
                    'message' => "DP stage {$stage}/{$stageTotal} has already been requested.",
                ], 422);
            }

            $stageRequest = CommissionStageRequest::create([
                'source_client_record_id' => $record->id,
                'commission_request_id' => null,
                'commission_stage' => $stage,
                'commission_stage_total' => $stageTotal,
                'stage_threshold_amount' => $summary['next_threshold_amount'],
                'requested_by_user_id' => auth()->id(),
                'requested_by_name' => auth()->user()->name,
                'requested_at' => now(),
                'status' => 'Requested',
                'processed_at' => null,
            ]);

            $record->update(['status' => 'Requested']);

            $this->notifyFinanceUsers(
                $record,
                'commission_request_submitted',
                'New Commission Request',
                "{$record->client_name} — DP stage {$stage}/{$stageTotal} was requested by " . auth()->user()->name . '.',
                $stageRequest->id
            );

            ActivityLog::log(
                'create',
                'Client Database',
                "Requested commission for '{$record->client_name}' DP stage {$stage}/{$stageTotal}"
            );

            $updatedSummary = $this->stageService->summarize($record->fresh());

            return response()->json(array_merge([
                'success' => true,
                'message' => "Commission request for DP stage {$stage}/{$stageTotal} was sent to Finance.",
                'commission_stage_request_id' => $stageRequest->id,
            ], $updatedSummary));
        });
    }

    public function prefillCommission($id)
    {
        $record = CommissionRequestSales::findOrFail($id);
        $summary = $this->stageService->summarize($record);

        if (!$summary['commission_ready']) {
            return response()->json([
                'success' => false,
                'message' => 'There is no commission stage currently available to request.',
                'downpayment_stage' => $summary['downpayment_stage'],
                'downpayment_stage_total' => $summary['downpayment_stage_total'],
                'filed_stages' => $summary['filed_stages'],
                'commission_stages' => $summary['commission_stages'],
                'all_commission_stages_requested' => $summary['all_commission_stages_requested'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'id' => $record->id,
            'source_client_record_id' => $record->id,
            'commission_stage' => $summary['next_requestable_stage'],
            'commission_stage_total' => $summary['downpayment_stage_total'],
            'stage_threshold_amount' => $summary['next_threshold_amount'],
            'next_commission_stage' => $summary['next_commission_stage'],
            'next_requestable_stage' => $summary['next_requestable_stage'],
            'client_name' => $record->client_name ?? '',
            'project_name' => $record->project_name ?? '',
            'agent_name' => $record->agent_name ?? '',
            'net_tcp' => $record->net_tcp ?? '',
            'reservation_date' => $record->reservation_date?->format('Y-m-d') ?? '',
            'terms_of_payment' => $record->terms_of_payment ?? '',
            'number_of_units' => $record->number_of_units ?? 1,
            'commission_percent' => $record->commission_percent ?? '',
            'date_requested' => now()->format('Y-m-d'),
            'developer_name' => $record->developer_name ?? '',
            'block_lot_number' => $record->block_lot_number ?? '',
            'property_details' => $record->property_details ?? '',
            'price_sqm' => $record->price_sqm ?? '',
            'lot_area' => $record->lot_area ?? '',
            'discount' => $record->discount ?? '',
            'mode_of_payment' => $record->mode_of_payment ?? '',
        ]);
    }

    public function prefillCommissionStageRequest($id)
    {
        $stageRequest = CommissionStageRequest::with('sourceClientRecord')->findOrFail($id);

        if ($stageRequest->commission_request_id) {
            return response()->json([
                'success' => false,
                'message' => 'This commission request has already been processed by Finance.',
                'commission_request_id' => $stageRequest->commission_request_id,
            ], 409);
        }

        $record = $stageRequest->sourceClientRecord;

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'The source client record could not be found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'commission_stage_request_id' => $stageRequest->id,
            'id' => $record->id,
            'source_client_record_id' => $record->id,
            'commission_stage' => $stageRequest->commission_stage,
            'commission_stage_total' => $stageRequest->commission_stage_total,
            'stage_threshold_amount' => $stageRequest->stage_threshold_amount,
            'client_name' => $record->client_name ?? '',
            'project_name' => $record->project_name ?? '',
            'agent_name' => $record->agent_name ?? '',
            'net_tcp' => $record->net_tcp ?? '',
            'reservation_date' => $record->reservation_date?->format('Y-m-d') ?? '',
            'terms_of_payment' => $record->terms_of_payment ?? '',
            'number_of_units' => $record->number_of_units ?? 1,
            'date_requested' => now()->format('Y-m-d'),
            'developer_name' => $record->developer_name ?? '',
            'block_lot_number' => $record->block_lot_number ?? '',
            'property_details' => $record->property_details ?? '',
            'price_sqm' => $record->price_sqm ?? '',
            'lot_area' => $record->lot_area ?? '',
            'discount' => $record->discount ?? '',
            'mode_of_payment' => $record->mode_of_payment ?? '',
        ]);
    }

    public function downpaymentSummary($id)
    {
        $record = CommissionRequestSales::findOrFail($id);
        $summary = $this->stageService->summarize($record);

        return response()->json(array_merge(['success' => true], $summary, [
            'commission_status' => $this->stageService->getSourceCommissionStatus($record),
        ]));
    }

    public function clientDatabase()
    {
        $commissionRequests = CommissionRequestSales::with([
            'commissionRequests' => fn ($query) => $query
                ->withTrashed()
                ->whereNotNull('commission_stage')
                ->orderByDesc('commission_stage')
                ->orderByDesc('id'),
            'commissionStageRequests' => fn ($query) => $query
                ->orderByDesc('commission_stage')
                ->orderByDesc('id'),
        ])->orderBy('date_requested', 'asc')->get();

        // Developer names can come from two places:
        //  1. Existing client records (the free-text developer_name column), and
        //  2. The Property Management list in Settings (properties.developer) —
        //     adding a property with a developer there should make that name
        //     available here right away, even before any client record uses it.
        // Merge both, dedupe case-insensitively, and sort so the dropdown always
        // reflects real, current data instead of the old 2-name hardcoded fallback.
        $developersFromClients = CommissionRequestSales::whereNotNull('developer_name')
            ->where('developer_name', '!=', '')
            ->distinct()
            ->pluck('developer_name');

        $developersFromProperties = Schema::hasTable('properties')
            ? Property::whereNotNull('developer')->where('developer', '!=', '')->distinct()->pluck('developer')
            : collect();

        $developers = $developersFromClients
            ->merge($developersFromProperties)
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique(fn ($name) => strtolower($name))
            ->sort()
            ->values();

        return view('client-database', compact('commissionRequests', 'developers'));
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
            'date_of_downpayment' => 'required|date|after_or_equal:reservation_date',
            'project_name'        => 'required|string|max:255',
            'property_details'    => 'nullable|string|max:255',
            'block_lot_number'    => 'required|string|max:255',
            'client_name'         => 'required|string|max:255',
            'lot_area'            => 'required|numeric|min:0',
            'price_sqm'           => 'nullable|numeric|min:0',
            'tcp'                 => 'nullable|numeric',
            'discount'            => 'nullable|numeric|min:0|max:100',
            'discount_value'      => 'nullable|numeric',
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
        $initialSummary = $this->stageService->summarize($record);
        $record->update([
            'downpayment_stage' => $initialSummary['downpayment_stage'],
            'downpayment_stage_total' => $initialSummary['downpayment_stage_total'],
            'downpayment_amount' => $initialSummary['total_downpayment'],
        ]);
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
            // Mirror what the Add form's default Laravel validation redirect
            // gives the page: every message in the errors bag, not just one.
            // The Edit modal is AJAX-driven (it can't rely on a full-page
            // redirect + $errors->any() the way the Add form does), so we
            // hand the same full message list back as JSON instead.
            $allMessages = collect($e->errors())->flatten()->all();
            return response()->json([
                'error'  => $allMessages[0] ?? 'Validation failed. Please check the form and try again.',
                'errors' => $allMessages,
            ], 422);
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

        // Notify finance when the release decision changes to Released or Not Yet Released.
        $newStatus = $commissionRequest->fresh()->status;
        if (in_array($newStatus, ['Released', 'Not Yet Released', 'Not Released'], true)
            && $oldStatus !== $newStatus) {
            $releaseDate = $commissionRequest->date_released
                ? Carbon::parse($commissionRequest->date_released)->format('F j, Y')
                : 'No release date';

            $this->notifyFinanceUsers(
                $commissionRequest,
                $newStatus === 'Released'
                    ? 'commission_released'
                    : 'commission_not_released',
                $newStatus === 'Released'
                    ? 'Commission Released'
                    : 'Commission Marked Not Yet Released',
                "{$commissionRequest->client_name} — {$commissionRequest->project_name}: {$newStatus} ({$releaseDate})."
            );

            $body = "<b>Client:</b> {$commissionRequest->client_name}<br>
                     <b>Project:</b> {$commissionRequest->project_name}<br>
                     <b>Agent:</b> {$commissionRequest->agent_name}<br>
                     <b>Status:</b> {$newStatus}<br>
                     <b>Commission:</b> ₱" . number_format($commissionRequest->commission ?? 0, 2) . "<br>
                     <b>Release Date:</b> {$releaseDate}";

            \App\Services\AdminEmailNotifier::send(
                "Commission {$newStatus} — {$commissionRequest->client_name}",
                "Commission Status Updated",
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

        $validated = $request->validate([
            'client_status' => 'nullable|in:Pending,Cancelled',
        ]);

        $requestedStatus = $validated['client_status'] ?? null;

        // Done is always system-driven and can never be selected manually.
        $record->update([
            'client_status' => $requestedStatus,
        ]);

        return back()->with('success', 'Status updated.');
    }

    public function getInstallments($id)
    {
        CommissionRequestSales::findOrFail($id);

        $installments = DownpaymentInstallment::where(
            'commission_request_sales_id',
            $id
        )->orderBy('term_number')->get();

        return response()->json($installments->map(function ($installment) {
            return [
                'id' => $installment->id,
                'term_number' => $installment->term_number,
                'amount' => $installment->amount,
                'is_paid' => (bool) $installment->is_paid,
                'paid_at' => $installment->paid_at,
                'paid_date' => $installment->paid_date
                    ?? ($installment->paid_at
                        ? Carbon::parse($installment->paid_at)->format('Y-m-d')
                        : null),
            ];
        })->values());
    }

    public function setupInstallments(Request $request, $id)
    {
        $validated = $request->validate([
            'terms' => 'required|integer|min:1|max:120',
            'total_amount' => 'nullable|numeric|min:0',
        ]);

        $record = CommissionRequestSales::findOrFail($id);
        $terms = (int) $validated['terms'];

        $hasPaidInstallment = DownpaymentInstallment::where(
            'commission_request_sales_id',
            $id
        )->where('is_paid', true)->exists();

        if ($hasPaidInstallment) {
            return response()->json([
                'error' => 'The installment plan cannot be regenerated after a payment has been recorded.',
            ], 422);
        }

        $calculatedTotal = $this->calculateTotalDownpayment($record);
        $requestedTotal = round((float) ($validated['total_amount'] ?? 0), 2);

        // Known payment terms are always calculated from Net TCP.
        // "Others" may use the amount supplied by the user.
        $totalAmount = $calculatedTotal > 0
            ? $calculatedTotal
            : $requestedTotal;

        if ($totalAmount <= 0) {
            return response()->json([
                'error' => 'The total downpayment could not be calculated.',
            ], 422);
        }

        return DB::transaction(function () use ($record, $id, $terms, $totalAmount) {
            DownpaymentInstallment::where(
                'commission_request_sales_id',
                $id
            )->delete();

            // Create blank installment rows. Each amount is entered manually.
            for ($termNumber = 1; $termNumber <= $terms; $termNumber++) {
                DownpaymentInstallment::create([
                    'commission_request_sales_id' => $id,
                    'term_number' => $termNumber,
                    'amount' => null,
                    'is_paid' => false,
                ]);
            }

            $planStatus = $terms . ' month' . ($terms > 1 ? 's' : '');

            $record->update([
                'downpayment_amount' => $totalAmount,
                'downpayment_terms' => $terms,
                'downpayment_per_term' => null,
                'downpayment_stage' => 0,
                'downpayment_stage_total' => $terms === 2 ? 2 : ($terms >= 3 ? 3 : 1),
                'downpayment_status' => $planStatus,
                'client_status' => $record->client_status === 'Cancelled'
                    ? 'Cancelled'
                    : 'Pending',
            ]);

            return response()->json(
                DownpaymentInstallment::where(
                    'commission_request_sales_id',
                    $id
                )->orderBy('term_number')->get()
            );
        });
    }

    public function updateInstallmentAmount(Request $request, $id)
    {
        $rawAmount = $request->input('amount');

        if (!is_scalar($rawAmount) || !is_numeric($rawAmount)) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a valid numeric payment amount.',
            ], 422);
        }

        $newAmount = (float) $rawAmount;

        if (!is_finite($newAmount) || $newAmount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a finite payment amount greater than zero.',
            ], 422);
        }

        $newAmount = round($newAmount, 2);

        return DB::transaction(function () use ($id, $newAmount) {
            $installment = DownpaymentInstallment::lockForUpdate()->findOrFail($id);

            if ($installment->is_paid) {
                return response()->json([
                    'success' => false,
                    'message' => 'A paid installment can no longer be edited.',
                ], 422);
            }

            $record = CommissionRequestSales::lockForUpdate()->findOrFail(
                $installment->commission_request_sales_id
            );

            $totalDownpayment = $this->calculateTotalDownpayment($record);

            if ($totalDownpayment <= 0) {
                $totalDownpayment = round((float) $record->downpayment_amount, 2);
            }

            if (!is_finite($totalDownpayment) || $totalDownpayment <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'The total downpayment could not be calculated.',
                ], 422);
            }

            $alreadyPaid = round((float) DownpaymentInstallment::where(
                'commission_request_sales_id',
                $record->id
            )->where('is_paid', true)->sum('amount'), 2);

            $remainingBalance = max(
                0,
                round($totalDownpayment - $alreadyPaid, 2)
            );

            if ($remainingBalance <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'The downpayment is already fully paid.',
                ], 422);
            }

            if ($newAmount > $remainingBalance + 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'The amount cannot exceed the remaining DP balance of ₱'
                        . number_format($remainingBalance, 2) . '.',
                    'remaining_balance' => $remainingBalance,
                ], 422);
            }

            $installment->update([
                'amount' => $newAmount,
            ]);

            return response()->json([
                'success' => true,
                'amount' => $newAmount,
                'remaining_balance' => $remainingBalance,
            ]);
        });
    }

    public function markInstallmentPaid(Request $request, $id)
    {
        $validated = $request->validate([
            'paid_date' => 'required|date',
        ]);

        $rawAmount = $request->input('amount');

        if (!is_scalar($rawAmount) || !is_numeric($rawAmount)) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a valid numeric payment amount.',
            ], 422);
        }

        $paymentAmount = (float) $rawAmount;

        if (!is_finite($paymentAmount) || $paymentAmount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a finite payment amount greater than zero.',
            ], 422);
        }

        $paymentAmount = round($paymentAmount, 2);

        return DB::transaction(function () use ($validated, $id, $paymentAmount) {
            $installment = DownpaymentInstallment::lockForUpdate()->findOrFail($id);

            if ($installment->is_paid) {
                return response()->json([
                    'success' => false,
                    'message' => 'This installment is already paid.',
                ], 422);
            }

            $record = CommissionRequestSales::lockForUpdate()->findOrFail(
                $installment->commission_request_sales_id
            );

            $totalDownpayment = $this->calculateTotalDownpayment($record);

            if ($totalDownpayment <= 0) {
                $totalDownpayment = round((float) $record->downpayment_amount, 2);
            }

            if (!is_finite($totalDownpayment) || $totalDownpayment <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'The total downpayment could not be calculated.',
                ], 422);
            }

            $alreadyPaid = round((float) DownpaymentInstallment::where(
                'commission_request_sales_id',
                $record->id
            )->where('is_paid', true)->sum('amount'), 2);

            $remainingBeforePayment = max(
                0,
                round($totalDownpayment - $alreadyPaid, 2)
            );

            if ($remainingBeforePayment <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'The downpayment is already fully paid.',
                ], 422);
            }

            if ($paymentAmount > $remainingBeforePayment + 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'The payment cannot exceed the remaining DP balance of ₱'
                        . number_format($remainingBeforePayment, 2) . '.',
                    'remaining_balance' => $remainingBeforePayment,
                ], 422);
            }

            $updates = [
                'amount' => $paymentAmount,
                'is_paid' => true,
                'paid_at' => now(),
            ];

            if (Schema::hasColumn('downpayment_installments', 'paid_date')) {
                $updates['paid_date'] = $validated['paid_date'];
            }

            $installment->update($updates);

            $summary = $this->syncDownpaymentAndClientStatus(
                $record,
                $record->downpayment_terms
                    ? $record->downpayment_terms . ' month' . ($record->downpayment_terms > 1 ? 's' : '')
                    : null
            );

            $triggerCommissionPopup = $summary['commission_ready'];

            return response()->json([
                'success' => true,
                'status' => $summary['status'],
                'client_status' => $summary['client_status'],
                'commission_status' => $summary['commission_status'],
                'commission_ready' => $triggerCommissionPopup,
                'paid_total' => $summary['paid_total'],
                'total_downpayment' => $summary['total_downpayment'],
                'remaining_balance' => $summary['remaining_balance'],
                'threshold_amount' => $summary['threshold_amount'],
                'threshold_basis' => $summary['threshold_basis'],
                'downpayment_stage' => $summary['downpayment_stage'],
                'downpayment_stage_total' => $summary['downpayment_stage_total'],
                'next_commission_stage' => $summary['next_commission_stage'],
                'next_requestable_stage' => $summary['next_requestable_stage'],
                'filed_stages' => $summary['filed_stages'],
                'commission_stages' => $summary['commission_stages'],
                'all_commission_stages_requested' => $summary['all_commission_stages_requested'],
                'message' => $triggerCommissionPopup
                    ? 'Commission stage ' . $summary['next_commission_stage'] . '/' . $summary['downpayment_stage_total'] . ' is ready to request.'
                    : 'Installment marked as paid.',
            ]);
        });
    }

    public function unmarkInstallmentPaid(Request $request, $id)
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        return DB::transaction(function () use ($id) {
            $installment = DownpaymentInstallment::lockForUpdate()->findOrFail($id);

            $hasCommissionRequest = CommissionRequest::withTrashed()
                ->where('source_client_record_id', $installment->commission_request_sales_id)
                ->exists()
                || CommissionStageRequest::where(
                    'source_client_record_id',
                    $installment->commission_request_sales_id
                )->exists();

            if ($hasCommissionRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'This payment can no longer be undone because a commission request has already been recorded.',
                ], 422);
            }

            $installment->update([
                'is_paid' => false,
                'paid_at' => null,
                'paid_date' => Schema::hasColumn('downpayment_installments', 'paid_date')
                    ? null
                    : $installment->paid_date,
            ]);

            $record = CommissionRequestSales::lockForUpdate()->findOrFail(
                $installment->commission_request_sales_id
            );

            $planStatus = $record->downpayment_terms
                ? $record->downpayment_terms . ' month' . ($record->downpayment_terms > 1 ? 's' : '')
                : null;

            $summary = $this->syncDownpaymentAndClientStatus($record, $planStatus);

            return response()->json([
                'success' => true,
                'status' => $summary['status'],
                'client_status' => $summary['client_status'],
                'commission_status' => $summary['commission_status'],
                'commission_ready' => $summary['commission_ready'],
                'paid_total' => $summary['paid_total'],
                'total_downpayment' => $summary['total_downpayment'],
                'remaining_balance' => $summary['remaining_balance'],
                'threshold_amount' => $summary['threshold_amount'],
                'threshold_basis' => $summary['threshold_basis'],
                'downpayment_stage' => $summary['downpayment_stage'],
                'downpayment_stage_total' => $summary['downpayment_stage_total'],
                'next_commission_stage' => $summary['next_commission_stage'],
                'next_requestable_stage' => $summary['next_requestable_stage'],
                'filed_stages' => $summary['filed_stages'],
                'commission_stages' => $summary['commission_stages'],
                'all_commission_stages_requested' => $summary['all_commission_stages_requested'],
            ]);
        });
    }

    public function updateDownpaymentInstallment(Request $request, $id)
    {
        $validated = $request->validate([
            'downpayment_terms' => 'required|integer|min:1|max:120',
            'downpayment_amount' => 'nullable|numeric|min:0',
        ]);

        $record = CommissionRequestSales::findOrFail($id);
        $totalDownpayment = $this->calculateTotalDownpayment($record);

        if ($totalDownpayment <= 0) {
            $totalDownpayment = round(
                (float) ($validated['downpayment_amount'] ?? $record->downpayment_amount ?? 0),
                2
            );
        }

        if (!is_finite($totalDownpayment) || $totalDownpayment <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'The total downpayment could not be calculated.',
            ], 422);
        }

        $record->update([
            'downpayment_amount' => $totalDownpayment,
            'downpayment_terms' => (int) $validated['downpayment_terms'],
            'downpayment_per_term' => null,
        ]);

        return response()->json([
            'success' => true,
            'total_downpayment' => $totalDownpayment,
        ]);
    }

    public function updateDownpaymentStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'downpayment_status' => 'nullable|string|max:50',
            'downpayment_amount' => 'nullable|numeric|min:0',
            'downpayment_date' => 'nullable|date',
        ]);

        $record = CommissionRequestSales::findOrFail($id);

        $finalStatuses = ['Spot Paid', 'Paid'];
        if (!auth()->user()->isAdmin()
            && in_array($record->downpayment_status, $finalStatuses, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can modify a finalized downpayment.',
            ], 403);
        }

        $requestedStatus = $validated['downpayment_status'] ?? null;
        $totalDownpayment = $this->calculateTotalDownpayment($record);

        if ($totalDownpayment <= 0) {
            $totalDownpayment = round(
                (float) ($validated['downpayment_amount'] ?? $record->downpayment_amount ?? 0),
                2
            );
        }

        $updates = [
            'downpayment_status' => $requestedStatus,
            'downpayment_amount' => $totalDownpayment,
        ];

        if (Schema::hasColumn('commission_requests_sales', 'downpayment_date')
            && !empty($validated['downpayment_date'])) {
            $updates['downpayment_date'] = $validated['downpayment_date'];
        }

        $record->update($updates);
        $record->refresh();

        if (in_array($requestedStatus, ['Spot Paid', 'Paid'], true)) {
            $record->update([
                'downpayment_status' => $requestedStatus,
                'downpayment_amount' => $totalDownpayment,
            ]);
            $record->refresh();

            $summary = $this->syncDownpaymentAndClientStatus($record);

            return response()->json([
                'success' => true,
                'status' => $record->downpayment_status,
                'client_status' => $record->client_status,
                'commission_status' => $record->status,
                'commission_ready' => $summary['commission_ready'],
                'paid_total' => $summary['paid_total'],
                'total_downpayment' => $summary['total_downpayment'],
                'remaining_balance' => $summary['remaining_balance'],
                'threshold_amount' => $summary['threshold_amount'],
                'threshold_basis' => $summary['threshold_basis'],
                'downpayment_stage' => $summary['downpayment_stage'],
                'downpayment_stage_total' => $summary['downpayment_stage_total'],
                'next_commission_stage' => $summary['next_commission_stage'],
                'next_requestable_stage' => $summary['next_requestable_stage'],
                'filed_stages' => $summary['filed_stages'],
                'commission_stages' => $summary['commission_stages'],
                'all_commission_stages_requested' => $summary['all_commission_stages_requested'],
                'message' => $summary['commission_ready']
                    ? 'Commission stage ' . $summary['next_commission_stage'] . '/' . $summary['downpayment_stage_total'] . ' is ready to request.'
                    : 'Downpayment status updated.',
            ]);
        }

        $summary = $this->syncDownpaymentAndClientStatus($record);

        return response()->json([
            'success' => true,
            'status' => $summary['status'],
            'client_status' => $summary['client_status'],
            'commission_status' => $summary['commission_status'],
            'commission_ready' => false,
            'paid_total' => $summary['paid_total'],
            'total_downpayment' => $summary['total_downpayment'],
            'remaining_balance' => $summary['remaining_balance'],
            'threshold_amount' => $summary['threshold_amount'],
            'threshold_basis' => $summary['threshold_basis'],
            'downpayment_stage' => $summary['downpayment_stage'],
            'downpayment_stage_total' => $summary['downpayment_stage_total'],
            'next_commission_stage' => $summary['next_commission_stage'],
            'next_requestable_stage' => $summary['next_requestable_stage'],
            'filed_stages' => $summary['filed_stages'],
            'commission_stages' => $summary['commission_stages'],
            'all_commission_stages_requested' => $summary['all_commission_stages_requested'],
            'message' => 'Downpayment status updated.',
        ]);
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