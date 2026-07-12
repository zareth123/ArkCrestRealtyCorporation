@extends('layouts.dashboard')
@section('title', 'Site Visit Database')
@section('content')
<style>
/* Header */
.svd-header{background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:28px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25)}
.svd-eyebrow{font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px}
.svd-title{font-size:24px;font-weight:700;color:white;margin:0 0 8px;position:relative;z-index:2}
.svd-subtitle{font-size:14px;color:rgba(255,255,255,.75);margin:0;position:relative;z-index:2;display:flex;align-items:center;gap:5px}
.svd-content{position:relative;z-index:2}
.svd-deco{position:absolute;top:0;right:0;width:300px;height:100%;pointer-events:none}
.svd-circle{position:absolute;border-radius:50%;background:rgba(255,255,255,.06)}
.svd-c1{width:220px;height:220px;top:-60px;right:-40px}
.svd-c2{width:140px;height:140px;top:40px;right:120px}
.svd-c3{width:90px;height:90px;bottom:-20px;right:60px}
/* Stats */
.svd-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:28px}
.stat-card{background:white;border-radius:16px;padding:24px;display:flex;align-items:center;gap:18px;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1.5px solid #f1f5f9;transition:transform .2s,box-shadow .2s}
.stat-card:hover{transform:translateY(-3px);box-shadow:0 8px 28px rgba(0,0,0,.1)}
.stat-icon{width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0}
.stat-card.pending .stat-icon{background:linear-gradient(135deg,#A37929,#d4a03a)}
.stat-card.confirmed .stat-icon{background:linear-gradient(135deg,#1e4575,#2563eb)}
.stat-card.cancelled .stat-icon{background:linear-gradient(135deg,#dc2626,#ef4444)}
.stat-val{font-size:28px;font-weight:800;color:#0f172a;line-height:1}
.stat-lbl{font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-top:4px}
.stat-card.pending{border-left:none}
.stat-card.confirmed{border-left:none}
.stat-card.cancelled{border-left:none}
.stat-card.stat-active{outline:2.5px solid transparent}
.stat-card.pending.stat-active{box-shadow:0 0 0 2.5px #d4a03a,0 8px 28px rgba(163,121,41,.2);}
.stat-card.confirmed.stat-active{box-shadow:0 0 0 2.5px #2563eb,0 8px 28px rgba(37,99,235,.2);}
.stat-card.cancelled.stat-active{box-shadow:0 0 0 2.5px #ef4444,0 8px 28px rgba(239,68,68,.2);}
@keyframes svdFadeIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
/* Section */
.section-block{background:white;border-radius:14px;box-shadow:0 1px 4px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);overflow:hidden;margin-bottom:24px;border:1px solid #f1f5f9}
.section-head{padding:18px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.section-head-left{display:flex;align-items:center;gap:10px}
.section-head h2{font-size:14px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-.2px}
.status-pill{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;letter-spacing:.3px}
.status-pill.pending{background:#fef3c7;color:#92400e}
.status-pill.confirmed{background:#dbeafe;color:#1e40af}
.status-pill.done{background:#dcfce7;color:#166534}
.status-pill.cancelled{background:#fee2e2;color:#991b1b}
.search-wrap{position:relative}
.search-wrap input{padding:8px 12px 8px 34px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;color:#111827;background:#f8fafc;width:220px;transition:all .2s}
.search-wrap input:focus{outline:none;border-color:#1e4575;background:white;box-shadow:0 0 0 3px rgba(30,69,117,.08)}
.search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#94a3b8}
/* Table */
.tbl-wrap{overflow-x:scroll;-webkit-overflow-scrolling:touch;}
.tbl-wrap::-webkit-scrollbar{height:8px;}
.tbl-wrap::-webkit-scrollbar-track{background:#f1f5f9;border-radius:4px;}
.tbl-wrap::-webkit-scrollbar-thumb{background:#94a3b8;border-radius:4px;}
.tbl-wrap::-webkit-scrollbar-thumb:hover{background:#475569;}
.svd-table{width:100%;border-collapse:collapse;min-width:900px}
.svd-table thead tr{background:linear-gradient(135deg,#0f2a4a,#1e4575)}
.svd-table thead th{padding:13px 18px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.8px;white-space:nowrap;border-right:1px solid rgba(255,255,255,.08)}
.svd-table thead th:last-child{border-right:none}
.svd-table tbody tr{border-bottom:1px solid #f1f5f9;transition:background .15s}
.svd-table tbody tr:hover{background:#f8fafc}
.svd-table tbody tr:last-child{border-bottom:none}
.svd-table td{padding:10px 14px;font-size:12px;color:#374151;vertical-align:middle;border-right:1px solid #f8fafc;white-space:nowrap;}
.svd-table td:last-child{border-right:none;white-space:normal;max-width:none}
.td-name{font-weight:700;color:#0f172a;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;max-width:130px}
.td-sub{font-size:11px;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;display:block;};display:block;max-width:130px}
.td-num{font-size:11px;color:#64748b;font-family:monospace;white-space:nowrap}
.td-muted{color:#94a3b8;font-size:12px}
/* Actions */
.actions{display:flex;gap:6px;align-items:center}
.btn-approve{padding:6px 14px;background:linear-gradient(135deg,#16a34a,#22c55e);color:white;border:none;border-radius:7px;font-size:11px;font-weight:700;cursor:pointer;transition:all .2s;letter-spacing:.3px;white-space:nowrap}
.btn-approve:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(22,163,74,.35)}
.btn-done{padding:6px 14px;background:linear-gradient(135deg,#16a34a,#22c55e);color:white;border:none;border-radius:7px;font-size:11px;font-weight:700;cursor:pointer;transition:all .2s;white-space:nowrap}
.btn-done:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(22,163,74,.35)}
.btn-reschedule{padding:6px 12px;background:linear-gradient(135deg,#d97706,#f59e0b);color:white;border:none;border-radius:7px;font-size:11px;font-weight:700;cursor:pointer;transition:all .2s;white-space:nowrap}
.btn-reschedule:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(217,119,6,.35)}
.btn-reserve{padding:6px 12px;background:linear-gradient(135deg,#7c3aed,#8b5cf6);color:white;border:none;border-radius:7px;font-size:11px;font-weight:700;cursor:pointer;transition:all .2s;white-space:nowrap;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
.btn-reserve:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(124,58,237,.35);color:white}
/* Reschedule modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center}
.modal-overlay.open{display:flex}
.modal-box{background:white;border-radius:16px;padding:28px;width:360px;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.modal-title{font-size:16px;font-weight:700;color:#0f172a;margin-bottom:16px}
.modal-field{margin-bottom:12px}
.modal-field label{display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.modal-field input{width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;color:#111827}
.modal-field input:focus{outline:none;border-color:#1e4575;box-shadow:0 0 0 3px rgba(30,69,117,.08)}
.modal-actions{display:flex;gap:10px;margin-top:16px}
.modal-save{flex:1;padding:10px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer}
.modal-cancel{padding:10px 16px;background:#f1f5f9;color:#374151;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer}
.btn-reject{padding:6px 14px;background:linear-gradient(135deg,#dc2626,#ef4444);color:white;border:none;border-radius:7px;font-size:11px;font-weight:700;cursor:pointer;transition:all .2s;letter-spacing:.3px;white-space:nowrap}
.btn-reject:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(220,38,38,.35)}
.btn-print{padding:6px 12px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:7px;font-size:11px;font-weight:700;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:5px;white-space:nowrap}
.btn-print:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(30,69,117,.35);}
.btn-delete{padding:6px 12px;background:linear-gradient(135deg,#7f1d1d,#b91c1c);color:white;border:none;border-radius:7px;font-size:11px;font-weight:700;cursor:pointer;transition:all .2s;white-space:nowrap}
.btn-delete:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(127,29,29,.4);}
.empty-state{text-align:center;padding:56px 20px;color:#94a3b8}
.empty-state svg{width:44px;height:44px;margin:0 auto 12px;display:block;opacity:.3}
.empty-state p{font-size:13px;font-weight:500}
.row-num{font-size:11px;color:#cbd5e1;font-weight:600;text-align:center;width:36px}

/* Quick Reference Tabs */
.qtab{display:flex;align-items:center;gap:7px;padding:11px 20px;background:none;border:none;font-size:13px;font-weight:600;color:#64748b;cursor:pointer;border-bottom:2px solid transparent;transition:all .2s;font-family:inherit;}
.qtab:hover{color:#1e4575;background:#f1f5f9;}
.qtab-active{color:#1e4575;border-bottom:2px solid #1e4575;background:white;}

/* Banner tabs */
.svd-tab{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:8px;color:rgba(255,255,255,.8);font-size:12px;font-weight:600;cursor:pointer;transition:all .2s;font-family:inherit;margin-bottom:10px;}
.svd-tab:hover{background:rgba(255,255,255,.22);color:white;}
.svd-tab-active{background:white;color:#1e4575;border-color:white;}
.svd-tab-badge{background:rgba(255,255,255,.25);color:white;border-radius:20px;padding:1px 7px;font-size:10px;font-weight:700;}
.svd-tab-active .svd-tab-badge{background:#1e4575;color:white;}
/* Responsive — this page's stat grid had no mobile rule at all,
   so 3 fixed equal columns forced the 3rd card off-screen. */
@media (max-width: 900px) {
    .svd-stats{grid-template-columns:repeat(2,1fr);gap:14px;}
}
@media (max-width: 560px) {
    .svd-stats{grid-template-columns:1fr;gap:12px;}
    .stat-card{padding:16px;gap:14px;}
    .stat-icon{width:46px;height:46px;font-size:20px;}
    .stat-val{font-size:22px;}
}
</style>

<div class="svd-header">
    <div class="svd-content">
        <div class="svd-eyebrow">Sales & Marketing</div>
        <h1 class="svd-title">Site Visit Database</h1>
        <p class="svd-subtitle">
            <svg style="width:15px;height:15px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Manage and track all property site visit schedules
        </p>
    </div>
    <div class="svd-deco">
        <div class="svd-circle svd-c1"></div>
        <div class="svd-circle svd-c2"></div>
        <div class="svd-circle svd-c3"></div>
    </div>
</div>

@php
    $pending       = $records->where('status','pending')->count();
    $confirmed     = $records->where('status','confirmed');
    $confirmedCount = $confirmed->count();
    $done          = $records->where('status','done')->count();
    $cancelled     = $records->where('status','cancelled')->count();
    $cancelled_count = $records->where('status','cancelled')->count();
@endphp
<div class="svd-stats">
    <div class="stat-card confirmed" onclick="toggleSection('section-confirmed')" data-section="section-confirmed" style="cursor:pointer;">
        <div class="stat-icon">
            <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:26px;height:26px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div><div class="stat-val">{{ $confirmedCount }}</div><div class="stat-lbl">Scheduled Tripping</div></div>
    </div>
    <div class="stat-card" style="border-left:none;" onclick="toggleSection('section-done')" data-section="section-done" style="cursor:pointer;">
        <div class="stat-icon" style="background:linear-gradient(135deg,#A37929,#d4a03a);">
            <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:26px;height:26px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div><div class="stat-val">{{ $done }}</div><div class="stat-lbl">Done Tripping</div></div>
    </div>
    <div class="stat-card cancelled" onclick="toggleSection('section-cancelled')" data-section="section-cancelled" style="cursor:pointer;">
        <div class="stat-icon">
            <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:26px;height:26px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div><div class="stat-val">{{ $cancelled_count }}</div><div class="stat-lbl">Cancelled Tripping</div></div>
    </div>
</div>

@if(session('success'))
    <div style="background:#f0fdf4;border-left:3px solid #22c55e;color:#16a34a;padding:10px 16px;border-radius:8px;font-size:13px;margin-bottom:16px;font-weight:500">
        &#10003; {{ session('success') }}
    </div>
@endif

<div class="section-block" id="section-confirmed" style="display:none;">
    <div class="section-head">
        <div class="section-head-left">
            <h2>Scheduled Tripping</h2>
            <span class="status-pill confirmed">{{ $confirmedCount }} record{{ $confirmedCount != 1 ? 's' : '' }}</span>
        </div>
    </div>
    <div class="tbl-wrap">
    <table class="svd-table">
        <thead><tr>
            <th style="width:40px;text-align:center">#</th>
            <th>Name of Client</th><th>Property</th><th>Company</th>
            <th>Name of Agent</th><th>Email</th><th>Mobile Number</th><th>Address</th>
            <th>Tripping Date</th><th>Tripping Time</th><th>Mode of Visit</th><th>Date Submitted</th><th>Actions</th>
        </tr></thead>
        <tbody>
        @foreach($confirmed->values() as $i => $r)
        <tr id="trip-{{ $r->id }}" data-id="{{ $r->id }}">
            <td class="row-num">{{ $i + 1 }}</td>
            <td><div class="td-name">{{ $r->client_name }}</div></td>
            <td><div class="td-name" style="font-size:12px">{{ $r->property_name ?? '—' }}</div></td>
            <td><div class="td-sub">{{ $r->company_name ?: '—' }}</div></td>
            <td><div class="td-name" style="font-size:12px">{{ $r->agent_name ?? '—' }}</div></td>
            <td><div class="td-muted">{{ $r->client_email ?: '—' }}</div></td>
            <td>
                <span data-code="{{ $r->client_phone_code ?? '+63' }}"></span>
                <span class="td-num">{{ $r->client_phone ? ($r->client_phone_code ?? '+63') . ' ' . ltrim($r->client_phone, '0') : '—' }}</span>
            </td>
            <td><div class="td-sub" style="font-size:12px;white-space:normal;min-width:140px;word-break:break-word;">{{ $r->client_address ?: '—' }}</div></td>
            <td><div class="td-name" style="font-size:12px">{{ $r->tripping_date ? $r->tripping_date->format('M j, Y') : '—' }}</div></td>
            <td><div class="td-sub">{{ $r->tripping_time ? \Carbon\Carbon::parse($r->tripping_time)->format('g:i A') : '—' }}</div></td>
            <td><div class="td-sub">{{ $r->tripping_type ?? '—' }}</div></td>
            <td><div class="td-sub" style="white-space:nowrap;">{{ $r->created_at ? $r->created_at->format('M j, Y g:i A') : '—' }}</div></td>
            <td>
                <div class="actions">
                    {{-- Reschedule --}}
                    <button class="btn-reschedule" onclick="openReschedule({{ $r->id }}, '{{ $r->tripping_date?->format('Y-m-d') }}', '{{ $r->tripping_time ?? '' }}')">
                        &#128197; Reschedule
                    </button>
                    {{-- Done --}}
                    <form method="POST" action="{{ route('site-visit-database.done', $r->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-done">&#10003; Done</button>
                    </form>
                    {{-- Reserve --}}
                    @php
                        $reserveParams = http_build_query([
                            'prefill_client'    => $r->client_name ?? '',
                            'prefill_project'   => $r->property_name ?? '',
                            'prefill_agent'     => $r->agent_name ?? '',
                            'prefill_date'      => $r->tripping_date ? $r->tripping_date->format('Y-m-d') : '',
                            'prefill_developer' => $r->company_name ?? '',
                        ]);
                    @endphp
                    <a href="{{ route('site-visit-database.reserve', $r->id) }}" class="btn-reserve">
                        &#128203; Reserve
                    </a>
                    {{-- Cancel --}}
                    <form method="POST" action="{{ route('site-visit-database.cancel', $r->id) }}" onsubmit="return confirm('Cancel this tripping?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-reject">&#10005; Cancel</button>
                    </form>
                    {{-- Print --}}
                    <button class="btn-print" onclick="autosaveAndPrint(this)" type="button">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print
                    </button>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>

{{-- Other status groups --}}
@foreach([
    'done'      => 'Done Tripping',
    'cancelled' => 'Cancelled Tripping',
] as $status => $label)
@php $grp = $records->where('status', $status); @endphp
@if($status === 'done' && $grp->isEmpty())
    {{-- skip done if empty --}}
@else
<div class="section-block" id="section-{{ $status }}" style="display:none;">
    <div class="section-head">
        <div class="section-head-left">
            <h2>{{ $label }}</h2>
            <span class="status-pill {{ $status }}">{{ $grp->count() }} record{{ $grp->count() != 1 ? 's' : '' }}</span>
        </div>
    </div>
    @if($grp->isEmpty())
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p>No {{ strtolower($label) }} at the moment.</p>
        </div>
    @else
    <div class="tbl-wrap">
    <table class="svd-table">
        <thead><tr>
            <th style="width:40px;text-align:center">#</th>
            <th>Name of Client</th><th>Property</th><th>Company</th>
            <th>Name of Agent</th><th>Email</th><th>Mobile Number</th><th>Address</th>
            <th>Tripping Date</th><th>Tripping Time</th><th>Mode of Visit</th>
            <th>Date Submitted</th><th>Actions</th>
        </tr></thead>
        <tbody>
        @foreach($grp->values() as $i => $r)
        <tr id="trip-{{ $r->id }}" data-id="{{ $r->id }}">
            <td class="row-num">{{ $i + 1 }}</td>
            <td><div class="td-name">{{ $r->client_name }}</div></td>
            <td><div class="td-name" style="font-size:12px">{{ $r->property_name ?? '—' }}</div></td>
            <td><div class="td-sub">{{ $r->company_name ?: '—' }}</div></td>
            <td>{{ $r->agent_name ?? '—' }}</td>
            <td><div class="td-muted">{{ $r->client_email ?: '—' }}</div></td>
            <td>
                <span data-code="{{ $r->client_phone_code ?? '+63' }}"></span>
                <span class="td-num">{{ $r->client_phone ? ($r->client_phone_code ?? '+63') . ' ' . ltrim($r->client_phone, '0') : '—' }}</span>
            </td>
            <td><div class="td-sub" style="font-size:12px;white-space:normal;min-width:140px;word-break:break-word;">{{ $r->client_address ?: '—' }}</div></td>
            <td>{{ $r->tripping_date ? $r->tripping_date->format('M j, Y') : '—' }}</td>
            <td><div class="td-sub">{{ $r->tripping_time ? \Carbon\Carbon::parse($r->tripping_time)->format('g:i A') : '—' }}</div></td>
            <td><div class="td-sub">{{ $r->tripping_type ?? '—' }}</div></td>
            <td><div class="td-sub" style="white-space:nowrap;">{{ $r->created_at ? $r->created_at->format('M j, Y g:i A') : '—' }}</div></td>
            <td>
                <div class="actions">
                    @if($status === 'done')
                    {{-- Reserve --}}
                    <a href="{{ route('site-visit-database.reserve', $r->id) }}" class="btn-reserve">
                        &#128203; Reserve
                    </a>
                    {{-- Cancel --}}
                    <form method="POST" action="{{ route('site-visit-database.cancel', $r->id) }}" onsubmit="return confirm('Cancel this record?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-reject">&#10005; Cancel</button>
                    </form>
                    @endif
                    {{-- Delete --}}
                    <form method="POST" action="{{ route('site-visit-database.destroy', $r->id) }}" onsubmit="return confirm('Delete this record?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-delete">Delete</button>
                    </form>
                    {{-- Print --}}
                    <button class="btn-print" onclick="autosaveAndPrint(this)" type="button">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print
                    </button>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
@endif
@endforeach

</div>

        
{{-- Reschedule Modal --}}
<div class="modal-overlay" id="rescheduleModal">
    <div class="modal-box">
        <div class="modal-title">&#128197; Reschedule Visit</div>
        <form method="POST" id="rescheduleForm">
            @csrf @method('PATCH')
            <div class="modal-field">
                <label>New Visit Date</label>
                <input type="date" name="tripping_date" id="rescheduleDate" required min="{{ date('Y-m-d') }}">
            </div>
            <div class="modal-field">
                <label>New Visit Time</label>
                <input type="time" name="tripping_time" id="rescheduleTime">
            </div>
            <div class="modal-actions">
                <button type="button" class="modal-cancel" onclick="closeReschedule()">Cancel</button>
                <button type="submit" class="modal-save">Save Reschedule</button>
            </div>
        </form>
    </div>
</div>

<script>
var _svLogo = "{{ asset('images/ArkCrest_Logo.png') }}";

function openReschedule(id, date, time) {
    document.getElementById('rescheduleForm').action = '/site-visit-database/' + id + '/reschedule';
    document.getElementById('rescheduleDate').value = date || '';
    document.getElementById('rescheduleTime').value = time || '';
    document.getElementById('rescheduleModal').classList.add('open');
}
function closeReschedule() {
    document.getElementById('rescheduleModal').classList.remove('open');
}
document.getElementById('rescheduleModal').addEventListener('click', function(e) {
    if (e.target === this) closeReschedule();
});

function showMainTab(id) {
    // All possible panels
    var allPanels = ['section-confirmed','section-cancelled','qpanel-clients','qpanel-properties','qpanel-transactions'];
    allPanels.forEach(function(pid) {
        var el = document.getElementById(pid);
        if (el) el.style.display = 'none';
        var btn = document.getElementById('mtab-' + pid);
        if (btn) btn.classList.remove('svd-tab-active');
    });
    var target = document.getElementById(id);
    if (target) { target.style.display = 'block'; target.style.animation = 'svdFadeIn .2s ease'; }
    var activeBtn = document.getElementById('mtab-' + id);
    if (activeBtn) activeBtn.classList.add('svd-tab-active');
}

function showQuickTab(name) {
    ['clients','properties','transactions'].forEach(function(t) {
        document.getElementById('qpanel-' + t).style.display = t === name ? 'block' : 'none';
        var btn = document.getElementById('qtab-' + t);
        if (btn) { btn.classList.toggle('qtab-active', t === name); }
    });
}

function filterTable(tableId, q) {
    q = q.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(function(row) {
        row.style.display = !q || row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

function toggleSection(id) {
    var allSections = ['section-confirmed','section-cancelled','section-done','section-rejected'];
    var el = document.getElementById(id);
    if (!el) return;
    var isAlreadyOpen = el.style.display === 'block';

    allSections.forEach(function(sid) {
        var s = document.getElementById(sid);
        if (s) s.style.display = 'none';
    });
    document.querySelectorAll('.stat-card').forEach(function(c) { c.classList.remove('stat-active'); });

    if (!isAlreadyOpen) {
        el.style.display = 'block';
        el.style.animation = 'svdFadeIn .25s ease';
        var activeCard = document.querySelector('[data-section="' + id + '"]');
        if (activeCard) activeCard.classList.add('stat-active');
    }
}

// Mark pending as active by default
document.addEventListener('DOMContentLoaded', function() {
    toggleSection('section-confirmed');
});

/* ---------- Print a single tripping row as a formatted slip ---------- */
// Reads the row's cells straight from the DOM (no extra request needed),
// builds a clean printable slip in a new tab, and triggers the print dialog.
// Works for any Print button inside a table row, regardless of which
// status section (confirmed / done / cancelled) it lives in.
function autosaveAndPrint(btn) {
    var row = btn.closest('tr');
    if (!row) return;
    var cells = row.querySelectorAll('td');
    var get = function (i) {
        if (!cells[i]) return '—';
        var text = cells[i].innerText.trim();
        return text || '—';
    };

    var rows = [
        ['Name of Client', get(1)],
        ['Property', get(2)],
        ['Company', get(3)],
        ['Name of Agent', get(4)],
        ['Email', get(5)],
        ['Mobile Number', get(6)],
        ['Address', get(7)],
        ['Tripping Date', get(8)],
        ['Tripping Time', get(9)],
        ['Mode of Visit', get(10)],
        ['Date Submitted', get(11)]
    ];

    // NOTE: closing tags are deliberately built with string concatenation
    // (e.g. '<' + '/tr>') rather than typed literally as '</tr>'. Writing
    // them out in full inside this inline <script> block causes the browser
    // to treat the sequence as the end of the *enclosing* script tag,
    // dumping the remaining JS as literal text on the page. Splitting the
    // sequence avoids that entirely. Same trick already used elsewhere in
    // this codebase (see the HR form's print function).
    var bodyRows = rows.map(function (pair) {
        return '<tr><td class="lbl">' + pair[0] + '<' + '/td><td>' + pair[1] + '<' + '/td><' + '/tr>';
    }).join('');

    // Styled to match the branded look of the Site Visit Form on the
    // Forms page: logo + underlined black title, blue subtitle, and a
    // bold-label / black-border info table.
    var printHtml = '<html><head><title>Site Visit Slip<' + '/title><style>'
        + '@page{size:letter;margin:.6in}'
        + 'body{font-family:Arial,sans-serif;color:#000;margin:0;padding:0}'
        + '.hdr{display:flex;align-items:center;justify-content:center;gap:16px;margin-bottom:18px}'
        + '.hdr img{width:70px;height:70px;object-fit:contain;flex-shrink:0}'
        + '.hdr .titles{text-align:center}'
        + '.hdr .company{font-size:22px;font-weight:700;text-decoration:underline;color:#000;letter-spacing:.3px}'
        + '.hdr .subtitle{font-size:20px;font-weight:700;color:#2563eb;margin-top:8px;letter-spacing:.3px}'
        + 'table{width:100%;border-collapse:collapse;font-size:13px}'
        + 'td{border:1px solid #000;padding:7px 10px;vertical-align:top}'
        + 'td.lbl{font-weight:700;background:#fafafa;width:180px;white-space:nowrap}'
        + '<' + '/style><' + '/head><body>'
        + '<div class="hdr">'
        + '<img src="' + _svLogo + '">'
        + '<div class="titles">'
        + '<div class="company">ARKCREST REALTY CORPORATION<' + '/div>'
        + '<div class="subtitle">SITE VISIT TRIPPING SLIP<' + '/div>'
        + '<' + '/div>'
        + '<' + '/div>'
        + '<table>' + bodyRows + '<' + '/table>'
        + '<' + '/body><' + '/html>';

    var win = window.open('', '_blank');
    if (!win) { return; } // popup blocked
    win.document.write(printHtml);
    win.document.close();
    win.focus();
    setTimeout(function () { win.print(); }, 300);
}

// ── Auto-poll for new pending trippings every 10 seconds ──
var _knownPendingIds = new Set(
    Array.from(document.querySelectorAll('#section-pending table tbody tr[data-id]'))
        .map(function(r) { return r.getAttribute('data-id'); })
);

// Tag existing rows with data-id
document.querySelectorAll('#section-pending table tbody tr').forEach(function(tr, i) {
    // rows don't have data-id yet — we'll rely on count comparison
});

var _pendingCount = {{ $records->where('status','pending')->count() }};

function pollPending() {
    fetch('/api/site-visit-database/pending', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        // Always update stat card count and section badge
        var statVal = document.querySelector('.stat-card.pending .stat-val');
        if (statVal) statVal.textContent = data.length;
        var sectionBadge = document.querySelector('#section-pending .status-pill.pending');
        if (sectionBadge) sectionBadge.textContent = data.length + ' record' + (data.length !== 1 ? 's' : '');

        var tbody = document.querySelector('#section-pending table tbody');
        if (!tbody) return;

        var wasNew = data.length > _pendingCount;
        _pendingCount = data.length;
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:32px;color:#94a3b8;">No pending site visits at the moment.</td></tr>';
            return;
        }

        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        tbody.innerHTML = data.map(function(r, i) {
            return '<tr>' +
                '<td class="row-num">' + (i+1) + '</td>' +
                '<td><div class="td-name">' + r.client_name + '</div></td>' +
                '<td><div class="td-name" style="font-size:12px">' + r.property_name + '</div></td>' +
                '<td><div class="td-sub">' + r.company_name + '</div></td>' +
                '<td><div class="td-name" style="font-size:12px">' + r.agent_name + '</div></td>' +
                '<td><div class="td-muted">' + r.client_email + '</div></td>' +
                '<td><span class="td-num">' + r.client_phone + '</span></td>' +
                '<td><div class="td-name" style="font-size:12px">' + r.tripping_date + '</div></td>' +
                '<td><div class="td-sub">' + r.tripping_time + '</div></td>' +
                '<td><div class="actions">' +
                    '<form method="POST" action="' + r.approve_url + '" style="display:inline"><input type="hidden" name="_token" value="' + csrf + '"><input type="hidden" name="_method" value="PATCH"><button type="submit" class="btn-approve">&#10003; Approve</button></form>' +
                    '<form method="POST" action="' + r.reject_url + '" style="display:inline" onsubmit="return confirm(\'Reject this tripping request?\')"><input type="hidden" name="_token" value="' + csrf + '"><input type="hidden" name="_method" value="PATCH"><button type="submit" class="btn-reject">&#10005; Reject</button></form>' +
                '</div></td>' +
            '</tr>';
        }).join('');

        // If new record added — make sure section is visible and flash it
        if (wasNew) {
            var sec = document.getElementById('section-pending');
            if (sec) {
                // Show section if hidden
                if (sec.style.display === 'none' || sec.style.display === '') {
                    sec.style.display = 'block';
                    toggleSection('section-confirmed');
                }
                sec.style.transition = 'box-shadow .3s';
                sec.style.boxShadow = '0 0 0 2.5px #d4a03a, 0 8px 28px rgba(163,121,41,.2)';
                setTimeout(function() { sec.style.boxShadow = ''; }, 2500);
                sec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    })
    .catch(function() {});
}

setInterval(pollPending, 5000);

// Flag images on phone cells
var CODE_TO_ISO = {'+63':'ph','+1':'us','+44':'gb','+61':'au','+65':'sg','+81':'jp','+82':'kr','+86':'cn','+852':'hk','+971':'ae','+966':'sa','+91':'in','+60':'my','+62':'id','+66':'th','+64':'nz','+49':'de','+33':'fr','+39':'it'};
document.querySelectorAll('[data-code]').forEach(function(el) {
    var code = el.getAttribute('data-code');
    var iso = CODE_TO_ISO[code];
    if (iso) {
        var img = document.createElement('img');
        img.src = 'https://flagcdn.com/w20/' + iso + '.png';
        img.style.cssText = 'width:16px;height:11px;border-radius:2px;vertical-align:middle;margin-right:4px;box-shadow:0 1px 2px rgba(0,0,0,.15)';
        el.parentNode.insertBefore(img, el);
    }
    el.remove();
});
</script>
@endsection