<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionRequest;
use App\Models\ArkcrestCommissionRate;

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
            'arkcrest_percent' => 'required|numeric|min:0|max:100',
            'payment_type'     => 'nullable|string|max:50',
        ]);

        $record  = CommissionRequest::findOrFail($id);
        $percent = $request->arkcrest_percent;
        $netTcp  = $record->net_tcp ?? 0;
        $terms   = $request->payment_type ?? $record->payment_type ?? 'Full Payment';

        $fullCommission = $netTcp * ($percent / 100);
        if ($terms === '2 Months Commission')      $arkcrestCommission = $fullCommission / 2;
        elseif ($terms === '3 Months Commission')  $arkcrestCommission = $fullCommission / 3;
        else                                       $arkcrestCommission = $fullCommission;

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
            'formatted'           => '₱' . number_format($arkcrestCommission, 2),
        ]);
    }
}
