@extends('layouts.dashboard')
@section('title', 'ARC Sales')
@section('content')
<style>
.arc-wrap{padding:24px 30px}
.arc-header{background:linear-gradient(135deg,#1e4575 0%,#2563eb 100%);border-radius:16px;padding:28px 32px;margin-bottom:24px;color:white}
.arc-header h1{font-size:24px;font-weight:700;margin:0 0 4px}
.arc-header p{font-size:13px;color:rgba(255,255,255,.7);margin:0}
.arc-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
.arc-card{background:white;border-radius:12px;padding:20px;border:1px solid #e8ecf0;box-shadow:0 1px 4px rgba(0,0,0,.05)}
.arc-card-label{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px}
.arc-card-value{font-size:22px;font-weight:800;color:#0f172a}
.arc-table-wrap{background:white;border-radius:12px;border:1px solid #e8ecf0;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05)}
.arc-table{width:100%;border-collapse:collapse}
.arc-table thead tr{background:linear-gradient(135deg,#0f2a4a,#1e4575)}
.arc-table thead th{padding:11px 14px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.7px;white-space:nowrap}
.arc-table tbody tr{border-bottom:1px solid #f1f5f9}
.arc-table tbody tr:hover{background:#f8fafc}
.arc-table td{padding:11px 14px;font-size:13px;color:#374151;vertical-align:middle}
.arc-pct-input{width:80px;padding:5px 8px;border:1.5px solid #e2e8f0;border-radius:6px;font-size:12px;text-align:center}
.arc-pct-input:focus{outline:none;border-color:#2563eb}
.arc-save-btn{padding:5px 12px;background:#2563eb;color:white;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer}
.arc-save-btn:hover{background:#1e4575}
</style>

<div class="arc-wrap">

    {{-- Header --}}
    <div class="arc-header">
        <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:6px;">Finance</div>
        <h1>ARC Sales</h1>
        <p>ArkCrest commission income from released agent commissions</p>
    </div>

    {{-- Period Filter --}}
    <form method="GET" style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <select name="month" style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;">
            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $m)
            <option value="{{ $i+1 }}" {{ $month == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
        </select>
        <select name="year" style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;">
            @foreach($years as $y)
            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <button type="submit" style="padding:8px 18px;background:#1e4575;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Filter</button>
    </form>

    {{-- Summary Cards --}}
    <div class="arc-cards">
        <div class="arc-card">
            <div class="arc-card-label">Released Commissions</div>
            <div class="arc-card-value">{{ $released->count() }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">transactions this month</div>
        </div>
        <div class="arc-card">
            <div class="arc-card-label">Total Released Amount</div>
            <div class="arc-card-value" style="color:#1e4575;">₱{{ number_format($totalReleasedCommission, 2) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">agent commissions released</div>
        </div>
        <div class="arc-card">
            <div class="arc-card-label">ARC Gross Sales</div>
            <div class="arc-card-value" style="color:#16a34a;" id="arcTotalDisplay">₱{{ number_format($totalArkcrestCommission, 2) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">ArkCrest commission income</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="arc-table-wrap">
        @if($released->isEmpty())
        <div style="padding:40px;text-align:center;color:#94a3b8;font-size:14px;">No released commissions for this period.</div>
        @else
        <div style="overflow-x:auto;">
        <table class="arc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date Released</th>
                    <th>Client</th>
                    <th>Project</th>
                    <th>Agent</th>
                    <th>Released Commission</th>
                    <th>ARC % </th>
                    <th>ARC Commission</th>
                </tr>
            </thead>
            <tbody>
            @foreach($released as $i => $r)
            @php $rate = $rates->get($r->id); @endphp
            <tr id="row-{{ $r->id }}">
                <td style="color:#cbd5e1;font-weight:600;">{{ $i + 1 }}</td>
                <td style="white-space:nowrap;color:#059669;font-weight:600;">{{ $r->date_released ? $r->date_released->format('M d, Y') : '—' }}</td>
                <td style="font-weight:600;color:#0f172a;">{{ $r->client_name ?? '—' }}</td>
                <td style="color:#64748b;">{{ $r->project_name ?? '—' }}</td>
                <td>{{ $r->agent_name ?? '—' }}</td>
                <td style="font-weight:600;color:#1e4575;">₱{{ number_format($r->commission ?? 0, 2) }}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <input type="number" class="arc-pct-input" id="pct-{{ $r->id }}"
                            value="{{ $rate ? $rate->arkcrest_percent : '' }}"
                            placeholder="0.00" step="0.01" min="0" max="100">
                        <span style="font-size:12px;color:#94a3b8;">%</span>
                        <button class="arc-save-btn" onclick="saveRate({{ $r->id }}, {{ $r->commission ?? 0 }})">Save</button>
                    </div>
                </td>
                <td style="font-weight:700;color:#16a34a;" id="arc-{{ $r->id }}">
                    {{ $rate ? '₱'.number_format($rate->arkcrest_commission, 2) : '—' }}
                </td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc;border-top:2px solid #e2e8f0;">
                    <td colspan="7" style="padding:12px 14px;font-size:13px;font-weight:700;color:#0f172a;text-align:right;">ARC Gross Sales Total:</td>
                    <td style="padding:12px 14px;font-size:14px;font-weight:800;color:#16a34a;" id="arcFooterTotal">₱{{ number_format($totalArkcrestCommission, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        </div>
        @endif
    </div>

</div>

<script>
var arcTotals = {};
@foreach($released as $r)
@php $rate = $rates->get($r->id); @endphp
arcTotals[{{ $r->id }}] = {{ $rate ? $rate->arkcrest_commission : 0 }};
@endforeach

function saveRate(id, releasedCommission) {
    const pct = parseFloat(document.getElementById('pct-' + id).value) || 0;
    fetch('/api/arkcrest-sales/' + id + '/rate', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body: JSON.stringify({arkcrest_percent: pct})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('arc-' + id).textContent = data.formatted;
            arcTotals[id] = data.arkcrest_commission;
            updateTotal();
        }
    });
}

function updateTotal() {
    const total = Object.values(arcTotals).reduce((a, b) => a + b, 0);
    const fmt = '₱' + total.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById('arcTotalDisplay').textContent = fmt;
    document.getElementById('arcFooterTotal').textContent = fmt;
}
</script>
@endsection
