<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\DepartmentalExpense;
use App\Models\CommissionRequest;
use App\Models\CommissionRequestSales;
use App\Models\SummaryReport;
use App\Models\TripSchedule;
use App\Models\Note;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $salesPositions = ['sales agent', 'sales manager', 'sales person', 'salesperson', 'sales team leader', 'sales personnel'];
        if (in_array(strtolower(trim($user->position ?? '')), $salesPositions)) {
            return redirect()->route('tripping');
        }

        if (!$user->isAdmin()) {
            $hidden = $user->hidden_pages ?? [];
            if (in_array('dashboard', $hidden)) {
                $fallbacks = ['sales-marketing', 'client-database', 'site-visit-database', 'forms', 'settings'];
                foreach ($fallbacks as $key) {
                    if (!in_array($key, $hidden)) {
                        return redirect()->route($key);
                    }
                }
                return redirect()->route('settings');
            }
        }

        $currentMonth = now()->format('F');
        $currentYear = now()->format('Y');
        $currentMonthNumber = now()->month;

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd   = now()->endOfMonth()->toDateString();

        $units = \App\Models\ArkcrestCommissionRate::whereHas('commissionRequest', function($q) use ($monthStart, $monthEnd) {
            $q->where('status', 'Released')->whereBetween('date_released', [$monthStart, $monthEnd]);
        })->count();

        $grossSales = \App\Models\ArkcrestCommissionRate::whereHas('commissionRequest', function($q) use ($monthStart, $monthEnd) {
            $q->where('status', 'Released')->whereBetween('date_released', [$monthStart, $monthEnd]);
        })->sum('arkcrest_commission');

        $pendingReservation = CommissionRequestSales::whereBetween('reservation_date', [$monthStart, $monthEnd])
            ->where(function($q) {
                $q->whereNull('downpayment_status')
                  ->orWhereNotIn('downpayment_status', ['Paid', 'Spot Paid']);
            })
            ->where(function($q) {
                $q->whereNull('client_status')
                  ->orWhere('client_status', '!=', 'Cancelled');
            })
            ->count();

        $cancelledReservation = CommissionRequestSales::whereBetween('reservation_date', [$monthStart, $monthEnd])
            ->where('client_status', 'Cancelled')
            ->count();

        $totalReservation = $units + $pendingReservation - $cancelledReservation;

        $yearStart = now()->startOfYear()->toDateString();
        $yearEnd   = now()->endOfYear()->toDateString();
        $yearlySales = \App\Models\ArkcrestCommissionRate::whereHas('commissionRequest', function($q) use ($yearStart, $yearEnd) {
            $q->where('status', 'Released')->whereBetween('date_released', [$yearStart, $yearEnd]);
        })->sum('arkcrest_commission');

        $monthlySales = [];
        for ($m = 1; $m <= 12; $m++) {
            $report = SummaryReport::where('month', $m)->where('year', $currentYear)->first();
            $monthlySales[] = $report ? (float)$report->gross_sales : 0;
        }

        $receivables = CommissionRequest::whereIn('status', ['Requested', 'Not Yet Released', 'Not Released'])->sum('commission');

        $departments = Department::where('slug', '!=', 'capex')->get();

        $departmentData = [];
        $totalExpenses = 0;
        $expenseBreakdown = [];
        
        foreach ($departments as $dept) {
            $deptExpenses = DepartmentalExpense::whereRaw('LOWER(TRIM(department)) = ?', [strtolower(trim($dept->name))])
                ->whereMonth('date_released', $currentMonthNumber)
                ->whereYear('date_released', $currentYear)
                ->sum('requested_amount');
            
            $remaining = $dept->allowable_budget - $deptExpenses;
            
            $departmentData[] = [
                'name' => $dept->name,
                'budget' => $dept->allowable_budget,
                'expenses' => $deptExpenses,
                'remaining' => $remaining,
                'percentage' => $dept->allowable_budget > 0 ? ($deptExpenses / $dept->allowable_budget) * 100 : 0
            ];

            $categories = [];
            $requests = DepartmentalExpense::whereRaw('LOWER(TRIM(department)) = ?', [strtolower(trim($dept->name))])
                ->whereMonth('date_released', $currentMonthNumber)
                ->whereYear('date_released', $currentYear)
                ->whereNotNull('requested_amount')
                ->where('requested_amount', '>', 0)
                ->get();
            
            foreach ($requests as $request) {
                $catName = $request->category;
                if (!isset($categories[$catName])) {
                    $categories[$catName] = 0;
                }
                $categories[$catName] += $request->requested_amount;
            }
            
            $expenseBreakdown[$dept->name] = $categories;
            $totalExpenses += $deptExpenses;
        }
        
        $tomorrowReleases = CommissionRequestSales::whereDate('date_released', Carbon::tomorrow()->toDateString())
            ->whereIn('status', ['Not Yet Released', 'Not Released'])
            ->orderBy('agent_name')
            ->get();

        $today = Carbon::today()->toDateString();
        $todayTrips     = TripSchedule::whereDate('tripping_date', $today)->whereIn('status', ['confirmed', 'pending'])->count();
        $todayReleases  = CommissionRequestSales::whereDate('date_released', $today)->whereIn('status', ['Not Yet Released', 'Not Released'])->count();
        $todayEvents    = CommissionRequestSales::where(function($q) use ($today) {
            $q->whereDate('reservation_date', $today)
              ->orWhereDate('date_of_downpayment', $today);
        })->count();

        return view('dashboard', compact('departmentData', 'totalExpenses', 'expenseBreakdown', 'currentMonth', 'currentYear', 'units', 'grossSales', 'yearlySales', 'receivables', 'monthlySales', 'tomorrowReleases', 'todayTrips', 'todayReleases', 'todayEvents', 'pendingReservation', 'cancelledReservation', 'totalReservation'));
    }
}
