<?php

namespace App\Http\Controllers;

use App\Models\CommissionRequest;
use App\Models\CommissionRequestSales;
use App\Models\CommissionStageRequest;
use App\Services\CommissionStageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionMonitoringController extends Controller
{
    public function __construct(
        private readonly CommissionStageService $stageService
    ) {
    }

    public function index()
    {
        $commissionRequests = CommissionRequest::orderBy('date_requested', 'asc')->get();
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $years = CommissionRequest::selectRaw('YEAR(date_requested) as year')
            ->whereNotNull('date_requested')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('commission-monitoring', compact(
            'commissionRequests',
            'years',
            'isAdmin'
        ));
    }

    public function dashboard()
    {
        return view('commission-dashboard');
    }

    private function validationRules(
        bool $updating = false,
        ?CommissionRequest $record = null
    ): array {
        return [
            'project_name' => 'required|string|max:255',
            'property_details' => 'nullable|string|max:255',
            'client_name' => 'required|string|max:255',
            'terms_of_payment' => 'required|string|max:255',
            'agent_name' => 'required|string|max:255',
            'number_of_units' => 'required|integer|min:1',
            'price_sqm' => 'required|numeric|min:0',
            'lot_area' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'net_tcp' => 'nullable|numeric|min:0',
            'commission_percent' => 'nullable|numeric|min:0|max:100',
            'commission' => ($updating ? 'nullable' : 'required') . '|numeric|min:0',
            'mode_of_payment' => 'required|string|max:255',
            'date_requested' => 'required|date',
            'reservation_date' => 'nullable|date',
            'date_released' => 'nullable|date',
            'status' => 'required|in:Not Yet Released,Released',
            'payment_type' => 'required|string|max:50',
            'value_of_payment_terms' => ($updating ? 'nullable' : 'required') . '|numeric|min:0',
            'remarks' => 'nullable|string',
            'source_client_record_id' => 'nullable|integer|exists:commission_requests_sales,id',
            'commission_stage_request_id' => 'nullable|integer|exists:commission_stage_requests,id',
            'commission_stage' => 'nullable|integer|min:1|max:3',
            'commission_stage_total' => 'nullable|integer|min:1|max:3',
            'stage_threshold_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->validationRules());

            return DB::transaction(function () use ($validated) {
                $source = null;
                $stageRequest = null;

                if (!empty($validated['commission_stage_request_id'])) {
                    $stageRequest = CommissionStageRequest::lockForUpdate()
                        ->findOrFail($validated['commission_stage_request_id']);

                    if ($stageRequest->commission_request_id) {
                        return redirect()->route('commission-monitoring')
                            ->with('error', 'This Sales request has already been processed by Finance.');
                    }

                    $source = CommissionRequestSales::lockForUpdate()
                        ->findOrFail($stageRequest->source_client_record_id);

                    $alreadyFiled = CommissionRequest::withTrashed()
                        ->where('source_client_record_id', $source->id)
                        ->where('commission_stage', $stageRequest->commission_stage)
                        ->lockForUpdate()
                        ->exists();

                    if ($alreadyFiled) {
                        return redirect()->route('commission-monitoring')
                            ->with('error', 'Commission stage ' . $stageRequest->commission_stage . '/' . $stageRequest->commission_stage_total . ' has already been recorded.');
                    }

                    // Stage ownership comes from the Sales request, never from
                    // editable form values.
                    $validated['source_client_record_id'] = $source->id;
                    $validated['commission_stage'] = $stageRequest->commission_stage;
                    $validated['commission_stage_total'] = $stageRequest->commission_stage_total;
                    $validated['stage_threshold_amount'] = $stageRequest->stage_threshold_amount;
                } elseif (!empty($validated['source_client_record_id'])) {
                    // Preserve direct Admin entry from Client Database when no
                    // Sales request token was supplied.
                    $source = CommissionRequestSales::lockForUpdate()
                        ->findOrFail($validated['source_client_record_id']);

                    $summary = $this->stageService->summarize($source);

                    if (!$summary['commission_ready']) {
                        return redirect()->route('commission-monitoring')
                            ->with('error', 'No commission stage is currently available for this client record.');
                    }

                    $stage = (int) $summary['next_requestable_stage'];
                    $alreadyFiled = CommissionRequest::withTrashed()
                        ->where('source_client_record_id', $source->id)
                        ->where('commission_stage', $stage)
                        ->lockForUpdate()
                        ->exists();

                    if ($alreadyFiled) {
                        return redirect()->route('commission-monitoring')
                            ->with('error', 'Commission stage ' . $stage . '/' . $summary['downpayment_stage_total'] . ' has already been requested.');
                    }

                    $validated['commission_stage'] = $stage;
                    $validated['commission_stage_total'] = $summary['downpayment_stage_total'];
                    $validated['stage_threshold_amount'] = $summary['next_threshold_amount'];
                } else {
                    unset(
                        $validated['commission_stage'],
                        $validated['commission_stage_total'],
                        $validated['stage_threshold_amount']
                    );
                }

                unset($validated['commission_stage_request_id']);

                $month = now()->format('m');
                $year = now()->format('y');
                $count = 1;

                while (CommissionRequest::withTrashed()
                    ->where('control_number', sprintf('CM-%s-%03d-%s', $month, $count, $year))
                    ->exists()) {
                    $count++;
                }

                $validated['control_number'] = sprintf('CM-%s-%03d-%s', $month, $count, $year);
                $validated['requestor_name'] = auth()->user()->name;
                $validated['department'] = 'Commission';
                $validated['category'] = 'Commission';
                $validated['requested_amount'] = $validated['net_tcp'] ?? 0;
                $validated['status'] = ($validated['status'] ?? null) === 'Released'
                    ? 'Released'
                    : 'Not Yet Released';

                $record = CommissionRequest::create($validated);

                if ($stageRequest) {
                    $stageRequest->update([
                        'commission_request_id' => $record->id,
                        'status' => $record->status,
                        'processed_at' => now(),
                    ]);
                }

                if ($source) {
                    $source->update([
                        'status' => $this->stageService->getSourceCommissionStatus(
                            $source,
                            $record->status
                        ),
                    ]);
                }

                \App\Models\ActivityLog::log(
                    'create',
                    'Commission Monitoring',
                    "Added commission request for client '{$validated['client_name']}'"
                );

                \App\Services\AdminEmailNotifier::send(
                    'New Commission Entry — ' . $validated['client_name'],
                    'New Commission Entry Added',
                    "<b>Client:</b> {$validated['client_name']}<br>" .
                    "<b>Project:</b> " . ($validated['project_name'] ?? 'N/A') . "<br>" .
                    "<b>Agent:</b> " . ($validated['agent_name'] ?? 'N/A') . "<br>" .
                    ($record->commission_stage
                        ? "<b>DP Stage:</b> {$record->commission_stage}/{$record->commission_stage_total}<br>"
                        : '') .
                    "<b>Net TCP:</b> ₱" . number_format($validated['net_tcp'] ?? 0, 2) . "<br>" .
                    "<b>Commission:</b> ₱" . number_format($validated['commission'] ?? 0, 2) . "<br>" .
                    "<b>Status:</b> {$record->status}"
                );

                $successMessage = $record->commission_stage
                    ? 'Commission request for DP stage ' . $record->commission_stage . '/' . $record->commission_stage_total . ' added.'
                    : 'Commission request added.';

                return redirect()->route('commission-monitoring')
                    ->with('success', $successMessage);
            });
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return redirect()->back()
                ->withErrors($exception->errors())
                ->withInput();
        } catch (\Throwable $exception) {
            \Log::error('Commission store error: ' . $exception->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to save: ' . $exception->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        return response()->json(CommissionRequest::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        try {
            $record = CommissionRequest::findOrFail($id);
            $validated = $request->validate($this->validationRules(true, $record));

            // Stage ownership is server-controlled and cannot be changed by editing.
            unset(
                $validated['source_client_record_id'],
                $validated['commission_stage'],
                $validated['commission_stage_total'],
                $validated['stage_threshold_amount']
            );

            $oldStatus = $record->status;

            if (($validated['status'] ?? null) === 'Not Released') {
                $validated['status'] = 'Not Yet Released';
            }

            $record->update($validated);
            $record->refresh();

            CommissionStageRequest::where('commission_request_id', $record->id)
                ->update([
                    'status' => $record->status,
                    'processed_at' => now(),
                    'updated_at' => now(),
                ]);

            if ($record->source_client_record_id) {
                $source = CommissionRequestSales::find($record->source_client_record_id);
                if ($source) {
                    $source->update([
                        'status' => $this->stageService->getSourceCommissionStatus(
                            $source,
                            $record->status
                        ),
                    ]);
                }
            }

            \App\Models\ActivityLog::log(
                'update',
                'Commission Monitoring',
                "Updated commission request ID: {$id}"
            );

            if ($record->status === 'Released' && $oldStatus !== 'Released') {
                \App\Services\AdminEmailNotifier::send(
                    'Commission Released — ' . ($record->client_name ?? ''),
                    'Commission Marked as Released',
                    "<b>Client:</b> " . ($record->client_name ?? 'N/A') . "<br>" .
                    "<b>Project:</b> " . ($record->project_name ?? 'N/A') . "<br>" .
                    "<b>Agent:</b> " . ($record->agent_name ?? 'N/A') . "<br>" .
                    ($record->commission_stage
                        ? "<b>DP Stage:</b> {$record->commission_stage}/{$record->commission_stage_total}<br>"
                        : '') .
                    "<b>Commission:</b> ₱" . number_format($record->commission ?? 0, 2) . "<br>" .
                    "<b>Date Released:</b> " . ($record->date_released?->format('F j, Y') ?? 'N/A')
                );
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }
            return redirect()
                ->route('commission-monitoring')
                ->with('success', 'Record updated successfully.');

            } catch (\Illuminate\Validation\ValidationException $exception) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => collect($exception->errors())->flatten()->first(),
                        'errors' => $exception->errors(),
                    ], 422);
                }

                return redirect()
                    ->back()
                    ->withErrors($exception->errors())
                    ->withInput();

            } catch (\Throwable $exception) {
                report($exception);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Something went wrong. Please try again.',
                    ], 500);
                }

                return redirect()
                    ->back()
                    ->with('error', 'Something went wrong. Please try again.')
                    ->withInput();
            }
        }
    

    public function destroy($id)
    {
        $record = CommissionRequest::findOrFail($id);
        $sourceId = $record->source_client_record_id;
        $clientName = $record->client_name ?? '';
        $projectName = $record->project_name ?? '';

        \App\Models\ActivityLog::log(
            'delete',
            'Commission Monitoring',
            "Deleted commission request ID: {$id} ({$clientName} - {$projectName})",
            [
                'id' => $record->id,
                'client_name' => $record->client_name,
                'project_name' => $record->project_name,
                'commission_stage' => $record->commission_stage,
                'commission_stage_total' => $record->commission_stage_total,
                'status' => $record->status,
            ]
        );

        $record->delete();

        if ($sourceId) {
            $source = CommissionRequestSales::find($sourceId);
            if ($source) {
                $source->update([
                    'status' => $this->stageService->getSourceCommissionStatus($source),
                ]);
            }
        }

        return redirect()->route('commission-monitoring')
            ->with('success', 'Commission request deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $ids = array_filter(
            (array) $request->input('ids', []),
            fn ($id) => is_numeric($id)
        );

        if (empty($ids)) {
            return redirect()->route('commission-monitoring')
                ->with('error', 'No records selected.');
        }

        $records = CommissionRequest::whereIn('id', $ids)->get();
        $sourceIds = $records->pluck('source_client_record_id')->filter()->unique();

        foreach ($records as $record) {
            \App\Models\ActivityLog::log(
                'delete',
                'Commission Monitoring',
                "Deleted commission request ID: {$record->id} ({$record->client_name} - {$record->project_name})",
                [
                    'id' => $record->id,
                    'client_name' => $record->client_name,
                    'project_name' => $record->project_name,
                    'commission_stage' => $record->commission_stage,
                    'commission_stage_total' => $record->commission_stage_total,
                ]
            );
        }

        CommissionRequest::whereIn('id', $ids)->delete();

        foreach ($sourceIds as $sourceId) {
            $source = CommissionRequestSales::find($sourceId);
            if ($source) {
                $source->update([
                    'status' => $this->stageService->getSourceCommissionStatus($source),
                ]);
            }
        }

        return redirect()->route('commission-monitoring')
            ->with('success', count($records) . ' commission request(s) deleted.');
    }
}
