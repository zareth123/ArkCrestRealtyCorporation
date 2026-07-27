@extends('layouts.academy')
@section('title', 'Practice History · ArkCrest Sales Academy')

@section('content')
<style>
.ph-page { display:flex;flex-direction:column;gap:16px; }
.ph-topbar {
    background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);
    border-radius:20px;padding:32px 40px;display:flex;align-items:center;justify-content:space-between;
    box-shadow:0 8px 32px rgba(30,69,117,.25);
}
.ph-title { font-size:22px;font-weight:700;color:white;margin:0 0 4px; }
.ph-sub { font-size:13px;color:rgba(255,255,255,.75);margin:0; }
.ph-back-btn { padding:8px 16px;background:rgba(255,255,255,.15);color:white;border:1.5px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600;text-decoration:none; }
.ph-back-btn:hover { background:rgba(255,255,255,.25); }

.ph-table-wrap { background:white;border-radius:14px;border:1px solid #e8ecf0;box-shadow:0 2px 12px rgba(0,0,0,.05);overflow:hidden; }
table.ph-table { width:100%;border-collapse:collapse; }
.ph-table thead tr { background:linear-gradient(135deg,#0f2a4a,#1e4575); }
.ph-table th { padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.6px; }
.ph-table td { padding:12px 18px;font-size:13px;color:#374151;border-bottom:1px solid #f1f5f9; }
.ph-table tr:last-child td { border-bottom:none; }
.ph-table tr.clickable { cursor:pointer; }
.ph-table tr.clickable:hover td { background:#f8faff; }

.ph-badge { padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;display:inline-block; }
.ph-badge-sold { background:#dcfce7;color:#166534; }
.ph-badge-notsold { background:#fee2e2;color:#991b1b; }
.ph-badge-abandoned { background:#f1f5f9;color:#475569; }
.ph-badge-progress { background:#fef3c7;color:#92400e; }
.ph-diff { font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b; }
.ph-score { font-weight:700;color:#1e4575; }
.ph-empty { text-align:center;color:#94a3b8;font-size:13px;padding:40px; }
</style>

<div class="ph-page">
    <div class="ph-topbar">
        <div>
            <h1 class="ph-title">Practice History</h1>
            <p class="ph-sub">Your past persuasion practice sessions and scores.</p>
        </div>
        <a href="{{ route('practice') }}" class="ph-back-btn">New Session</a>
    </div>

    <div class="ph-table-wrap">
        @if($sessions->isEmpty())
            <div class="ph-empty">No practice sessions yet — start one from the scenario picker.</div>
        @else
            <table class="ph-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Buyer</th>
                        <th>Difficulty</th>
                        <th>Status</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $s)
                        @php
                            $badgeClass = match($s->status) {
                                'SOLD' => 'ph-badge-sold',
                                'NOT_SOLD' => 'ph-badge-notsold',
                                'ABANDONED' => 'ph-badge-abandoned',
                                default => 'ph-badge-progress',
                            };
                            $statusLabel = ucfirst(strtolower(str_replace('_', ' ', $s->status)));
                        @endphp
                        <tr class="clickable" onclick="window.location='{{ route('practice.chat', $s) }}'">
                            <td>{{ $s->created_at->format('M d, Y g:ia') }}</td>
                            <td>{{ $s->scenario->buyer_name ?? '—' }} <span style="color:#94a3b8;">({{ $s->scenario->name ?? '—' }})</span></td>
                            <td><span class="ph-diff">{{ ucfirst(strtolower($s->difficulty)) }}</span></td>
                            <td><span class="ph-badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                            <td><span class="ph-score">{{ $s->overall_score !== null ? $s->overall_score.'/100' : '—' }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($sessions->hasPages())
        <div>{{ $sessions->links() }}</div>
    @endif
</div>
@endsection