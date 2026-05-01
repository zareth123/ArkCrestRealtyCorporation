@extends('layouts.dashboard')

@section('content')
<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-content">
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
    @keyframes slideInLeft { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 768px) { .metrics-grid { grid-template-columns: 1fr; } .welcome-title { font-size: 24px; } }
</style>

<div class="sales-dashboard">
    {{-- SHARED DATE FILTER removed, now inside Top Performers --}}

    @if(!in_array('sales-marketing.cards', $hiddenSections))
    <div class="metrics-grid">
        <div class="metric-card card-gold">
            <div class="metric-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div class="metric-content">
                <div class="metric-label">Units</div>
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
                <div class="metric-subtitle">{{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} – {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</div>
            </div>
        </div>
        <div class="metric-card card-blue">
            <div class="metric-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <div class="metric-content">
                <div class="metric-label">Total Records</div>
                <div class="metric-value">{{ $totalRecords }}</div>
                <div class="metric-subtitle">{{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} – {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</div>
            </div>
        </div>
    </div>
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
                <p class="card-subtitle-modern">Based on client database records</p>
            </div>
            <form method="GET" action="{{ route('sales-marketing') }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <label style="font-size:12px;color:#6b7280;font-weight:500">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;outline:none">
                <label style="font-size:12px;color:#6b7280;font-weight:500">To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" style="padding:7px 10px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;color:#374151;outline:none">
                <button type="submit" style="padding:7px 14px;background:#1e4575;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Filter</button>
            </form>
        </div>
        <div class="card-body-modern">
            @if($teams->isEmpty())
                {{-- No teams: flat top performers --}}
                @if($topPerformers->isEmpty())
                    <p style="color:#6b7280;text-align:center;padding:20px 0;">No data for this period.</p>
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
                                <span style="font-size:11px;color:#6b7280">{{ $agent->deals }} {{ $agent->deals == 1 ? 'deal' : 'deals' }}</span>
                            </div>
                            <div style="text-align:right">
                                <div style="font-weight:700;color:#1e4575;font-size:14px">₱{{ number_format($agent->total_sales, 2) }}</div>
                                <div style="font-size:11px;color:#6b7280">Commission: ₱{{ number_format($agent->total_commission, 2) }}</div>
                            </div>
                        </div>
                        <div style="background:#f3f4f6;border-radius:999px;height:10px;overflow:hidden">
                            <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#1e4575,#2563eb);border-radius:999px"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            @else
                {{-- Teams configured: cards per team --}}
                @if($teamPerformance->every(fn($t) => $t['teamTotal'] == 0))
                    <p style="color:#6b7280;text-align:center;padding:20px 0;">No sales data for this period.</p>
                @else
                @php $maxTeam = $teamPerformance->max('teamTotal') ?: 1; @endphp
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:24px;">
                    @foreach($teamPerformance as $i => $tp)
                    @php
                        $quota    = $tp['quota'];
                        $quotaPct = $quota ? min(100, round(($tp['teamTotal'] / $quota->quota_amount) * 100)) : null;
                        $palettes = [
                            ['bg'=>'#0f2a4a','accent'=>'#3b82f6','light'=>'#dbeafe'],
                            ['bg'=>'#064e3b','accent'=>'#10b981','light'=>'#d1fae5'],
                            ['bg'=>'#3b0764','accent'=>'#a855f7','light'=>'#f3e8ff'],
                            ['bg'=>'#451a03','accent'=>'#f59e0b','light'=>'#fef3c7'],
                            ['bg'=>'#450a0a','accent'=>'#ef4444','light'=>'#fee2e2'],
                            ['bg'=>'#0c4a6e','accent'=>'#0ea5e9','light'=>'#e0f2fe'],
                        ];
                        $p = $palettes[$i % count($palettes)];
                    @endphp
                    <div style="border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.12);background:white;">
                        {{-- Header --}}
                        <div style="background:{{ $p['bg'] }};padding:22px 24px;position:relative;overflow:hidden;">
                            <div style="position:absolute;top:-30px;right:-30px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.04);"></div>
                            <div style="position:absolute;bottom:-40px;left:-20px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.03);"></div>
                            <div style="position:relative;">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
                                    <div>
                                        <div style="display:inline-block;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:6px;padding:3px 10px;font-size:10px;font-weight:700;color:rgba(255,255,255,.8);letter-spacing:1px;text-transform:uppercase;margin-bottom:8px;">
                                            {{ $tp['team']->team_name ?? 'Team' }}
                                        </div>
                                        <div style="font-size:20px;font-weight:800;color:white;line-height:1.1;letter-spacing:-.3px;">{{ $tp['team']->leader_name }}</div>
                                        @if($tp['team']->sales_manager && $tp['team']->sales_manager !== $tp['team']->leader_name)
                                        <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:3px;">{{ $tp['team']->sales_manager }}</div>
                                        @endif
                                    </div>
                                    <div style="text-align:right;flex-shrink:0;margin-left:12px;">
                                        <div style="font-size:26px;font-weight:900;color:white;line-height:1;">₱{{ number_format($tp['teamTotal'] / 1000000, 2) }}<span style="font-size:14px;font-weight:600;opacity:.7;">M</span></div>
                                        <div style="font-size:11px;color:rgba(255,255,255,.55);margin-top:2px;">{{ $tp['teamDeals'] }} {{ $tp['teamDeals'] == 1 ? 'deal' : 'deals' }}</div>
                                    </div>
                                </div>
                                <div style="font-size:10px;color:rgba(255,255,255,.4);margin-bottom:{{ $quota ? '12px' : '0' }};">
                                    {{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} — {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                                </div>
                                @if($quota)
                                <div>
                                    <div style="display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,.6);margin-bottom:5px;">
                                        <span>Quota ₱{{ number_format($quota->quota_amount / 1000000, 2) }}M</span>
                                        <span style="font-weight:700;color:{{ $quotaPct >= 100 ? '#86efac' : ($quotaPct >= 70 ? '#fde68a' : '#fca5a5') }}">{{ $quotaPct }}%</span>
                                    </div>
                                    <div style="background:rgba(255,255,255,.15);border-radius:999px;height:5px;overflow:hidden;">
                                        <div style="height:100%;width:{{ $quotaPct }}%;background:{{ $quotaPct >= 100 ? '#86efac' : ($quotaPct >= 70 ? '#fde68a' : '#fca5a5') }};border-radius:999px;"></div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        {{-- Stats --}}
                        <div style="display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid #f1f5f9;">
                            <div style="padding:14px 20px;border-right:1px solid #f1f5f9;">
                                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;">Net TCP</div>
                                <div style="font-size:16px;font-weight:800;color:#0f172a;">₱{{ number_format($tp['teamTotal'], 0) }}</div>
                            </div>
                            <div style="padding:14px 20px;">
                                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;">Commission</div>
                                <div style="font-size:16px;font-weight:800;color:#0f172a;">₱{{ number_format($tp['teamCommission'], 0) }}</div>
                            </div>
                        </div>
                        {{-- Agents --}}
                        <div style="padding:16px 20px;">
                            @if($tp['agentSales']->isNotEmpty())
                            <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.8px;margin-bottom:12px;">Agents</div>
                            @php $maxAgent = $tp['agentSales']->max('total_sales') ?: 1; @endphp
                            @foreach($tp['agentSales'] as $agent)
                            @php $aPct = round(($agent->total_sales / $maxAgent) * 100); @endphp
                            <div style="margin-bottom:10px;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                    <span style="font-size:12px;font-weight:600;color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:170px;">{{ $agent->agent_name }}</span>
                                    <span style="font-size:11px;font-weight:700;color:{{ $p['bg'] }};flex-shrink:0;margin-left:8px;">₱{{ number_format($agent->total_sales / 1000000, 2) }}M</span>
                                </div>
                                <div style="background:#f1f5f9;border-radius:999px;height:4px;overflow:hidden;">
                                    <div style="height:100%;width:{{ $aPct }}%;background:{{ $p['accent'] }};border-radius:999px;"></div>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div style="text-align:center;padding:12px 0;color:#94a3b8;font-size:12px;">No sales data for this period.</div>
                            @endif
                        </div>
                        {{-- Footer --}}
                        <div style="padding:10px 20px 14px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f1f5f9;">
                            <span style="font-size:11px;color:#94a3b8;">{{ $tp['team']->agents->count() + 1 }} members</span>
                            <span style="font-size:11px;font-weight:700;background:{{ $p['light'] }};color:{{ $p['bg'] }};padding:3px 10px;border-radius:20px;">Rank #{{ $i + 1 }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
