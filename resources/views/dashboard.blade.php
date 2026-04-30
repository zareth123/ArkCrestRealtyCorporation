@extends('layouts.dashboard')

@section('content')

@if(isset($tomorrowReleases) && $tomorrowReleases->count() > 0)
<div id="releaseNotifBanner" style="background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;box-shadow:0 4px 16px rgba(30,69,117,.3);">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;background:rgba(255,255,255,.2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:22px;">🔔</div>
        <div>
            <div style="font-weight:700;font-size:15px;margin-bottom:3px;">
                {{ $tomorrowReleases->count() }} Commission Release{{ $tomorrowReleases->count() > 1 ? 's' : '' }} Tomorrow — {{ \Carbon\Carbon::tomorrow()->format('F j, Y') }}
            </div>
            <div style="font-size:13px;opacity:.85;">
                {{ $tomorrowReleases->pluck('agent_name')->join(', ') }}
            </div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
        <a href="{{ route('commission-monitoring') }}" style="padding:8px 18px;background:white;color:#1e4575;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">View</a>
        <button onclick="document.getElementById('releaseNotifBanner').style.display='none'" style="background:rgba(255,255,255,.2);border:none;color:white;width:30px;height:30px;border-radius:6px;cursor:pointer;font-size:16px;">&#x2715;</button>
    </div>
</div>
@endif

<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-content">
        <div class="welcome-eyebrow">Finance Dashboard</div>
        <h1 class="welcome-title">Happy ArkCrest Morning, {{ auth()->user()->preferred_address ? auth()->user()->preferred_address.' '.auth()->user()->name : auth()->user()->name }}!</h1>
        <p class="welcome-subtitle">
            <svg style="width:15px;height:15px;display:inline;vertical-align:middle;margin-right:5px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ $currentMonth }} {{ $currentYear }} Overview
        </p>
        <div class="today-pills">
            <span class="today-pill">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ $todayEvents }} Event{{ $todayEvents != 1 ? 's' : '' }} Today
            </span>
            <span class="today-pill">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ $todayTrips }} Tripping{{ $todayTrips != 1 ? 's' : '' }} Today
            </span>
            <span class="today-pill {{ $todayReleases > 0 ? 'today-pill-alert' : '' }}">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $todayReleases }} Release{{ $todayReleases != 1 ? 's' : '' }} Today
            </span>
        </div>
    </div>
    <div class="welcome-decoration">
        <div class="decoration-circle circle-1"></div>
        <div class="decoration-circle circle-2"></div>
        <div class="decoration-circle circle-3"></div>
    </div>
</div>

<!-- Top Metrics Cards -->
@if(!in_array('dashboard.budget-cards', $hiddenSections))
<div class="metrics-grid">
    <div class="metric-card card-blue">
        <div class="metric-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        </div>
        <div class="metric-content">
            <div class="metric-label">Monthly Performance</div>
            <div class="metric-value">{{ number_format($units, 0) }} <span style="font-size:14px;font-weight:500;color:#64748b;">units</span></div>
            <div style="font-size:13px;font-weight:700;color:#1e4575;">&#8369;{{ number_format($grossSales, 0) }}</div>
            <div class="metric-subtitle" style="margin-bottom:4px;">Gross Sales — {{ $currentMonth }} {{ $currentYear }}</div>
            <div style="font-size:12px;line-height:1.7;">
                <span style="color:#64748b;">Pending: <strong>{{ $pendingReservation }}</strong></span><br>
                <span style="color:#dc2626;">Cancelled: <strong>{{ $cancelledReservation }}</strong></span><br>
                <span style="color:#1e4575;font-weight:700;">Total: {{ $totalReservation }}</span>
            </div>
        </div>
    </div>
    <div class="metric-card card-gold">
        <div class="metric-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <div class="metric-content">
            <div class="metric-label">Receivables</div>
            <div class="metric-value">&#8369;{{ number_format($receivables, 0) }}</div>
            <div class="metric-subtitle">Pending commission releases</div>
        </div>
    </div>
    <div class="metric-card card-blue">
        <div class="metric-icon" style="background:linear-gradient(135deg,#059669,#10b981);">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div class="metric-content">
            <div class="metric-label">Total Sales</div>
            <div class="metric-value">&#8369;{{ number_format($yearlySales, 0) }}</div>
            <div class="metric-subtitle">Year-to-date — {{ $currentYear }}</div>
        </div>
    </div>
</div>
@endif

<!-- Department Expenses Section -->
@if(!in_array('dashboard.expenses-breakdown', $hiddenSections) || !in_array('dashboard.dept-list', $hiddenSections))
<div class="section-grid">
    <!-- Expenses Breakdown -->
    @if(!in_array('dashboard.expenses-breakdown', $hiddenSections))
    <div class="dashboard-card expenses-card">
        <div class="card-header-modern">
            <div class="header-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="card-title-modern">Expenses Breakdown</h3>
                <p class="card-subtitle-modern">{{ $currentMonth }} {{ $currentYear }}</p>
            </div>
        </div>
        <div class="card-body-modern">
            <div class="total-expenses-summary">
                <div class="total-expenses-label">Total Expenses</div>
                <div class="total-expenses-value">₱{{ number_format($totalExpenses, 2) }}</div>
                <div class="total-expenses-subtitle">All Departments Combined</div>
            </div>
            @foreach($departmentData as $dept)
                @if($dept['expenses'] > 0)
                <div class="expense-item">
                    <div class="expense-header">
                        <span class="expense-dept">{{ $dept['name'] }}</span>
                        <span class="expense-amount">₱{{ number_format($dept['expenses'], 2) }}</span>
                    </div>
                    @if(isset($expenseBreakdown[$dept['name']]) && count($expenseBreakdown[$dept['name']]) > 0)
                        <div class="expense-categories">
                            @foreach($expenseBreakdown[$dept['name']] as $catName => $catAmount)
                                <span class="category-badge">{{ $catName }}: ₱{{ number_format($catAmount, 0) }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <!-- Department List -->
    @if(!in_array('dashboard.dept-list', $hiddenSections))
    <div class="dashboard-card departments-card">
        <div class="card-header-modern">
            <div class="header-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <h3 class="card-title-modern">Department Expenses</h3>
                <p class="card-subtitle-modern">Monthly Overview</p>
            </div>
        </div>
        <div class="card-body-modern">
            <div class="department-list-modern">
                @foreach($departmentData as $index => $dept)
                @php $pct = min($dept['percentage'], 100); $color = $pct < 70 ? '#059669' : ($pct < 90 ? '#d97706' : '#dc2626'); @endphp
                <div class="dept-item-modern fade-in-up gpu-accelerated">
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                            <div class="dept-info">
                                <div class="dept-icon dept-icon-{{ $index % 6 }}">
                                    @if(stripos($dept['name'], 'admin') !== false)
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    @elseif(stripos($dept['name'], 'sales') !== false || stripos($dept['name'], 'marketing') !== false)
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @elseif(stripos($dept['name'], 'hr') !== false || stripos($dept['name'], 'human') !== false)
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @else
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </div>
                                <span class="dept-name-modern">{{ $dept['name'] }}</span>
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <div class="dept-amount-modern">₱{{ number_format($dept['expenses'], 0) }}</div>
                                <div style="font-size:10px;color:#94a3b8;">of ₱{{ number_format($dept['budget'], 0) }}</div>
                            </div>
                        </div>
                        <div style="background:#f1f5f9;border-radius:999px;height:5px;overflow:hidden;">
                            <div style="height:100%;width:{{ $pct }}%;background:{{ $color }};border-radius:999px;transition:width .6s;"></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-top:4px;">
                            <span style="font-size:10px;color:#94a3b8;">{{ number_format($pct, 1) }}% used</span>
                            <span style="font-size:10px;font-weight:600;color:{{ $dept['remaining'] >= 0 ? '#059669' : '#dc2626' }};">₱{{ number_format($dept['remaining'], 0) }} left</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endif

<!-- Budget Table Section -->
@if(!in_array('dashboard.budget-table', $hiddenSections))
<div class="dashboard-card budget-card">
    <div class="card-header-modern">
        <div class="header-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <h3 class="card-title-modern">Budget Overview</h3>
            <p class="card-subtitle-modern">Remaining Fund per Department - {{ $currentMonth }} {{ $currentYear }}</p>
        </div>
    </div>
    <div class="card-body-modern">
        <div class="table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Total Budget</th>
                        <th>Used</th>
                        <th>Remaining</th>
                        <th>Usage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departmentData as $dept)
                    <tr>
                        <td>
                            <div class="table-dept">{{ $dept['name'] }}</div>
                        </td>
                        <td>₱{{ number_format($dept['budget'], 2) }}</td>
                        <td>₱{{ number_format($dept['expenses'], 2) }}</td>
                        <td class="remaining-amount">₱{{ number_format($dept['remaining'], 2) }}</td>
                        <td>
                            <div class="progress-container">
                                <div class="progress-bar">
                                    <div class="progress-fill progress-{{ $dept['percentage'] < 70 ? 'success' : ($dept['percentage'] < 90 ? 'warning' : 'danger') }}" 
                                         style="width: {{ min($dept['percentage'], 100) }}%"></div>
                                </div>
                                <span class="progress-text">{{ number_format($dept['percentage'], 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<style>
/* ===== WELCOME BANNER ===== */
.welcome-banner {
    background: linear-gradient(135deg, #1e4575 0%, #2563eb 60%, #1e4575 100%);
    border-radius: 20px;
    padding: 36px 40px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(30,69,117,.25);
}
.welcome-eyebrow {
    font-size: 12px;
    font-weight: 700;
    color: rgba(255,255,255,.6);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 8px;
}
.welcome-title {
    font-size: 28px;
    font-weight: 700;
    color: white;
    margin: 0 0 8px;
    position: relative;
    z-index: 2;
}
.welcome-subtitle {
    font-size: 14px;
    color: rgba(255,255,255,.75);
    margin: 0 0 12px;
    position: relative;
    z-index: 2;
}
.today-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
    position: relative;
    z-index: 2;
}
.today-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,.15);
    color: white;
    font-size: 12px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 20px;
    backdrop-filter: blur(4px);
}
.today-pill-alert {
    background: rgba(251,191,36,.25);
    color: #fef3c7;
}
.welcome-content { position: relative; z-index: 2; }
.welcome-decoration { position: absolute; top: 0; right: 0; width: 300px; height: 100%; pointer-events: none; }
.decoration-circle { position: absolute; border-radius: 50%; background: rgba(255,255,255,.06); }
.circle-1 { width: 220px; height: 220px; top: -60px; right: -40px; }
.circle-2 { width: 140px; height: 140px; top: 40px; right: 120px; }
.circle-3 { width: 90px; height: 90px; bottom: -20px; right: 60px; }

/* ===== METRIC CARDS ===== */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}
.metric-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    border: 1.5px solid #f1f5f9;
    transition: transform .2s, box-shadow .2s;
}
.metric-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,0,0,.1); }
.metric-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.card-blue .metric-icon { background: linear-gradient(135deg,#1e4575,#2563eb); }
.card-gold .metric-icon { background: linear-gradient(135deg,#A37929,#d4a03a); }
.metric-icon svg { width: 26px; height: 26px; color: white; }
.metric-content { flex: 1; min-width: 0; }
.metric-label { font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
.metric-value { font-size: 24px; font-weight: 700; color: #0f172a; line-height: 1.2; margin-bottom: 4px; }
.metric-subtitle { font-size: 12px; color: #94a3b8; }
.metric-badge { width:32px;height:32px;border-radius:8px;background:#dbeafe;color:#1e4575;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.metric-badge-gold { background:#fef3c7;color:#92400e; }
.metric-badge-red { background:#fee2e2;color:#dc2626; }
.card-blue { border-left: none; }
.card-gold { border-left: none; }
.card-red { border-left: none; }
.dept-item-modern { padding: 14px 16px; }

/* ===== SECTION GRID ===== */
.section-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 28px;
}

/* ===== CARDS ===== */
.dashboard-card {
    background: white;
    border-radius: 16px;
    border: 1.5px solid #f1f5f9;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    overflow: hidden;
}
.card-header-modern {
    padding: 20px 24px;
    border-bottom: 1.5px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fafbfc;
}
.header-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg,#1e4575,#2563eb);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.header-icon svg { width: 22px; height: 22px; color: white; }
.card-title-modern { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; }
.card-subtitle-modern { font-size: 12px; color: #94a3b8; margin: 3px 0 0; }
.card-body-modern { padding: 20px 24px; }

/* ===== TOTAL EXPENSES SUMMARY ===== */
.total-expenses-summary {
    background: linear-gradient(135deg,#1e4575,#2563eb);
    border-radius: 12px;
    padding: 18px 22px;
    margin-bottom: 18px;
    text-align: center;
}
.total-expenses-label { font-size: 11px; color: rgba(255,255,255,.7); font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
.total-expenses-value { font-size: 28px; font-weight: 700; color: white; margin-bottom: 2px; }
.total-expenses-subtitle { font-size: 11px; color: rgba(255,255,255,.6); }

/* ===== EXPENSE ITEMS ===== */
.expense-item {
    padding: 12px 14px;
    border-radius: 10px;
    background: #f8fafc;
    margin-bottom: 10px;
    border: 1px solid #f1f5f9;
    transition: background .2s;
}
.expense-item:hover { background: #f1f5f9; }
.expense-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
.expense-dept { font-weight: 600; color: #1e4575; font-size: 13px; }
.expense-amount { font-weight: 700; color: #0f172a; font-size: 14px; }
.expense-categories { display: flex; flex-wrap: wrap; gap: 5px; }
.category-badge { background: white; padding: 3px 8px; border-radius: 5px; font-size: 11px; color: #64748b; border: 1px solid #e2e8f0; }

/* ===== DEPT LIST ===== */
.department-list-modern { display: flex; flex-direction: column; gap: 10px; }
.dept-item-modern {
    display: flex; justify-content: space-between; align-items: center;
    padding: 12px 14px;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    transition: all .2s;
}
.dept-item-modern:hover { background: #f1f5f9; transform: translateX(3px); }
.dept-info { display: flex; align-items: center; gap: 12px; }
.dept-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: white;
}
.dept-icon-0 { background: linear-gradient(135deg,#1e4575,#2563eb); }
.dept-icon-1 { background: linear-gradient(135deg,#A37929,#d4a03a); }
.dept-icon-2 { background: linear-gradient(135deg,#1e4575,#2563eb); }
.dept-icon-3 { background: linear-gradient(135deg,#A37929,#d4a03a); }
.dept-icon-4 { background: linear-gradient(135deg,#1e4575,#2563eb); }
.dept-icon-5 { background: linear-gradient(135deg,#A37929,#d4a03a); }
.dept-name-modern { font-weight: 600; color: #334155; font-size: 13px; }
.dept-amount-modern { font-weight: 700; color: #0f172a; font-size: 14px; }

/* ===== BUDGET TABLE ===== */
.budget-card { margin-bottom: 0; }
.table-container { overflow-x: auto; }
.modern-table { width: 100%; border-collapse: collapse; }
.modern-table thead tr { background: linear-gradient(135deg,#1e4575,#2563eb); }
.modern-table th {
    padding: 14px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 12px;
    color: white;
    text-transform: uppercase;
    letter-spacing: .5px;
    white-space: nowrap;
}
.modern-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
.modern-table tbody tr:last-child { border-bottom: none; }
.modern-table tbody tr:hover { background: #f8fafc; }
.modern-table td { padding: 14px 16px; font-size: 13px; color: #334155; }
.table-dept { font-weight: 600; color: #1e4575; }
.remaining-amount { font-weight: 700; color: #059669; }
.progress-container { display: flex; align-items: center; gap: 10px; }
.progress-bar { flex: 1; height: 7px; background: #e2e8f0; border-radius: 4px; overflow: hidden; min-width: 80px; }
.progress-fill { height: 100%; border-radius: 4px; transition: width .6s ease; }
.progress-success { background: linear-gradient(90deg,#A37929,#d4a03a); }
.progress-warning { background: linear-gradient(90deg,#f59e0b,#d97706); }
.progress-danger { background: linear-gradient(90deg,#ef4444,#dc2626); }
.progress-text { font-weight: 600; font-size: 12px; color: #64748b; min-width: 40px; text-align: right; }

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .section-grid { grid-template-columns: 1fr; }
    .metrics-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 640px) {
    .metrics-grid { grid-template-columns: 1fr; }
    .welcome-title { font-size: 22px; }
    .welcome-banner { padding: 24px; }
}
</style>

@endsection
