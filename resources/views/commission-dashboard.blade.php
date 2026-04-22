@extends('layouts.dashboard')
@section('title', 'Commission Dashboard')
@section('content')
<style>
.cd-header{background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:28px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25)}
.cd-header h1{font-size:28px;font-weight:700;color:white;margin:0 0 8px;position:relative;z-index:2}
.cd-header p{font-size:14px;color:rgba(255,255,255,.75);margin:0;position:relative;z-index:2}
.cd-deco{position:absolute;top:-40px;right:-40px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.06)}
.cd-deco2{position:absolute;top:40px;right:120px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.04)}
.cd-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.cd-stat{background:white;border-radius:12px;padding:20px;border:1px solid #e8ecf0;box-shadow:0 1px 4px rgba(0,0,0,.05)}
.cd-stat-val{font-size:22px;font-weight:800;color:#0f172a;line-height:1}
.cd-stat-lbl{font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-top:4px}
.cd-stat-sub{font-size:12px;color:#64748b;margin-top:6px}
.cd-card{background:white;border-radius:12px;border:1px solid #e8ecf0;box-shadow:0 1px 4px rgba(0,0,0,.05);overflow:hidden;margin-bottom:20px}
.cd-card-hdr{padding:14px 18px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.cd-card-hdr h3{font-size:14px;font-weight:700;color:#0f172a;margin:0}
.cd-table{width:100%;border-collapse:collapse}
.cd-table thead tr{background:linear-gradient(135deg,#0f2a4a,#1e4575)}
.cd-table thead th{padding:11px 16px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.7px;white-space:nowrap}
.cd-table tbody tr{border-bottom:1px solid #f1f5f9;transition:background .15s}
.cd-table tbody tr:hover{background:#f8fafc}
.cd-table tbody tr:last-child{border-bottom:none}
.cd-table td{padding:11px 16px;font-size:13px;color:#374151;vertical-align:middle}
.cd-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700}
.cd-badge-released{background:#dcfce7;color:#166534}
.cd-badge-pending{background:#fef3c7;color:#92400e}
.cd-bar-wrap{background:#f1f5f9;border-radius:4px;height:6px;overflow:hidden;min-width:80px}
.cd-bar-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,#1e4575,#2563eb);transition:width .4s}
.cd-search{position:relative}
.cd-search input{padding:7px 12px 7px 32px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;color:#374151;background:#f8fafc;width:200px}
.cd-search input:focus{outline:none;border-color:#1e4575;background:white}
.cd-search svg{position:absolute;left:9px;top:50%;transform:translateY(-50%);width:13px;height:13px;color:#94a3b8}
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
use App\Models\CommissionRequestSales;

$all = CommissionRequestSales::all();
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
    <div class="cd-card-hdr">
        <h3>Per Agent Breakdown</h3>
        <div class="cd-search">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" placeholder="Search agent..." oninput="multiSearch(this.value, 'cdAgentTable')">
        </div>
    </div>
    <div style="overflow-x:auto;">
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
            <tr>
                <td style="color:#cbd5e1;font-weight:600;text-align:center;">{{ $i + 1 }}</td>
                <td style="font-weight:700;color:#0f172a;">{{ $a['agent'] }}</td>
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
    <div class="cd-card-hdr">
        <h3>Recent Transactions</h3>
        <div class="cd-search">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" placeholder="Search..." oninput="multiSearch(this.value, 'cdTxTable')">
        </div>
    </div>
    <div style="overflow-x:auto;">
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
            <tr>
                <td style="color:#cbd5e1;font-weight:600;text-align:center;">{{ $i + 1 }}</td>
                <td style="font-weight:600;color:#0f172a;">{{ $tx->agent_name ?: '—' }}</td>
                <td>{{ $tx->client_name ?: '—' }}</td>
                <td style="color:#64748b;">{{ $tx->project_name ?: '—' }}</td>
                <td style="color:#1e4575;font-weight:600;">{{ $tx->net_tcp ? '₱'.number_format($tx->net_tcp, 2) : '—' }}</td>
                <td style="color:#16a34a;font-weight:600;">{{ $tx->commission ? '₱'.number_format($tx->commission, 2) : '—' }}</td>
                <td style="color:#64748b;">{{ $tx->date_requested ? $tx->date_requested->format('M d, Y') : '—' }}</td>
                <td>
                    <span class="cd-badge {{ $tx->status === 'Released' ? 'cd-badge-released' : 'cd-badge-pending' }}">{{ $tx->status ?: 'Pending' }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:40px;color:#94a3b8;">No transactions found.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
