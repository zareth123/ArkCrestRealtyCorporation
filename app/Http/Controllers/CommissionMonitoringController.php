<?php

namespace App\Http\Controllers;

use App\Models\CommissionRequest;
use App\Models\CommissionRequestSales;
use App\Models\CommissionStageRequest;
use App\Models\SystemNotification;
use App\Services\CommissionStageService;
use App\Support\ExactFinancialMath;
use Illuminate\Http\Request;
use Carbon\Carbon;
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

    private function nextControlNumber(): string
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

    private function releaseDateFor(?string $dateRequested, ?string $modeOfPayment): ?string
    {
        if (!$dateRequested || !$modeOfPayment) {
            return null;
        }

        $mode = strtoupper(trim($modeOfPayment));
        $sevenDayModes = ['BANK TRANSFER', 'CASH PAYMENT', "MANAGER'S CHECK", 'BANK DEPOSIT'];
        $tenDayModes = ['PERSONAL CHECK', 'POST-DATED CHECK'];

        $days = in_array($mode, $sevenDayModes, true)
            ? 7
            : (in_array($mode, $tenDayModes, true) ? 10 : null);

        return $days === null
            ? null
            : Carbon::parse($dateRequested)->addDays($days)->format('Y-m-d');
    }

    private function normalizeFinancialFields(array $validated, bool $autoCalculateReleaseDate = true): array
    {
        $discountSource = $validated['discount_calculation_source'] ?? 'percent';
        $commissionSource = $validated['commission_calculation_source'] ?? 'percent';

        $tcp = ExactFinancialMath::multiplyToMoney(
            $validated['price_sqm'] ?? 0,
            $validated['lot_area'] ?? 0
        );

        if ($tcp !== '0.00') {
            if ($discountSource === 'value') {
                $discountValue = ExactFinancialMath::clampMoney(
                    $validated['discount_value'] ?? 0,
                    '0.00',
                    $tcp
                );
                $discountPercent = ExactFinancialMath::percentageFromAmount(
                    $discountValue,
                    $tcp
                );
            } else {
                $discountPercent = ExactFinancialMath::normalizePercentage(
                    $validated['discount'] ?? 0
                );
                $discountValue = ExactFinancialMath::moneyFromPercentage(
                    $tcp,
                    $discountPercent
                );
            }

            $validated['discount'] = $discountPercent;
            $validated['discount_value'] = $discountValue;
            $validated['net_tcp'] = ExactFinancialMath::subtractMoney($tcp, $discountValue);
        } else {
            $validated['discount'] = ExactFinancialMath::normalizePercentage($validated['discount'] ?? 0);
            $validated['discount_value'] = '0.00';
            $validated['net_tcp'] = '0.00';
        }

        if ($validated['net_tcp'] !== '0.00') {
            if ($commissionSource === 'value') {
                $commission = ExactFinancialMath::normalizeMoney($validated['commission'] ?? 0);
                $commissionPercent = ExactFinancialMath::percentageFromAmount(
                    $commission,
                    $validated['net_tcp']
                );
            } else {
                $commissionPercent = ExactFinancialMath::normalizePercentage(
                    $validated['commission_percent'] ?? 0
                );
                $commission = ExactFinancialMath::moneyFromPercentage(
                    $validated['net_tcp'],
                    $commissionPercent
                );
            }

            $validated['commission_percent'] = $commissionPercent;
            $validated['commission'] = $commission;

            $termDivisor = match ($validated['payment_type'] ?? '') {
                '2 Months Commission' => 2,
                '3 Months Commission' => 3,
                default => 1,
            };

            $validated['value_of_payment_terms'] = ExactFinancialMath::divideMoney(
                $commission,
                $termDivisor
            );
        }

        if ($autoCalculateReleaseDate) {
            $validated['date_released'] = $this->releaseDateFor(
                $validated['date_requested'] ?? null,
                $validated['mode_of_payment'] ?? null
            );
        }
        // When $autoCalculateReleaseDate is false, $validated['date_released']
        // already holds whatever the Edit form submitted, unchanged.

        unset(
            $validated['discount_calculation_source'],
            $validated['commission_calculation_source']
        );

        return $validated;
    }

    public function processCommissionNotification(Request $request, int $notificationId)
    {
        try {
            $result = DB::transaction(function () use ($notificationId) {
                $notification = SystemNotification::where('id', $notificationId)
                    ->where('user_id', auth()->id())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($notification->type !== 'commission_request_submitted' || !$notification->note_id) {
                    abort(422, 'This notification is not a commission request.');
                }

                $stageRequest = CommissionStageRequest::lockForUpdate()
                    ->findOrFail($notification->note_id);

                if ($stageRequest->commission_request_id) {
                    $existing = CommissionRequest::find($stageRequest->commission_request_id);

                    if (!$existing) {
                        abort(409, 'The linked commission request is no longer available.');
                    }

                    $notification->update(['is_read' => true]);

                    return $existing;
                }

                $source = CommissionRequestSales::lockForUpdate()
                    ->findOrFail($stageRequest->source_client_record_id);

                $existing = CommissionRequest::withTrashed()
                    ->where('source_client_record_id', $source->id)
                    ->where('commission_stage', $stageRequest->commission_stage)
                    ->lockForUpdate()
                    ->first();

                if ($existing?->trashed()) {
                    abort(409, 'This commission stage was previously deleted. Restore it from Deleted Records before continuing.');
                }

                if ($existing) {
                    $stageRequest->update([
                        'commission_request_id' => $existing->id,
                        'status' => $existing->status,
                        'processed_at' => $stageRequest->processed_at ?: now(),
                    ]);
                    $notification->update(['is_read' => true]);

                    return $existing;
                }

                $record = CommissionRequest::create([
                    'control_number' => $this->nextControlNumber(),
                    'requestor_name' => $stageRequest->requested_by_name ?: 'Sales & Marketing',
                    'department' => 'Commission',
                    'category' => 'Commission',
                    'date_requested' => $stageRequest->requested_at?->format('Y-m-d') ?: now()->format('Y-m-d'),
                    'requested_amount' => $source->net_tcp ?? 0,
                    'status' => 'Requested',
                    'date_released' => null,
                    'project_name' => $source->project_name,
                    'property_details' => $source->block_lot_number ?: $source->property_details,
                    'client_name' => $source->client_name,
                    'terms_of_payment' => $source->terms_of_payment,
                    'agent_name' => $source->agent_name,
                    'number_of_units' => $source->number_of_units ?: 1,
                    'net_tcp' => $source->net_tcp,
                    'commission' => null,
                    'commission_percent' => null,
                    'mode_of_payment' => null,
                    'reservation_date' => $source->reservation_date?->format('Y-m-d'),
                    'price_sqm' => $source->price_sqm,
                    'lot_area' => $source->lot_area,
                    'discount' => $source->discount,
                    'discount_value' => $source->discount_value,
                    'remarks' => $source->remarks,
                    'payment_type' => null,
                    'value_of_payment_terms' => null,
                    'source_client_record_id' => $source->id,
                    'commission_stage' => $stageRequest->commission_stage,
                    'commission_stage_total' => $stageRequest->commission_stage_total,
                    'stage_threshold_amount' => $stageRequest->stage_threshold_amount,
                ]);

                $stageRequest->update([
                    'commission_request_id' => $record->id,
                    'status' => 'Requested',
                    'processed_at' => now(),
                ]);

                $source->update([
                    'status' => $this->stageService->getSourceCommissionStatus($source, 'Requested'),
                ]);

                $notification->update(['is_read' => true]);

                \App\Models\ActivityLog::log(
                    'create',
                    'Commission Monitoring',
                    "Automatically created commission request for '{$source->client_name}' DP stage {$stageRequest->commission_stage}/{$stageRequest->commission_stage_total}"
                );

                return $record;
            });

            return response()->json([
                'success' => true,
                'commission_request_id' => $result->id,
                'url' => route('commission-monitoring', ['open_request' => $result->id]),
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Unable to process the commission notification.',
            ], $exception->getStatusCode());
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create the commission request. Please try again.',
            ], 500);
        }
    }

    private function validationRules(
        bool $updating = false,
        ?CommissionRequest $record = null
    ): array {
        return [
            'project_name' => 'required|string|max:255',
            'property_details' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'terms_of_payment' => 'required|string|max:255',
            'agent_name' => 'required|string|max:255',
            'number_of_units' => 'required|integer|min:1',
            'price_sqm' => 'required|numeric|min:0',
            'lot_area' => 'required|numeric|min:0',
            'discount' => ['nullable', 'regex:/^(?:100(?:\.0{1,30})?|(?:\d{1,2})(?:\.\d{1,30})?)$/'],
            'discount_value' => 'nullable|numeric|min:0',
            'discount_calculation_source' => 'nullable|in:percent,value',
            'net_tcp' => 'required|numeric|min:0',
            'commission_percent' => ['required', 'regex:/^(?:100(?:\.0{1,30})?|(?:\d{1,2})(?:\.\d{1,30})?)$/'],
            'commission' => 'required|numeric|min:0',
            'commission_calculation_source' => 'nullable|in:percent,value',
            'mode_of_payment' => 'required|string|max:255',
            'date_requested' => 'required|date',
            'reservation_date' => 'nullable|date',
            'date_released' => 'nullable|date',
            'status' => 'required|in:Not Yet Released,Released',
            'payment_type' => 'required|string|max:50',
            'value_of_payment_terms' => 'required|numeric|min:0',
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
            $validated = $this->normalizeFinancialFields(
                $request->validate($this->validationRules())
            );

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

                $validated['control_number'] = $this->nextControlNumber();
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
            // If the record was still "Requested" before this save, this is
            // the Fill Up flow completing it for the first time — keep the
            // auto-calculated Date Released. Any other status means this is
            // a genuine Edit of an already-processed record, so respect
            // whatever Date Released the admin typed in the form.
            $isCompletingFillUp = $record->status === 'Requested';

            // Client-settled fields must always come from the linked client
            // record. This prevents an Edit/Save cycle from recalculating a
            // precise discount from a previously shortened percentage value.
            if ($record->source_client_record_id) {
                $source = CommissionRequestSales::findOrFail($record->source_client_record_id);

                $request->merge([
                    'project_name' => $source->project_name,
                    'property_details' => $source->block_lot_number ?: $source->property_details,
                    'client_name' => $source->client_name,
                    'terms_of_payment' => $source->terms_of_payment,
                    'agent_name' => $source->agent_name,
                    'number_of_units' => $source->number_of_units ?: 1,
                    'price_sqm' => $source->price_sqm,
                    'lot_area' => $source->lot_area,
                    'discount' => $source->discount,
                    'discount_value' => $source->discount_value,
                    // The actual money amount is authoritative. Derive and
                    // store the full percentage from it without rounding.
                    'discount_calculation_source' => 'value',
                    'net_tcp' => $source->net_tcp,
                    'reservation_date' => $source->reservation_date?->format('Y-m-d'),
                ]);
            } else {
                // Terms of payment remains settled even for older/manual rows.
                $request->merge([
                    'terms_of_payment' => $record->terms_of_payment,
                ]);
            }

            $validated = $this->normalizeFinancialFields(
                $request->validate($this->validationRules(true, $record)),
                $isCompletingFillUp
            );

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
