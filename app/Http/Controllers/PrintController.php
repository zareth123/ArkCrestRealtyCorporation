<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TripSchedule;

class PrintController extends Controller
{
    public function printByStatus($status)
    {
        $trips = TripSchedule::where('status', $status)
            ->orderBy('tripping_date', 'asc')
            ->get();

        return view('print.tripping-multiple', compact('trips', 'status'));
    }
}