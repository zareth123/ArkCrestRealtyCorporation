<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionRequest;
use App\Models\CommissionRequestSales;
use App\Models\DepartmentalExpense;
use App\Models\TripSchedule;

class CalendarController extends Controller
{
    public function salesCalendar(Request $request)
    {
        $month = (int) $request->get('month', date('n'));
        $year  = (int) $request->get('year', date('Y'));
        $view  = $request->get('view', 'month'); 

        $dateFrom = sprintf('%04d-%02d-01', $year, $month);
        $dateTo   = date('Y-m-t', strtotime($dateFrom));

        $sales = CommissionRequestSales::where(function($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('date_of_downpayment', [$dateFrom, $dateTo]);
        })->get();

        $trips = TripSchedule::whereBetween('tripping_date', [$dateFrom, $dateTo])
            ->whereIn('status', ['confirmed', 'pending'])
            ->orderBy('tripping_date')
            ->get();

        $eventsByDay = collect();

        foreach ($sales as $s) {

            if ($s->date_of_downpayment && $s->date_of_downpayment->month == $month && $s->date_of_downpayment->year == $year && $s->client_status !== 'Done') {
                $day = $s->date_of_downpayment->day;
                if (!$eventsByDay->has($day)) $eventsByDay->put($day, collect());
                $eventsByDay->get($day)->push([
                    'type'    => 'downpayment',
                    'label'   => $s->client_name,
                    'sub'     => $s->project_name,
                    'agent'   => $s->agent_name,
                    'amount'  => $s->net_tcp,
                    'date'    => $s->date_of_downpayment->format('Y-m-d'),
                    'status'  => $s->status,
                    'id'      => $s->id,
                ]);
            }
        }

        foreach ($trips as $t) {
            $day = $t->tripping_date->day;
            if (!$eventsByDay->has($day)) $eventsByDay->put($day, collect());
            $eventsByDay->get($day)->push([
                'type'   => 'trip',
                'label'  => $t->client_name,
                'sub'    => $t->property_name,
                'agent'  => $t->agent_name,
                'amount' => null,
                'date'   => $t->tripping_date->format('Y-m-d'),
                'status' => $t->status,
                'id'     => $t->id,
                'time'   => $t->tripping_time,
            ]);
        }

        $salesYears = CommissionRequestSales::selectRaw('YEAR(date_requested) as y')->whereNotNull('date_requested')->distinct()->pluck('y');
        $tripYears  = TripSchedule::selectRaw('YEAR(tripping_date) as y')->whereNotNull('tripping_date')->distinct()->pluck('y');
        $availableYears = $salesYears->merge($tripYears)->unique()->sort()->values();
        if (!$availableYears->contains($year)) $availableYears->prepend($year);
        $availableYears = $availableYears->sortDesc()->values();

        $totalSales    = $sales->filter(fn($s) => $s->date_requested && $s->date_requested->format('Y-m') === sprintf('%04d-%02d', $year, $month))->count();
        $totalReleases = $sales->filter(fn($s) => $s->date_released && $s->date_released->format('Y-m') === sprintf('%04d-%02d', $year, $month))->count();
        $totalTrips    = $trips->count();
        $totalNetTcp   = $sales->filter(fn($s) => $s->date_requested && $s->date_requested->format('Y-m') === sprintf('%04d-%02d', $year, $month))->sum('net_tcp');

        $allEvents = $eventsByDay->flatten(1)->sortBy('date')->values();

        $weekStart = now()->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d');
        $weekEnd   = now()->endOfWeek(\Carbon\Carbon::SUNDAY)->format('Y-m-d');

        $thisWeekTrips = TripSchedule::whereBetween('tripping_date', [$weekStart, $weekEnd])
            ->whereIn('status', ['confirmed', 'pending'])
            ->orderBy('tripping_date')->orderBy('tripping_time')
            ->get();

        $thisWeekDownpayments = CommissionRequestSales::whereBetween('date_of_downpayment', [$weekStart, $weekEnd])
            ->orderBy('date_of_downpayment')
            ->get();

        return view('sales-calendar', compact(
            'month', 'year', 'view',
            'eventsByDay', 'allEvents',
            'availableYears',
            'totalSales', 'totalReleases', 'totalTrips', 'totalNetTcp',
            'thisWeekTrips', 'thisWeekDownpayments', 'weekStart', 'weekEnd'
        ));
    }

    public function index(Request $request)
    {
        $month = $request->get('month', date('n'));
        $year  = $request->get('year', date('Y'));
        $view  = $request->get('view', 'month');

        $clientReleases = CommissionRequestSales::whereNotNull('date_released')
            ->whereYear('date_released', $year)
            ->whereMonth('date_released', $month)
            ->get()
            ->each(fn($r) => $r->_type = 'sales');
        
        $commissionReleases = CommissionRequest::whereNotNull('date_released')
            ->whereYear('date_released', $year)
            ->whereMonth('date_released', $month)
            ->get()
            ->each(fn($r) => $r->_type = 'commission');

        $expenseReleases = DepartmentalExpense::whereNotNull('date_released')
            ->whereYear('date_released', $year)
            ->whereMonth('date_released', $month)
            ->get()
            ->each(fn($r) => $r->_type = 'expense');

        $releases = $clientReleases->merge($commissionReleases)->merge($expenseReleases)->sortBy('date_released');

        $releasesByDay = $releases->groupBy(fn($r) => $r->date_released->day);

        $clientYears = CommissionRequestSales::whereNotNull('date_released')
            ->selectRaw('YEAR(date_released) as year')
            ->distinct()
            ->pluck('year');
        
        $commissionYears = CommissionRequest::whereNotNull('date_released')
            ->selectRaw('YEAR(date_released) as year')
            ->distinct()
            ->pluck('year');

        $expenseYears = DepartmentalExpense::whereNotNull('date_released')
            ->selectRaw('YEAR(date_released) as year')
            ->distinct()
            ->pluck('year');

        $availableYears = $clientYears->merge($commissionYears)->merge($expenseYears)->unique()->sortDesc()->values();

        if (!$availableYears->contains((int)$year)) {
            $availableYears->prepend((int)$year);
        }

        return view('calendar', compact('month', 'year', 'view', 'releases', 'releasesByDay', 'availableYears'));
    }
}
