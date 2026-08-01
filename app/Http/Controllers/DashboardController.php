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
    public function index(Request $request)
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

        // Month/year being viewed — defaults to today, but can be overridden
        // via ?month=&year= (see the filter dropdown in dashboard.blade.php).
        // "Today" / "Tomorrow" items below (notifications, today's pills)
        // intentionally stay tied to the real current date regardless of
        // this filter, since those are live operational indicators rather
        // than historical reporting.
        $selectedMonth = (int) $request->query('month', now()->month);
        $selectedYear  = (int) $request->query('year', now()->year);
        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = now()->month;
        }
        $viewDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1);
        $isCurrentMonth = $viewDate->isSameMonth(now());

        $currentMonth = $viewDate->format('F');
        $currentYear = $viewDate->format('Y');
        $currentMonthNumber = $viewDate->month;

        $monthStart = $viewDate->copy()->startOfMonth()->toDateString();
        $monthEnd   = $viewDate->copy()->endOfMonth()->toDateString();

        // Units and Gross Sales are whatever was manually entered on the
        // Summary Report page for this month/year — not computed from
        // ArkcrestCommissionRate/CommissionRequest anymore, since Summary
        // Report is the source of truth the Finance team actually fills in.
        $summaryReportForMonth = SummaryReport::where('month', $currentMonthNumber)
            ->where('year', $currentYear)
            ->first();
        $units = $summaryReportForMonth ? (float) $summaryReportForMonth->units : 0;
        $grossSales = $summaryReportForMonth ? (float) $summaryReportForMonth->gross_sales : 0;

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

        $yearStart = $viewDate->copy()->startOfYear()->toDateString();
        $yearEnd   = $viewDate->copy()->endOfYear()->toDateString();
        $yearlySales = \App\Models\ArkcrestCommissionRate::whereHas('commissionRequest', function($q) use ($yearStart, $yearEnd) {
            $q->where('status', 'Released')->whereBetween('date_released', [$yearStart, $yearEnd]);
        })->sum('arkcrest_commission');

        $monthlySales = [];
        for ($m = 1; $m <= 12; $m++) {
            $report = SummaryReport::where('month', $m)->where('year', $currentYear)->first();
            $monthlySales[] = $report ? (float)$report->gross_sales : 0;
        }

        $receivables = CommissionRequest::where('status', 'Not Yet Released')->sum('commission');

        $departments = Department::where('slug', '!=', 'capex')->get();

        $departmentData = [];
        $totalExpenses = 0;
        $expenseBreakdown = [];
        
        foreach ($departments as $dept) {
            // Same formula as the "Departments Expenses" cards on the
            // Departmental Expenses page (DepartmentalExpensesController::
            // remainingBudget) — only LIQUIDATED requests count, using the
            // verified total_expenses amount. Not-yet-liquidated requests
            // are excluded entirely so the two pages always agree. This is
            // scoped to the current month here (that page is all-time).
            $requests = DepartmentalExpense::whereRaw('LOWER(TRIM(department)) = ?', [strtolower(trim($dept->name))])
                ->where('status', 'LIQUIDATED')
                ->whereMonth('date_released', $currentMonthNumber)
                ->whereYear('date_released', $currentYear)
                ->get();

            $deptExpenses = $requests->sum(fn($r) => (float) $r->total_expenses);

            $remaining = $dept->allowable_budget - $deptExpenses;

            $departmentData[] = [
                'name' => $dept->name,
                'budget' => $dept->allowable_budget,
                'expenses' => $deptExpenses,
                'remaining' => $remaining,
                'percentage' => $dept->allowable_budget > 0 ? ($deptExpenses / $dept->allowable_budget) * 100 : 0
            ];

            $categories = [];
            foreach ($requests as $request) {
                $amount = (float) $request->total_expenses;
                if ($amount <= 0) {
                    continue;
                }
                $catName = $request->category;
                if (!isset($categories[$catName])) {
                    $categories[$catName] = 0;
                }
                $categories[$catName] += $amount;
            }

            $expenseBreakdown[$dept->name] = $categories;
            $totalExpenses += $deptExpenses;
        }
        
        $tomorrowReleases = CommissionRequestSales::whereDate('date_released', Carbon::tomorrow()->toDateString())
            ->whereIn('status', ['Not Yet Released', 'Not Released'])
            ->orderBy('agent_name')
            ->get();

        $today = Carbon::today()->toDateString();
        $todayTrips = TripSchedule::whereDate('tripping_date', $today)->whereIn('status', ['confirmed', 'pending'])->count();

        // Commission releases due today (PR #177)
        $todayReleaseRecords = CommissionRequest::whereDate('date_released', $today)
            ->whereIn('status', ['Not Yet Released', 'Not Released'])
            ->orderBy('agent_name')
            ->get();
        $todayReleases      = $todayReleaseRecords->count();
        $todayReleasesTotal = $todayReleaseRecords->sum('commission');

        // Expense releases due today. Unlike commissions, an expense's
        // date_released is only ever populated once it's actually RELEASED
        // (normalizeWorkflow() forces it to null while release_status is
        // NOT YET RELEASED), so we key off date_requested instead to find
        // today's pending expense releases.
        $todayExpenseReleaseRecords = DepartmentalExpense::whereDate('date_requested', $today)
            ->where('release_status', 'NOT YET RELEASED')
            ->orderBy('department')
            ->orderBy('requestor_name')
            ->get();
        $todayExpenseReleases      = $todayExpenseReleaseRecords->count();
        $todayExpenseReleasesTotal = $todayExpenseReleaseRecords->sum('requested_amount');

        $todayEvents = CommissionRequestSales::where(function($q) use ($today) {
            $q->whereDate('reservation_date', $today)
              ->orWhereDate('date_of_downpayment', $today);
        })->count();

        $availableYears = collect([now()->year])
            ->merge(SummaryReport::pluck('year'))
            ->merge(DepartmentalExpense::whereNotNull('date_released')->selectRaw('YEAR(date_released) as year')->pluck('year'))
            ->map(fn($y) => (int) $y)
            ->unique()
            ->sortDesc()
            ->values();

        return view('dashboard', compact('departmentData', 'totalExpenses', 'expenseBreakdown', 'currentMonth', 'currentYear', 'units', 'grossSales', 'yearlySales', 'receivables', 'monthlySales', 'tomorrowReleases', 'todayTrips', 'todayReleases', 'todayReleaseRecords', 'todayReleasesTotal', 'todayEvents', 'todayExpenseReleaseRecords', 'todayExpenseReleases', 'todayExpenseReleasesTotal', 'pendingReservation', 'cancelledReservation', 'totalReservation', 'selectedMonth', 'selectedYear', 'isCurrentMonth', 'availableYears'));
    }
}