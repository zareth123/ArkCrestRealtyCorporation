<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TripSchedule;

class PrintController extends Controller
{
   public function searchAndPrint(Request $request)
{
    $keyword = strtolower(trim($request->keyword));

    // Month mapping
    $months = [
        'january' => 1, 'jan' => 1, '01' => 1,
        'february' => 2, 'feb' => 2, '02' => 2,
        'march' => 3, 'mar' => 3, '03' => 3,
        'april' => 4, 'apr' => 4, '04' => 4,
        'may' => 5, '05' => 5,
        'june' => 6, 'jun' => 6, '06' => 6,
        'july' => 7, 'jul' => 7, '07' => 7,
        'august' => 8, 'aug' => 8, '08' => 8,
        'september' => 9, 'sep' => 9, '09' => 9,
        'october' => 10, 'oct' => 10, '10' => 10,
        'november' => 11, 'nov' => 11, '11' => 11,
        'december' => 12, 'dec' => 12, '12' => 12,
    ];

    $query = TripSchedule::query();

    // 🔍 Normal text search
    $query->where(function ($q) use ($keyword) {
        $q->where('client_name', 'LIKE', "%$keyword%")
          ->orWhere('agent_name', 'LIKE', "%$keyword%")
          ->orWhere('property_name', 'LIKE', "%$keyword%")
          ->orWhere('company_name', 'LIKE', "%$keyword%")
          ->orWhere('status', 'LIKE', "%$keyword%")
          ->orWhere('tripping_type', 'LIKE', "%$keyword%")
          ->orWhere('client_email', 'LIKE', "%$keyword%")
          ->orWhere('tripping_date', 'LIKE', "%$keyword%");
    });

    // 📅 Month filter (January, Jan, 01)
    if (isset($months[$keyword])) {
        $query->orWhereMonth('tripping_date', $months[$keyword]);
    }

    // 📅 Year filter (2026)
    if (is_numeric($keyword) && strlen($keyword) == 4) {
        $query->orWhereYear('tripping_date', $keyword);
    }

    $trips = $query->orderBy('tripping_date', 'asc')->get();

    return view('print.tripping-multiple', compact('trips', 'keyword'));
}
}