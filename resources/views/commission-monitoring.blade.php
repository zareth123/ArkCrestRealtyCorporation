<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionRequest;

class CommissionMonitoringController extends Controller
{
    public function index()
    {
        $commissionRequests = CommissionRequest::orderBy('date_requested', 'asc')->get();
        $isAdmin = auth()->check() && auth()->user()->isAdmin();
        $years = CommissionRequest::selectRaw('YEAR(date_requested) as year')
            ->whereNotNull('date_requested')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        return view('commission-monitoring', compact('commissionRequests', 'years', 'isAdmin'));
    }

    public function dashboard()
    {
        return view('commission-dashboard');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'project_name'       => 'required|string|max:255',
                'property_details'   => 'nullable|string|max:255',
                'client_name'        => 'required|string|max:255',
                'terms_of_payment'   => 'required|string|max:255',
                'agent_name'         => 'required|string|max:255',
                'number_of_units'    => 'nullable|integer|min:1',
                'price_sqm'          => 'nullable|numeric',
                'lot_area'           => 'nullable|numeric',
                'discount'           => 'nullable|numeric',
                'net_tcp'            => 'nullable|numeric',
                'commission_percent' => 'nullable|numeric',
                'commission'         => 'nullable|numeric',
                'mode_of_payment'    => 'nullable|string|max:255',
                'date_requested'     => 'nullable|date',
                'reservation_date'   => 'nullable|date',
                'date_released'      => 'nullable|date',
                'status'             => 'nullable|string|max:50',
                'payment_type'       => 'nullable|string|max:50',
                'value_of_payment_terms' => 'nullable|numeric',
                'payment_type'       => 'nullable|string|max:50',
                'value_of_payment_terms' => 'nullable|numeric',
                'remarks'            => 'nullable|string',
            ]);

            // Auto-generate control number for commission monitoring
            $month = now()->format('m');
            $year  = now()->format('y');
            $count = 1;
            while (CommissionRequest::withTrashed()->where('control_number', sprintf('CM-%s-%03d-%s', $month, $count, $year))->exists()) {
                $count++;
            }
            $validated['control_number'] = sprintf('CM-%s-%03d-%s', $month, $count, $year);
            $validated['requestor_name'] = $validated['requestor_name'] ?? auth()->user()->name;
            $validated['department']     = $validated['department'] ?? 'Commission';
            $validated['category']       = $validated['category'] ?? 'Commission';
            $validated['requested_amount'] = $validated['net_tcp'] ?? 0;

            CommissionRequest::create($validated);
            \App\Models\ActivityLog::log('create', 'Commission Monitoring', "Added commission request for client '{$validated['client_name']}'");
            return redirect()->route('commission-monitoring')->with('success', 'Commission request added.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Commission store error: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to save: ' . $e->getMessage())->withInput();
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
            $validated = $request->validate([
                'project_name'      => 'nullable|string|max:255',
                'property_details'  => 'nullable|string|max:255',
                'client_name'       => 'nullable|string|max:255',
                'terms_of_payment'  => 'nullable|string|max:255',
                'agent_name'        => 'nullable|string|max:255',
                'number_of_units'   => 'nullable|integer',
                'price_sqm'         => 'nullable|numeric',
                'lot_area'          => 'nullable|numeric',
                'discount'          => 'nullable|numeric',
                'net_tcp'           => 'nullable|numeric',
                'commission_percent'=> 'nullable|numeric',
                'commission'        => 'nullable|numeric',
                'mode_of_payment'   => 'nullable|string|max:255',
                'date_requested'    => 'nullable|date',
                'reservation_date'  => 'nullable|date',
                'date_released'     => 'nullable|date',
                'status'            => 'nullable|string|max:50',
                'payment_type'      => 'nullable|string|max:50',
                'value_of_payment_terms' => 'nullable|numeric',
                'payment_type'      => 'nullable|string|max:50',
                'value_of_payment_terms' => 'nullable|numeric',
                'remarks'           => 'nullable|string',
            ]);
            $record->update($validated);
            \App\Models\ActivityLog::log('update', 'Commission Monitoring', "Updated commission request ID: {$id}");
            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }
            return redirect()->route('commission-monitoring')->with('success', 'Record updated successfully.');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $record = CommissionRequest::findOrFail($id);
        $clientName = $record->client_name ?? '';
        $projectName = $record->project_name ?? '';
        \App\Models\ActivityLog::log('delete', 'Commission Monitoring', "Deleted commission request ID: {$id} ({$clientName} - {$projectName})", [
            'id'           => $record->id,
            'client_name'  => $record->client_name ?? null,
            'project_name' => $record->project_name ?? null,
            'agent'        => $record->agent ?? null,
            'tcp'          => $record->tcp ?? null,
            'reservation_date' => $record->reservation_date ?? null,
            'status'       => $record->status ?? null,
        ]);
        $record->delete();
        return redirect()->route('commission-monitoring')->with('success', 'Commission request deleted.');
    }
}
