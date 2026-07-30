@extends('layouts.dashboard')

@section('content')
@php
    $monthNames  = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
    $dayNames    = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $firstDay    = (int) date('w', mktime(0,0,0,$month,1,$year));
    $daysInMonth = (int) date('t', mktime(0,0,0,$month,1,$year));
    $today       = date('Y-m-d');
    $prevMonth   = $month == 1 ? 12 : $month - 1;
    $prevYear    = $month == 1 ? $year - 1 : $year;
    $nextMonth   = $month == 12 ? 1 : $month + 1;
    $nextYear    = $month == 12 ? $year + 1 : $year;
    $totalEvents = collect($releasesByDay ?? [])->sum(fn($e) => count($e));
    $view        = $view ?? 'month';
@endphp

<style>
.cal-page { display:flex;flex-direction:column;gap:0; }
.cal-page.is-month-view { height:calc(100vh - 62px - 20px); }

/* Top bar */
.cal-topbar {
    display:flex;align-items:center;justify-content:space-between;
    padding:0 0 16px;flex-shrink:0;
}
.cal-page-title { font-size:28px;font-weight:700;color:#1e4575;letter-spacing:-.3px; }
.cal-page-sub { font-size:12px;color:#94a3b8;margin-top:2px; }
.cal-controls { display:flex;align-items:center;gap:10px; }
.cal-nav-btn {
    display:inline-flex;align-items:center;justify-content:center;
    width:32px;height:32px;border-radius:8px;
    background:white;border:1.5px solid #e2e8f0;
    color:#1e4575;text-decoration:none;font-size:16px;font-weight:700;
    transition:all .2s;
}
.cal-nav-btn:hover { background:#1e4575;color:white;border-color:#1e4575; }
.cal-month-pill {
    background:linear-gradient(135deg,#1e4575,#2563eb);
    color:white;padding:6px 20px;border-radius:20px;
    font-size:14px;font-weight:700;letter-spacing:.3px;
    min-width:160px;text-align:center;
}
.cal-today-btn {
    padding:6px 14px;background:white;color:#1e4575;
    border:1.5px solid #1e4575;border-radius:8px;
    text-decoration:none;font-size:12px;font-weight:600;
    transition:all .2s;
}
.cal-today-btn:hover { background:#1e4575;color:white; }
.cal-year-sel {
    padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:8px;
    font-size:13px;font-weight:500;color:#374151;background:white;
    cursor:pointer;outline:none;
}

/* Stats bar */
.cal-stats {
    display:flex;gap:12px;margin-bottom:14px;flex-shrink:0;
}
.cal-stat-card {
    background:white;border:1px solid #e8ecf0;border-radius:10px;
    padding:10px 16px;display:flex;align-items:center;gap:10px;
    box-shadow:0 1px 4px rgba(0,0,0,.04);
}
.cal-stat-icon {
    width:32px;height:32px;border-radius:8px;
    background:linear-gradient(135deg,#e8edf5,#dce6f5);
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.cal-stat-val { font-size:16px;font-weight:700;color:#1e4575; }
.cal-stat-lbl { font-size:10px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.4px; }

/* Calendar grid */
.cal-grid-wrap {
    flex:1;background:white;border-radius:12px;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
    overflow:hidden;border:1px solid #e8ecf0;
    display:flex;flex-direction:column;min-height:0;
}
.cal-day-headers {
    display:grid;grid-template-columns:repeat(7,1fr);
    background:#f8fafc;border-bottom:2px solid #e8ecf0;
    flex-shrink:0;
}
.cal-day-hdr {
    padding:10px 0;text-align:center;
    font-size:11px;font-weight:700;color:#64748b;
    letter-spacing:.6px;text-transform:uppercase;
}
.cal-day-hdr.weekend { color:#94a3b8; }
.cal-days {
    display:grid;grid-template-columns:repeat(7,1fr);
    flex:1;min-height:0;
}
.cal-cell {
    border-right:1px solid #d1d5db;border-bottom:1px solid #d1d5db;
    padding:6px 7px;display:flex;flex-direction:column;
    overflow-y:auto;overflow-x:hidden;transition:background .15s;
}
.cal-cell::-webkit-scrollbar { width:4px; }
.cal-cell::-webkit-scrollbar-thumb { background:#cbd5e1;border-radius:2px; }
.cal-cell:nth-child(7n) { border-right:none; }
.cal-cell.empty { background:#fafbfc; }
.cal-cell.weekend { background:#fafbfc; }
.cal-cell.today { background:linear-gradient(135deg,#eff6ff,#e8f0fe); }
.cal-cell:not(.empty):not(.today):hover { background:#f8faff; }
.cal-day-num {
    display:inline-flex;align-items:center;justify-content:center;
    width:22px;height:22px;border-radius:50%;
    font-size:12px;font-weight:700;color:#1e293b;
    align-self:flex-end;flex-shrink:0;margin-bottom:3px;
}
.cal-cell.today .cal-day-num {
    background:linear-gradient(135deg,#1e4575,#2563eb);
    color:white;font-weight:700;
    box-shadow:0 2px 6px rgba(30,69,117,.3);
}
.cal-cell.weekend .cal-day-num { color:#94a3b8; }
.cal-event {
    border-radius:4px;padding:2px 6px;
    font-size:10px;margin-bottom:2px;cursor:pointer;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    flex-shrink:0;transition:opacity .15s;
    box-shadow:0 1px 3px rgba(0,0,0,.15);
    font-weight:600;background:#059669;color:white;
}
.cal-event.cal-event-expense { background:#dc2626; }
.cal-event.cal-event-cash-advance { background:#4f46e5; }
.cal-event.cal-event-agent-cash-advance { background:#0891b2; }
.cal-event:hover { opacity:.85; }

/* Expense status badges — matches departmental-expenses-enhanced.css */
.status-badge {
    padding:6px 14px;border-radius:20px;font-size:11px;font-weight:700;
    text-transform:uppercase;letter-spacing:.5px;display:inline-block;
}
.status-not-yet-liquidated, .status-not-liquidated {
    background:linear-gradient(135deg,rgba(239,68,68,.2),rgba(239,68,68,.1));
    color:#dc2626;border:2px solid #dc2626;
}
.status-liquidated {
    background:linear-gradient(135deg,rgba(34,197,94,.2),rgba(34,197,94,.1));
    color:#16a34a;border:2px solid #22c55e;
}
.status-pending {
    background:linear-gradient(135deg,rgba(245,158,11,.2),rgba(245,158,11,.1));
    color:#92400e;border:2px solid #f59e0b;
}
.status-rejected {
    background:linear-gradient(135deg,rgba(107,114,128,.2),rgba(107,114,128,.1));
    color:#374151;border:2px solid #6b7280;
}

/* Commission/Sales status badges — matches commission-monitoring.blade.php */
.cal-status-badge {
    padding:4px 12px;border-radius:12px;font-size:11px;font-weight:600;
    text-transform:uppercase;display:inline-block;
}
.cal-status-released { background:#dcfce7;color:#166534; }
.cal-status-pending  { background:#fee2e2;color:#991b1b; }

/* Cash Advance status badges — matches cash-advance.blade.php */
.ca-badge { display:inline-block;padding:4px 12px;border-radius:12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap; }
.ca-badge-pending  { background:#eef2ff;color:#4338ca; }
.ca-badge-approved { background:#dcfce7;color:#166534; }
.ca-badge-rejected { background:#fee2e2;color:#991b1b; }
.cal-more {
    font-size:9px;color:#94a3b8;text-align:right;
    margin-top:1px;flex-shrink:0;font-weight:600;
    cursor:pointer;text-decoration:underline;
}
.cal-more:hover { color:#1e4575; }

/* Legend */
.cal-legend {
    display:flex;align-items:center;gap:16px;
    padding:10px 0 0;font-size:11px;color:#64748b;flex-shrink:0;
}

/* ---- List-view table filters (search + column filter dropdown), matches the
   "All Expenses" / Commission Monitoring filter pattern ---- */
.cal-filters-bar { display:flex;flex-direction:column;gap:10px;padding:12px 16px;background:#f8fafc;border-bottom:1px solid #e8ecf0; }
.cal-filters-row { display:flex;justify-content:flex-start;align-items:center;flex-wrap:wrap;gap:12px; }
.cal-search-wrapper { display:flex;align-items:center;gap:10px;width:100%;max-width:420px; }
.cal-search-box { display:flex;align-items:center;gap:8px;background:white;border:1.5px solid #d0d5dd;border-radius:8px;padding:0 10px;height:40px;flex:1; }
.cal-search-box svg { width:15px;height:15px;color:#8a9bad;flex-shrink:0; }
.cal-search-box input { border:none;outline:none;font-size:13px;width:100%;color:#344054;background:transparent; }
.cal-column-filter-dropdown { position:relative; }
.cal-column-filter-btn {
    display:inline-flex;align-items:center;gap:6px;white-space:nowrap;
    font-size:13px;font-weight:600;color:#1e4575;background:white;
    border:2px solid #1e4575;border-radius:8px;padding:9px 14px;
    cursor:pointer;height:40px;box-sizing:border-box;transition:all .2s ease;
}
.cal-column-filter-btn:hover { background:#eef2f7; }
.cal-filter-count-badge {
    background:#A37929;color:white;font-size:11px;font-weight:700;border-radius:999px;
    min-width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;padding:0 5px;
}
.cal-column-filter-menu {
    position:absolute;top:calc(100% + 6px);left:0;min-width:220px;max-height:300px;overflow-y:auto;
    background:white;border:1.5px solid #d0d5dd;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);
    z-index:500;padding:6px;
}
.cal-column-filter-menu-item { display:flex;align-items:center;gap:8px;padding:9px 10px;font-size:13px;font-weight:500;color:#344054;border-radius:6px;cursor:pointer;white-space:nowrap; }
.cal-column-filter-menu-item:hover { background:#eef2f7; }
.cal-column-filter-menu-item .cfm-check { width:14px;color:#A37929;font-weight:700;visibility:hidden; }
.cal-column-filter-menu-item.is-active .cfm-check { visibility:visible; }
.cal-column-filter-menu-item.is-active { color:#1e4575;font-weight:700; }
.cal-active-filters-row { display:flex;flex-wrap:wrap;align-items:center;gap:10px; }
.cal-filter-chip { display:flex;align-items:center;gap:6px;background:#eef2f7;border:1.5px solid #d0d5dd;border-radius:8px;padding:6px 8px 6px 12px; }
.cal-filter-chip label { font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap; }
.cal-filter-chip input, .cal-filter-chip select { font-size:13px;padding:6px 8px;border:1.5px solid #d0d5dd;border-radius:6px;color:#344054;min-width:120px; }
.cal-filter-chip .cfm-remove { background:none;border:none;color:#8a9bad;cursor:pointer;font-size:16px;line-height:1;padding:2px 4px; }
.cal-filter-chip .cfm-remove:hover { color:#dc2626; }
.cal-clear-filters-btn { font-size:12px;font-weight:600;color:#1e4575;background:white;border:1px solid #d0d5dd;border-radius:6px;padding:8px 14px;cursor:pointer;white-space:nowrap; }
.cal-no-results-row td { padding:24px !important;text-align:center;color:#94a3b8;font-size:13px; }

@media (max-width: 768px) {
    .cal-search-wrapper { max-width:100%;flex-direction:column;align-items:stretch;gap:10px; }
    .cal-column-filter-dropdown { width:100%; }
    .cal-column-filter-btn { width:100%;justify-content:center; }
    .cal-column-filter-menu { left:0;right:0;min-width:0;width:100%;box-sizing:border-box; }
    .cal-active-filters-row { flex-direction:column;align-items:stretch; }
    .cal-filter-chip { width:100%;flex-wrap:wrap;box-sizing:border-box; }
    .cal-filter-chip input, .cal-filter-chip select { flex:1 1 auto;min-width:0;width:100%; }
    .cal-clear-filters-btn { width:100%;text-align:center; }
}
</style>

<div class="cal-page {{ $view === 'list' ? '' : 'is-month-view' }}">    {{-- Top Bar --}}
    <div class="cal-topbar" style="background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:16px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
        <div style="position:relative;z-index:2;">
            <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;">Finance</div>
            <h1 style="font-size:24px;font-weight:700;color:white;margin:0 0 6px;">Calendar</h1>
            <p style="font-size:13px;color:rgba(255,255,255,.75);margin:0;">Commission release schedule &bull; {{ $monthNames[$month] }} {{ $year }}</p>
        </div>
        <div class="cal-controls" style="position:relative;z-index:2;">
            <form method="GET" action="{{ route('calendar') }}" style="display:flex;align-items:center;gap:6px;">
                <select name="month" class="cal-month-sel" onchange="this.form.submit()" style="background:rgba(255,255,255,.15);color:white;border:1.5px solid rgba(255,255,255,.3);border-radius:8px;padding:6px 10px;font-size:13px;font-weight:600;">
                    @foreach($monthNames as $num => $name)
                        @if($num > 0)
                        <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }} style="color:#1e4575;background:white;">{{ $name }}</option>
                        @endif
                    @endforeach
                </select>
                <select name="year" class="cal-year-sel" onchange="this.form.submit()" style="background:rgba(255,255,255,.15);color:white;border:1.5px solid rgba(255,255,255,.3);border-radius:8px;padding:6px 10px;font-size:13px;font-weight:600;">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }} style="color:#1e4575;background:white;">{{ $y }}</option>
                    @endforeach
                </select>
            </form>
            
            <a href="{{ route('calendar', ['month'=>date('n'),'year'=>date('Y')]) }}" class="cal-today-btn" style="background:rgba(255,255,255,.2);color:white;border:1.5px solid rgba(255,255,255,.3);">Today</a>
            </a>
            <a href="{{ route('calendar', ['month'=>$month,'year'=>$year,'view'=>'list']) }}" style="{{ ($view??'month')=='list' ? 'background:rgba(255,255,255,.25);color:white;border-color:rgba(255,255,255,.4);' : 'background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);border-color:rgba(255,255,255,.2);' }} padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;border:1.5px solid;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:13px;height:13px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                List
            </a>
        </div>
        <div style="position:absolute;top:0;right:0;width:300px;height:100%;pointer-events:none;">
            <div style="position:absolute;width:220px;height:220px;top:-60px;right:-40px;border-radius:50%;background:rgba(255,255,255,.06);"></div>
            <div style="position:absolute;width:140px;height:140px;top:20px;right:120px;border-radius:50%;background:rgba(255,255,255,.04);"></div>
        </div>
    </div>

    {{-- Calendar Grid / List --}}
    @if($view === 'list')
    @php
        $commissionListRows = $releases->where('_type', 'commission');
        $expenseListRows    = $releases->where('_type', 'expense');
    @endphp
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Commission Release section --}}
        <div style="background:white;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid #e8ecf0;overflow:hidden;">
            <div style="padding:12px 16px;background:#f8fafc;border-bottom:1px solid #e8ecf0;font-size:12px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:8px;">
                Commission Release
                <span style="background:linear-gradient(135deg,#1e4575,#2563eb);color:white;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;letter-spacing:.3px;">{{ $commissionListRows->count() }} {{ Str::plural('record', $commissionListRows->count()) }}</span>
            </div>
            @if($commissionListRows->isEmpty())
            <div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">No commission releases for {{ $monthNames[$month] }} {{ $year }}</div>
            @else
            <div class="cal-filters-bar">
                <div class="cal-filters-row">
                    <div class="cal-column-filter-dropdown" id="calCommissionFilterDropdown">
                        <button type="button" class="cal-column-filter-btn" onclick="calFilters.commission.toggleMenu(event)">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            <span>Filter</span>
                            <span id="calCommissionFilterBadge" class="cal-filter-count-badge" style="display:none;">0</span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div id="calCommissionFilterMenu" class="cal-column-filter-menu" style="display:none;"></div>
                    </div>
                    <div class="cal-search-wrapper">
                        <div class="cal-search-box">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" id="calCommissionSearch" placeholder="Search commission releases...">
                        </div>
                    </div>
                </div>
                <div id="calCommissionActiveFilters" class="cal-active-filters-row" style="display:none;"></div>
            </div>
            <div class="tbl-wrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table style="width:100%;border-collapse:collapse;min-width:700px;">
                <thead><tr style="background:linear-gradient(135deg,#0f2a4a,#1e4575);">
                    @foreach(['Date Released','Agent','Client','Project','Net TCP','Commission','Status'] as $h)
                    <th style="padding:12px 16px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.7px;white-space:nowrap;">{{ $h }}</th>
                    @endforeach
                </tr></thead>
                <tbody id="calCommissionTableBody">
                @foreach($commissionListRows as $r)
                <tr style="border-bottom:1px solid #f1f5f9;cursor:pointer;"
                    data-date-released="{{ $r->date_released ? $r->date_released->format('Y-m-d') : '' }}"
                    data-agent="{{ $r->agent_name }}"
                    data-client="{{ $r->client_name }}"
                    data-project="{{ $r->project_name }}"
                    data-net-tcp="{{ $r->net_tcp }}"
                    data-commission="{{ $r->commission }}"
                    data-status="{{ $r->status === 'Not Released' ? 'Not Yet Released' : $r->status }}"
                    onclick="showEventDetail('{{ $r->_type }}', {{ $r->id }})" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding:11px 16px;font-size:13px;font-weight:600;color:#059669;white-space:nowrap;">{{ $r->date_released ? $r->date_released->format('M d, Y') : ' ' }}</td>
                    <td style="padding:11px 16px;font-size:13px;color:#0f172a;font-weight:600;">{{ $r->agent_name ?? ' ' }}</td>
                    <td style="padding:11px 16px;font-size:13px;color:#374151;">{{ $r->client_name ?? ' ' }}</td>
                    <td style="padding:11px 16px;font-size:13px;color:#374151;">{{ $r->project_name ?? ' ' }}</td>
                    <td style="padding:11px 16px;font-size:13px;color:#374151;">{{ $r->net_tcp ? '₱'.number_format($r->net_tcp,2) : ' ' }}</td>
                <td style="padding:11px 16px;font-size:13px;font-weight:700;color:#059669;">{{ $r->commission ? '₱'.number_format($r->commission,2) : ' ' }}</td>
                    <td style="padding:11px 16px;"><span class="cal-status-badge {{ $r->status === 'Released' ? 'cal-status-released' : 'cal-status-pending' }}">{{ $r->status === 'Not Released' ? 'Not Yet Released' : ($r->status ?? ' ') }}</span></td>
                </tr>
                @endforeach
                <tr id="calCommissionNoResults" class="cal-no-results-row" style="display:none;"><td colspan="7">No records match your filters.</td></tr>
                </tbody>
            </table>
            </div>
            @endif
        </div>

        {{-- Expenses Release Date section --}}
        <div style="background:white;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid #e8ecf0;overflow:hidden;">
            <div style="padding:12px 16px;background:#f8fafc;border-bottom:1px solid #e8ecf0;font-size:12px;font-weight:700;color:#dc2626;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:8px;">
                Expenses Release Date
                <span style="background:linear-gradient(135deg,#7f1d1d,#dc2626);color:white;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;letter-spacing:.3px;">{{ $expenseListRows->count() }} {{ Str::plural('record', $expenseListRows->count()) }}</span>
            </div>
            @if($expenseListRows->isEmpty())
            <div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">No expense releases for {{ $monthNames[$month] }} {{ $year }}</div>
            @else
            <div class="cal-filters-bar">
                <div class="cal-filters-row">
                    <div class="cal-column-filter-dropdown" id="calExpenseFilterDropdown">
                        <button type="button" class="cal-column-filter-btn" onclick="calFilters.expense.toggleMenu(event)">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            <span>Filter</span>
                            <span id="calExpenseFilterBadge" class="cal-filter-count-badge" style="display:none;">0</span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div id="calExpenseFilterMenu" class="cal-column-filter-menu" style="display:none;"></div>
                    </div>
                    <div class="cal-search-wrapper">
                        <div class="cal-search-box">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" id="calExpenseSearch" placeholder="Search expense releases...">
                        </div>
                    </div>
                </div>
                <div id="calExpenseActiveFilters" class="cal-active-filters-row" style="display:none;"></div>
            </div>
            <div class="tbl-wrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table style="width:100%;border-collapse:collapse;min-width:700px;">
                <thead><tr style="background:linear-gradient(135deg,#7f1d1d,#dc2626);">
                    @foreach(['Date Released','Requestor Name','Department','Category','Requested Amount','Status'] as $h)
                    <th style="padding:12px 16px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.7px;white-space:nowrap;">{{ $h }}</th>
                    @endforeach
                </tr></thead>
                <tbody id="calExpenseTableBody">
                @foreach($expenseListRows as $r)
                <tr style="border-bottom:1px solid #f1f5f9;cursor:pointer;"
                    data-date-released="{{ $r->date_released ? $r->date_released->format('Y-m-d') : '' }}"
                    data-requestor="{{ $r->requestor_name }}"
                    data-department="{{ $r->department }}"
                    data-category="{{ $r->category }}"
                    data-amount="{{ $r->requested_amount }}"
                    data-status="{{ $r->status }}"
                    onclick="showEventDetail('{{ $r->_type }}', {{ $r->id }})" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding:11px 16px;font-size:13px;font-weight:600;color:#dc2626;white-space:nowrap;">{{ $r->date_released ? $r->date_released->format('M d, Y') : ' ' }}</td>
                    <td style="padding:11px 16px;font-size:13px;color:#0f172a;font-weight:600;">{{ $r->requestor_name ?? ' ' }}</td>
                    <td style="padding:11px 16px;font-size:13px;color:#374151;">{{ $r->department ?? ' ' }}</td>
                    <td style="padding:11px 16px;font-size:13px;color:#374151;">{{ $r->category ?? ' ' }}</td>
                    <td style="padding:11px 16px;font-size:13px;font-weight:700;color:#dc2626;">{{ $r->requested_amount ? '₱'.number_format($r->requested_amount,2) : ' ' }}</td>
                    <td style="padding:11px 16px;"><span class="status-badge status-{{ strtolower(str_replace(' ', '-', $r->status ?? '')) }}">{{ $r->status ?? ' ' }}</span></td>
                </tr>
                @endforeach
                <tr id="calExpenseNoResults" class="cal-no-results-row" style="display:none;"><td colspan="6">No records match your filters.</td></tr>
                </tbody>
            </table>
            </div>
            @endif
        </div>

        {{-- Cash Advance section --}}
        @php $cashAdvanceListRows = $releases->where('_type', 'cash_advance'); @endphp
        <div style="background:white;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid #e8ecf0;overflow:hidden;">
            <div style="padding:12px 16px;background:#f8fafc;border-bottom:1px solid #e8ecf0;font-size:12px;font-weight:700;color:#4f46e5;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:8px;">
            Cash Advance Repayment Date
            <span style="background:linear-gradient(135deg,#312e81,#4f46e5);color:white;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;letter-spacing:.3px;">{{ $cashAdvanceListRows->count() }} {{ Str::plural('record', $cashAdvanceListRows->count()) }}</span>
        </div>
            @if($cashAdvanceListRows->isEmpty())
            <div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">No cash advances for {{ $monthNames[$month] }} {{ $year }}</div>
            @else
            <div class="cal-filters-bar">
                <div class="cal-filters-row">
                    <div class="cal-column-filter-dropdown" id="calCashAdvanceFilterDropdown">
                        <button type="button" class="cal-column-filter-btn" onclick="calFilters.cashAdvance.toggleMenu(event)">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            <span>Filter</span>
                            <span id="calCashAdvanceFilterBadge" class="cal-filter-count-badge" style="display:none;">0</span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div id="calCashAdvanceFilterMenu" class="cal-column-filter-menu" style="display:none;"></div>
                    </div>
                    <div class="cal-search-wrapper">
                        <div class="cal-search-box">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" id="calCashAdvanceSearch" placeholder="Search cash advances...">
                        </div>
                    </div>
                </div>
                <div id="calCashAdvanceActiveFilters" class="cal-active-filters-row" style="display:none;"></div>
            </div>
            <div class="tbl-wrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table style="width:100%;border-collapse:collapse;min-width:700px;">
                <thead><tr style="background:linear-gradient(135deg,#312e81,#4f46e5);">
                    @foreach(['Cash Advance No.','Employee','Repayment Term','Amount','Payment Stage','Status','Date Paid'] as $h)
                    <th style="padding:12px 16px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.7px;white-space:nowrap;">{{ $h }}</th>
                    @endforeach
                </tr></thead>
                <tbody id="calCashAdvanceTableBody">
                @foreach($cashAdvanceListRows as $r)
                <tr style="border-bottom:1px solid #f1f5f9;"
                    data-control="{{ $r->control_number }}"
                    data-employee="{{ $r->employee_name }}"
                    data-repayment-term="Term {{ $r->term_number }}"
                    data-amount="{{ $r->amount }}"
                    data-stage="{{ $r->term_number }}/{{ $r->total_terms ?? '?' }}"
                    data-status="{{ ucfirst(strtolower($r->status ?? '')) }}"
                    data-date-paid="{{ $r->date_paid ? $r->date_paid->format('Y-m-d') : '' }}"
                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding:14px 18px;font-size:13px;font-weight:700;color:#4f46e5;white-space:nowrap;">{{ $r->control_number ?? ' ' }}</td>
                    <td style="padding:14px 18px;font-size:13px;color:#0f172a;font-weight:600;">{{ $r->employee_name ?? ' ' }}</td>
                    <td style="padding:14px 18px;font-size:13px;color:#374151;">Term {{ $r->term_number }}</td>
                    <td style="padding:14px 18px;font-size:13px;font-weight:700;color:#4f46e5;">{{ $r->amount ? '₱'.number_format($r->amount,2) : ' ' }}</td>
                    <td style="padding:14px 18px;font-size:13px;color:#374151;">{{ $r->term_number }}/{{ $r->total_terms ?? '?' }}</td>
                    <td style="padding:14px 18px;"><span class="ca-badge ca-badge-{{ $r->status === 'PAID' ? 'approved' : 'pending' }}">{{ ucfirst(strtolower($r->status ?? '')) }}</span></td>
                    <td style="padding:14px 18px;font-size:13px;font-weight:600;color:#4f46e5;white-space:nowrap;">{{ $r->date_paid ? $r->date_paid->format('M d, Y') : ' ' }}</td>
                </tr>
                @endforeach
                <tr id="calCashAdvanceNoResults" class="cal-no-results-row" style="display:none;"><td colspan="7">No records match your filters.</td></tr>
                </tbody>
            </table>
            </div>
            @endif
        </div>

        {{-- Agent Cash Advance section --}}
        @php $agentCashAdvanceListRows = $releases->where('_type', 'agent_cash_advance'); @endphp
        <div style="background:white;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid #e8ecf0;overflow:hidden;">
            <div style="padding:12px 16px;background:#f8fafc;border-bottom:1px solid #e8ecf0;font-size:12px;font-weight:700;color:#0891b2;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:8px;">
            Agent Cash Advance Repayment Date
            <span style="background:linear-gradient(135deg,#155e75,#0891b2);color:white;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;letter-spacing:.3px;">{{ $agentCashAdvanceListRows->count() }} {{ Str::plural('record', $agentCashAdvanceListRows->count()) }}</span>
        </div>
            @if($agentCashAdvanceListRows->isEmpty())
            <div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">No agent cash advances for {{ $monthNames[$month] }} {{ $year }}</div>
            @else
            <div class="cal-filters-bar">
                <div class="cal-filters-row">
                    <div class="cal-column-filter-dropdown" id="calAgentCashAdvanceFilterDropdown">
                        <button type="button" class="cal-column-filter-btn" onclick="calFilters.agentCashAdvance.toggleMenu(event)">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            <span>Filter</span>
                            <span id="calAgentCashAdvanceFilterBadge" class="cal-filter-count-badge" style="display:none;">0</span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div id="calAgentCashAdvanceFilterMenu" class="cal-column-filter-menu" style="display:none;"></div>
                    </div>
                    <div class="cal-search-wrapper">
                        <div class="cal-search-box">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" id="calAgentCashAdvanceSearch" placeholder="Search agent cash advances...">
                        </div>
                    </div>
                </div>
                <div id="calAgentCashAdvanceActiveFilters" class="cal-active-filters-row" style="display:none;"></div>
            </div>
            <div class="tbl-wrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
            <table style="width:100%;border-collapse:collapse;min-width:700px;">
                <thead><tr style="background:linear-gradient(135deg,#155e75,#0891b2);">
                    @foreach(['Cash Advance No.','Agent','Repayment Term','Amount','Payment Stage','Status','Date Paid'] as $h)
                    <th style="padding:12px 16px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.7px;white-space:nowrap;">{{ $h }}</th>
                    @endforeach
                </tr></thead>
                <tbody id="calAgentCashAdvanceTableBody">
                @foreach($agentCashAdvanceListRows as $r)
                <tr style="border-bottom:1px solid #f1f5f9;"
                    data-control="{{ $r->control_number }}"
                    data-agent="{{ $r->agent_name }}"
                    data-repayment-term="Term {{ $r->term_number }}"
                    data-amount="{{ $r->amount }}"
                    data-stage="{{ $r->term_number }}/{{ $r->total_terms ?? '?' }}"
                    data-status="{{ ucfirst(strtolower($r->status ?? '')) }}"
                    data-date-paid="{{ $r->date_paid ? $r->date_paid->format('Y-m-d') : '' }}"
                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding:14px 18px;font-size:13px;font-weight:700;color:#0891b2;white-space:nowrap;">{{ $r->control_number ?? ' ' }}</td>
                    <td style="padding:14px 18px;font-size:13px;color:#0f172a;font-weight:600;">{{ $r->agent_name ?? ' ' }}</td>
                    <td style="padding:14px 18px;font-size:13px;color:#374151;">Term {{ $r->term_number }}</td>
                    <td style="padding:14px 18px;font-size:13px;font-weight:700;color:#0891b2;">{{ $r->amount ? '₱'.number_format($r->amount,2) : ' ' }}</td>
                    <td style="padding:14px 18px;font-size:13px;color:#374151;">{{ $r->term_number }}/{{ $r->total_terms ?? '?' }}</td>
                    <td style="padding:14px 18px;"><span class="ca-badge ca-badge-{{ $r->status === 'PAID' ? 'approved' : 'pending' }}">{{ ucfirst(strtolower($r->status ?? '')) }}</span></td>
                    <td style="padding:14px 18px;font-size:13px;font-weight:600;color:#0891b2;white-space:nowrap;">{{ $r->date_paid ? $r->date_paid->format('M d, Y') : ' ' }}</td>
                </tr>
                @endforeach
                <tr id="calAgentCashAdvanceNoResults" class="cal-no-results-row" style="display:none;"><td colspan="7">No records match your filters.</td></tr>
                </tbody>
            </table>
            </div>
            @endif
        </div>

    </div>
    @else
    <div class="cal-grid-wrap">
        <div class="cal-day-headers">
            @foreach($dayNames as $i => $d)
            <div class="cal-day-hdr {{ in_array($i,[0,6]) ? 'weekend' : '' }}">{{ $d }}</div>
            @endforeach
        </div>
        <div class="cal-days">
            @for($i = 0; $i < $firstDay; $i++)
            <div class="cal-cell empty"></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $dateStr   = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $isToday   = $dateStr === $today;
                $events    = $releasesByDay->get($day, collect());
                $col       = ($firstDay + $day - 1) % 7;
                $isWeekend = $col === 0 || $col === 6;
                $cls       = $isToday ? 'today' : ($isWeekend ? 'weekend' : '');
            @endphp
            <div class="cal-cell {{ $cls }}">
                <span class="cal-day-num">{{ $day }}</span>
                @php
                    $typeClass = ['expense' => 'cal-event-expense', 'cash_advance' => 'cal-event-cash-advance', 'agent_cash_advance' => 'cal-event-agent-cash-advance'];
                @endphp
                @foreach($events->take(2) as $event)
                @php
                    $label = match($event->_type) {
                        'expense' => $event->requestor_name,
                        'cash_advance' => $event->employee_name,
                        'agent_cash_advance' => $event->agent_name,
                        default => $event->client_name,
                    };
                @endphp
                <div class="cal-event {{ $typeClass[$event->_type] ?? '' }}" onclick="showEventDetail('{{ $event->_type }}', {{ $event->id }})" title="{{ $label }}">
                    {{ $label }}
                </div>
                @endforeach
                @if($events->count() > 2)
                <div class="cal-more" onclick="event.stopPropagation(); showDayEvents('{{ $dateStr }}')">+{{ $events->count()-2 }} more</div>
                @endif
            </div>
            @endfor

            @php $rem = ($firstDay + $daysInMonth) % 7; @endphp
            @if($rem > 0)
                @for($i = 0; $i < (7 - $rem); $i++)
                <div class="cal-cell empty"></div>
                @endfor
            @endif
        </div>
    </div>
    @endif

</div>

{{-- Day Events Modal --}}
<div id="calDayModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:white;border-radius:14px;width:480px;max-width:95vw;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);padding:18px 22px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div>
                <div style="color:rgba(255,255,255,.65);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px;">All Releases</div>
                <div style="color:white;font-size:16px;font-weight:700;" id="calDayModalTitle"> </div>
            </div>
            <button onclick="document.getElementById('calDayModal').style.display='none'" style="background:rgba(255,255,255,.15);border:none;color:white;width:28px;height:28px;border-radius:7px;cursor:pointer;font-size:16px;line-height:1;">&times;</button>
        </div>
        <div style="padding:14px 16px;overflow-y:auto;" id="calDayModalBody"></div>
    </div>
</div>

{{-- Event Detail Modal --}}
<div id="calEventModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:white;border-radius:14px;width:440px;max-width:95vw;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);padding:18px 22px;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="color:rgba(255,255,255,.65);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px;">Release Details</div>
                <div style="color:white;font-size:16px;font-weight:700;" id="calModalTitle"> </div>
            </div>
            <button onclick="document.getElementById('calEventModal').style.display='none'" style="background:rgba(255,255,255,.15);border:none;color:white;width:28px;height:28px;border-radius:7px;cursor:pointer;font-size:16px;line-height:1;">&times;</button>
        </div>
        <div style="padding:20px 22px;" id="calEventBody"></div>
    </div>
</div>

<script>
const calEvents = @json($releases->values());
function showEventDetail(type, id) {
    const ev = calEvents.find(e => e.id == id && e._type == type);
    if (!ev) return;
    const fmt = v => v ? '\u20B1' + parseFloat(v).toLocaleString('en-US',{minimumFractionDigits:2}) : ' ';
    const fmtDate = v => { if(!v) return ' '; try { return new Date(v).toLocaleDateString('en-US',{month:'long',day:'numeric',year:'numeric'}); } catch(e){ return v; } };

    const isExpense = type === 'expense';
    const isCashAdvance = type === 'cash_advance';
    const isAgentCashAdvance = type === 'agent_cash_advance';
    const rows = isExpense ? [
        ['Date Released', fmtDate(ev.date_released), false],
        ['Requestor Name', ev.requestor_name||' ', false],
        ['Department', ev.department||' ', false],
        ['Category', ev.category||' ', false],
        ['Requested Amount', fmt(ev.requested_amount), true],
        ['Status', ev.status||' ', false],
    ] : isCashAdvance ? [
        ['Cash Advance No.', ev.control_number||' ', false],
        ['Employee', ev.employee_name||' ', false],
        ['Repayment Term', 'Term ' + ev.term_number, false],
        ['Amount', fmt(ev.amount), true],
        ['Payment Stage', (ev.term_number||'?') + '/' + (ev.total_terms||'?'), false],
        ['Status', ev.status||' ', false],
        ['Date Paid', fmtDate(ev.date_paid), false],
    ] : isAgentCashAdvance ? [
        ['Cash Advance No.', ev.control_number||' ', false],
        ['Agent', ev.agent_name||' ', false],
        ['Repayment Term', 'Term ' + ev.term_number, false],
        ['Amount', fmt(ev.amount), true],
        ['Payment Stage', (ev.term_number||'?') + '/' + (ev.total_terms||'?'), false],
        ['Status', ev.status||' ', false],
        ['Date Paid', fmtDate(ev.date_paid), false],
    ] : [
        ['Date Released', fmtDate(ev.date_released), false],
        ['Agent', ev.agent_name||' ', false],
        ['Project', ev.project_name||' ', false],
        ['Net TCP', fmt(ev.net_tcp), false],
        ['Commission', fmt(ev.commission), true],
        ['Status', ev.status||' ', false],
    ];

    const highlightColor = isExpense ? '#dc2626' : isCashAdvance ? '#4f46e5' : isAgentCashAdvance ? '#0891b2' : '#059669';
    document.getElementById('calModalTitle').textContent = (isExpense ? ev.requestor_name : isCashAdvance ? ev.employee_name : isAgentCashAdvance ? ev.agent_name : ev.client_name) || ' ';
    document.getElementById('calEventBody').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            ${rows.map(([lbl,val,highlight]) => `
                <div style="background:#f8fafc;border-radius:8px;padding:10px 12px;border:1px solid #f1f5f9;">
                    <div style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">${lbl}</div>
                    <div style="font-size:13px;font-weight:${highlight?'700':'600'};color:${highlight?highlightColor:'#1e293b'};">${val}</div>
                </div>
            `).join('')}
        </div>`;
    document.getElementById('calEventModal').style.display = 'flex';
}

function showDayEvents(dateStr) {
    const dayEvents = calEvents.filter(e => e._date_key === dateStr);
    const fmt = v => v ? '\u20B1' + parseFloat(v).toLocaleString('en-US',{minimumFractionDigits:2}) : ' ';
    const dt = new Date(dateStr + 'T00:00:00');
    document.getElementById('calDayModalTitle').textContent = dt.toLocaleDateString('en-US',{month:'long',day:'numeric',year:'numeric'});
    document.getElementById('calDayModalBody').innerHTML = dayEvents.map(ev => {
        const isExpense = ev._type === 'expense';
        const isCashAdvance = ev._type === 'cash_advance';
        const isAgentCashAdvance = ev._type === 'agent_cash_advance';
        const title = isExpense ? (ev.requestor_name || ' ') : isCashAdvance ? (ev.employee_name || ' ') : isAgentCashAdvance ? (ev.agent_name || ' ') : (ev.client_name || ' ');
        const subtitle = isExpense ? (ev.department || ' ') : isCashAdvance ? (ev.control_number || ' ') : isAgentCashAdvance ? (ev.control_number || ' ') : (ev.agent_name || ' ');
        const amount = isExpense ? fmt(ev.requested_amount) : (isCashAdvance || isAgentCashAdvance) ? fmt(ev.amount) : fmt(ev.commission);
        const amountColor = isExpense ? '#dc2626' : isCashAdvance ? '#4f46e5' : isAgentCashAdvance ? '#0891b2' : '#059669';
        return `
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border-radius:8px;border:1px solid #f1f5f9;margin-bottom:8px;cursor:pointer;"
             onclick="document.getElementById('calDayModal').style.display='none';showEventDetail('${ev._type}', ${ev.id})">
            <div>
                <div style="font-size:13px;font-weight:700;color:#1e293b;">${title}</div>
                <div style="font-size:11px;color:#94a3b8;">${subtitle}</div>
            </div>
            <div style="font-size:12px;font-weight:700;color:${amountColor};">${amount}</div>
        </div>`;
    }).join('') || '<div style="text-align:center;color:#94a3b8;font-size:13px;padding:20px;">No releases found.</div>';
    document.getElementById('calDayModal').style.display = 'flex';
}
// ---- List-view table filters (search + column filter dropdown) ----
// Reusable per-table engine, mirrors the "All Expenses" / Commission Monitoring filter pattern.
function createCalTableFilter(opts) {
    const state = { columnFilters: {} };

    function fieldConfig(key) {
        return opts.fields.find(f => f.key === key);
    }

    function toggleMenu(evt) {
        if (evt) evt.stopPropagation();
        const menu = document.getElementById(opts.menuId);
        if (!menu) return;
        const isOpen = menu.style.display === 'block';
        menu.style.display = isOpen ? 'none' : 'block';
        if (!isOpen) renderMenu();
    }

    function closeMenu() {
        const menu = document.getElementById(opts.menuId);
        if (menu) menu.style.display = 'none';
    }

    function renderMenu() {
        const menu = document.getElementById(opts.menuId);
        if (!menu) return;
        menu.innerHTML = opts.fields.map(f => {
            const active = state.columnFilters.hasOwnProperty(f.key);
            return `<div class="cal-column-filter-menu-item${active ? ' is-active' : ''}" onclick="calFilters.${opts.name}.toggleField('${f.key}')">
                        <span class="cfm-check">&#10003;</span><span>${f.label}</span>
                    </div>`;
        }).join('');
    }

    function toggleField(key) {
        if (state.columnFilters.hasOwnProperty(key)) {
            removeField(key);
        } else {
            const f = fieldConfig(key);
            state.columnFilters[key] = (f && f.type === 'daterange') ? { from: '', to: '' } : '';
            renderMenu();
            renderActive();
            closeMenu();
            setTimeout(() => {
                const el = document.getElementById(opts.name + 'Input_' + key) || document.getElementById(opts.name + 'Input_' + key + '_from');
                if (el) el.focus();
            }, 0);
        }
    }

    function removeField(key) {
        delete state.columnFilters[key];
        renderMenu();
        renderActive();
        apply();
    }

    function clearAll() {
        Object.keys(state.columnFilters).forEach(k => delete state.columnFilters[k]);
        renderMenu();
        renderActive();
        apply();
    }

    function updateValue(key, value) {
        state.columnFilters[key] = value;
        apply();
    }

    function updateRangeValue(key, part, value) {
        if (!state.columnFilters[key] || typeof state.columnFilters[key] !== 'object') {
            state.columnFilters[key] = { from: '', to: '' };
        }
        state.columnFilters[key][part] = value;
        apply();
    }

    function renderActive() {
        const row = document.getElementById(opts.activeRowId);
        const badge = document.getElementById(opts.badgeId);
        if (!row) return;
        const keys = Object.keys(state.columnFilters);

        if (badge) {
            badge.style.display = keys.length ? 'inline-flex' : 'none';
            badge.textContent = keys.length;
        }

        if (keys.length === 0) {
            row.style.display = 'none';
            row.innerHTML = '';
            return;
        }

        row.style.display = 'flex';
        row.innerHTML = keys.map(key => {
            const f = fieldConfig(key);
            let inputHtml = '';
            if (f.type === 'select') {
                const val = state.columnFilters[key] || '';
                inputHtml = `<select id="${opts.name}Input_${key}" onchange="calFilters.${opts.name}.updateValue('${key}', this.value)">
                                <option value="">All</option>
                                ${f.options.map(o => `<option value="${o}" ${val === o ? 'selected' : ''}>${o}</option>`).join('')}
                             </select>`;
            } else if (f.type === 'daterange') {
                const range = (state.columnFilters[key] && typeof state.columnFilters[key] === 'object') ? state.columnFilters[key] : { from: '', to: '' };
                inputHtml = `<input type="date" id="${opts.name}Input_${key}_from" value="${range.from || ''}" onchange="calFilters.${opts.name}.updateRangeValue('${key}', 'from', this.value)">
                             <span style="color:#8a9bad;font-size:12px;">to</span>
                             <input type="date" id="${opts.name}Input_${key}_to" value="${range.to || ''}" onchange="calFilters.${opts.name}.updateRangeValue('${key}', 'to', this.value)">`;
            } else {
                const val = state.columnFilters[key] || '';
                inputHtml = `<input type="text" id="${opts.name}Input_${key}" placeholder="Search ${f.label.toLowerCase()}..." value="${val}" oninput="calFilters.${opts.name}.updateValue('${key}', this.value)">`;
            }
            return `<div class="cal-filter-chip">
                        <label>${f.label}</label>
                        ${inputHtml}
                        <button type="button" class="cfm-remove" title="Remove filter" onclick="calFilters.${opts.name}.removeField('${key}')">&times;</button>
                    </div>`;
        }).join('') + `<button type="button" class="cal-clear-filters-btn" onclick="calFilters.${opts.name}.clearAll()">Clear Filters</button>`;
    }

    function matchesColumnFilters(row) {
        for (const key in state.columnFilters) {
            const f = fieldConfig(key);
            if (!f) continue;

            if (f.type === 'daterange') {
                const range = state.columnFilters[key];
                if (!range || (!range.from && !range.to)) continue;
                const rowVal = (row.getAttribute(f.dataAttr) || '').toString();
                if (!rowVal) return false;
                if (range.from && rowVal < range.from) return false;
                if (range.to && rowVal > range.to) return false;
                continue;
            }

            const filterVal = (state.columnFilters[key] || '').toString().trim().toLowerCase();
            if (!filterVal) continue;
            const rowVal = (row.getAttribute(f.dataAttr) || '').toString().toLowerCase();

            if (f.type === 'select') {
                if (rowVal !== filterVal) return false;
            } else {
                if (!rowVal.includes(filterVal)) return false;
            }
        }
        return true;
    }

    function apply() {
        const searchInput = document.getElementById(opts.searchId);
        const tableBody = document.getElementById(opts.tableBodyId);
        if (!tableBody) return;
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const searchWords = searchTerm.split(/\s+/).filter(w => w.length > 0);

        const dataRows = Array.from(tableBody.querySelectorAll('tr[data-status], tr[' + opts.fields[0].dataAttr + ']'));
        let visible = 0;

        for (const row of dataRows) {
            const text = row.textContent.toLowerCase();
            const matchesSearch = searchWords.length === 0 || searchWords.every(w => text.includes(w));
            const columnMatch = matchesColumnFilters(row);

            if (matchesSearch && columnMatch) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        }

        const noResults = document.getElementById(opts.noResultsId);
        if (noResults) {
            noResults.style.display = (visible === 0 && dataRows.length > 0) ? '' : 'none';
        }
    }

    document.addEventListener('click', function(evt) {
        const wrapper = document.getElementById(opts.dropdownId);
        if (wrapper && !wrapper.contains(evt.target)) closeMenu();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById(opts.searchId);
        if (searchInput) searchInput.addEventListener('input', apply);
    });

    return { toggleMenu, toggleField, removeField, clearAll, updateValue, updateRangeValue, apply };
}

const calFilters = {
    commission: createCalTableFilter({
        name: 'commission',
        dropdownId: 'calCommissionFilterDropdown',
        menuId: 'calCommissionFilterMenu',
        activeRowId: 'calCommissionActiveFilters',
        badgeId: 'calCommissionFilterBadge',
        searchId: 'calCommissionSearch',
        tableBodyId: 'calCommissionTableBody',
        noResultsId: 'calCommissionNoResults',
        fields: [
            { key: 'agent',         label: 'Agent',          dataAttr: 'data-agent',          type: 'text' },
            { key: 'client',        label: 'Client',         dataAttr: 'data-client',         type: 'text' },
            { key: 'project',       label: 'Project',        dataAttr: 'data-project',        type: 'text' },
            { key: 'date_released', label: 'Date Released',  dataAttr: 'data-date-released',  type: 'daterange' },
            { key: 'status',        label: 'Status',         dataAttr: 'data-status',         type: 'select', options: ['Requested', 'Not Yet Released', 'Released'] },
        ]
    }),
    expense: createCalTableFilter({
        name: 'expense',
        dropdownId: 'calExpenseFilterDropdown',
        menuId: 'calExpenseFilterMenu',
        activeRowId: 'calExpenseActiveFilters',
        badgeId: 'calExpenseFilterBadge',
        searchId: 'calExpenseSearch',
        tableBodyId: 'calExpenseTableBody',
        noResultsId: 'calExpenseNoResults',
        fields: [
            { key: 'requestor',     label: 'Requestor Name', dataAttr: 'data-requestor',      type: 'text' },
            { key: 'department',    label: 'Department',     dataAttr: 'data-department',     type: 'text' },
            { key: 'category',      label: 'Category',       dataAttr: 'data-category',       type: 'text' },
            { key: 'date_released', label: 'Date Released',  dataAttr: 'data-date-released',  type: 'daterange' },
            { key: 'status',        label: 'Status',         dataAttr: 'data-status',         type: 'text' },
        ]
    }),
    cashAdvance: createCalTableFilter({
        name: 'cashAdvance',
        dropdownId: 'calCashAdvanceFilterDropdown',
        menuId: 'calCashAdvanceFilterMenu',
        activeRowId: 'calCashAdvanceActiveFilters',
        badgeId: 'calCashAdvanceFilterBadge',
        searchId: 'calCashAdvanceSearch',
        tableBodyId: 'calCashAdvanceTableBody',
        noResultsId: 'calCashAdvanceNoResults',
        fields: [
            { key: 'control',       label: 'Cash Advance No.', dataAttr: 'data-control',        type: 'text' },
            { key: 'employee',      label: 'Employee',         dataAttr: 'data-employee',        type: 'text' },
            { key: 'stage',         label: 'Payment Stage',    dataAttr: 'data-stage',           type: 'text' },
            { key: 'date_paid',     label: 'Date Paid',        dataAttr: 'data-date-paid',       type: 'daterange' },
            { key: 'status',        label: 'Status',           dataAttr: 'data-status',          type: 'text' },
        ]
    }),
    agentCashAdvance: createCalTableFilter({
        name: 'agentCashAdvance',
        dropdownId: 'calAgentCashAdvanceFilterDropdown',
        menuId: 'calAgentCashAdvanceFilterMenu',
        activeRowId: 'calAgentCashAdvanceActiveFilters',
        badgeId: 'calAgentCashAdvanceFilterBadge',
        searchId: 'calAgentCashAdvanceSearch',
        tableBodyId: 'calAgentCashAdvanceTableBody',
        noResultsId: 'calAgentCashAdvanceNoResults',
        fields: [
            { key: 'control',       label: 'Cash Advance No.', dataAttr: 'data-control',        type: 'text' },
            { key: 'agent',         label: 'Agent',            dataAttr: 'data-agent',           type: 'text' },
            { key: 'stage',         label: 'Payment Stage',    dataAttr: 'data-stage',           type: 'text' },
            { key: 'date_paid',     label: 'Date Paid',        dataAttr: 'data-date-paid',       type: 'daterange' },
            { key: 'status',        label: 'Status',           dataAttr: 'data-status',          type: 'text' },
        ]
    }),
};
</script>
@endsection