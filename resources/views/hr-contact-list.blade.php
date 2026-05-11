@extends('layouts.dashboard')
@section('title', 'ARC Contact List')
@section('content')

<style>
.hr-banner { background:linear-gradient(135deg,#0f2444 0%,#1e4575 50%,#2563eb 100%);border-radius:20px;padding:36px 40px;margin-bottom:28px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25); }
.hr-banner-content { position:relative;z-index:2; }
.hr-banner-label { font-size:11px;font-weight:700;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px; }
.hr-banner h1 { font-size:30px;font-weight:800;color:white;margin:0 0 6px; }
.hr-banner p { font-size:14px;color:rgba(255,255,255,.7);margin:0; }
.hr-banner-deco span { position:absolute;border-radius:50%;background:rgba(255,255,255,.06); }

.hr-card { background:white;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);margin-bottom:20px;overflow:hidden; }
.hr-card-header { padding:12px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between; }
.hr-card-title { font-size:15px;font-weight:700;color:#0f172a; }
.hr-card-sub { font-size:12px;color:#94a3b8;margin-top:2px; }

.hr-group-header { background:linear-gradient(135deg,#0f2444,#1a3a6b);padding:10px 16px;display:flex;align-items:center;justify-content:space-between; }
.hr-group-title { font-size:12px;font-weight:700;color:#d4a03a;text-transform:uppercase;letter-spacing:1px; }

.hr-table { width:100%;border-collapse:collapse;font-size:13px; }
.hr-table thead tr { background:#f8fafc; }
.hr-table thead th { padding:8px 12px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;border-bottom:1.5px solid #e2e8f0; }
.hr-table tbody tr { border-bottom:1px solid #f1f5f9;transition:background .15s; }
.hr-table tbody tr:hover { background:#f8fafc; }
.hr-table tbody td { padding:9px 12px;color:#374151; }

.hr-btn { padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;border:none;transition:all .2s; }
.hr-btn-primary { background:linear-gradient(135deg,#1e4575,#2563eb);color:white; }
.hr-btn-primary:hover { opacity:.9; }
.hr-btn-danger { background:#fee2e2;color:#991b1b; }
.hr-btn-danger:hover { background:#fecaca; }
.hr-btn-gold { background:rgba(212,160,58,.2);color:#d4a03a;border:1px solid rgba(212,160,58,.4) !important; }
.hr-btn-lg { padding:10px 24px;font-size:13px; }

.hr-modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;padding:20px; }
.hr-modal { background:white;border-radius:16px;padding:24px 28px;width:480px;max-width:95vw;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2); }
.hr-modal-title { font-size:16px;font-weight:700;color:#0f172a;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid #f1f5f9; }
.hr-form-grid2 { display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px; }
.hr-form-group label { display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px; }
.hr-input { width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;color:#0f172a;background:#fff;outline:none;transition:border-color .2s;box-sizing:border-box; }
.hr-input:focus { border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1); }
</style>

<div class="hr-banner">
    <div class="hr-banner-content">
        <div class="hr-banner-label">Human Resource</div>
        <h1>ARC Contact List</h1>
        <p>Directory of ArkCrest personnel with their contact information</p>
    </div>
    <div style="position:absolute;top:0;right:0;width:320px;height:100%;pointer-events:none;">
        <span style="position:absolute;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.06);top:-60px;right:-40px;"></span>
        <span style="position:absolute;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.06);bottom:-30px;right:100px;"></span>
    </div>
</div>

@if(session('success'))
<div style="background:#d1fae5;color:#065f46;padding:12px 18px;border-radius:10px;margin-bottom:20px;font-weight:600;display:flex;align-items:center;gap:8px;">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

@php $isAdmin = auth()->user()->isAdmin(); @endphp

<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
    <button type="button" class="hr-btn hr-btn-primary hr-btn-lg" onclick="openAddContactModal('', this)">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add New Contact
    </button>
</div>

@if($personnelContacts->isEmpty())
    <div class="hr-card"><div style="padding:40px;text-align:center;color:#94a3b8;">No contacts yet.</div></div>
@else
@php $grouped = $personnelContacts->groupBy(fn($c) => $c->company ?: 'Others'); @endphp
<div id="contactGroupsContainer">
@foreach($grouped as $grpCompany => $contacts)
<div class="hr-card" data-group="{{ addslashes($grpCompany) }}">
    <div class="hr-group-header">
        <div class="hr-group-title" style="display:flex;align-items:center;gap:8px;">
            @if($isAdmin)
            <span class="drag-handle" title="Drag to reorder group" style="cursor:grab;color:rgba(212,160,58,.5);font-size:16px;line-height:1;padding:0 4px;user-select:none;">⠿</span>
            @endif
            <svg width="14" height="14" fill="none" stroke="#d4a03a" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ $grpCompany }} <span style="color:rgba(212,160,58,.6);font-weight:400;">({{ $contacts->count() }})</span>
        </div>
        <button type="button" class="hr-btn hr-btn-gold" onclick="openAddContactModal('{{ addslashes($grpCompany) }}', this)">+ Add</button>
    </div>
    <div style="overflow-x:auto;">
    <table class="hr-table">
        <thead><tr>
            @if($isAdmin)<th style="width:20px;"></th>@endif
            <th>Name</th><th>Contact No.</th><th>Email</th><th>Facebook</th>
            @if($isAdmin)<th>Actions</th>@endif
        </tr></thead>
        <tbody>
            @foreach($contacts as $contact)
            <tr data-id="{{ $contact->id }}">
                @if($isAdmin)
                <td style="color:#cbd5e1;font-size:16px;cursor:grab;user-select:none;text-align:center;" title="Drag to reorder">⠿</td>
                @endif
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#A37929,#d4a03a);display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700;flex-shrink:0;">{{ strtoupper(substr($contact->name,0,1)) }}</div>
                        <span style="font-weight:600;color:#0f172a;">{{ $contact->name }}</span>
                    </div>
                </td>
                <td>{{ $contact->phone ?: '—' }}</td>
                <td>@if($contact->email)<a href="https://mail.google.com/mail/?view=cm&to={{ urlencode($contact->email) }}" target="_blank" style="color:#1e4575;text-decoration:none;">{{ $contact->email }}</a>@else —@endif</td>
                <td>@if($contact->facebook)
                    @php $fbUrl = str_starts_with($contact->facebook, 'http') ? $contact->facebook : 'https://facebook.com/' . $contact->facebook; @endphp
                    <a href="{{ $fbUrl }}" target="_blank" style="color:#1877f2;text-decoration:none;display:flex;align-items:center;gap:5px;">
                        <svg width="14" height="14" fill="#1877f2" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        {{ Str::limit($contact->facebook, 30) }}
                    </a>
                @else —@endif</td>
                @if($isAdmin)
                <td style="white-space:nowrap;">
                    <button type="button" class="hr-btn hr-btn-primary" onclick="openContactModal({{ $contact->id }}, '{{ addslashes($contact->name) }}', '{{ addslashes($contact->company) }}', '{{ addslashes($contact->phone) }}', '{{ addslashes($contact->email) }}', '{{ addslashes($contact->facebook) }}', this)">Edit</button>
                    <form method="POST" action="{{ route('settings.personnel-contacts.destroy', $contact->id) }}" style="display:inline;" onsubmit="return confirm('Remove this contact?')">@csrf @method('DELETE')
                        <button type="submit" class="hr-btn hr-btn-danger">Delete</button>
                    </form>
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endforeach
</div>{{-- end contactGroupsContainer --}}
@endif

{{-- Add Contact Modal --}}
<div id="contactAddModal" class="hr-modal-overlay" onclick="if(event.target===this)closeAddContactModal();">
    <div class="hr-modal">
        <div class="hr-modal-title">
            <svg width="18" height="18" fill="none" stroke="#1e4575" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Add New Contact
        </div>
        <form method="POST" action="{{ route('settings.personnel-contacts.store') }}">@csrf
            <div class="hr-form-grid2">
                <div class="hr-form-group"><label>Name <span style="color:#ef4444;">*</span></label><input class="hr-input" type="text" name="name" required placeholder="Full name"></div>
                <div class="hr-form-group"><label>Company / Group</label><input class="hr-input" id="addModalCompany" type="text" name="company" placeholder="e.g. Executives"></div>
                <div class="hr-form-group"><label>Contact No.</label><input class="hr-input" type="text" name="phone" placeholder="+63 9XX XXX XXXX"></div>
                <div class="hr-form-group"><label>Email</label><input class="hr-input" type="email" name="email" placeholder="email@example.com"></div>
            </div>
            <div class="hr-form-group" style="margin-bottom:20px;"><label>Facebook</label><input class="hr-input" type="text" name="facebook" placeholder="Facebook name or URL"></div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="hr-btn hr-btn-primary hr-btn-lg" style="flex:1;">Add Contact</button>
                <button type="button" onclick="closeAddContactModal()" class="hr-btn" style="flex:1;background:#f1f5f9;color:#374151;padding:10px 24px;font-size:13px;">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Contact Modal --}}
<div id="contactEditModal" class="hr-modal-overlay" onclick="if(event.target===this)closeContactModal();">
    <div class="hr-modal">
        <div class="hr-modal-title">
            <svg width="18" height="18" fill="none" stroke="#1e4575" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Contact
        </div>
        <form id="contactEditForm" method="POST">@csrf @method('PUT')
            <div class="hr-form-grid2">
                <div class="hr-form-group"><label>Name <span style="color:#ef4444;">*</span></label><input class="hr-input" type="text" id="edit_name" name="name" required></div>
                <div class="hr-form-group"><label>Company</label><input class="hr-input" type="text" id="edit_company" name="company"></div>
                <div class="hr-form-group"><label>Contact No.</label><input class="hr-input" type="text" id="edit_phone" name="phone"></div>
                <div class="hr-form-group"><label>Email</label><input class="hr-input" type="email" id="edit_email" name="email"></div>
            </div>
            <div class="hr-form-group" style="margin-bottom:20px;"><label>Facebook</label><input class="hr-input" type="text" id="edit_facebook" name="facebook"></div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="hr-btn hr-btn-primary hr-btn-lg" style="flex:1;">Save Changes</button>
                <button type="button" onclick="closeContactModal()" class="hr-btn" style="flex:1;background:#f1f5f9;color:#374151;padding:10px 24px;font-size:13px;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddContactModal(company, btn) {
    var f = document.getElementById('addModalCompany');
    if(f) f.value = company || '';
    document.getElementById('contactAddModal').style.display = 'flex';
}
function closeAddContactModal() { document.getElementById('contactAddModal').style.display = 'none'; }
function openContactModal(id, name, company, phone, email, facebook, btn) {
    document.getElementById('contactEditForm').action = '/settings/personnel-contacts/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_company').value = company;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_facebook').value = facebook;
    document.getElementById('contactEditModal').style.display = 'flex';
}
function closeContactModal() { document.getElementById('contactEditModal').style.display = 'none'; }
</script>

@if(auth()->user()->isAdmin())
<script>
// ── Drag & Drop for Contact List ──
var _csrf = document.querySelector('meta[name=csrf-token]').content;

// ── ROW drag (within a group) ──
function initRowDrag(tbody) {
    var dragging = null;
    tbody.querySelectorAll('tr[data-id]').forEach(function(row) {
        row.setAttribute('draggable', 'true');
        row.style.cursor = 'grab';

        row.addEventListener('dragstart', function(e) {
            dragging = row;
            row.style.opacity = '0.4';
            e.dataTransfer.effectAllowed = 'move';
        });
        row.addEventListener('dragend', function() {
            row.style.opacity = '1';
            dragging = null;
            saveRowOrder(tbody);
        });
        row.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (dragging && dragging !== row) {
                var rect = row.getBoundingClientRect();
                var mid = rect.top + rect.height / 2;
                if (e.clientY < mid) {
                    tbody.insertBefore(dragging, row);
                } else {
                    tbody.insertBefore(dragging, row.nextSibling);
                }
            }
        });
    });
}

function saveRowOrder(tbody) {
    var items = [];
    tbody.querySelectorAll('tr[data-id]').forEach(function(row, i) {
        items.push({ id: parseInt(row.getAttribute('data-id')), sort_order: i + 1 });
    });
    fetch('/api/personnel-contacts/reorder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf },
        body: JSON.stringify({ items: items })
    });
}

// ── GROUP drag (whole card) ──
var _dragGroup = null;
function initGroupDrag(container) {
    container.querySelectorAll('.hr-card[data-group]').forEach(function(card) {
        var handle = card.querySelector('.drag-handle');
        if (!handle) return;

        handle.addEventListener('mousedown', function() { card.setAttribute('draggable', 'true'); });
        handle.addEventListener('mouseup', function() { card.setAttribute('draggable', 'false'); });

        card.addEventListener('dragstart', function(e) {
            _dragGroup = card;
            card.style.opacity = '0.4';
            e.dataTransfer.effectAllowed = 'move';
        });
        card.addEventListener('dragend', function() {
            card.style.opacity = '1';
            card.setAttribute('draggable', 'false');
            _dragGroup = null;
            saveGroupOrder(container);
        });
        card.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (_dragGroup && _dragGroup !== card) {
                var rect = card.getBoundingClientRect();
                var mid = rect.top + rect.height / 2;
                if (e.clientY < mid) {
                    container.insertBefore(_dragGroup, card);
                } else {
                    container.insertBefore(_dragGroup, card.nextSibling);
                }
            }
        });
    });
}

function saveGroupOrder(container) {
    // Collect all row IDs in new order across all groups
    var items = [];
    var order = 1;
    container.querySelectorAll('.hr-card[data-group] tr[data-id]').forEach(function(row) {
        items.push({ id: parseInt(row.getAttribute('data-id')), sort_order: order++ });
    });
    if (items.length) {
        fetch('/api/personnel-contacts/reorder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf },
            body: JSON.stringify({ items: items })
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('contactGroupsContainer');
    if (!container) return;
    initGroupDrag(container);
    container.querySelectorAll('tbody').forEach(function(tbody) {
        initRowDrag(tbody);
    });
});
</script>
@endif

@endsection
