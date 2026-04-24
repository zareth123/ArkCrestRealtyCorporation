<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionRequest;
use App\Models\SummaryReport;
use Carbon\Carbon;

class SummaryReportController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        // Get available years from commission_requests and summary_reports
        $availablePeriods = CommissionRequest::selectRaw('MONTH(date_requested) as month, YEAR(date_requested) as year')
            ->whereNotNull('date_requested')
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
        
        // Get expenses by department for selected month/year
        $departments = [
            'Administrative' => 'Administrative Expenses',
            'Sales & Marketing' => 'Sales & Marketing Expenses',
            'Human Resource' => 'Human Resource Expenses',
            'Finance' => 'Finance Expenses',
            'Executive' => 'Executive Expenses',
            'CAPEX' => 'CAPEX'
        ];
        
        $departmentExpenses = [];
        $totalExpenses = 0;
        
        foreach ($departments as $deptKey => $deptName) {
            $expenses = CommissionRequest::where('department', $deptKey)
                ->whereYear('date_requested', $selectedYear)
                ->whereMonth('date_requested', $selectedMonth)
                ->sum('requested_amount');
            
            $departmentExpenses[$deptKey] = $expenses;
            $totalExpenses += $expenses;
        }
        
        // Calculate net sales
        $netSales = $summaryReport->gross_sales - $totalExpenses;
        
        return view('summary-report', compact(
            'availablePeriods',
            'allYears',
            'selectedMonth',
            'selectedYear',
            'summaryReport',
            'departments',
            'departmentExpenses',
            'totalExpenses',
            'netSales'
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
        $availableYears = \App\Models\CommissionRequest::selectRaw('YEAR(date_requested) as year')
            ->whereNotNull('date_requested')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Ensure current year is always included
        if (!$availableYears->contains($currentYear)) {
            $availableYears->prepend($currentYear);
        }
        
        // Get all months data for the selected year
        $monthlyData = [];
        $departments = [
            'Administrative' => 'Administrative Expenses',
            'Sales & Marketing' => 'Sales & Marketing Expenses',
            'Human Resource' => 'Human Resource Expenses',
            'Finance' => 'Finance Expenses',
            'Executive' => 'Executive Expenses',
            'CAPEX' => 'CAPEX'
        ];
        
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
                $expenses = CommissionRequest::where('department', $deptKey)
                    ->whereYear('date_requested', $selectedYear)
                    ->whereMonth('date_requested', $month)
                    ->sum('requested_amount');
                
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
