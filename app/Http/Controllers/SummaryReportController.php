<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionRequest;
use App\Models\DepartmentalExpense;
use App\Models\SummaryReport;
use Carbon\Carbon;

class SummaryReportController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        // Get available years from commission_requests and summary_reports
        $availablePeriods = DepartmentalExpense::selectRaw('MONTH(date_released) as month, YEAR(date_released) as year')
            ->whereNotNull('date_released')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Also include years from summary_reports (in case data was saved without expenses)
        $summaryYears = SummaryReport::selectRaw('year')->distinct()->pluck('year');
        $expenseYears = $availablePeriods->pluck('year')->unique();
        $allYears = $expenseYears->merge($summaryYears)->unique()->sort()->values();

        // Always include current year
        if (!$allYears->contains($currentYear)) {
            $allYears->push($currentYear);
        }
        $allYears = $allYears->sortDesc()->values();
        
        // Get selected month/year from request or use current
        $selectedMonth = $request->get('month', $currentMonth);
        $selectedYear = $request->get('year', $currentYear);
        
        // Get or new summary report for selected period (don't save until user explicitly updates)
        $summaryReport = SummaryReport::firstOrNew(
            ['month' => $selectedMonth, 'year' => $selectedYear],
            ['units' => 0, 'gross_sales' => 0, 'coh' => 0]
        );
        
        // Get expenses by department for selected month/year — dynamic from Department model
        $allDepts = \App\Models\Department::orderBy('name')->get();
        $departments = $allDepts->pluck('name', 'name')->toArray();

        // Also include any departments found in expenses that may not be in Department table
        $expenseDepts = DepartmentalExpense::whereYear('date_released', $selectedYear)
            ->whereMonth('date_released', $selectedMonth)
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department');
        foreach ($expenseDepts as $d) {
            if (!isset($departments[$d])) {
                $departments[$d] = $d;
            }
        }

        $departmentExpenses = [];
        $totalExpenses = 0;

        foreach (array_keys($departments) as $deptKey) {
            $expenses = DepartmentalExpense::where('department', $deptKey)
                ->whereYear('date_released', $selectedYear)
                ->whereMonth('date_released', $selectedMonth)
                ->selectRaw('SUM(COALESCE(total_expenses, requested_amount, 0)) as total')
                ->value('total') ?? 0;

            $departmentExpenses[$deptKey] = $expenses;
            $totalExpenses += $expenses;
        }
        
        // Units, Pending, Cancelled, Total Reservation for selected month (from ARC Sales)
        $monthStart = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth()->toDateString();
        $monthEnd   = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->endOfMonth()->toDateString();

        $units = \App\Models\ArkcrestCommissionRate::whereHas('commissionRequest', function($q) use ($monthStart, $monthEnd) {
            $q->where('status', 'Released')->whereBetween('date_released', [$monthStart, $monthEnd]);
        })->count();

        $grossSalesFromClient = \App\Models\ArkcrestCommissionRate::whereHas('commissionRequest', function($q) use ($monthStart, $monthEnd) {
            $q->where('status', 'Released')->whereBetween('date_released', [$monthStart, $monthEnd]);
        })->sum('arkcrest_commission');

        $pendingReservation = \App\Models\CommissionRequestSales::whereBetween('reservation_date', [$monthStart, $monthEnd])
            ->where(function($q) { $q->whereNull('downpayment_status')->orWhereNotIn('downpayment_status', ['Paid', 'Spot Paid']); })
            ->where(function($q) { $q->whereNull('client_status')->orWhere('client_status', '!=', 'Cancelled'); })
            ->count();

        $cancelledReservation = \App\Models\CommissionRequestSales::whereBetween('reservation_date', [$monthStart, $monthEnd])
            ->where('client_status', 'Cancelled')->count();

        $totalReservation = $units + $pendingReservation - $cancelledReservation;

        // Net TCP from Client Database for selected month — based on date_of_downpayment
        $netTcp = \App\Models\CommissionRequestSales::whereNotNull('date_of_downpayment')
            ->whereBetween('date_of_downpayment', [$monthStart, $monthEnd])
            ->where('client_status', '!=', 'Cancelled')
            ->sum('net_tcp');

        // Calculate net sales from the editable gross_sales value.
        // Trust the saved record whenever one exists for this period — a saved
        // value of 0 is a valid, intentional value and should not be silently
        // replaced by the auto-computed suggestion. Same treatment for units,
        // so the summary cards at the top of the page stay in sync with what
        // was actually saved instead of always showing the live commission-data
        // calculation.
        $editableUnits = $summaryReport->exists
            ? $summaryReport->units
            : $units;
        $editableGrossSales = $summaryReport->exists
            ? $summaryReport->gross_sales
            : $grossSalesFromClient;
        $netSales = $editableGrossSales - $totalExpenses;
        
        return view('summary-report', compact(
            'availablePeriods',
            'allYears',
            'selectedMonth',
            'selectedYear',
            'summaryReport',
            'departments',
            'departmentExpenses',
            'totalExpenses',
            'netSales',
            'units',
            'grossSalesFromClient',
            'editableUnits',
            'editableGrossSales',
            'pendingReservation',
            'cancelledReservation',
            'totalReservation',
            'netTcp'
        ));
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'units' => 'required|numeric',
            'gross_sales' => 'required|numeric',
            'coh' => 'required|numeric'
        ]);

        // Check period lock
        if (\App\Models\PeriodLock::isLocked((int)$validated['month'], (int)$validated['year'])) {
            return response()->json([
                'success' => false,
                'message' => date('F Y', mktime(0,0,0,$validated['month'],1,$validated['year'])) . ' is locked. No changes allowed for this period.'
            ], 422);
        }
        
        $report = SummaryReport::updateOrCreate(
            ['month' => $validated['month'], 'year' => $validated['year']],
            [
                'units' => $validated['units'],
                'gross_sales' => $validated['gross_sales'],
                'coh' => $validated['coh']
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Summary report updated successfully',
            'data' => $report
        ]);
    }
    
    public function yearly(Request $request)
    {
        $currentYear = date('Y');
        $selectedYear = $request->get('year', $currentYear);

        // Get distinct years from data
        $availableYears = DepartmentalExpense::selectRaw('YEAR(date_released) as year')
            ->whereNotNull('date_released')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Ensure current year is always included
        if (!$availableYears->contains($currentYear)) {
            $availableYears->prepend($currentYear);
        }
        
        // Get all months data for the selected year
        $monthlyData = [];

        // Dynamic departments
        $allDepts = \App\Models\Department::orderBy('name')->get();
        $departments = $allDepts->pluck('name', 'name')->toArray();
        // Also include any departments found in expenses
        $expenseDepts = DepartmentalExpense::whereYear('date_released', $selectedYear)
            ->whereNotNull('department')->distinct()->pluck('department');
        foreach ($expenseDepts as $d) {
            if (!isset($departments[$d])) $departments[$d] = $d;
        }
        
        // Initialize totals
        $yearlyTotals = [
            'units' => 0,
            'gross_sales' => 0,
            'coh' => 0,
            'total_expenses' => 0,
            'net_sales' => 0
        ];
        
        foreach ($departments as $deptKey => $deptName) {
            $yearlyTotals[$deptKey] = 0;
        }
        
        // Get data for each month (January to December)
        for ($month = 1; $month <= 12; $month++) {
            $summaryReport = SummaryReport::where('month', $month)
                ->where('year', $selectedYear)
                ->first();
            
            if (!$summaryReport) {
                $summaryReport = new SummaryReport([
                    'month' => $month,
                    'year' => $selectedYear,
                    'units' => 0,
                    'gross_sales' => 0,
                    'coh' => 0
                ]);
            }
            
            // Get expenses by department for this month
            $departmentExpenses = [];
            $monthTotalExpenses = 0;
            
            foreach ($departments as $deptKey => $deptName) {
                $expenses = DepartmentalExpense::where('department', $deptKey)
                    ->whereYear('date_released', $selectedYear)
                    ->whereMonth('date_released', $month)
                    ->selectRaw('SUM(COALESCE(total_expenses, requested_amount, 0)) as total')
                    ->value('total') ?? 0;
                
                $departmentExpenses[$deptKey] = $expenses;
                $monthTotalExpenses += $expenses;
                $yearlyTotals[$deptKey] += $expenses;
            }
            
            $netSales = $summaryReport->gross_sales - $monthTotalExpenses;
            
            $monthlyData[$month] = [
                'summary' => $summaryReport,
                'expenses' => $departmentExpenses,
                'total_expenses' => $monthTotalExpenses,
                'net_sales' => $netSales
            ];
            
            // Add to yearly totals
            $yearlyTotals['units'] += $summaryReport->units;
            $yearlyTotals['gross_sales'] += $summaryReport->gross_sales;
            $yearlyTotals['coh'] += $summaryReport->coh;
            $yearlyTotals['total_expenses'] += $monthTotalExpenses;
            $yearlyTotals['net_sales'] += $netSales;
        }
        
        return view('summary-report-yearly', compact(
            'selectedYear',
            'availableYears',
            'monthlyData',
            'departments',
            'yearlyTotals'
        ));
    }
}