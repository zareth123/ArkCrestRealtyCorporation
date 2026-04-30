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
        // Sales Agent / Sales Manager can only access the site visit form
        $user = auth()->user();
        $salesPositions = ['sales agent', 'sales manager'];
        if (in_array(strtolower(trim($user->position ?? '')), $salesPositions)) {
            return redirect()->route('tripping');
        }

        // If dashboard is hidden for this user, redirect to first visible page
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

        // Get current month and year
        $currentMonth = now()->format('F');
        $currentYear = now()->format('Y');
        $currentMonthNumber = now()->month;
        
        // Monthly Performance from Client Database (CommissionRequestSales)
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd   = now()->endOfMonth()->toDateString();

        // Units = distinct block/lot numbers with downpayment made this month (not cancelled)
        $units = CommissionRequestSales::whereNotNull('date_of_downpayment')
            ->whereBetween('date_of_downpayment', [$monthStart, $monthEnd])
            ->where('client_status', '!=', 'Cancelled')
            ->whereNotNull('block_lot_number')
            ->distinct('block_lot_number')
            ->count('block_lot_number');

        // Gross Sales = net_tcp of clients who have made a downpayment this month
        $grossSales = CommissionRequestSales::whereNotNull('date_of_downpayment')
            ->whereBetween('date_of_downpayment', [$monthStart, $monthEnd])
            ->where('client_status', '!=', 'Cancelled')
            ->sum('net_tcp');

        // Pending Reservation = in client database, downpayment not yet paid, not cancelled
        $pendingReservation = CommissionRequestSales::where(function($q) {
                $q->whereNull('downpayment_status')
                  ->orWhereNotIn('downpayment_status', ['Paid', 'Spot Paid']);
            })
            ->where('client_status', '!=', 'Cancelled')
            ->whereNull('client_status')
            ->count();

        // Cancelled Reservation = all cancelled records in client database
        $cancelledReservation = CommissionRequestSales::where('client_status', 'Cancelled')->count();

        // Total Reservation = units + pending - cancelled
        $totalReservation = $units + $pendingReservation - $cancelledReservation;

        // Yearly total sales from summary reports
        $yearlySales = SummaryReport::where('year', $currentYear)->sum('gross_sales');

        // Monthly sales trend for chart (all 12 months of current year)
        $monthlySales = [];
        for ($m = 1; $m <= 12; $m++) {
            $report = SummaryReport::where('month', $m)->where('year', $currentYear)->first();
            $monthlySales[] = $report ? (float)$report->gross_sales : 0;
        }

        // Receivables = all "Not Yet Released" commissions (all time, not just current month)
        $receivables = CommissionRequestSales::where('status', 'Not Yet Released')->sum('commission');
        
        // Exclude CAPEX from dashboard
        $departments = Department::where('slug', '!=', 'capex')->get();
        
        // Calculate total expenses per department from commission requests (current month only)
        $departmentData = [];
        $totalExpenses = 0;
        $expenseBreakdown = [];
        
        foreach ($departments as $dept) {
            // Sum requested_amount from commission_requests for this department (current month only)
            // Use case-insensitive comparison and trim whitespace
            // Filter by date_requested month
            $deptExpenses = DepartmentalExpense::whereRaw('LOWER(TRIM(department)) = ?', [strtolower(trim($dept->name))])
                ->whereMonth('date_requested', $currentMonthNumber)
                ->whereYear('date_requested', $currentYear)
                ->sum('requested_amount');
            
            $remaining = $dept->allowable_budget - $deptExpenses;
            
            $departmentData[] = [
                'name' => $dept->name,
                'budget' => $dept->allowable_budget,
                'expenses' => $deptExpenses,
                'remaining' => $remaining,
                'percentage' => $dept->allowable_budget > 0 ? ($deptExpenses / $dept->allowable_budget) * 100 : 0
            ];
            
            // Get expense breakdown by category for this department (current month only)
            $categories = [];
            $requests = DepartmentalExpense::whereRaw('LOWER(TRIM(department)) = ?', [strtolower(trim($dept->name))])
                ->whereMonth('date_requested', $currentMonthNumber)
                ->whereYear('date_requested', $currentYear)
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
        
        // Tomorrow's commission releases (for notification banner)
        $tomorrowReleases = CommissionRequestSales::whereDate('date_released', Carbon::tomorrow()->toDateString())
            ->where('status', 'Not Yet Released')
            ->orderBy('agent_name')
            ->get();

        // Today's summary for banner
        $today = Carbon::today()->toDateString();
        $todayTrips     = TripSchedule::whereDate('tripping_date', $today)->whereIn('status', ['confirmed', 'pending'])->count();
        $todayReleases  = CommissionRequestSales::whereDate('date_released', $today)->where('status', 'Not Yet Released')->count();
        $todayEvents    = CommissionRequestSales::where(function($q) use ($today) {
            $q->whereDate('reservation_date', $today)
              ->orWhereDate('date_of_downpayment', $today);
        })->count();

        return view('dashboard', compact('departmentData', 'totalExpenses', 'expenseBreakdown', 'currentMonth', 'currentYear', 'units', 'grossSales', 'yearlySales', 'receivables', 'monthlySales', 'tomorrowReleases', 'todayTrips', 'todayReleases', 'todayEvents', 'pendingReservation', 'cancelledReservation', 'totalReservation'));
    }
}
