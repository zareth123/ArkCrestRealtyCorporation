@extends('layouts.dashboard')

@section('content')
{{-- analytics-zero-filter-v3: contributors only; permanent doughnut legend --}}
<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-content">
        <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;">Sales & Marketing</div>
        <h1 class="welcome-title">Happy ArkCrest Morning, {{ auth()->user()->preferred_address ? auth()->user()->preferred_address.' '.auth()->user()->name : auth()->user()->name }}! 🎯</h1>
        <p class="welcome-subtitle">
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Sales Dashboard Overview - {{ date('F Y') }}
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
        </div>
    </div>
    <div class="welcome-decoration">
        <div class="decoration-circle circle-1"></div>
        <div class="decoration-circle circle-2"></div>
        <div class="decoration-circle circle-3"></div>
    </div>
</div>

<style>
    .welcome-banner {
        background: linear-gradient(135deg, #1e4575 0%, #2563eb 60%, #1e4575 100%);
        border-radius: 20px;
        padding: 36px 40px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(30,69,117,.25);
    }
    .welcome-content { position: relative; z-index: 2; }
    .welcome-title { font-size: 28px; font-weight: 700; color: white; margin: 0 0 8px 0; }
    .welcome-subtitle { font-size: 14px; color: rgba(255,255,255,0.75); margin: 0 0 12px; display: flex; align-items: center; gap: 8px; }
    .today-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; position: relative; z-index: 2; }
    .today-pill { display: inline-flex; align-items: center; gap: 5px; background: rgba(255,255,255,.15); color: white; font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 20px; backdrop-filter: blur(4px); }
    .today-pill-alert { background: rgba(251,191,36,.25); color: #fef3c7; }
    .icon-sm { width: 15px; height: 15px; }
    .welcome-decoration { position: absolute; top: 0; right: 0; width: 300px; height: 100%; pointer-events: none; }
    .decoration-circle { position: absolute; border-radius: 50%; background: rgba(163,121,41,0.2); }
    .circle-1 { width: 200px; height: 200px; top: -50px; right: -50px; animation: float 6s ease-in-out infinite; }
    .circle-2 { width: 150px; height: 150px; top: 50px; right: 100px; animation: float 8s ease-in-out infinite 1s; }
    .circle-3 { width: 100px; height: 100px; bottom: -30px; right: 50px; animation: float 7s ease-in-out infinite 2s; }
    .sales-dashboard { padding: 0; }
    .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .metric-card { background: white; border-radius: 12px; padding: 28px 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; animation: fadeInUp 0.6s ease-out both; position: relative; overflow: hidden; min-height: 130px; }
    .metric-card::before { content: ''; position: absolute; top: 0; left: 0; width: 5px; height: 100%; transition: width 0.3s; }
    .metric-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
    .metric-card:hover::before { width: 100%; opacity: 0.05; }
    .card-blue::before { background: #1e4575; }
    .card-gold::before { background: #A37929; }
    .metric-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .card-blue .metric-icon { background: linear-gradient(135deg, #1e4575, #2563eb); }
    .card-gold .metric-icon { background: linear-gradient(135deg, #A37929, #d4a03a); }
    .metric-icon svg { width: 30px; height: 30px; color: white; }
    .metric-content { flex: 1; min-width: 0; overflow: hidden; }
    .metric-label { font-size: 13px; color: #6b7280; font-weight: 500; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .metric-value { font-size: clamp(18px, 2vw, 28px); font-weight: 700; color: #111827; line-height: 1.2; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .metric-subtitle { font-size: 12px; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dashboard-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); animation: fadeInUp 0.6s ease-out both; }
    .card-header-modern { padding: 24px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 16px; }
    .header-icon { width: 48px; height: 48px; border-radius: 10px; background: linear-gradient(135deg, #1e4575, #2563eb); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .header-icon svg { width: 24px; height: 24px; color: white; }
    .card-title-modern { font-size: 18px; font-weight: 700; color: #111827; margin: 0; }
    .card-subtitle-modern { font-size: 13px; color: #6b7280; margin: 4px 0 0 0; }
    .card-body-modern { padding: 24px; }
    .agent-list-modern { display: flex; flex-direction: column; gap: 12px; }
    .agent-item-modern { display: flex; justify-content: space-between; align-items: center; padding: 16px; border-radius: 8px; background: #f9fafb; transition: all 0.3s; }
    .agent-item-modern:hover { background: linear-gradient(135deg, #f0f4f8, #e5e7eb); transform: translateX(-4px); }
    .agent-info { display: flex; align-items: center; gap: 16px; }
    .agent-rank { width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #A37929, #d4a03a); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; }
    .agent-details { display: flex; flex-direction: column; gap: 4px; }
    .agent-name-modern { font-weight: 600; color: #374151; font-size: 14px; }
    .agent-units { font-size: 12px; color: #6b7280; }
    .agent-sales-modern { font-weight: 700; color: #111827; font-size: 15px; }
    .analytics-card { background: white; border-radius: 14px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; }
    .analytics-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 22px; }
    .analytics-title { margin: 0; color: #111827; font-size: 18px; font-weight: 700; }
    .analytics-subtitle { margin: 5px 0 0; color: #64748b; font-size: 13px; line-height: 1.5; }
    .analytics-period { flex-shrink: 0; padding: 7px 11px; border-radius: 8px; background: #f1f5f9; color: #475569; font-size: 11px; font-weight: 700; }
    .analytics-control-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 18px; align-items: end; margin-bottom: 16px; }
    .analytics-control-label { display: block; margin-bottom: 8px; color: #64748b; font-size: 11px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; }
    .team-filter { display: flex; flex-wrap: wrap; gap: 8px; }
    .team-filter-btn { padding: 7px 15px; border: 1px solid #cbd5e1; border-radius: 999px; background: white; color: #334155; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .2s ease; }
    .team-filter-btn:hover { border-color: #1e4575; color: #1e4575; }
    .team-filter-btn.is-active { border-color: #1e4575; background: #1e4575; color: white; box-shadow: 0 3px 8px rgba(30,69,117,.2); }
    .metric-toggle { display: inline-grid; grid-template-columns: repeat(4, auto); padding: 4px; border-radius: 10px; background: #f1f5f9; gap: 3px; }
    .metric-toggle-btn { padding: 7px 11px; border: 0; border-radius: 7px; background: transparent; color: #64748b; font-size: 12px; font-weight: 700; white-space: nowrap; cursor: pointer; transition: all .2s ease; }
    .metric-toggle-btn:hover { color: #1e4575; }
    .metric-toggle-btn.is-active { background: white; color: #1e4575; box-shadow: 0 1px 4px rgba(15,23,42,.12); }
    .metric-hint { min-height: 18px; margin: 0 0 16px; color: #64748b; font-size: 12px; }
    .analytics-summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 20px; }
    .analytics-summary-item { min-width: 0; padding: 14px 16px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; }
    .analytics-summary-label { color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; }
    .analytics-summary-value { margin-top: 5px; color: #0f172a; font-size: 18px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .analytics-summary-detail { margin-top: 3px; color: #94a3b8; font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .team-charts-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr); gap: 20px; align-items: stretch; }
    .analytics-chart-panel { min-width: 0; padding: 18px; border: 1px solid #e2e8f0; border-radius: 12px; background: white; }
    .analytics-chart-title { margin: 0; color: #334155; font-size: 13px; font-weight: 700; }
    .analytics-chart-subtitle { margin: 4px 0 12px; color: #94a3b8; font-size: 11px; }
    .analytics-chart-wrap { position: relative; width: 100%; height: 320px; }
    .analytics-chart-empty { display: none; height: 100%; align-items: center; justify-content: center; padding: 24px; color: #94a3b8; font-size: 12px; text-align: center; }
    @keyframes slideInLeft { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 1024px) {
        .analytics-control-row { grid-template-columns: 1fr; align-items: start; }
        .metric-toggle { width: 100%; grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .metric-toggle-btn { padding-left: 6px; padding-right: 6px; }
        .team-charts-grid { grid-template-columns: 1fr; }
        .analytics-chart-wrap { height: 300px; }
    }
    @media (max-width: 768px) {
        .metrics-grid { grid-template-columns: 1fr; }
        .welcome-title { font-size: 24px; }
        .analytics-card { padding: 18px; }
        .analytics-header { flex-direction: column; gap: 10px; }
        .analytics-period { align-self: flex-start; }
        .analytics-summary-grid { grid-template-columns: 1fr; }
        .team-filter { flex-wrap: nowrap; overflow-x: auto; padding-bottom: 5px; scrollbar-width: thin; }
        .team-filter-btn { flex: 0 0 auto; }
        .metric-toggle { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .analytics-chart-panel { padding: 14px; }
        .analytics-chart-wrap { height: 270px; }
    }
</style>

<div class="sales-dashboard">
    {{-- SHARED DATE FILTER --}}
    <form method="GET" action="{{ route('sales-marketing') }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:20px;background:white;padding:12px 18px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);">
        <svg fill="none" stroke="#1e4575" viewBox="0 0 24 24" style="width:16px;height:16px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span style="font-size:12px;font-weight:700;color:#1e4575;">Date Range</span>
        <label style="font-size:12px;color:#6b7280;font-weight:500">From</label>
        <input type="date" name="date_from" value="{{ $dateFrom }}" style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;outline:none">
        <label style="font-size:12px;color:#6b7280;font-weight:500">To</label>
        <input type="date" name="date_to" value="{{ $dateTo }}" style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;outline:none">
        <button type="submit" style="padding:7px 16px;background:#1e4575;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Filter</button>
        <span style="font-size:11px;color:#94a3b8;margin-left:4px;">Applies to all cards, charts &amp; top performers</span>
    </form>

    @if(!in_array('sales-marketing.cards', $hiddenSections))
    <div class="metrics-grid">
        <div class="metric-card card-gold">
            <div class="metric-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div class="metric-content">
                <div class="metric-label">Sold Units</div>
                <div class="metric-value">{{ number_format($units, 0) }}</div>
                <div style="font-size:11px;color:#64748b;margin-top:4px;line-height:1.7;">
                    Gross Sales: <strong>₱{{ number_format($grossSalesFromClient, 0) }}</strong><br>
                    <span>Pending Reservation: <strong>{{ $pendingReservation }}</strong></span><br>
                    <span style="color:#dc2626;">Cancelled Reservation: <strong>{{ $cancelledReservation }}</strong></span><br>
                    <span style="color:#1e4575;font-weight:700;">Total Reservation: {{ $totalReservation }}</span>
                </div>
            </div>
        </div>
        <div class="metric-card card-blue">
            <div class="metric-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="metric-content">
                <div class="metric-label">Total Net TCP</div>
                <div class="metric-value">₱{{ number_format($totalNetTcp, 2) }}</div>
                <div class="metric-subtitle">Year {{ \Carbon\Carbon::parse($dateFrom)->format('Y') }} &middot;</div>
            </div>
        </div>
        <div class="metric-card card-blue">
            <div class="metric-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <div class="metric-content">
                <div class="metric-label">Total Sold Units</div>
                <div class="metric-value">{{ number_format($totalRecords, 0) }}</div>
                <div class="metric-subtitle">Year {{ \Carbon\Carbon::parse($dateFrom)->format('Y') }} &middot;</div>
            </div>
        </div>
    </div>
    @endif

    @if(!in_array('sales-marketing.charts', $hiddenSections) && count($chartTeamData) > 0)
    <div class="analytics-card">
        <div class="analytics-header">
            <div>
                <h3 class="analytics-title">Team Performance Analytics</h3>
                <p class="analytics-subtitle">Compare Net TCP, released ArkCrest share, units and deals by agent. The doughnut chart shows each agent's contribution to the selected metric.</p>
            </div>
            <div class="analytics-period">
                {{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} – {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
            </div>
        </div>

        <div class="analytics-control-row">
            <div>
                <span class="analytics-control-label">View by team</span>
                <div class="team-filter" id="teamBtns" role="group" aria-label="Choose a sales team">
                    @foreach($chartTeamData as $i => $teamChart)
                    <button
                        type="button"
                        class="team-filter-btn {{ $i === 0 ? 'is-active' : '' }}"
                        data-team-index="{{ $i }}"
                        aria-pressed="{{ $i === 0 ? 'true' : 'false' }}">
                        {{ $teamChart['team'] }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div>
                <span class="analytics-control-label">Metric</span>
                <div class="metric-toggle" id="metricToggle" role="group" aria-label="Choose an analytics metric">
                    <button type="button" class="metric-toggle-btn is-active" data-metric="net_tcp" aria-pressed="true">Net TCP</button>
                    <button type="button" class="metric-toggle-btn" data-metric="arkcrest_share" aria-pressed="false">ArkCrest Share</button>
                    <button type="button" class="metric-toggle-btn" data-metric="units" aria-pressed="false">Units</button>
                    <button type="button" class="metric-toggle-btn" data-metric="deals" aria-pressed="false">Deals</button>
                </div>
            </div>
        </div>

        <p class="metric-hint" id="metricHint"></p>

        <div class="analytics-summary-grid">
            <div class="analytics-summary-item">
                <div class="analytics-summary-label" id="teamTotalLabel">Total Net TCP</div>
                <div class="analytics-summary-value" id="teamTotalValue">₱0</div>
                <div class="analytics-summary-detail" id="teamTotalDetail">All Agents</div>
            </div>
            <div class="analytics-summary-item">
                <div class="analytics-summary-label">Members with activity</div>
                <div class="analytics-summary-value" id="activeMembersValue">0</div>
                <div class="analytics-summary-detail" id="activeMembersDetail">0 total members</div>
            </div>
            <div class="analytics-summary-item">
                <div class="analytics-summary-label">Top contributor</div>
                <div class="analytics-summary-value" id="topContributorValue">—</div>
                <div class="analytics-summary-detail" id="topContributorDetail">No activity for this period</div>
            </div>
        </div>

        <div class="team-charts-grid">
            <div class="analytics-chart-panel">
                <h4 class="analytics-chart-title" id="memberBarLabel">Agent comparison</h4>
                <p class="analytics-chart-subtitle" id="memberBarSubtitle">Net TCP by agent</p>
                <div class="analytics-chart-wrap">
                    <canvas id="memberBarChart"></canvas>
                    <div class="analytics-chart-empty" id="memberBarEmpty">No data is available for the selected team and metric.</div>
                </div>
            </div>

            <div class="analytics-chart-panel">
                <h4 class="analytics-chart-title" id="memberPieLabel">Contribution breakdown</h4>
                <p class="analytics-chart-subtitle" id="memberPieSubtitle">Share of team Net TCP</p>
                <div class="analytics-chart-wrap">
                    <canvas id="memberPieChart"></canvas>
                    <div class="analytics-chart-empty" id="memberPieEmpty">No contribution data is available for this metric.</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    (function() {
        var teamData = @json($chartTeamData);
        var palette = ['#1e4575','#A37929','#2563eb','#16a34a','#dc2626','#7c3aed','#0891b2','#d97706','#db2777','#059669'];
        var metricConfig = {
            net_tcp: {
                label: 'Net TCP',
                totalLabel: 'Total Net TCP',
                hint: 'Net TCP is the total property selling price after discounts for non-cancelled records in the selected period.',
                currency: true
            },
            arkcrest_share: {
                label: 'ArkCrest Share',
                totalLabel: 'Total ArkCrest Share',
                hint: 'ArkCrest Share includes only released commission requests with a saved ArkCrest commission rate that are linked to these sales records.',
                currency: true
            },
            units: {
                label: 'Units Sold',
                totalLabel: 'Total Units Sold',
                hint: 'Units are based on the Number of Units field, with one unit counted when a block/lot is present and the field is blank.',
                currency: false
            },
            deals: {
                label: 'Deals',
                totalLabel: 'Total Deals',
                hint: 'Deals count non-cancelled client records with a downpayment date inside the selected period.',
                currency: false
            }
        };

        var activeTeamIndex = 0;
        var activeMetric = 'net_tcp';
        var memberBar = null;
        var memberPie = null;

        function formatMetric(value, metric) {
            var numericValue = Number(value || 0);
            var config = metricConfig[metric];

            if (config.currency) {
                return '₱' + numericValue.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            return numericValue.toLocaleString('en-PH', {
                maximumFractionDigits: 0
            });
        }

        function formatAxisValue(value, metric) {
            var numericValue = Number(value || 0);
            var prefix = metricConfig[metric].currency ? '₱' : '';
            var absoluteValue = Math.abs(numericValue);

            if (absoluteValue >= 1000000000) return prefix + (numericValue / 1000000000).toFixed(1) + 'B';
            if (absoluteValue >= 1000000) return prefix + (numericValue / 1000000).toFixed(1) + 'M';
            if (absoluteValue >= 1000) return prefix + (numericValue / 1000).toFixed(1) + 'K';

            return prefix + numericValue.toLocaleString('en-PH');
        }

        function setEmptyState(canvasId, emptyId, isEmpty) {
            var canvas = document.getElementById(canvasId);
            var empty = document.getElementById(emptyId);
            canvas.style.display = isEmpty ? 'none' : 'block';
            empty.style.display = isEmpty ? 'flex' : 'none';
        }

        function updateButtonStates() {
            document.querySelectorAll('.team-filter-btn').forEach(function(button) {
                var isActive = Number(button.getAttribute('data-team-index')) === activeTeamIndex;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            document.querySelectorAll('.metric-toggle-btn').forEach(function(button) {
                var isActive = button.getAttribute('data-metric') === activeMetric;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }

        function normalizeMetricValue(rawValue, metric) {
            var numericValue = Number(rawValue);

            if (!Number.isFinite(numericValue) || numericValue <= 0) {
                return 0;
            }

            // Currency contributions smaller than one cent are effectively zero.
            if (metricConfig[metric].currency) {
                return Math.round((numericValue + Number.EPSILON) * 100) / 100;
            }

            // Units and deals must be whole positive counts.
            return Math.round(numericValue);
        }

        function hasContribution(value, metric) {
            return metricConfig[metric].currency ? value >= 0.01 : value >= 1;
        }

        function renderAnalytics() {
            var team = teamData[activeTeamIndex] || { team: 'All Agents', members: [], totals: {} };
            var config = metricConfig[activeMetric];
            var allMembers = (team.members || []).slice();

            // Only contributors to the currently selected metric belong in either chart.
            // This filtering happens before labels, values and legend items are created.
            var members = allMembers.map(function(member) {
                var normalizedMember = Object.assign({}, member);
                normalizedMember._chartValue = normalizeMetricValue(member[activeMetric], activeMetric);
                return normalizedMember;
            }).filter(function(member) {
                return hasContribution(member._chartValue, activeMetric);
            }).sort(function(left, right) {
                var valueDifference = right._chartValue - left._chartValue;
                return valueDifference !== 0 ? valueDifference : String(left.name || '').localeCompare(String(right.name || ''));
            });

            var labels = members.map(function(member) { return member.name; });
            var values = members.map(function(member) { return member._chartValue; });
            var colors = members.map(function(_, index) { return palette[index % palette.length]; });
            var total = values.reduce(function(sum, value) { return sum + value; }, 0);
            // Activity follows the selected metric, so the count matches the charts.
            var membersWithActivity = members.length;
            var topContributor = members.length > 0 ? members[0] : null;
            var noMetricData = members.length === 0;

            updateButtonStates();

            document.getElementById('metricHint').textContent = config.hint;
            document.getElementById('teamTotalLabel').textContent = config.totalLabel;
            document.getElementById('teamTotalValue').textContent = formatMetric(total, activeMetric);
            document.getElementById('teamTotalDetail').textContent = team.team;
            document.getElementById('activeMembersValue').textContent = membersWithActivity.toLocaleString('en-PH');
            document.getElementById('activeMembersDetail').textContent = allMembers.length.toLocaleString('en-PH') + ' total member' + (allMembers.length === 1 ? '' : 's');
            document.getElementById('topContributorValue').textContent = topContributor ? topContributor.name : '—';
            document.getElementById('topContributorDetail').textContent = topContributor
                ? formatMetric(topContributor._chartValue, activeMetric) + ' ' + config.label
                : 'No activity for this period';
            document.getElementById('memberBarLabel').textContent = team.team + ' — Agent comparison';
            document.getElementById('memberBarSubtitle').textContent = config.label + ' by agent';
            document.getElementById('memberPieLabel').textContent = team.team + ' — Contribution breakdown';
            document.getElementById('memberPieSubtitle').textContent = 'Share of team ' + config.label;

            if (memberBar) memberBar.destroy();
            if (memberPie) memberPie.destroy();

            setEmptyState('memberBarChart', 'memberBarEmpty', noMetricData);
            setEmptyState('memberPieChart', 'memberPieEmpty', noMetricData);

            if (noMetricData) {
                return;
            }

            memberBar = new Chart(document.getElementById('memberBarChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: config.label,
                        data: values,
                        backgroundColor: colors,
                        borderRadius: 7,
                        maxBarThickness: 54
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' ' + config.label + ': ' + formatMetric(context.raw, activeMetric);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { autoSkip: false, maxRotation: 35, minRotation: 0, font: { size: 11 } }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                callback: function(value) { return formatAxisValue(value, activeMetric); }
                            },
                            grid: { color: 'rgba(148,163,184,.18)' }
                        }
                    }
                }
            });

            memberPie = new Chart(document.getElementById('memberPieChart'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    // Do not register click events, so legend entries stay permanent.
                    events: ['mousemove', 'mouseout'],
                    plugins: {
                        legend: {
                            position: 'bottom',
                            // Keep the legend informational only. Clicking a name must
                            // never hide/cross out a contributor or change the chart.
                            onClick: function() {},
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 12,
                                font: { size: 10 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var percentage = total > 0 ? ((Number(context.raw) / total) * 100).toFixed(1) : '0.0';
                                    return ' ' + context.label + ': ' + formatMetric(context.raw, activeMetric) + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        document.querySelectorAll('.team-filter-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                activeTeamIndex = Number(button.getAttribute('data-team-index'));
                renderAnalytics();
            });
        });

        document.querySelectorAll('.metric-toggle-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                activeMetric = button.getAttribute('data-metric');
                renderAnalytics();
            });
        });

        renderAnalytics();
    })();
    </script>
    @endif

    @if(!in_array('sales-marketing.top-performers', $hiddenSections))
    <div class="dashboard-card">
        <div class="card-header-modern">
            <div class="header-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div style="flex:1">
                <h3 class="card-title-modern">Top Performers</h3>
                <p class="card-subtitle-modern">Based on client database records &mdash; {{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} – {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
            </div>
        </div>
        <div class="card-body-modern">
            {{-- Always show flat top performers from client database --}}
            @if($topPerformers->isEmpty())
                <p style="color:#6b7280;text-align:center;padding:20px 0;">No sales data for this period.</p>
            @else
            @php $maxVal = $topPerformers->max('total_sales') ?: 1; @endphp
            <div style="display:flex;flex-direction:column;gap:14px">
                @foreach($topPerformers as $i => $agent)
                @php $pct = round(($agent->total_sales / $maxVal) * 100); @endphp
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <span style="width:24px;height:24px;background:#1e4575;color:white;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0">{{ $i+1 }}</span>
                            <span style="font-weight:600;color:#111827;font-size:14px">{{ $agent->agent_name }}</span>
                            <span style="font-size:11px;color:#6b7280">{{ $agent->units }} {{ $agent->units == 1 ? 'unit' : 'units' }}</span>
                            @if($agent->position)
                            <span style="font-size:10px;font-weight:700;background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:20px;">{{ $agent->position }}</span>
                            @endif
                        </div>
                        <div style="text-align:right">
                            <div style="font-weight:700;color:#1e4575;font-size:14px">₱{{ number_format($agent->total_sales, 2) }}</div>
                        </div>
                    </div>
                    <div style="background:#f3f4f6;border-radius:999px;height:10px;overflow:hidden">
                        <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#1e4575,#2563eb);border-radius:999px"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection