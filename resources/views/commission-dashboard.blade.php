\@extends('layouts.dashboard')
@section('title', 'Commission Dashboard')
@section('content')
<style>
.cd-header{background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:28px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25)}
.cd-header h1{font-size:28px;font-weight:700;color:white;margin:0 0 8px;position:relative;z-index:2}
.cd-header p{font-size:14px;color:rgba(255,255,255,.75);margin:0;position:relative;z-index:2}
.cd-deco{position:absolute;top:-40px;right:-40px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.06)}
.cd-deco2{position:absolute;top:40px;right:120px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.04)}
.cd-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
@media (max-width:768px){.cd-stats{grid-template-columns:repeat(2,1fr);gap:12px}.cd-header{padding:22px 20px}.cd-card-hdr{gap:8px}.cd-search input{width:100%}.cd-search{width:100%}}
@media (max-width:480px){.cd-stats{grid-template-columns:1fr}.cd-stat-val{font-size:19px}}
.cd-stat{background:white;border-radius:12px;padding:20px;border:1px solid #e8ecf0;box-shadow:0 1px 4px rgba(0,0,0,.05)}
.cd-stat-val{font-size:22px;font-weight:800;color:#0f172a;line-height:1}
.cd-stat-lbl{font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-top:4px}
.cd-stat-sub{font-size:12px;color:#64748b;margin-top:6px}
.cd-card{background:white;border-radius:12px;border:1px solid #e8ecf0;box-shadow:0 1px 4px rgba(0,0,0,.05);overflow:hidden;margin-bottom:20px}
.cd-card-hdr{padding:14px 18px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.cd-card-hdr h3{font-size:14px;font-weight:700;color:#0f172a;margin:0}
.cd-table{width:100%;border-collapse:collapse}
.cd-table thead tr{background:#1e4575}
.cd-table thead th{padding:11px 16px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.7px;white-space:nowrap;position:sticky;top:0;background:#1e4575;z-index:4;box-shadow:0 2px 4px -2px rgba(0,0,0,.25)}
.cd-table tbody tr{border-bottom:1px solid #f1f5f9;transition:background .15s}
.cd-table tbody tr:hover{background:#f8fafc}
.cd-table tbody tr:last-child{border-bottom:none}
.cd-table td{padding:11px 16px;font-size:13px;color:#374151;vertical-align:middle}
.cd-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700}
.cd-badge-released{background:#dcfce7;color:#166534}
.cd-badge-pending{background:#fee2e2;color:#991b1b}
.cd-bar-wrap{background:#f1f5f9;border-radius:4px;height:6px;overflow:hidden;min-width:80px}
.cd-bar-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,#1e4575,#2563eb);transition:width .4s}
.cd-search{position:relative}
.cd-search input{padding:7px 12px 7px 32px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;color:#374151;background:#f8fafc;width:200px}
.cd-search input:focus{outline:none;border-color:#1e4575;background:white}
.cd-search svg{position:absolute;left:9px;top:50%;transform:translateY(-50%);width:13px;height:13px;color:#94a3b8}
.cd-table-scroll{overflow-x:auto !important;overflow-y:visible !important;max-height:none !important;}

/* ---- Filter dropdown + chips (matches Commission Monitoring / ARC Sales pattern) ---- */
.cd-filters-bar{display:flex;flex-direction:column;gap:10px;width:100%;}
.cd-filters-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.column-filter-dropdown{position:relative}
.column-filter-btn{display:inline-flex;align-items:center;gap:6px;white-space:nowrap;font-size:12px;font-weight:600;color:#1e4575;background:white;border:2px solid #1e4575;border-radius:8px;padding:7px 12px;cursor:pointer;height:34px;box-sizing:border-box;transition:all .2s ease}
.column-filter-btn:hover{background:#eef2f7}
.filter-count-badge{background:#A37929;color:white;font-size:10px;font-weight:700;border-radius:999px;min-width:16px;height:16px;display:inline-flex;align-items:center;justify-content:center;padding:0 5px}
.column-filter-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:200px;max-height:300px;overflow-y:auto;background:white;border:1.5px solid #d0d5dd;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:500;padding:6px}
.column-filter-menu-item{display:flex;align-items:center;gap:8px;padding:8px 10px;font-size:12px;font-weight:500;color:#344054;border-radius:6px;cursor:pointer;white-space:nowrap}
.column-filter-menu-item:hover{background:#eef2f7}
.column-filter-menu-item .cfm-check{width:14px;color:#A37929;font-weight:700;visibility:hidden}
.column-filter-menu-item.is-active .cfm-check{visibility:visible}
.column-filter-menu-item.is-active{color:#1e4575;font-weight:700}
.active-column-filters-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
.column-filter-chip{display:flex;align-items:center;gap:6px;background:#f5f7fa;border:1.5px solid #d0d5dd;border-radius:8px;padding:5px 6px 5px 10px}
.column-filter-chip label{font-size:10px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
.column-filter-chip input,.column-filter-chip select{font-size:12px;padding:5px 7px;border:1.5px solid #d0d5dd;border-radius:6px;color:#344054;min-width:110px}
.column-filter-chip .cfm-remove{background:none;border:none;color:#8a9bad;cursor:pointer;font-size:15px;line-height:1;padding:2px 4px}
.column-filter-chip .cfm-remove:hover{color:#dc2626}
.clear-column-filters-btn{font-size:11px;font-weight:600;color:#1e4575;background:#eef2f7;border:1px solid #d0d5dd;border-radius:6px;padding:7px 12px;cursor:pointer;white-space:nowrap}
@media (max-width:768px){
  .column-filter-menu{left:0;right:0;min-width:0;width:100%;box-sizing:border-box}
  .active-column-filters-row{flex-direction:column;align-items:stretch}
  .column-filter-chip{width:100%;flex-wrap:wrap;box-sizing:border-box}
  .column-filter-chip label{flex:1 1 100%}
  .column-filter-chip input,.column-filter-chip select{flex:1 1 auto;min-width:0;width:100%}
  .clear-column-filters-btn{width:100%;text-align:center}
}
</style>

<div class="cd-header">
    <div class="cd-deco"></div>
    <div class="cd-deco2"></div>
    <div style="position:relative;z-index:2;">
        <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;">Finance</div>
        <h1>Commission Dashboard</h1>
        <p>Per agent breakdown — released vs pending commissions</p>
    </div>
</div>

@php
use App\Models\CommissionRequest;

$all = CommissionRequest::all();
$totalCommission  = $all->sum('commission');
$released         = $all->where('status', 'Released');
$pending          = $all->where('status', '!=', 'Released');
$totalReleased    = $released->sum('commission');
$totalPending     = $pending->sum('commission');
$totalAgents      = $all->pluck('agent_name')->filter()->unique()->count();

// Per-agent breakdown
$byAgent = $all->groupBy('agent_name')->map(function($rows, $agent) {
    $rel = $rows->where('status', 'Released');
    $pen = $rows->where('status', '!=', 'Released');
    return [
        'agent'     => $agent ?: '—',
        'total'     => $rows->count(),
        'released'  => $rel->count(),
        'pending'   => $pen->count(),
        'comm_rel'  => $rel->sum('commission'),
        'comm_pen'  => $pen->sum('commission'),
        'comm_tot'  => $rows->sum('commission'),
        'net_tcp'   => $rows->sum('net_tcp'),
    ];
})->sortByDesc('comm_tot')->values();
@endphp

{{-- Summary Stats --}}
<div class="cd-stats">
    <div class="cd-stat">
        <div class="cd-stat-val">{{ $totalAgents }}</div>
        <div class="cd-stat-lbl">Total Agents</div>
        <div class="cd-stat-sub">with commission records</div>
    </div>
    <div class="cd-stat">
        <div class="cd-stat-val" style="color:#1e4575;">₱{{ number_format($totalCommission, 2) }}</div>
        <div class="cd-stat-lbl">Total Commission</div>
        <div class="cd-stat-sub">{{ $all->count() }} transactions</div>
    </div>
    <div class="cd-stat">
        <div class="cd-stat-val" style="color:#16a34a;">₱{{ number_format($totalReleased, 2) }}</div>
        <div class="cd-stat-lbl">Released</div>
        <div class="cd-stat-sub">{{ $released->count() }} transactions</div>
    </div>
    <div class="cd-stat">
        <div class="cd-stat-val" style="color:#d97706;">₱{{ number_format($totalPending, 2) }}</div>
        <div class="cd-stat-lbl">Pending</div>
        <div class="cd-stat-sub">{{ $pending->count() }} transactions</div>
    </div>
</div>

{{-- Per Agent Table --}}
<div class="cd-card">
    <div class="cd-card-hdr" style="flex-direction:column;align-items:stretch;">
        <h3>Per Agent Breakdown</h3>
        <div class="cd-filters-bar">
            <div class="cd-filters-row">
                <div class="column-filter-dropdown" id="cdAgentFilterDropdown">
                    <button type="button" class="column-filter-btn" onclick="toggleCdFilterMenu(event, 'agent')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        <span>Filter</span>
                        <span id="cdAgentFilterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="cdAgentFilterMenu" class="column-filter-menu" style="display:none;"></div>
                </div>
                <div class="cd-search" style="width:220px;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="cdAgentSearchInput" placeholder="Search agent..." style="width:100%;" oninput="applyCdAgentFilters()">
                </div>
            </div>
            <div id="cdAgentActiveFiltersRow" class="active-column-filters-row" style="display:none;"></div>
        </div>
    </div>
    <div class="cd-table-scroll" style="overflow-x:auto;">
    <table class="cd-table" id="cdAgentTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Agent Name</th>
                <th>Transactions</th>
                <th>Released</th>
                <th>Pending</th>
                <th>Commission Released</th>
                <th>Commission Pending</th>
                <th>Total Commission</th>
                <th>Total Net TCP</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            @forelse($byAgent as $i => $a)
            <tr
                data-agent="{{ $a['agent'] }}"
                data-transactions="{{ $a['total'] }}"
                data-released="{{ $a['released'] }}"
                data-pending="{{ $a['pending'] }}"
                data-commission-released="{{ $a['comm_rel'] }}"
                data-commission-pending="{{ $a['comm_pen'] }}"
                data-total-commission="{{ $a['comm_tot'] }}"
                data-total-net-tcp="{{ $a['net_tcp'] }}">
                <td style="color:#cbd5e1;font-weight:600;text-align:center;">{{ $i + 1 }}</td>
                <td style="font-weight:700;color:#0f172a;white-space:nowrap;">{{ $a['agent'] }}</td>
                <td style="text-align:center;">{{ $a['total'] }}</td>
                <td style="text-align:center;"><span class="cd-badge cd-badge-released">{{ $a['released'] }}</span></td>
                <td style="text-align:center;"><span class="cd-badge cd-badge-pending">{{ $a['pending'] }}</span></td>
                <td style="color:#16a34a;font-weight:600;">₱{{ number_format($a['comm_rel'], 2) }}</td>
                <td style="color:#d97706;font-weight:600;">₱{{ number_format($a['comm_pen'], 2) }}</td>
                <td style="color:#1e4575;font-weight:700;">₱{{ number_format($a['comm_tot'], 2) }}</td>
                <td style="color:#64748b;">₱{{ number_format($a['net_tcp'], 2) }}</td>
                <td style="min-width:100px;">
                    @php $pct = $a['comm_tot'] > 0 ? round(($a['comm_rel'] / $a['comm_tot']) * 100) : 0; @endphp
                    <div style="display:flex;align-items:center;gap:6px;">
                        <div class="cd-bar-wrap" style="flex:1;"><div class="cd-bar-fill" style="width:{{ $pct }}%;background:{{ $pct >= 80 ? '#16a34a' : ($pct >= 50 ? '#2563eb' : '#d97706') }};"></div></div>
                        <span style="font-size:11px;color:#64748b;flex-shrink:0;">{{ $pct }}%</span>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align:center;padding:40px;color:#94a3b8;">No commission records found.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

{{-- Recent Transactions --}}
<div class="cd-card">
    <div class="cd-card-hdr" style="flex-direction:column;align-items:stretch;">
        <h3>Recent Transactions</h3>
        <div class="cd-filters-bar">
            <div class="cd-filters-row">
                <div class="column-filter-dropdown" id="cdTxFilterDropdown">
                    <button type="button" class="column-filter-btn" onclick="toggleCdFilterMenu(event, 'tx')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        <span>Filter</span>
                        <span id="cdTxFilterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="cdTxFilterMenu" class="column-filter-menu" style="display:none;"></div>
                </div>
                <div class="cd-search" style="width:220px;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="cdTxSearchInput" placeholder="Search..." style="width:100%;" oninput="applyCdTxFilters()">
                </div>
            </div>
            <div id="cdTxActiveFiltersRow" class="active-column-filters-row" style="display:none;"></div>
        </div>
    </div>
    <div class="cd-table-scroll" style="overflow-x:auto;">
    <table class="cd-table" id="cdTxTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Agent</th>
                <th>Client</th>
                <th>Project</th>
                <th>Net TCP</th>
                <th>Commission</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($all->sortByDesc('date_requested')->take(50) as $i => $tx)
            <tr
                data-agent="{{ $tx->agent_name ?? '' }}"
                data-client="{{ $tx->client_name ?? '' }}"
                data-project="{{ $tx->project_name ?? '' }}"
                data-net-tcp="{{ $tx->net_tcp ?? 0 }}"
                data-commission="{{ $tx->commission ?? 0 }}"
                data-date="{{ $tx->date_requested ? $tx->date_requested->format('Y-m-d') : '' }}"
                data-status="{{ $tx->status === 'Not Released' ? 'Not Yet Released' : ($tx->status ?: 'Not Yet Released') }}">
                <td style="color:#cbd5e1;font-weight:600;text-align:center;">{{ $i + 1 }}</td>
                <td style="font-weight:600;color:#0f172a;">{{ $tx->agent_name ?: '—' }}</td>
                <td>{{ $tx->client_name ?: '—' }}</td>
                <td style="color:#64748b;">{{ $tx->project_name ?: '—' }}</td>
                <td style="color:#1e4575;font-weight:600;">{{ $tx->net_tcp ? '₱'.number_format($tx->net_tcp, 2) : '—' }}</td>
                <td style="color:#16a34a;font-weight:600;">{{ $tx->commission ? '₱'.number_format($tx->commission, 2) : '—' }}</td>
                <td style="color:#64748b;">{{ $tx->date_requested ? $tx->date_requested->format('M d, Y') : '—' }}</td>
                <td>
                    <span class="cd-badge {{ $tx->status === 'Released' ? 'cd-badge-released' : 'cd-badge-pending' }}">{{ $tx->status === 'Not Released' ? 'Not Yet Released' : ($tx->status ?: 'Not Yet Released') }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:40px;color:#94a3b8;">No transactions found.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<script>
function toggleCdFilterMenu(e, which) {
    e.stopPropagation();
    var menuId = which === 'agent' ? 'cdAgentFilterMenu' : 'cdTxFilterMenu';
    var menu = document.getElementById(menuId);
    if (menu.style.display === 'block') { menu.style.display = 'none'; return; }
    if (which === 'agent') renderCdAgentFilterMenu(); else renderCdTxFilterMenu();
    menu.style.display = 'block';
}

/* ---- Per Agent Breakdown ---- */
var CD_AGENT_COLUMNS = [
    { key: 'agent',               label: 'Agent Name',          type: 'text',   data: 'agent' },
    { key: 'transactions',        label: 'Transactions',        type: 'number', data: 'transactions' },
    { key: 'released',            label: 'Released',            type: 'number', data: 'released' },
    { key: 'pending',             label: 'Pending',              type: 'number', data: 'pending' },
    { key: 'commission-released', label: 'Commission Released', type: 'number', data: 'commissionReleased' },
    { key: 'commission-pending',  label: 'Commission Pending',  type: 'number', data: 'commissionPending' },
    { key: 'total-commission',    label: 'Total Commission',    type: 'number', data: 'totalCommission' },
    { key: 'total-net-tcp',       label: 'Total Net TCP',       type: 'number', data: 'totalNetTcp' },
];
var cdAgentFilters = {};

function renderCdAgentFilterMenu() {
    var menu = document.getElementById('cdAgentFilterMenu');
    menu.innerHTML = '';
    CD_AGENT_COLUMNS.forEach(function (col) {
        var item = document.createElement('div');
        item.className = 'column-filter-menu-item' + (cdAgentFilters.hasOwnProperty(col.key) ? ' is-active' : '');
        item.innerHTML = '<span class="cfm-check">✓</span><span>' + col.label + '</span>';
        item.onclick = function (ev) { ev.stopPropagation(); toggleCdAgentFilterColumn(col.key); };
        menu.appendChild(item);
    });
}

function toggleCdAgentFilterColumn(key) {
    if (cdAgentFilters.hasOwnProperty(key)) delete cdAgentFilters[key];
    else cdAgentFilters[key] = '';
    renderCdAgentFilterMenu();
    renderCdAgentActiveChips();
    updateCdAgentBadge();
    applyCdAgentFilters();
    document.getElementById('cdAgentFilterMenu').style.display = 'none';
}

function removeCdAgentFilterColumn(key) {
    delete cdAgentFilters[key];
    renderCdAgentActiveChips();
    updateCdAgentBadge();
    applyCdAgentFilters();
}

function updateCdAgentBadge() {
    var badge = document.getElementById('cdAgentFilterCountBadge');
    var count = Object.keys(cdAgentFilters).length;
    badge.style.display = count > 0 ? 'inline-flex' : 'none';
    badge.textContent = count;
}

function renderCdAgentActiveChips() {
    var row = document.getElementById('cdAgentActiveFiltersRow');
    var keys = Object.keys(cdAgentFilters);
    row.innerHTML = '';
    if (keys.length === 0) { row.style.display = 'none'; return; }
    row.style.display = 'flex';

    keys.forEach(function (key) {
        var col = CD_AGENT_COLUMNS.find(function (c) { return c.key === key; });
        if (!col) return;
        var chip = document.createElement('div');
        chip.className = 'column-filter-chip';
        var label = document.createElement('label');
        label.textContent = col.label;
        chip.appendChild(label);

        var input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Search ' + col.label.toLowerCase() + '...';
        input.value = cdAgentFilters[key];
        input.oninput = function () { cdAgentFilters[key] = this.value; applyCdAgentFilters(); };
        chip.appendChild(input);

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'cfm-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function () { removeCdAgentFilterColumn(key); };
        chip.appendChild(removeBtn);

        row.appendChild(chip);
    });

    var clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.className = 'clear-column-filters-btn';
    clearBtn.textContent = 'Clear Filters';
    clearBtn.onclick = function () {
        cdAgentFilters = {};
        renderCdAgentActiveChips();
        updateCdAgentBadge();
        applyCdAgentFilters();
    };
    row.appendChild(clearBtn);
}

function applyCdAgentFilters() {
    var globalSearch = (document.getElementById('cdAgentSearchInput').value || '').toLowerCase().trim();
    var rows = document.querySelectorAll('#cdAgentTable tbody tr');

    rows.forEach(function (row) {
        if (!row.dataset.agent) return; // skip empty-state row
        var visible = true;

        for (var key in cdAgentFilters) {
            var col = CD_AGENT_COLUMNS.find(function (c) { return c.key === key; });
            if (!col) continue;
            var val = (cdAgentFilters[key] || '').toString().trim().toLowerCase();
            if (!val) continue;
            var cellVal = (row.dataset[col.data] || '').toString().toLowerCase();
            if (!cellVal.includes(val)) { visible = false; break; }
        }

        if (visible && globalSearch) {
            var haystack = Object.values(row.dataset).join(' ').toLowerCase();
            if (!haystack.includes(globalSearch)) visible = false;
        }

        row.style.display = visible ? '' : 'none';
    });
}

/* ---- Recent Transactions ---- */
var CD_TX_COLUMNS = [
    { key: 'agent',      label: 'Agent',      type: 'text',   data: 'agent' },
    { key: 'client',     label: 'Client',     type: 'text',   data: 'client' },
    { key: 'project',    label: 'Project',    type: 'text',   data: 'project' },
    { key: 'net-tcp',    label: 'Net TCP',    type: 'number', data: 'netTcp' },
    { key: 'commission', label: 'Commission', type: 'number', data: 'commission' },
    { key: 'date',       label: 'Date',       type: 'daterange',   data: 'date' },
    { key: 'status',     label: 'Status',     type: 'select', data: 'status', options: ['Requested', 'Not Yet Released', 'Released'] },
];
var cdTxFilters = {};

function renderCdTxFilterMenu() {
    var menu = document.getElementById('cdTxFilterMenu');
    menu.innerHTML = '';
    CD_TX_COLUMNS.forEach(function (col) {
        var item = document.createElement('div');
        item.className = 'column-filter-menu-item' + (cdTxFilters.hasOwnProperty(col.key) ? ' is-active' : '');
        item.innerHTML = '<span class="cfm-check">✓</span><span>' + col.label + '</span>';
        item.onclick = function (ev) { ev.stopPropagation(); toggleCdTxFilterColumn(col.key); };
        menu.appendChild(item);
    });
}

function toggleCdTxFilterColumn(key) {
    if (cdTxFilters.hasOwnProperty(key)) delete cdTxFilters[key];
    else cdTxFilters[key] = '';
    renderCdTxFilterMenu();
    renderCdTxActiveChips();
    updateCdTxBadge();
    applyCdTxFilters();
    document.getElementById('cdTxFilterMenu').style.display = 'none';
}

function removeCdTxFilterColumn(key) {
    delete cdTxFilters[key];
    renderCdTxActiveChips();
    updateCdTxBadge();
    applyCdTxFilters();
}

function updateCdTxBadge() {
    var badge = document.getElementById('cdTxFilterCountBadge');
    var count = Object.keys(cdTxFilters).length;
    badge.style.display = count > 0 ? 'inline-flex' : 'none';
    badge.textContent = count;
}

function renderCdTxActiveChips() {
    var row = document.getElementById('cdTxActiveFiltersRow');
    var keys = Object.keys(cdTxFilters);
    row.innerHTML = '';
    if (keys.length === 0) { row.style.display = 'none'; return; }
    row.style.display = 'flex';

    keys.forEach(function (key) {
        var col = CD_TX_COLUMNS.find(function (c) { return c.key === key; });
        if (!col) return;
        var chip = document.createElement('div');
        chip.className = 'column-filter-chip';
        var label = document.createElement('label');
        label.textContent = col.label;
        chip.appendChild(label);

        var input;
        if (col.type === 'daterange') {
            if (!cdTxFilters[key] || typeof cdTxFilters[key] !== 'object') {
                cdTxFilters[key] = { from: '', to: '' };
            }
            var range = cdTxFilters[key];

            input = document.createElement('span');
            input.style.display = 'flex';
            input.style.alignItems = 'center';
            input.style.gap = '6px';

            var fromInput = document.createElement('input');
            fromInput.type = 'date';
            fromInput.value = range.from || '';
            fromInput.onchange = function () { range.from = this.value; applyCdTxFilters(); };

            var toLabel = document.createElement('span');
            toLabel.textContent = 'to';
            toLabel.style.cssText = 'color:#8a9bad;font-size:12px;';

            var toInput = document.createElement('input');
            toInput.type = 'date';
            toInput.value = range.to || '';
            toInput.onchange = function () { range.to = this.value; applyCdTxFilters(); };

            input.appendChild(fromInput);
            input.appendChild(toLabel);
            input.appendChild(toInput);
        } else if (col.type === 'select') {
            input = document.createElement('select');
            var optAll = document.createElement('option');
            optAll.value = ''; optAll.textContent = 'All';
            input.appendChild(optAll);
            col.options.forEach(function (o) {
                var opt = document.createElement('option');
                opt.value = o; opt.textContent = o;
                if (cdTxFilters[key] === o) opt.selected = true;
                input.appendChild(opt);
            });
            input.onchange = function () { cdTxFilters[key] = this.value; applyCdTxFilters(); };
        } else {
            input = document.createElement('input');
            input.type = col.type === 'date' ? 'date' : 'text';
            input.placeholder = 'Search ' + col.label.toLowerCase() + '...';
            input.value = cdTxFilters[key];
            input.oninput = function () { cdTxFilters[key] = this.value; applyCdTxFilters(); };
        }
        chip.appendChild(input);

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'cfm-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function () { removeCdTxFilterColumn(key); };
        chip.appendChild(removeBtn);

        row.appendChild(chip);
    });

    var clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.className = 'clear-column-filters-btn';
    clearBtn.textContent = 'Clear Filters';
    clearBtn.onclick = function () {
        cdTxFilters = {};
        renderCdTxActiveChips();
        updateCdTxBadge();
        applyCdTxFilters();
    };
    row.appendChild(clearBtn);
}

function applyCdTxFilters() {
    var globalSearch = (document.getElementById('cdTxSearchInput').value || '').toLowerCase().trim();
    var rows = document.querySelectorAll('#cdTxTable tbody tr');

    rows.forEach(function (row) {
        if (typeof row.dataset.status === 'undefined') return; // skip empty-state row
        var visible = true;

        for (var key in cdTxFilters) {
            var col = CD_TX_COLUMNS.find(function (c) { return c.key === key; });
            if (!col) continue;

            if (col.type === 'daterange') {
                var range = cdTxFilters[key];
                if (!range || (!range.from && !range.to)) continue;
                var cellDate = (row.dataset[col.data] || '').toString();
                if (!cellDate) { visible = false; break; }
                if (range.from && cellDate < range.from) { visible = false; break; }
                if (range.to && cellDate > range.to) { visible = false; break; }
                continue;
            }

            var val = (cdTxFilters[key] || '').toString().trim();
            if (!val) continue;
            var cellVal = (row.dataset[col.data] || '').toString();

            if (col.type === 'date') {
                if (cellVal !== val) { visible = false; break; }
            } else if (col.type === 'select') {
                if (cellVal !== val) { visible = false; break; }
            } else if (col.type === 'number') {
                if (!cellVal.replace(/[^0-9.]/g, '').includes(val.replace(/[^0-9.]/g, ''))) { visible = false; break; }
            } else {
                if (!cellVal.toLowerCase().includes(val.toLowerCase())) { visible = false; break; }
            }
        }

        if (visible && globalSearch) {
            var haystack = Object.values(row.dataset).join(' ').toLowerCase();
            if (!haystack.includes(globalSearch)) visible = false;
        }

        row.style.display = visible ? '' : 'none';
    });
}

document.addEventListener('click', function (e) {
    var d1 = document.getElementById('cdAgentFilterDropdown');
    if (d1 && !d1.contains(e.target)) document.getElementById('cdAgentFilterMenu').style.display = 'none';
    var d2 = document.getElementById('cdTxFilterDropdown');
    if (d2 && !d2.contains(e.target)) document.getElementById('cdTxFilterMenu').style.display = 'none';
});
</script>
@endsection
