@extends('layouts.dashboard')
@section('title', 'Manage Practice Scenarios')

@section('content')
<style>
.pa-page { display:flex;flex-direction:column;gap:16px; }
.pa-topbar {
    background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);
    border-radius:20px;padding:32px 40px;display:flex;align-items:center;justify-content:space-between;
    box-shadow:0 8px 32px rgba(30,69,117,.25);
}
.pa-title { font-size:22px;font-weight:700;color:white;margin:0 0 4px; }
.pa-sub { font-size:13px;color:rgba(255,255,255,.75);margin:0; }
.pa-add-btn { padding:9px 18px;background:white;color:#1e4575;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer; }
.pa-add-btn:hover { opacity:.9; }

.pa-alert { background:#dcfce7;color:#166534;padding:12px 18px;border-radius:10px;font-size:13px;font-weight:600; }

.pa-table-wrap { background:white;border-radius:14px;border:1px solid #e8ecf0;box-shadow:0 2px 12px rgba(0,0,0,.05);overflow:hidden; }
table.pa-table { width:100%;border-collapse:collapse; }
.pa-table thead tr { background:linear-gradient(135deg,#0f2a4a,#1e4575); }
.pa-table th { padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.6px; }
.pa-table td { padding:12px 18px;font-size:13px;color:#374151;border-bottom:1px solid #f1f5f9;vertical-align:top; }
.pa-table tr:last-child td { border-bottom:none; }
.pa-diff-pill { padding:3px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase; }
.pa-diff-EASY { background:#f0fdf4;color:#16a34a; }
.pa-diff-MEDIUM { background:#fffbeb;color:#d97706; }
.pa-diff-HARD { background:#fef2f2;color:#dc2626; }
.pa-status-active { color:#16a34a;font-weight:700;font-size:12px; }
.pa-status-inactive { color:#94a3b8;font-weight:700;font-size:12px; }
.pa-row-actions { display:flex;gap:8px; }
.pa-icon-btn { border:1px solid #e2e8f0;background:white;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:600;cursor:pointer;color:#374151; }
.pa-icon-btn:hover { background:#f8fafc; }
.pa-icon-btn.danger { color:#dc2626;border-color:#fecaca; }
.pa-icon-btn.danger:hover { background:#fef2f2; }

/* Modal */
.pa-modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center; }
.pa-modal-box { background:white;border-radius:16px;width:640px;max-width:95vw;max-height:88vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2); }
.pa-modal-hdr { padding:18px 24px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;display:flex;align-items:center;justify-content:space-between; }
.pa-modal-close { background:rgba(255,255,255,.15);border:none;color:white;width:28px;height:28px;border-radius:7px;cursor:pointer;font-size:16px; }
.pa-modal-body { padding:22px 24px;display:flex;flex-direction:column;gap:14px; }
.pa-field-row { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
.pa-field label { display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px; }
.pa-field input, .pa-field select, .pa-field textarea {
    width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:13px;font-family:inherit;
}
.pa-field textarea { resize:vertical;min-height:60px; }
.pa-field input:focus, .pa-field select:focus, .pa-field textarea:focus { outline:none;border-color:#2563eb; }
.pa-field-hint { font-size:11px;color:#94a3b8;margin-top:3px; }
.pa-checkbox-row { display:flex;align-items:center;gap:8px; }
.pa-modal-actions { padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;gap:10px;justify-content:flex-end; }
.pa-btn-primary { border:none;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer; }
.pa-btn-secondary { border:1.5px solid #e2e8f0;background:white;color:#374151;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer; }
</style>

<div class="pa-page">
    <div class="pa-topbar">
        <div>
            <h1 class="pa-title">Manage Practice Scenarios</h1>
            <p class="pa-sub">Add, edit, or retire buyer personas used in Persuasion Practice.</p>
        </div>
        <button type="button" class="pa-add-btn" onclick="paOpenModal()">+ New Scenario</button>
    </div>

    @if(session('success'))
        <div class="pa-alert">{{ session('success') }}</div>
    @endif

    <div class="pa-table-wrap">
        <table class="pa-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Buyer</th>
                    <th>Difficulty</th>
                    <th>Status</th>
                    <th style="width:150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scenarios as $s)
                    <tr>
                        <td>
                            <div style="font-weight:600;color:#1e293b;">{{ $s->name }}</div>
                            <div style="color:#94a3b8;font-size:12px;">{{ $s->tagline }}</div>
                        </td>
                        <td>{{ $s->buyer_name }}</td>
                        <td><span class="pa-diff-pill pa-diff-{{ $s->difficulty }}">{{ ucfirst(strtolower($s->difficulty)) }}</span></td>
                        <td><span class="{{ $s->is_active ? 'pa-status-active' : 'pa-status-inactive' }}">{{ $s->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <div class="pa-row-actions">
                                <button type="button" class="pa-icon-btn" onclick='paEditScenario(@json($s))'>Edit</button>
                                <form method="POST" action="{{ route('practice.admin.destroy', $s) }}" onsubmit="return confirm('Remove this scenario? Past sessions using it will be kept.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="pa-icon-btn danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:30px;">No scenarios yet — add the first one above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pa-modal-overlay" id="paModalOverlay">
    <div class="pa-modal-box">
        <div class="pa-modal-hdr">
            <span id="paModalTitle" style="font-weight:700;">New Scenario</span>
            <button type="button" class="pa-modal-close" onclick="paCloseModal()">&times;</button>
        </div>
        <form id="paForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="paMethod" value="POST">
            <div class="pa-modal-body">
                <div class="pa-field-row">
                    <div class="pa-field">
                        <label>Scenario Name</label>
                        <input type="text" name="name" id="paName" required maxlength="150">
                    </div>
                    <div class="pa-field">
                        <label>Difficulty</label>
                        <select name="difficulty" id="paDifficulty" required>
                            <option value="EASY">Easy</option>
                            <option value="MEDIUM">Medium</option>
                            <option value="HARD">Hard</option>
                        </select>
                    </div>
                </div>
                <div class="pa-field">
                    <label>Tagline (shown on the scenario card)</label>
                    <input type="text" name="tagline" id="paTagline" maxlength="200">
                </div>
                <div class="pa-field-row">
                    <div class="pa-field">
                        <label>Buyer Name</label>
                        <input type="text" name="buyer_name" id="paBuyerName" required maxlength="100">
                    </div>
                    <div class="pa-field">
                        <label>Buyer Budget (₱, optional)</label>
                        <input type="number" name="buyer_budget" id="paBuyerBudget" step="0.01" min="0">
                    </div>
                </div>
                <div class="pa-field">
                    <label>Buyer Backstory</label>
                    <textarea name="buyer_backstory" id="paBackstory"></textarea>
                </div>
                <div class="pa-field">
                    <label>Personality Traits</label>
                    <textarea name="personality_traits" id="paTraits"></textarea>
                    <div class="pa-field-hint">One trait per line.</div>
                </div>
                <div class="pa-field">
                    <label>Common Objections</label>
                    <textarea name="common_objections" id="paObjections"></textarea>
                    <div class="pa-field-hint">One objection per line.</div>
                </div>
                <div class="pa-field">
                    <label>Win Conditions (what convinces this buyer)</label>
                    <textarea name="win_conditions" id="paWinConditions"></textarea>
                    <div class="pa-field-hint">One condition per line.</div>
                </div>
                <div class="pa-field">
                    <label>Walk-Away Triggers</label>
                    <textarea name="walkaway_triggers" id="paWalkaway"></textarea>
                    <div class="pa-field-hint">One trigger per line.</div>
                </div>
                <div class="pa-checkbox-row">
                    <input type="checkbox" name="is_active" id="paIsActive" value="1" checked>
                    <label for="paIsActive" style="margin:0;text-transform:none;font-weight:600;color:#374151;">Active (visible to agents)</label>
                </div>
            </div>
            <div class="pa-modal-actions">
                <button type="button" class="pa-btn-secondary" onclick="paCloseModal()">Cancel</button>
                <button type="submit" class="pa-btn-primary">Save Scenario</button>
            </div>
        </form>
    </div>
</div>

<script>
function paOpenModal() {
    document.getElementById('paForm').reset();
    document.getElementById('paForm').action = @json(route('practice.admin.store'));
    document.getElementById('paMethod').value = 'POST';
    document.getElementById('paModalTitle').textContent = 'New Scenario';
    document.getElementById('paIsActive').checked = true;
    document.getElementById('paModalOverlay').style.display = 'flex';
}

function paEditScenario(s) {
    document.getElementById('paForm').action = '/practice/admin/' + s.id;
    document.getElementById('paMethod').value = 'PUT';
    document.getElementById('paModalTitle').textContent = 'Edit Scenario';
    document.getElementById('paName').value = s.name || '';
    document.getElementById('paTagline').value = s.tagline || '';
    document.getElementById('paDifficulty').value = s.difficulty || 'EASY';
    document.getElementById('paBuyerName').value = s.buyer_name || '';
    document.getElementById('paBuyerBudget').value = s.buyer_budget || '';
    document.getElementById('paBackstory').value = s.buyer_backstory || '';
    document.getElementById('paTraits').value = s.personality_traits || '';
    document.getElementById('paObjections').value = s.common_objections || '';
    document.getElementById('paWinConditions').value = s.win_conditions || '';
    document.getElementById('paWalkaway').value = s.walkaway_triggers || '';
    document.getElementById('paIsActive').checked = !!s.is_active;
    document.getElementById('paModalOverlay').style.display = 'flex';
}

function paCloseModal() {
    document.getElementById('paModalOverlay').style.display = 'none';
}
</script>
@endsection