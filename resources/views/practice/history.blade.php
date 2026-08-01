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

.ph-pagination .pagination {
    list-style:none;display:flex;gap:6px;padding:0;margin:16px 0 0;align-items:center;flex-wrap:wrap;
}
.ph-pagination .page-item .page-link {
    display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;
    border:1px solid #e2e8f0;border-radius:8px;background:white;color:#1e4575;font-size:13px;font-weight:600;
    text-decoration:none;line-height:1;
}
.ph-pagination .page-item .page-link:hover { background:#f8faff;border-color:#c7d7f0; }
.ph-pagination .page-item.active .page-link { background:#1e4575;border-color:#1e4575;color:white; }
.ph-pagination .page-item.disabled .page-link { color:#cbd5e1;cursor:not-allowed;background:#f8fafc; }

.ph-remarks-btn {
    border:1.5px solid #dbe4f0;background:#f8faff;color:#1e4575;font-size:11.5px;font-weight:700;
    padding:6px 12px;border-radius:8px;cursor:pointer;text-transform:uppercase;letter-spacing:.4px;
}
.ph-remarks-btn:hover { background:#eef3ff; }
.ph-remarks-btn:disabled { opacity:.4;cursor:not-allowed; }

/* Remarks modal — same look as the end-of-session scorecard popup */
.ph-score-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center; }
.ph-score-overlay.open { display:flex; }
.ph-score-box { background:white;border-radius:16px;width:480px;max-width:95vw;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2); }
.ph-score-hdr { padding:20px 24px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white; }
.ph-score-outcome { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;opacity:.85; }
.ph-score-overall { font-size:28px;font-weight:700;margin-top:4px; }
.ph-score-body { padding:20px 24px;display:flex;flex-direction:column;gap:14px; }
.ph-score-rubric { display:grid;grid-template-columns:1fr 1fr;gap:10px; }
.ph-score-metric { background:#f8fafc;border-radius:10px;padding:10px 12px;border:1px solid #f1f5f9; }
.ph-score-metric-lbl { font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px; }
.ph-score-metric-val { font-size:18px;font-weight:700;color:#1e4575; }
.ph-score-summary { font-size:13px;color:#374151;line-height:1.6; }
.ph-score-suggestions { list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px; }
.ph-score-suggestions li { font-size:12.5px;color:#374151;padding:8px 12px;background:#f8fafc;border-radius:8px;border-left:3px solid #2563eb; }
.ph-score-actions { padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end; }
.ph-score-close {
    padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;
    background:linear-gradient(135deg,#1e4575,#2563eb);color:white;
}

@media (max-width: 768px) {
    .ph-topbar { flex-direction:column;align-items:flex-start;gap:12px;padding:20px; }
    .ph-back-btn { width:100%;text-align:center;box-sizing:border-box; }
    .ph-table-wrap { overflow-x:auto !important; }
    table.ph-table { min-width:520px; }
}
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
                        <th>Remarks</th>
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
                            <td>
                                @if($s->scorecard)
                                    <button type="button" class="ph-remarks-btn"
                                        onclick="event.stopPropagation(); phShowRemarksById({{ $s->id }})">
                                        View Remarks
                                    </button>
                                @else
                                    <button type="button" class="ph-remarks-btn" disabled>No Remarks</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($sessions->hasPages())
        <div class="ph-pagination">{{ $sessions->links('pagination::bootstrap-4') }}</div>
    @endif
</div>

<script id="phScorecardsData" type="application/json">
{!! json_encode(
    $sessions->mapWithKeys(fn ($s) => [$s->id => ['status' => $s->status, 'scorecard' => $s->scorecard]]),
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) !!}
</script>

<!-- Remarks modal -->
<div class="ph-score-overlay" id="phRemarksOverlay" onclick="if(event.target===this) phCloseRemarks()">
    <div class="ph-score-box">
        <div class="ph-score-hdr">
            <div class="ph-score-outcome" id="phRemarksOutcome"></div>
            <div class="ph-score-overall" id="phRemarksOverall"></div>
        </div>
        <div class="ph-score-body">
            <div class="ph-score-rubric">
                <div class="ph-score-metric">
                    <div class="ph-score-metric-lbl">Rapport</div>
                    <div class="ph-score-metric-val" id="phRemarksRapport">—</div>
                </div>
                <div class="ph-score-metric">
                    <div class="ph-score-metric-lbl">Objection Handling</div>
                    <div class="ph-score-metric-val" id="phRemarksObjection">—</div>
                </div>
                <div class="ph-score-metric">
                    <div class="ph-score-metric-lbl">Product Knowledge</div>
                    <div class="ph-score-metric-val" id="phRemarksProduct">—</div>
                </div>
                <div class="ph-score-metric">
                    <div class="ph-score-metric-lbl">Closing Technique</div>
                    <div class="ph-score-metric-val" id="phRemarksClosing">—</div>
                </div>
            </div>
            <div class="ph-score-summary" id="phRemarksSummary"></div>
            <ul class="ph-score-suggestions" id="phRemarksSuggestions"></ul>
        </div>
        <div class="ph-score-actions">
            <button type="button" class="ph-score-close" onclick="phCloseRemarks()">Close</button>
        </div>
    </div>
</div>

<script>
var phScorecardsMap = JSON.parse(document.getElementById('phScorecardsData').textContent || '{}');

function phShowRemarksById(sessionId) {
    var entry = phScorecardsMap[sessionId] || {};
    var status = entry.status;
    var scorecard = entry.scorecard || {};
    var outcomeLabel = status === 'SOLD' ? 'Sold! 🎉' : (status === 'ABANDONED' ? 'Ended Early' : (status === 'IN_PROGRESS' ? 'In Progress' : 'Not Sold'));

    document.getElementById('phRemarksOutcome').textContent = outcomeLabel;
    document.getElementById('phRemarksOverall').textContent = (scorecard.overall_score ?? '—') + (scorecard.overall_score != null ? '/100' : '');
    document.getElementById('phRemarksRapport').textContent = scorecard.rapport ?? '—';
    document.getElementById('phRemarksObjection').textContent = scorecard.objection_handling ?? '—';
    document.getElementById('phRemarksProduct').textContent = scorecard.product_knowledge ?? '—';
    document.getElementById('phRemarksClosing').textContent = scorecard.closing_technique ?? '—';
    document.getElementById('phRemarksSummary').textContent = scorecard.summary || 'No summary available.';

    var list = document.getElementById('phRemarksSuggestions');
    var suggestions = scorecard.suggestions || [];
    if (suggestions.length) {
        list.innerHTML = '';
        suggestions.forEach(function (s) {
            var li = document.createElement('li');
            li.textContent = s;
            list.appendChild(li);
        });
    } else {
        list.innerHTML = '<li>No specific suggestions this time.</li>';
    }

    document.getElementById('phRemarksOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function phCloseRemarks() {
    document.getElementById('phRemarksOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') phCloseRemarks();
});
</script>
@endsection