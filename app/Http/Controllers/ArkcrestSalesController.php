<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionRequest;
use App\Models\ArkcrestCommissionRate;
use App\Support\ExactFinancialMath;

class ArkcrestSalesController extends Controller
{
    public function index(Request $request)
    {
        $released = CommissionRequest::where('status', 'Released')
            ->orderBy('date_released')
            ->get();

        $rates = ArkcrestCommissionRate::whereIn('commission_request_id', $released->pluck('id'))
            ->get()->keyBy('commission_request_id');

        $totalReleasedCommission = $released->sum('commission');
        $totalNetTcp = $released->sum('net_tcp');
        $totalArkcrestCommission = $rates->sum('arkcrest_commission');
        $totalUnits = $released->sum('number_of_units');

        return view('arkcrest-sales', compact(
            'released', 'rates',
            'totalReleasedCommission', 'totalNetTcp', 'totalArkcrestCommission', 'totalUnits'
        ));
    }

    public function saveRate(Request $request, $id)
    {
        $request->validate([
            'arkcrest_percent' => ['required', 'regex:/^(?:100(?:\.0{1,30})?|(?:\d{1,2})(?:\.\d{1,30})?)$/'],
            'payment_type'     => 'nullable|string|max:50',
        ]);

        $record  = CommissionRequest::findOrFail($id);
        $percent = ExactFinancialMath::normalizePercentage($request->arkcrest_percent);
        $netTcp  = $record->net_tcp ?? '0.00';
        $terms   = $request->payment_type ?? $record->payment_type ?? 'Full Payment';

        $fullCommission = ExactFinancialMath::moneyFromPercentage($netTcp, $percent);
        $termDivisor = match ($terms) {
            '2 Months Commission' => 2,
            '3 Months Commission' => 3,
            default => 1,
        };
        $arkcrestCommission = ExactFinancialMath::divideMoney($fullCommission, $termDivisor);

        ArkcrestCommissionRate::updateOrCreate(
            ['commission_request_id' => $id],
            ['arkcrest_percent' => $percent, 'arkcrest_commission' => $arkcrestCommission]
        );

        if ($request->payment_type) {
            $record->update(['payment_type' => $request->payment_type]);
        }

        return response()->json([
            'success'             => true,
            'arkcrest_commission' => $arkcrestCommission,
            'formatted'           => '₱' . ExactFinancialMath::formatMoney($arkcrestCommission),
        ]);
    }
}
