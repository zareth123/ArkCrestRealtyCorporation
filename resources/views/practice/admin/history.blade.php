@extends('layouts.dashboard')
@section('title', 'Practice History · Admin')

@section('content')
<style>
.pah-page { display:flex;flex-direction:column;gap:16px; }
.pah-topbar {
    background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);
    border-radius:20px;padding:32px 40px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;
    box-shadow:0 8px 32px rgba(30,69,117,.25);
}
.pah-title { font-size:22px;font-weight:700;color:white;margin:0 0 4px; }
.pah-sub { font-size:13px;color:rgba(255,255,255,.75);margin:0; }
.pah-back-btn { padding:8px 16px;background:rgba(255,255,255,.15);color:white;border:1.5px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600;text-decoration:none; }
.pah-back-btn:hover { background:rgba(255,255,255,.25); }

.pah-alert { background:#dcfce7;color:#166534;padding:12px 18px;border-radius:10px;font-size:13px;font-weight:600; }

.pah-table-wrap { background:white;border-radius:14px;border:1px solid #e8ecf0;box-shadow:0 2px 12px rgba(0,0,0,.05);overflow:hidden; }
table.pah-table { width:100%;border-collapse:collapse; }
.pah-table thead tr { background:linear-gradient(135deg,#0f2a4a,#1e4575); }
.pah-table th { padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.6px; }
.pah-table td { padding:12px 18px;font-size:13px;color:#374151;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
.pah-table tr:last-child td { border-bottom:none; }
.pah-table tr.clickable { cursor:pointer; }
.pah-table tr.clickable:hover td { background:#f8faff; }

/* Toned-down version of the site-wide gold hover/zoom effect — kept the
   look, just softened so it doesn't jitter or overpower this table.
   Gold matches the ArkCrest brand gold (#d4a855 / #f0c96a). */
.pah-table tbody tr:hover {
    transform:scaleY(1.008) !important;
    box-shadow:0 1px 4px rgba(212,168,85,.25) !important;
    background:#fdf3dc !important;
    position:relative !important;
    z-index:1;
}
.pah-table tbody tr:hover td {
    color:#a3781d !important;
    font-weight:500 !important;
}
.pah-table tbody tr:hover td.pah-agent { font-weight:700 !important;color:#8a6314 !important; }

.pah-badge { padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;display:inline-block; }
.pah-badge-sold { background:#dcfce7;color:#166534; }
.pah-badge-notsold { background:#fee2e2;color:#991b1b; }
.pah-badge-abandoned { background:#f1f5f9;color:#475569; }
.pah-badge-progress { background:#fef3c7;color:#92400e; }
.pah-diff { font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b; }
.pah-score { font-weight:700;color:#1e4575; }
.pah-empty { text-align:center;color:#94a3b8;font-size:13px;padding:40px; }
.pah-agent { font-weight:600;color:#1e293b; }

.pah-row-actions { display:flex;gap:8px;flex-wrap:wrap; }
.pah-icon-btn { border:1.5px solid #dbe4f0;background:#f8faff;color:#1e4575;font-size:11.5px;font-weight:700;padding:6px 12px;border-radius:8px;cursor:pointer;text-transform:uppercase;letter-spacing:.4px; }
.pah-icon-btn:hover { background:#eef3ff; }
.pah-icon-btn:disabled { opacity:.4;cursor:not-allowed; }
.pah-icon-btn.danger { color:#dc2626;border-color:#fecaca;background:white; }
.pah-icon-btn.danger:hover { background:#fef2f2; }

.pah-check-col { width:36px;text-align:center; }

/* Unified toolbar — Filter / Search / Delete Selected / count / Clear / Sort */
.pah-toolbar2 { display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:14px 18px;border-bottom:1px solid #f1f5f9;background:white; }

.pah-pill-btn { display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap; }
.pah-pill-btn svg { width:14px;height:14px; }

.pah-filter-btn { background:white;color:#1e4575;border:1.5px solid #1e4575; }
.pah-filter-btn:hover { background:#f0f5ff; }
.pah-filter-btn .chev { width:11px;height:11px;margin-left:2px;transition:transform .15s; }
.pah-filter-btn.open .chev { transform:rotate(180deg); }

.pah-sort-btn { background:linear-gradient(135deg,#0f2a4a,#1e4575);color:white;border:none; }
.pah-sort-btn:hover { filter:brightness(1.08); }

.pah-clear-btn { background:#eef1f5;color:#475569;border:1.5px solid #e2e8f0; }
.pah-clear-btn:hover { background:#e5e9ef; }

.pah-search-wrap { position:relative;flex:1;min-width:200px;max-width:320px; }
.pah-search-wrap svg { position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#94a3b8;pointer-events:none; }
.pah-search-input { width:100%;box-sizing:border-box;padding:9px 12px 9px 32px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;color:#1e293b; }
.pah-search-input:focus { outline:none;border-color:#2563eb; }

.pah-bulk-btn-delete2 { padding:9px 16px;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;border:none;background:#fecaca;color:#7f1d1d;white-space:nowrap; }
.pah-bulk-btn-delete2:hover:not(:disabled) { background:#fca5a5; }
.pah-bulk-btn-delete2:disabled { opacity:.6;cursor:not-allowed; }

.pah-search-count { font-size:12.5px;color:#94a3b8;font-weight:600;white-space:nowrap; }

/* Dropdown menus (Filter + Sort) */
.pah-dd-wrap { position:relative; }
.pah-dd-menu {
    display:none;position:absolute;top:calc(100% + 6px);left:0;min-width:210px;background:white;
    border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 12px 32px rgba(15,42,74,.16);z-index:40;
    padding:10px;
}
.pah-dd-menu.pah-dd-right { left:auto;right:0; }
.pah-dd-menu.open { display:block; }
.pah-dd-label { font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;padding:6px 8px 4px; }
.pah-dd-check { display:flex;align-items:center;gap:8px;padding:7px 8px;font-size:13px;color:#1e293b;border-radius:6px;cursor:pointer; }
.pah-dd-check:hover { background:#f8faff; }
.pah-dd-item { display:block;width:100%;text-align:left;padding:8px 10px;font-size:13px;color:#1e293b;background:none;border:none;border-radius:6px;cursor:pointer; }
.pah-dd-item:hover { background:#f8faff; }
.pah-dd-item.active { background:#eef3ff;color:#1e4575;font-weight:700; }
.pah-dd-divider { height:1px;background:#f1f5f9;margin:6px 2px; }

.pah-pagination .pagination {
    list-style:none;display:flex;gap:6px;padding:0;margin:16px 0 0;align-items:center;flex-wrap:wrap;
}
.pah-pagination .page-item .page-link {
    display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;
    border:1px solid #e2e8f0;border-radius:8px;background:white;color:#1e4575;font-size:13px;font-weight:600;
    text-decoration:none;line-height:1;
}
.pah-pagination .page-item .page-link:hover { background:#f8faff;border-color:#c7d7f0; }
.pah-pagination .page-item.active .page-link { background:#1e4575;border-color:#1e4575;color:white; }
.pah-pagination .page-item.disabled .page-link { color:#cbd5e1;cursor:not-allowed;background:#f8fafc; }

/* Remarks modal — same look as the end-of-session scorecard popup */
.pah-score-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center; }
.pah-score-overlay.open { display:flex; }
.pah-score-box { background:white;border-radius:16px;width:480px;max-width:95vw;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2); }
.pah-score-hdr { padding:20px 24px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white; }
.pah-score-outcome { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;opacity:.85; }
.pah-score-overall { font-size:28px;font-weight:700;margin-top:4px; }
.pah-score-agent { font-size:12px;color:rgba(255,255,255,.75);margin-top:6px; }
.pah-score-body { padding:20px 24px;display:flex;flex-direction:column;gap:14px; }
.pah-score-rubric { display:grid;grid-template-columns:1fr 1fr;gap:10px; }
.pah-score-metric { background:#f8fafc;border-radius:10px;padding:10px 12px;border:1px solid #f1f5f9; }
.pah-score-metric-lbl { font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px; }
.pah-score-metric-val { font-size:18px;font-weight:700;color:#1e4575; }
.pah-score-summary { font-size:13px;color:#374151;line-height:1.6; }
.pah-score-suggestions { list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px; }
.pah-score-suggestions li { font-size:12.5px;color:#374151;padding:8px 12px;background:#f8fafc;border-radius:8px;border-left:3px solid #2563eb; }
.pah-score-actions { padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end; }
.pah-score-close {
    padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;
    background:linear-gradient(135deg,#1e4575,#2563eb);color:white;
}

@media (max-width: 768px) {
    .pah-topbar { flex-direction:column;align-items:flex-start;gap:12px;padding:20px; }
    .pah-back-btn { width:100%;text-align:center;box-sizing:border-box; }
    .pah-table-wrap { overflow-x:auto !important; }
    table.pah-table { min-width:820px; }
}
</style>

<div class="pah-page">
    <div class="pah-topbar">
        <div>
            <h1 class="pah-title">Practice History</h1>
            <p class="pah-sub">Every agent's persuasion practice sessions, scores, and transcripts.</p>
        </div>
        <a href="{{ route('practice.admin') }}" class="pah-back-btn">Manage Scenarios</a>
    </div>



    <div class="pah-table-wrap">
        @if($sessions->isEmpty())
            <div class="pah-empty">No practice sessions yet.</div>
        @else
            <div class="pah-toolbar2">
                <div class="pah-dd-wrap">
                    <button type="button" class="pah-pill-btn pah-filter-btn" id="pahFilterBtn" onclick="pahToggleDropdown('pahFilterMenu', this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        Filter
                        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="pah-dd-menu" id="pahFilterMenu">
                        <div class="pah-dd-label">Difficulty</div>
                        <label class="pah-dd-check"><input type="checkbox" class="pah-filter-diff" value="easy" onchange="pahApplyFilters()"> Easy</label>
                        <label class="pah-dd-check"><input type="checkbox" class="pah-filter-diff" value="medium" onchange="pahApplyFilters()"> Medium</label>
                        <label class="pah-dd-check"><input type="checkbox" class="pah-filter-diff" value="hard" onchange="pahApplyFilters()"> Hard</label>
                        <div class="pah-dd-divider"></div>
                        <div class="pah-dd-label">Status</div>
                        <label class="pah-dd-check"><input type="checkbox" class="pah-filter-status" value="sold" onchange="pahApplyFilters()"> Sold</label>
                        <label class="pah-dd-check"><input type="checkbox" class="pah-filter-status" value="not_sold" onchange="pahApplyFilters()"> Not Sold</label>
                        <label class="pah-dd-check"><input type="checkbox" class="pah-filter-status" value="abandoned" onchange="pahApplyFilters()"> Abandoned</label>
                        <label class="pah-dd-check"><input type="checkbox" class="pah-filter-status" value="in_progress" onchange="pahApplyFilters()"> In Progress</label>
                    </div>
                </div>

                <div class="pah-search-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="pahSearchInput" class="pah-search-input" placeholder="Search request..." oninput="pahApplyFilters()">
                </div>

                <button type="button" id="pahBulkDeleteBtn" class="pah-bulk-btn-delete2" disabled onclick="pahBulkDelete()">Delete Selected (<span id="pahSelCount">0</span>)</button>

                <div class="pah-search-count"><span id="pahVisibleCount">{{ $sessions->count() }}</span> record(s) shown</div>

                <button type="button" class="pah-pill-btn pah-clear-btn" onclick="pahClearAll()">Clear</button>

                <div class="pah-dd-wrap" style="margin-left:auto;">
                    <button type="button" class="pah-pill-btn pah-sort-btn" onclick="pahToggleDropdown('pahSortMenu', this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h11M3 12h7M3 17h4"/><path d="M17 4v16m0 0l-3-3m3 3l3-3"/></svg>
                        Sort
                    </button>
                    <div class="pah-dd-menu pah-dd-right" id="pahSortMenu">
                        <button type="button" class="pah-dd-item" onclick="pahSortPreset('date', -1)">Date — Newest first</button>
                        <button type="button" class="pah-dd-item" onclick="pahSortPreset('date', 1)">Date — Oldest first</button>
                        <div class="pah-dd-divider"></div>
                        <button type="button" class="pah-dd-item" onclick="pahSortPreset('agent', 1)">Agent — A to Z</button>
                        <button type="button" class="pah-dd-item" onclick="pahSortPreset('agent', -1)">Agent — Z to A</button>
                        <div class="pah-dd-divider"></div>
                        <button type="button" class="pah-dd-item" onclick="pahSortPreset('score', -1)">Score — High to Low</button>
                        <button type="button" class="pah-dd-item" onclick="pahSortPreset('score', 1)">Score — Low to High</button>
                        <div class="pah-dd-divider"></div>
                        <button type="button" class="pah-dd-item" onclick="pahSortPreset('difficulty', -1)">Difficulty — Hard to Easy</button>
                        <button type="button" class="pah-dd-item" onclick="pahSortPreset('difficulty', 1)">Difficulty — Easy to Hard</button>
                    </div>
                </div>
            </div>
            <table class="pah-table">
                <thead>
                    <tr>
                        <th class="pah-check-col"><input type="checkbox" id="pahSelectAll" onchange="pahToggleSelectAll(this)"></th>
                        <th>Date</th>
                        <th>Agent</th>
                        <th>Buyer</th>
                        <th>Difficulty</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $s)
                        @php
                            $badgeClass = match($s->status) {
                                'SOLD' => 'pah-badge-sold',
                                'NOT_SOLD' => 'pah-badge-notsold',
                                'ABANDONED' => 'pah-badge-abandoned',
                                default => 'pah-badge-progress',
                            };
                            $statusLabel = ucfirst(strtolower(str_replace('_', ' ', $s->status)));
                            $diffRank = match(strtoupper($s->difficulty ?? '')) {
                                'EASY' => 1, 'MEDIUM' => 2, 'HARD' => 3, default => 0,
                            };
                        @endphp
                        <tr class="clickable"
                            data-idx="{{ $loop->index }}"
                            data-search="{{ strtolower(($s->user->name ?? '').' '.($s->scenario->buyer_name ?? '').' '.($s->scenario->name ?? '')) }}"
                            data-date="{{ $s->created_at->timestamp }}"
                            data-agent="{{ strtolower($s->user->name ?? '') }}"
                            data-buyer="{{ strtolower($s->scenario->buyer_name ?? '') }}"
                            data-difficulty="{{ $diffRank }}"
                            data-diff-label="{{ strtolower($s->difficulty ?? '') }}"
                            data-status="{{ strtolower($s->status) }}"
                            data-score="{{ $s->overall_score ?? -1 }}"
                            onclick="window.location='{{ route('practice.chat', $s) }}'">
                            <td class="pah-check-col" onclick="event.stopPropagation()"><input type="checkbox" class="pah-row-check" value="{{ $s->id }}" onchange="pahUpdateSelection()"></td>
                            <td>{{ $s->created_at->format('M d, Y g:ia') }}</td>
                            <td class="pah-agent">{{ $s->user->name ?? '—' }}</td>
                            <td>{{ $s->scenario->buyer_name ?? '—' }} <span style="color:#94a3b8;">({{ $s->scenario->name ?? '—' }})</span></td>
                            <td><span class="pah-diff">{{ ucfirst(strtolower($s->difficulty)) }}</span></td>
                            <td><span class="pah-badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                            <td><span class="pah-score">{{ $s->overall_score !== null ? $s->overall_score.'/100' : '—' }}</span></td>
                            <td>
                                @if($s->scorecard)
                                    <button type="button" class="pah-icon-btn"
                                        onclick="event.stopPropagation(); pahShowRemarksById({{ $s->id }})">
                                        View Remarks
                                    </button>
                                @else
                                    <button type="button" class="pah-icon-btn" disabled>No Remarks</button>
                                @endif
                            </td>
                            <td>
                                <div class="pah-row-actions">
                                    <a href="{{ route('practice.chat', $s) }}" class="pah-icon-btn" onclick="event.stopPropagation()">View Chat</a>
                                    <form method="POST" action="{{ route('practice.admin.history.destroy', $s) }}"
                                        onclick="event.stopPropagation()"
                                        onsubmit="return confirm('Delete this practice session for {{ $s->user->name ?? 'this agent' }}? This cannot be undone from here.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="pah-icon-btn danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if($sessions->hasPages())
        <div class="pah-pagination">{{ $sessions->links('pagination::bootstrap-4') }}</div>
    @endif
</div>

<script id="pahScorecardsData" type="application/json">
{!! json_encode(
    $sessions->mapWithKeys(fn ($s) => [$s->id => [
        'status'   => $s->status,
        'agent'    => $s->user->name ?? '—',
        'scorecard' => $s->scorecard,
    ]]),
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) !!}
</script>

<!-- Remarks modal -->
<div class="pah-score-overlay" id="pahRemarksOverlay" onclick="if(event.target===this) pahCloseRemarks()">
    <div class="pah-score-box">
        <div class="pah-score-hdr">
            <div class="pah-score-outcome" id="pahRemarksOutcome"></div>
            <div class="pah-score-overall" id="pahRemarksOverall"></div>
            <div class="pah-score-agent" id="pahRemarksAgent"></div>
        </div>
        <div class="pah-score-body">
            <div class="pah-score-rubric">
                <div class="pah-score-metric">
                    <div class="pah-score-metric-lbl">Rapport</div>
                    <div class="pah-score-metric-val" id="pahRemarksRapport">—</div>
                </div>
                <div class="pah-score-metric">
                    <div class="pah-score-metric-lbl">Objection Handling</div>
                    <div class="pah-score-metric-val" id="pahRemarksObjection">—</div>
                </div>
                <div class="pah-score-metric">
                    <div class="pah-score-metric-lbl">Product Knowledge</div>
                    <div class="pah-score-metric-val" id="pahRemarksProduct">—</div>
                </div>
                <div class="pah-score-metric">
                    <div class="pah-score-metric-lbl">Closing Technique</div>
                    <div class="pah-score-metric-val" id="pahRemarksClosing">—</div>
                </div>
            </div>
            <div class="pah-score-summary" id="pahRemarksSummary"></div>
            <ul class="pah-score-suggestions" id="pahRemarksSuggestions"></ul>
        </div>
        <div class="pah-score-actions">
            <button type="button" class="pah-score-close" onclick="pahCloseRemarks()">Close</button>
        </div>
    </div>
</div>

<script>
var pahScorecardsMap = JSON.parse(document.getElementById('pahScorecardsData').textContent || '{}');

function pahShowRemarksById(sessionId) {
    var entry = pahScorecardsMap[sessionId] || {};
    var status = entry.status;
    var scorecard = entry.scorecard || {};
    var outcomeLabel = status === 'SOLD' ? 'Sold! 🎉' : (status === 'ABANDONED' ? 'Ended Early' : (status === 'IN_PROGRESS' ? 'In Progress' : 'Not Sold'));

    document.getElementById('pahRemarksOutcome').textContent = outcomeLabel;
    document.getElementById('pahRemarksOverall').textContent = (scorecard.overall_score ?? '—') + (scorecard.overall_score != null ? '/100' : '');
    document.getElementById('pahRemarksAgent').textContent = 'Agent: ' + (entry.agent || '—');
    document.getElementById('pahRemarksRapport').textContent = scorecard.rapport ?? '—';
    document.getElementById('pahRemarksObjection').textContent = scorecard.objection_handling ?? '—';
    document.getElementById('pahRemarksProduct').textContent = scorecard.product_knowledge ?? '—';
    document.getElementById('pahRemarksClosing').textContent = scorecard.closing_technique ?? '—';
    document.getElementById('pahRemarksSummary').textContent = scorecard.summary || 'No summary available.';

    var list = document.getElementById('pahRemarksSuggestions');
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

    document.getElementById('pahRemarksOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function pahCloseRemarks() {
    document.getElementById('pahRemarksOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') pahCloseRemarks();
});

function pahRowChecks() {
    return Array.prototype.slice.call(document.querySelectorAll('.pah-row-check'));
}

function pahUpdateSelection() {
    var checks = pahRowChecks();
    var checked = checks.filter(function (c) { return c.checked; });

    document.getElementById('pahSelCount').textContent = checked.length;
    document.getElementById('pahBulkDeleteBtn').disabled = checked.length === 0;

    var selectAll = document.getElementById('pahSelectAll');
    if (selectAll) {
        selectAll.checked = checks.length > 0 && checked.length === checks.length;
        selectAll.indeterminate = checked.length > 0 && checked.length < checks.length;
    }
}

function pahToggleSelectAll(source) {
    pahRowChecks().forEach(function (c) { c.checked = source.checked; });
    pahUpdateSelection();
}

function pahBulkDelete() {
    var ids = pahRowChecks().filter(function (c) { return c.checked; }).map(function (c) { return c.value; });
    if (!ids.length) return;

    showConfirm(
        'Delete ' + ids.length + ' selected practice session(s)? This cannot be undone from here.',
        function () {
            var btn = document.getElementById('pahBulkDeleteBtn');
            btn.disabled = true;
            btn.textContent = 'Deleting…';

            fetch(@json(route('practice.admin.history.bulk-destroy')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ ids: ids }),
            })
                .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
                .then(function (result) {
                    if (!result.ok) {
                        showToast(result.data.message || 'Could not delete the selected sessions.', 'error', 'Delete Failed');
                        btn.disabled = false;
                        btn.textContent = 'Delete Selected';
                        return;
                    }
                    showToast(result.data.message, 'success', 'Deleted');
                    setTimeout(function () { window.location.reload(); }, 900);
                })
                .catch(function () {
                    showToast('Could not reach the server — please try again.', 'error', 'Delete Failed');
                    btn.disabled = false;
                    btn.textContent = 'Delete Selected';
                });
        },
        'Delete selected sessions?'
    );
}

/* ---- Dropdowns (Filter + Sort) ---- */
function pahToggleDropdown(menuId, btnEl) {
    var menu = document.getElementById(menuId);
    var isOpen = menu.classList.contains('open');
    document.querySelectorAll('.pah-dd-menu.open').forEach(function (m) { m.classList.remove('open'); });
    document.querySelectorAll('.pah-filter-btn.open').forEach(function (b) { b.classList.remove('open'); });
    if (!isOpen) {
        menu.classList.add('open');
        if (btnEl && btnEl.classList.contains('pah-filter-btn')) btnEl.classList.add('open');
    }
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('.pah-dd-wrap')) {
        document.querySelectorAll('.pah-dd-menu.open').forEach(function (m) { m.classList.remove('open'); });
        document.querySelectorAll('.pah-filter-btn.open').forEach(function (b) { b.classList.remove('open'); });
    }
});

/* ---- Search + Filter (combined) ---- */
function pahApplyFilters() {
    var q = (document.getElementById('pahSearchInput').value || '').toLowerCase().trim();
    var diffChecked = Array.prototype.slice.call(document.querySelectorAll('.pah-filter-diff:checked')).map(function (c) { return c.value; });
    var statusChecked = Array.prototype.slice.call(document.querySelectorAll('.pah-filter-status:checked')).map(function (c) { return c.value; });
    var rows = Array.prototype.slice.call(document.querySelectorAll('.pah-table tbody tr'));
    var visible = 0;

    rows.forEach(function (row) {
        var searchMatch = !q || (row.getAttribute('data-search') || '').indexOf(q) !== -1;
        var diffMatch = !diffChecked.length || diffChecked.indexOf(row.getAttribute('data-diff-label')) !== -1;
        var statusMatch = !statusChecked.length || statusChecked.indexOf(row.getAttribute('data-status')) !== -1;
        var match = searchMatch && diffMatch && statusMatch;
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    var counter = document.getElementById('pahVisibleCount');
    if (counter) counter.textContent = visible;
}

function pahClearAll() {
    document.getElementById('pahSearchInput').value = '';
    document.querySelectorAll('.pah-filter-diff, .pah-filter-status').forEach(function (c) { c.checked = false; });

    var tbody = document.querySelector('.pah-table tbody');
    var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
    rows.sort(function (a, b) { return parseInt(a.getAttribute('data-idx')) - parseInt(b.getAttribute('data-idx')); });
    rows.forEach(function (row) { tbody.appendChild(row); });

    pahSortState.key = null; pahSortState.dir = 1;

    pahApplyFilters();
}

/* ---- Sort (via the Sort dropdown presets only — column headers are plain, not clickable) ---- */
var pahSortState = { key: null, dir: 1 };

function pahSortRows(key, dir) {
    var tbody = document.querySelector('.pah-table tbody');
    if (!tbody) return;
    var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
    var numericKeys = ['date', 'difficulty', 'score'];
    var isNumeric = numericKeys.indexOf(key) !== -1;

    rows.sort(function (a, b) {
        var av = a.getAttribute('data-' + key) || '';
        var bv = b.getAttribute('data-' + key) || '';
        if (isNumeric) { av = parseFloat(av); bv = parseFloat(bv); }
        if (av < bv) return -1 * dir;
        if (av > bv) return 1 * dir;
        return 0;
    });
    rows.forEach(function (row) { tbody.appendChild(row); });
}

function pahSortPreset(key, dir) {
    pahSortState.key = key;
    pahSortState.dir = dir;
    pahSortRows(key, dir);

    document.querySelectorAll('.pah-dd-menu.open').forEach(function (m) { m.classList.remove('open'); });
}
</script>
@endsection