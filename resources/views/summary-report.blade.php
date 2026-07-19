@extends('layouts.dashboard')

@section('content')

<!-- Add Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="summary-report-page">
    <!-- Page Banner -->
    <div style="background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:24px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25);">
        <div style="position:absolute;top:-40px;right:-40px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.06);"></div>
        <div style="position:absolute;top:40px;right:120px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.04);"></div>
        <div style="position:relative;z-index:2;">
            <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;">Finance</div>
            <h1 style="font-size:24px;font-weight:700;color:white;margin:0 0 6px;">Summary Report</h1>
            <p style="font-size:13px;color:rgba(255,255,255,.75);margin:0;">Monthly & yearly financial performance overview</p>
        </div>
    </div>

    <!-- View Toggle Buttons -->
    <div id="viewToggleContainer" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div id="periodSelectorContainer">
            <div>
                <label style="font-weight: 600; margin-bottom: 8px; display: block;">Select Period</label>
                <div style="display:flex;align-items:center;gap:10px;">
                    <select id="monthSelector" class="form-control" style="min-width:130px;" onchange="loadPeriod()">
                        <option value="1" {{ $selectedMonth == 1 ? 'selected' : '' }}>January</option>
                        <option value="2" {{ $selectedMonth == 2 ? 'selected' : '' }}>February</option>
                        <option value="3" {{ $selectedMonth == 3 ? 'selected' : '' }}>March</option>
                        <option value="4" {{ $selectedMonth == 4 ? 'selected' : '' }}>April</option>
                        <option value="5" {{ $selectedMonth == 5 ? 'selected' : '' }}>May</option>
                        <option value="6" {{ $selectedMonth == 6 ? 'selected' : '' }}>June</option>
                        <option value="7" {{ $selectedMonth == 7 ? 'selected' : '' }}>July</option>
                        <option value="8" {{ $selectedMonth == 8 ? 'selected' : '' }}>August</option>
                        <option value="9" {{ $selectedMonth == 9 ? 'selected' : '' }}>September</option>
                        <option value="10" {{ $selectedMonth == 10 ? 'selected' : '' }}>October</option>
                        <option value="11" {{ $selectedMonth == 11 ? 'selected' : '' }}>November</option>
                        <option value="12" {{ $selectedMonth == 12 ? 'selected' : '' }}>December</option>
                    </select>
                    <select id="yearSelector" class="form-control" style="min-width:100px;" onchange="loadPeriod()">
                        @foreach($allYears as $year)
                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="view-toggle" style="display: flex; gap: 10px; background: #f0f2f5; padding: 4px; border-radius: 8px;">
            <button id="monthlyViewBtn" class="view-btn active" onclick="switchView('monthly')">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Monthly View
            </button>
            <button id="yearlyViewBtn" class="view-btn" onclick="switchView('yearly')">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Yearly View
            </button>
        </div>
    </div>

    <!-- Monthly View -->
    <div id="monthlyView" class="view-container">

        <!-- Summary Cards -->
        @if(!in_array('summary-report.cards', $hiddenSections))
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">

            {{-- Card 1: Units + Gross Sales --}}
            <div class="summary-card">
                <div class="card-icon" style="background: #10b981;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div class="card-content">
                    <div class="card-label">Units</div>
                    <div class="card-value" id="card_units">{{ number_format($editableUnits, 0) }}</div>
                    <div style="font-size:12px;color:#64748b;margin-top:4px;">
                        Gross Sales: <strong id="card_gross_sales">&#8369;{{ number_format($editableGrossSales, 0) }}</strong>
                    </div>
                </div>
            </div>

            {{-- Card 2: Total Expenses --}}
            <div class="summary-card">
                <div class="card-icon" style="background: #3b82f6;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="card-content">
                    <div class="card-label">Total Expenses</div>
                    <div class="card-value" id="card_total_expenses"><span style="font-size:20px;margin-right:4px;">&#8369;</span>{{ number_format($totalExpenses, 2) }}</div>
                </div>
            </div>

            {{-- Card 4: Net Sales --}}
            <div class="summary-card">
                <div class="card-icon" style="background: #f59e0b;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <div class="card-content">
                    <div class="card-label">Net Sales</div>
                    <div class="card-value" id="card_net_sales"><span style="font-size:20px;margin-right:4px;">&#8369;</span>{{ number_format($netSales, 2) }}</div>
                </div>
            </div>

        </div>
        @endif

        <!-- Charts Row -->
        @if(!in_array('summary-report.charts', $hiddenSections))
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
            <!-- Department Expenses Chart -->
            <div class="chart-card" style="position: relative;">
                <h3 class="chart-title">
                    <svg style="width: 20px; height: 20px; display: inline-block; vertical-align: middle; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Department Expenses Breakdown
                </h3>
                <div style="position: relative; height: 350px;">
                    <canvas id="deptExpensesChart"></canvas>
                </div>
            </div>

            <!-- Expense Distribution Pie Chart -->
            <div class="chart-card" style="position: relative;">
                <h3 class="chart-title">
                    <svg style="width: 20px; height: 20px; display: inline-block; vertical-align: middle; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    Expense Distribution
                </h3>
                <div style="position: relative; height: 350px; display: flex; align-items: center; justify-content: center;">
                    <canvas id="expenseDistributionChart"></canvas>
                </div>
            </div>
        </div>
        @endif

        <!-- Comprehensive Summary Report Table -->
        @if(!in_array('summary-report.table', $hiddenSections))
        <div class="report-table-card">
            <h3 style="margin-bottom: 20px; font-size: 18px; font-weight: 600; color: #1e4575;">
                <svg style="width: 20px; height: 20px; display: inline-block; vertical-align: middle; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Summary Report
            </h3>
            <table class="report-table">
                <tbody>
                    <tr class="editable-row header-row">
                        <td class="label-cell">Units</td>
                        <td class="value-cell">
                            <input type="text" id="units" class="form-control-inline"
                                value="{{ $summaryReport->exists ? $summaryReport->units : $units }}"
                                oninput="recalcNetSales()">
                        </td>
                    </tr>
                    <tr class="editable-row header-row">
                        <td class="label-cell">Gross Sales</td>
                        <td class="value-cell">
                            <input type="text" id="gross_sales" class="form-control-inline"
                                value="{{ $summaryReport->exists ? $summaryReport->gross_sales : $grossSalesFromClient }}"
                                oninput="recalcNetSales()">
                        </td>
                    </tr>
                    <tr class="divider-row">
                        <td colspan="2"><div class="divider-line"></div></td>
                    </tr>
                    @foreach($departments as $deptKey => $deptName)
                    <tr class="dept-row">
                        <td class="label-cell">{{ $deptName }}</td>
                        <td class="value-cell">₱ {{ number_format($departmentExpenses[$deptKey], 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="divider-row">
                        <td colspan="2"><div class="divider-line"></div></td>
                    </tr>
                    <tr class="total-row">
                        <td class="label-cell">Total Expenses</td>
                        <td class="value-cell" id="total_expenses_display">₱ {{ number_format($totalExpenses, 2) }}</td>
                    </tr>
                    <tr class="net-sales-row">
                        <td class="label-cell">Net Sales</td>
                        <td class="value-cell" id="net_sales">₱ {{ number_format($netSales, 2) }}</td>
                    </tr>
                    <tr class="divider-row">
                        <td colspan="2"><div class="divider-line"></div></td>
                    </tr>
                    <tr class="editable-row header-row">
                        <td class="label-cell">COH</td>
                        <td class="value-cell">
                            <input type="text" id="coh" class="form-control-inline" value="{{ $summaryReport->coh }}">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: right; padding: 20px 12px; background: #f9fafb;">
                            <button onclick="saveSummaryReport()" class="btn-submit">
                                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                Save Monthly Data
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </div>
    <div id="yearlyView" class="view-container" style="display: none;">
        <!-- Year Selector (moved to container above) -->
        <script>
            // This will be executed when yearly view is shown
        </script>
        <div style="text-align: center; padding: 60px 20px;">
            <svg style="width: 80px; height: 80px; margin: 0 auto 20px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <h3 style="font-size: 20px; color: #374151; margin-bottom: 8px;">Yearly View Coming Soon</h3>
            <p style="color: #6b7280;">This feature will show annual trends and comparisons</p>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toastNotification" class="custom-toast">
    <div class="toast-icon" id="toastIcon"></div>
    <div class="toast-content">
        <div class="toast-title" id="toastTitle"></div>
        <div class="toast-message" id="toastMessage"></div>
    </div>
</div>

<style>
/* View Toggle */
.view-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: none;
    background: transparent;
    color: #6b7280;
    font-weight: 500;
    font-size: 14px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.view-btn:hover {
    background: #e5e7eb;
}

.view-btn.active {
    background: white;
    color: #1e4575;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Summary Cards */
.summary-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    min-width: 0;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.card-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.card-icon svg {
    width: 26px;
    height: 26px;
}

.card-content {
    flex: 1;
    min-width: 0;
}

.card-label {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 6px;
    font-weight: 500;
    white-space: nowrap;
}

.card-value {
    font-size: clamp(16px, 1.8vw, 26px);
    font-weight: 700;
    color: #111827;
    line-height: 1;
    display: flex;
    align-items: baseline;
    white-space: nowrap;
}

.card-value span {
    font-size: clamp(12px, 1.4vw, 20px) !important;
}

@media (max-width: 1400px) {
    .card-value {
        font-size: clamp(14px, 1.5vw, 22px);
    }
    .card-value span {
        font-size: clamp(11px, 1.2vw, 18px) !important;
    }
}

@media (max-width: 1200px) {
    .card-value {
        font-size: clamp(12px, 1.3vw, 20px);
    }
    .card-value span {
        font-size: clamp(10px, 1vw, 16px) !important;
    }
}

@media (max-width: 1000px) {
    .card-value {
        font-size: clamp(11px, 1.2vw, 18px);
    }
    .card-value span {
        font-size: clamp(9px, 0.9vw, 14px) !important;
    }
}

/* Chart Cards */
.chart-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    min-width: 0;      /* prevents the card from being forced wider than its grid column */
    overflow: hidden;  /* NEW: clips anything that still tries to overflow, instead of pushing the whole page wider */
}

.chart-canvas-wrap {
    position: relative;
    height: 350px;
    width: 100%;       /* NEW: force the canvas's container to the card's actual width */
}
.chart-canvas-wrap canvas {
    max-width: 100% !important;  /* NEW: hard cap so Chart.js can never render wider than the container */
}

.chart-title {
    font-size: 17px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #111827;
    display: flex;
    align-items: center;
}

/* Data Entry Card */
.data-entry-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.form-control-inline {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
}

.form-control-inline:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.editable-row {
    background: #f9fafb;
}

.spacer-row {
    height: 10px;
}

.spacer-row td {
    padding: 0 !important;
    border: none !important;
}

/* Report Table Card */
.report-table-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    overflow: hidden;
    border-radius: 8px;
}

.report-table td {
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
}

.label-cell {
    font-weight: 600;
    color: #1e4575;
    font-size: 14px;
}

.value-cell {
    text-align: right;
    font-weight: 600;
    color: #111827;
    font-size: 14px;
}

.header-row {
    background: linear-gradient(135deg, #1e4575 0%, #2563eb 100%);
}

.header-row .label-cell {
    color: white;
}

.header-row .value-cell {
    color: white;
}

.dept-row {
    transition: background 0.2s;
}

.dept-row:hover {
    background: #f0f4f8;
}

.divider-row {
    height: 2px;
}

.divider-row td {
    padding: 0 !important;
    border: none !important;
}

.divider-line {
    height: 2px;
    background: linear-gradient(90deg, #1e4575 0%, #A37929 50%, #1e4575 100%);
    margin: 8px 0;
}

.total-row {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    font-size: 15px;
    border-top: 3px solid #A37929;
    border-bottom: 3px solid #A37929;
}

.total-row td {
    padding: 18px 20px;
    font-weight: 700;
    color: #92400e;
}

.net-sales-row {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    font-size: 15px;
    border-bottom: 3px solid #059669;
}

.net-sales-row td {
    padding: 18px 20px;
    font-weight: 700;
    color: #065f46;
}

.btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: linear-gradient(135deg, #1e4575 0%, #2563eb 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(30, 69, 117, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(30, 69, 117, 0.4);
    background: linear-gradient(135deg, #1a3a5f 0%, #1d4ed8 100%);
}

.btn-submit:active {
    transform: translateY(0);
}

/* Toast */
.custom-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    transform: translateY(10px);
    background: white;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.15);
    padding: 14px 18px;
    display: none;
    align-items: center;
    gap: 12px;
    min-width: 280px;
    max-width: 380px;
    z-index: 10000;
    border-left: 4px solid #A37929;
    transition: all 0.3s ease;
}

.custom-toast.show {
    display: flex;
    transform: translateY(0);
    opacity: 1;
}

.custom-toast.hiding {
    opacity: 0;
    transform: translateY(10px);
}

.toast-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    flex-shrink: 0;
}

.custom-toast.success .toast-icon {
    background: #10b981;
    color: white;
}

.custom-toast.error .toast-icon {
    background: #ef4444;
    color: white;
}

.toast-content {
    flex: 1;
}

.toast-title {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 4px;
}

.toast-message {
    font-size: 13px;
    color: #6b7280;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
    to {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.9);
    }
}
</style>

<script>
const totalExpenses = {{ $totalExpenses }};
const departmentExpenses = @json($departmentExpenses);
const departments = @json(array_keys($departmentExpenses));

// Initialize Charts
let deptChart, pieChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    calculateNetSales();
    
    // Add comma formatting with a small delay to ensure inputs are ready
    setTimeout(function() {
        addCommaFormatting();
    }, 100);
    
    // Add input listeners for real-time updates
    document.getElementById('gross_sales').addEventListener('input', function() {
        calculateNetSales();
        updateSummaryCards();
    });
    
    document.getElementById('units').addEventListener('input', function() {
        updateSummaryCards();
    });
});

// Format number inputs with comma separators
function formatNumberInputs() {
    const inputs = document.querySelectorAll('#units, #gross_sales, #coh');
    
    inputs.forEach(input => {
        // Format initial value on page load
        if (input.value && input.value !== '') {
            const numValue = parseFloat(input.value.replace(/,/g, ''));
            if (!isNaN(numValue) && numValue !== 0) {
                input.value = formatNumberWithCommas(numValue);
            }
        }
        
        // Add blur event for formatting when user leaves the field
        input.addEventListener('blur', function(e) {
            const value = e.target.value.replace(/,/g, '').trim();
            if (value && value !== '') {
                const numValue = parseFloat(value);
                if (!isNaN(numValue)) {
                    e.target.value = formatNumberWithCommas(numValue);
                }
            }
        });
        
        // Add focus event to remove commas for easier editing
        input.addEventListener('focus', function(e) {
            const value = e.target.value.replace(/,/g, '');
            if (value && value !== '') {
                e.target.value = value;
            }
        });
    });
}

// Helper function to format number with commas
function formatNumberWithCommas(num) {
    const parts = num.toString().split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return parts.join('.');
}

// Add comma formatting to input fields
function addCommaFormatting() {
    const inputs = document.querySelectorAll('#units, #gross_sales, #coh');
    
    inputs.forEach(input => {
        // Format initial value on page load
        formatInputValue(input);
        
        // REAL-TIME formatting while typing
        input.addEventListener('input', function(e) {
            // Get cursor position
            let start = e.target.selectionStart;
            let originalValue = e.target.value;
            
            // Remove all commas
            let valueWithoutCommas = originalValue.replace(/,/g, '');
            
            // Only allow numbers and one decimal point
            valueWithoutCommas = valueWithoutCommas.replace(/[^\d.]/g, '');
            let parts = valueWithoutCommas.split('.');
            if (parts.length > 2) {
                valueWithoutCommas = parts[0] + '.' + parts[1];
            }
            
            // Add commas to integer part
            if (valueWithoutCommas) {
                let intPart = parts[0];
                let decPart = parts[1] !== undefined ? '.' + parts[1] : '';
                
                // Add commas every 3 digits from right
                intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                
                let newValue = intPart + decPart;
                e.target.value = newValue;
                
                // Calculate new cursor position
                let commasBeforeCursor = originalValue.substring(0, start).split(',').length - 1;
                let commasInNew = newValue.substring(0, start).split(',').length - 1;
                let newPosition = start + (commasInNew - commasBeforeCursor);
                
                e.target.setSelectionRange(newPosition, newPosition);
            }
        });
        
        // On blur, ensure proper formatting
        input.addEventListener('blur', function(e) {
            formatInputValue(e.target);
        });
    });
}

function formatInputValue(input) {
    let value = input.value.replace(/,/g, '');
    if (value && value !== '' && value !== '0') {
        const num = parseFloat(value);
        if (!isNaN(num) && num !== 0) {
            input.value = num.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }
    }
}

function updateSummaryCards() {
    const units = parseFloat(document.getElementById('units').value.replace(/,/g, '')) || 0;
    const grossSales = parseFloat(document.getElementById('gross_sales').value.replace(/,/g, '')) || 0;
    const netSales = grossSales - totalExpenses;
    
    // Update Units card
    document.getElementById('card_units').textContent = units.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
    
    // Update Gross Sales card
    const cardGross = document.getElementById('card_gross_sales');
    if (cardGross) cardGross.textContent = '₱' + grossSales.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
    
    // Update Net Sales card
    document.getElementById('card_net_sales').innerHTML = '<span style="font-size: 20px; margin-right: 4px;">₱</span>' + netSales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function initializeCharts() {
    // Department Expenses Bar Chart
    const deptCtx = document.getElementById('deptExpensesChart').getContext('2d');
    deptChart = new Chart(deptCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(departmentExpenses),
            datasets: [{
                label: 'Expenses',
                data: Object.values(departmentExpenses),
                backgroundColor: [
                    '#3b82f6',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6',
                    '#ec4899'
                ],
                borderRadius: 8,
                barPercentage: 0.85,
                categoryPercentage: 0.9
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return 'Expenses: ₱' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        },
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                }
            }
        }
    });

    // Expense Distribution Pie Chart
    const pieCtx = document.getElementById('expenseDistributionChart').getContext('2d');
    pieChart = new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(departmentExpenses),
            datasets: [{
                data: Object.values(departmentExpenses),
                backgroundColor: [
                    '#3b82f6',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6',
                    '#ec4899'
                ],
                borderWidth: 3,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            var total = 0;
                            var dataArr = context.dataset.data;
                            for (var i = 0; i < dataArr.length; i++) {
                                total += Number(dataArr[i]);
                            }
                            var value = context.parsed;
                            var percentage = total > 0 ? ((value / total) * 100).toFixed(2) : '0.00';
                            return context.label + ': ' + percentage + '%';
                        }
                    }
                }
            }
        }
    });
}

function switchView(view) {
    if (view === 'yearly') {
        // Redirect to yearly report page
        window.location.href = '{{ route("summary-report.yearly") }}?year={{ $selectedYear }}';
    }
    // Monthly view is already loaded, no action needed
}

function calculateNetSales() {
    // Remove commas before parsing
    const grossSalesValue = document.getElementById('gross_sales').value.replace(/,/g, '');
    const grossSales = parseFloat(grossSalesValue) || 0;
    const netSales = grossSales - totalExpenses;
    document.getElementById('net_sales').textContent = '₱ ' + netSales.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function recalcNetSales() {
    calculateNetSales();
    updateSummaryCards();
}

function loadPeriod() {
    const month = document.getElementById('monthSelector').value;
    const year = document.getElementById('yearSelector').value;

    localStorage.setItem('summaryReportMonth', month);
    localStorage.setItem('summaryReportYear', year);

    window.location.href = `{{ route('summary-report') }}?month=${month}&year=${year}`;
}

// On page load
window.addEventListener('DOMContentLoaded', function() {
    const savedMonth = localStorage.getItem('summaryReportMonth');
    const savedYear = localStorage.getItem('summaryReportYear');
    
    const urlParams = new URLSearchParams(window.location.search);
    const urlMonth = urlParams.get('month');
    const urlYear = urlParams.get('year');
    
    if (savedMonth && savedYear && !urlMonth && !urlYear) {
        window.location.href = `{{ route('summary-report') }}?month=${savedMonth}&year=${savedYear}`;
    }
});

function saveSummaryReport() {
    const month = {{ $selectedMonth }};
    const year = {{ $selectedYear }};
    
    // Remove commas before parsing
    const units = parseFloat(document.getElementById('units').value.replace(/,/g, '')) || 0;
    const grossSales = parseFloat(document.getElementById('gross_sales').value.replace(/,/g, '')) || 0;
    const coh = parseFloat(document.getElementById('coh').value.replace(/,/g, '')) || 0;
    
    fetch('/api/summary-report/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            month: month,
            year: year,
            units: units,
            gross_sales: grossSales,
            coh: coh
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Success', 'Summary report saved successfully!');
            calculateNetSales();
        } else {
            showToast('error', 'Error', data.message || 'Error saving summary report');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error', 'Error saving summary report');
    });
}

function showToast(type, title, message, callback) {
    const toast = document.getElementById('toastNotification');
    const icon = document.getElementById('toastIcon');
    const titleEl = document.getElementById('toastTitle');
    const messageEl = document.getElementById('toastMessage');
    
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    icon.textContent = icons[type] || icons.info;
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    toast.classList.remove('success', 'error', 'warning', 'info', 'hiding');
    toast.classList.add(type);
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.add('hiding');
        setTimeout(() => {
            toast.classList.remove('show', 'hiding');
            if (callback) callback();
        }, 300);
    }, 2500);
}
</script>
@endsection