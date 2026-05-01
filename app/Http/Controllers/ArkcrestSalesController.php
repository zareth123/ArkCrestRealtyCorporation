<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionRequest;
use App\Models\ArkcrestCommissionRate;

class ArkcrestSalesController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', date('n'));
        $year  = $request->get('year', date('Y'));

        // All Released commission requests for selected month/year
        $released = CommissionRequest::where('status', 'Released')
            ->whereYear('date_released', $year)
            ->whereMonth('date_released', $month)
            ->orderBy('date_released')
            ->get();

        // Load existing ArkCrest rates
        $rates = ArkcrestCommissionRate::whereIn('commission_request_id', $released->pluck('id'))
            ->get()->keyBy('commission_request_id');

        // Available years
        $years = CommissionRequest::where('status', 'Released')
            ->whereNotNull('date_released')
            ->selectRaw('YEAR(date_released) as y')
            ->distinct()->pluck('y')->sortDesc();
        if (!$years->contains((int)$year)) $years->prepend((int)$year);

        // Totals
        $totalReleasedCommission = $released->sum('commission');
        $totalArkcrestCommission = $rates->sum('arkcrest_commission');

        return view('arkcrest-sales', compact(
            'released', 'rates', 'month', 'year', 'years',
            'totalReleasedCommission', 'totalArkcrestCommission'
        ));
    }

    public function saveRate(Request $request, $id)
    {
        $request->validate([
            'arkcrest_percent' => 'required|numeric|min:0|max:100',
        ]);

        $record = CommissionRequest::findOrFail($id);
        $percent = $request->arkcrest_percent;
        $commission = ($record->commission ?? 0) * ($percent / 100);

        ArkcrestCommissionRate::updateOrCreate(
            ['commission_request_id' => $id],
            ['arkcrest_percent' => $percent, 'arkcrest_commission' => $commission]
        );

        return response()->json([
            'success' => true,
            'arkcrest_commission' => $commission,
            'formatted' => '₱' . number_format($commission, 2),
        ]);
    }
}
