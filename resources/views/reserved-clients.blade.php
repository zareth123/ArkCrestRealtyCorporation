@extends('layouts.dashboard')
@section('title', 'List of Clients')
@section('content')
<style>
.lc-header{background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:28px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25)}
.lc-eyebrow{font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px}
.lc-title{font-size:28px;font-weight:700;color:white;margin:0 0 8px}
.lc-sub{font-size:14px;color:rgba(255,255,255,.75);margin:0;display:flex;align-items:center;gap:5px}
.lc-deco{position:absolute;top:0;right:0;width:300px;height:100%;pointer-events:none}
.lc-circle{position:absolute;border-radius:50%;background:rgba(255,255,255,.06)}
.lc-c1{width:220px;height:220px;top:-60px;right:-40px}
.lc-c2{width:140px;height:140px;top:40px;right:120px}
.lc-c3{width:90px;height:90px;bottom:-20px;right:60px}
.lc-card{background:white;border-radius:14px;box-shadow:0 1px 4px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);overflow:hidden;border:1px solid #f1f5f9}
.lc-head{padding:16px 22px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:#f8fafc}
.lc-badge{display:inline-block;padding:3px 12px;border-radius:20px;font-size:11px;font-weight:700;background:#dbeafe;color:#1e40af}
.lc-search{position:relative}
.lc-search svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#94a3b8}
.lc-search input{padding:8px 12px 8px 34px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;color:#111827;background:white;width:320px;transition:all .2s}
.lc-search input:focus{outline:none;border-color:#1e4575;box-shadow:0 0 0 3px rgba(30,69,117,.08)}
.lc-table{width:100%;border-collapse:collapse;min-width:700px}
.lc-table thead tr{background:#1e4575}
.lc-table thead th{padding:13px 18px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.8px;white-space:nowrap;border-right:1px solid rgba(255,255,255,.08);position:sticky;top:0;background:#1e4575;z-index:4;box-shadow:0 2px 4px -2px rgba(0,0,0,.25)}
.lc-table thead th:last-child{border-right:none}
.lc-table tbody tr{border-bottom:1px solid #f1f5f9;transition:background .15s}
.lc-table tbody tr:nth-child(even){background:#fafbfc}
.lc-table tbody tr:hover{background:#eff6ff}
.lc-table tbody tr:last-child{border-bottom:none}
.lc-table td{padding:12px 18px;font-size:13px;color:#374151;vertical-align:middle;white-space:nowrap}
.lc-name{font-weight:700;color:#0f172a}
.lc-muted{color:#64748b;font-size:12px}
.lc-tag{display:inline-block;background:#f1f5f9;color:#374151;border-radius:20px;padding:2px 10px;font-size:11px;font-weight:600;margin:1px 2px}
.lc-empty{text-align:center;padding:56px 20px;color:#94a3b8}
.lc-btn{padding:5px 14px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;border:none;transition:all .2s;white-space:nowrap;text-decoration:none;display:inline-block}
.lc-btn-view{background:linear-gradient(135deg,#1e4575,#2563eb);color:white}
.lc-btn-edit{background:linear-gradient(135deg,#A37929,#d4a03a);color:white}
.lc-btn-del{background:linear-gradient(135deg,#7f1d1d,#b91c1c);color:white}
.lc-btn:hover{transform:translateY(-1px);opacity:.9}
/* Edit modal */
.lc-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center}
.lc-modal.open{display:flex}
.lc-modal-box{background:white;border-radius:16px;width:500px;max-width:95vw;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2)}
.lc-modal-hdr{background:linear-gradient(135deg,#1e4575,#2563eb);padding:16px 22px;display:flex;align-items:center;justify-content:space-between}
.lc-modal-body{padding:22px}
.lc-label{font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:5px}
.lc-input{padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;color:#374151;font-family:inherit;width:100%;box-sizing:border-box}
.lc-input:focus{outline:none;border-color:#1e4575;box-shadow:0 0 0 3px rgba(30,69,117,.08)}
.lc-table-scroll{overflow-x:auto !important;overflow-y:visible !important;max-height:none !important;}
@media (max-width:900px){
  .lc-view-grid{grid-template-columns:1fr 1fr !important;}
}
@media (max-width:600px){
  .lc-view-modal-box{width:100% !important;max-width:100% !important;border-radius:0 !important;}
  .lc-view-grid{grid-template-columns:1fr !important;}
}

/* ---- Filter dropdown + chips (matches Client Database / Commission Monitoring pattern) ---- */
.column-filter-dropdown{position:relative}
.column-filter-btn{display:inline-flex;align-items:center;gap:6px;white-space:nowrap;font-size:12px;font-weight:600;color:#1e4575;background:white;border:2px solid #1e4575;border-radius:8px;padding:8px 13px;cursor:pointer;height:34px;box-sizing:border-box;transition:all .2s ease}
.column-filter-btn:hover{background:#eef2f7}
.filter-count-badge{background:#A37929;color:white;font-size:10px;font-weight:700;border-radius:999px;min-width:16px;height:16px;display:inline-flex;align-items:center;justify-content:center;padding:0 5px}
.column-filter-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:200px;max-height:300px;overflow-y:auto;background:white;border:1.5px solid #d0d5dd;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:500;padding:6px}
.column-filter-menu-item{display:flex;align-items:center;gap:8px;padding:8px 10px;font-size:12px;font-weight:500;color:#344054;border-radius:6px;cursor:pointer;white-space:nowrap}
.column-filter-menu-item:hover{background:#eef2f7}
.column-filter-menu-item .cfm-check{width:14px;color:#A37929;font-weight:700;visibility:hidden}
.column-filter-menu-item.is-active .cfm-check{visibility:visible}
.column-filter-menu-item.is-active{color:#1e4575;font-weight:700}
.active-column-filters-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
.column-filter-chip{display:flex;align-items:center;gap:6px;background:#f5f7fa;border:1.5px solid #d0d5dd;border-radius:8px;padding:5px 6px 5px 10px}
.column-filter-chip label{font-size:10px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
.column-filter-chip input,.column-filter-chip select{font-size:12px;padding:5px 7px;border:1.5px solid #d0d5dd;border-radius:6px;color:#344054;min-width:120px}
.column-filter-chip .cfm-remove{background:none;border:none;color:#8a9bad;cursor:pointer;font-size:15px;line-height:1;padding:2px 4px}
.column-filter-chip .cfm-remove:hover{color:#dc2626}
.clear-column-filters-btn{font-size:11px;font-weight:600;color:#1e4575;background:#eef2f7;border:1px solid #d0d5dd;border-radius:6px;padding:7px 12px;cursor:pointer;white-space:nowrap}
@media (max-width:768px){
  .column-filter-menu{left:0;right:0;min-width:0;width:100%;box-sizing:border-box}
  .active-column-filters-row{flex-direction:column;align-items:stretch}
  .column-filter-chip{width:100%;flex-wrap:wrap;box-sizing:border-box}
  .column-filter-chip label{flex:1 1 100%}
  .column-filter-chip input,.column-filter-chip select{flex:1 1 auto;min-width:0;width:100%}
  .clear-column-filters-btn{width:100%;text-align:center}
}
</style>

<div class="lc-header">
    <div style="position:relative;z-index:2;">
        <div class="lc-eyebrow">Client Database</div>
        <h1 class="lc-title">List of Clients</h1>
        <p class="lc-sub">
            <svg style="width:15px;height:15px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            All clients from the client database
        </p>
    </div>
    <div class="lc-deco">
        <div class="lc-circle lc-c1"></div>
        <div class="lc-circle lc-c2"></div>
        <div class="lc-circle lc-c3"></div>
    </div>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border-left:3px solid #22c55e;color:#16a34a;padding:10px 16px;border-radius:8px;font-size:13px;margin-bottom:16px;font-weight:500">&#10003; {{ session('success') }}</div>
@endif

<div class="lc-card">
    <div class="lc-head" style="flex-direction:column;align-items:stretch;gap:14px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <h2 style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">List of Clients</h2>
            <span class="lc-badge">{{ $clients->count() }} record{{ $clients->count() != 1 ? 's' : '' }}</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <div class="column-filter-dropdown" id="lcColumnFilterDropdown">
                <button type="button" class="column-filter-btn" onclick="toggleLcColumnFilterMenu(event)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Filter</span>
                    <span id="lcFilterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div id="lcColumnFilterMenu" class="column-filter-menu" style="display:none;"></div>
            </div>
            <div class="lc-search">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="lcSearch" placeholder="Search by name, email, phone..." oninput="lcApplyFilters()">
            </div>
        </div>
        <div id="lcActiveColumnFiltersRow" class="active-column-filters-row" style="display:none;"></div>
    </div>

    @if($clients->isEmpty())
    <div class="lc-empty">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:44px;height:44px;margin:0 auto 12px;display:block;opacity:.3;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <p style="font-size:13px;font-weight:500;">No clients in the database yet.</p>
    </div>
    @else
    <div class="lc-table-scroll" style="overflow-x:auto">
        <table class="lc-table" id="rcTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($clients as $i => $c)
            @php
                $trips = $tripData->get($c->client_name, collect());
                $emails = $trips->pluck('client_email')->filter()->unique()->values();
                $phones = $trips->map(fn($t) => trim(($t->client_phone_code ?? '+63').' '.ltrim($t->client_phone ?? '','0')))->filter()->unique()->values();
                $contact = $contactMap->get($c->client_name);
                $address = $contact?->address ?? '';
            @endphp
            <tr
                data-client="{{ strtolower($c->client_name ?? '') }}"
                data-email="{{ strtolower($emails->implode(', ')) }}"
                data-phone="{{ strtolower($phones->implode(', ')) }}"
                data-address="{{ strtolower($address ?? '') }}">
                <td style="color:#cbd5e1;font-size:11px;font-weight:600;">{{ $i + 1 }}</td>
                <td><div class="lc-name">{{ $c->client_name }}</div></td>
                <td>
                    @forelse($emails as $email)
                        <span class="lc-tag">{{ $email }}</span>
                    @empty <span class="lc-muted">—</span>
                    @endforelse
                </td>
                <td>
                    @forelse($phones as $phone)
                        <span class="lc-tag">{{ $phone }}</span>
                    @empty <span class="lc-muted">—</span>
                    @endforelse
                </td>
                <td><div class="lc-muted">{{ $address ?: '—' }}</div></td>
                <td>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <button type="button" class="lc-btn lc-btn-view" onclick="lcViewRow({{ $c->id }})">View</button>
                        <button class="lc-btn lc-btn-edit" onclick="openEdit('{{ addslashes($c->client_name) }}', '{{ addslashes($address) }}')">Edit</button>
                        <form method="POST" action="{{ route('client-database.destroy', $c->id) }}" onsubmit="return confirm('Delete {{ addslashes($c->client_name) }}?')" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="lc-btn lc-btn-del">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- View Client Details Modal --}}
<div id="lcViewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
    <div class="lc-view-modal-box" style="background:white;border-radius:16px;width:95%;max-width:960px;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);color:white;padding:20px 24px;border-radius:16px 16px 0 0;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;font-size:18px;font-weight:700">Commission Request Details</h3>
            <button onclick="document.getElementById('lcViewModal').style.display='none'" style="background:rgba(255,255,255,0.2);border:none;color:white;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:18px">✕</button>
        </div>
        <div style="padding:24px;max-height:70vh;overflow-y:auto;overflow-x:hidden;">
            <div class="lc-view-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;min-width:0;" id="lcViewContent"></div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end">
            <button onclick="document.getElementById('lcViewModal').style.display='none'" style="padding:10px 20px;background:#f3f4f6;color:#374151;border:2px solid #d0d5dd;border-radius:8px;font-weight:600;cursor:pointer">Close</button>
        </div>
    </div>
</div>

{{-- Edit Address Modal --}}
<div class="lc-modal" id="lcEditModal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="lc-modal-box">
        <div class="lc-modal-hdr">
            <div style="color:white;font-size:15px;font-weight:700;">Edit Client Info</div>
            <button onclick="document.getElementById('lcEditModal').classList.remove('open')" style="background:rgba(255,255,255,.15);border:none;color:white;width:28px;height:28px;border-radius:7px;cursor:pointer;font-size:16px;">&times;</button>
        </div>
        <div class="lc-modal-body">
            <form id="lcEditForm" method="POST" action="{{ route('clients.store') }}">
                @csrf
                <input type="hidden" name="_method" id="lcMethod" value="POST">
                <input type="hidden" name="name" id="lc_name">
                <div style="margin-bottom:14px;">
                    <label class="lc-label">Client Name</label>
                    <input class="lc-input" type="text" id="lc_name_display" readonly style="background:#f8fafc;color:#64748b;">
                </div>
                <div style="margin-bottom:18px;">
                    <label class="lc-label">Address</label>
                    <input class="lc-input" type="text" name="address" id="lc_address" placeholder="Enter address...">
                </div>
                <div style="display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" onclick="document.getElementById('lcEditModal').classList.remove('open')" style="padding:9px 18px;background:#f1f5f9;color:#374151;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Cancel</button>
                    <button type="submit" style="padding:9px 22px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var LC_FILTERABLE_FIELDS = [
    { key: 'client',  label: 'Client Name', dataAttr: 'data-client',  type: 'text' },
    { key: 'email',   label: 'Email',       dataAttr: 'data-email',   type: 'text' },
    { key: 'phone',   label: 'Phone',       dataAttr: 'data-phone',   type: 'text' },
    { key: 'address', label: 'Address',     dataAttr: 'data-address', type: 'text' },
];
var lcColumnFilters = {};

function lcFieldConfig(key) {
    return LC_FILTERABLE_FIELDS.find(function (f) { return f.key === key; });
}

function toggleLcColumnFilterMenu(e) {
    e.stopPropagation();
    var menu = document.getElementById('lcColumnFilterMenu');
    if (menu.style.display === 'block') { menu.style.display = 'none'; return; }
    renderLcColumnFilterMenu();
    menu.style.display = 'block';
}

function renderLcColumnFilterMenu() {
    var menu = document.getElementById('lcColumnFilterMenu');
    menu.innerHTML = '';
    LC_FILTERABLE_FIELDS.forEach(function (f) {
        var item = document.createElement('div');
        item.className = 'column-filter-menu-item' + (lcColumnFilters.hasOwnProperty(f.key) ? ' is-active' : '');
        item.innerHTML = '<span class="cfm-check">✓</span><span>' + f.label + '</span>';
        item.onclick = function (ev) { ev.stopPropagation(); lcToggleColumnFilter(f.key); };
        menu.appendChild(item);
    });
}

function lcToggleColumnFilter(key) {
    if (lcColumnFilters.hasOwnProperty(key)) delete lcColumnFilters[key];
    else lcColumnFilters[key] = '';
    renderLcColumnFilterMenu();
    renderLcActiveColumnFilters();
    updateLcFilterBadge();
    lcApplyFilters();
    document.getElementById('lcColumnFilterMenu').style.display = 'none';
}

function lcRemoveColumnFilter(key) {
    delete lcColumnFilters[key];
    renderLcActiveColumnFilters();
    updateLcFilterBadge();
    lcApplyFilters();
}

function updateLcFilterBadge() {
    var badge = document.getElementById('lcFilterCountBadge');
    var count = Object.keys(lcColumnFilters).length;
    badge.style.display = count > 0 ? 'inline-flex' : 'none';
    badge.textContent = count;
}

function renderLcActiveColumnFilters() {
    var row = document.getElementById('lcActiveColumnFiltersRow');
    var keys = Object.keys(lcColumnFilters);
    row.innerHTML = '';
    if (keys.length === 0) { row.style.display = 'none'; return; }
    row.style.display = 'flex';

    keys.forEach(function (key) {
        var f = lcFieldConfig(key);
        if (!f) return;
        var chip = document.createElement('div');
        chip.className = 'column-filter-chip';
        var label = document.createElement('label');
        label.textContent = f.label;
        chip.appendChild(label);

        var input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Search ' + f.label.toLowerCase() + '...';
        input.value = lcColumnFilters[key];
        input.oninput = function () { lcColumnFilters[key] = this.value; lcApplyFilters(); };
        chip.appendChild(input);

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'cfm-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function () { lcRemoveColumnFilter(key); };
        chip.appendChild(removeBtn);

        row.appendChild(chip);
    });

    var clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.className = 'clear-column-filters-btn';
    clearBtn.textContent = 'Clear Filters';
    clearBtn.onclick = function () {
        lcColumnFilters = {};
        renderLcActiveColumnFilters();
        updateLcFilterBadge();
        lcApplyFilters();
    };
    row.appendChild(clearBtn);
}

function lcMatchesColumnFilters(row) {
    for (var key in lcColumnFilters) {
        var f = lcFieldConfig(key);
        if (!f) continue;
        var filterVal = (lcColumnFilters[key] || '').toString().trim().toLowerCase();
        if (!filterVal) continue;
        var rowVal = (row.getAttribute(f.dataAttr) || '').toString().toLowerCase();
        if (!rowVal.includes(filterVal)) return false;
    }
    return true;
}

function lcApplyFilters() {
    var raw = (document.getElementById('lcSearch').value || '').toLowerCase().trim();
    var keywords = raw ? raw.split(/\s+/).filter(function (k) { return k.length > 0; }) : [];

    document.querySelectorAll('#rcTable tbody tr').forEach(function (row) {
        var text = row.textContent.toLowerCase();
        var keyMatch = keywords.length === 0 || keywords.every(function (k) { return text.includes(k); });
        var columnMatch = lcMatchesColumnFilters(row);
        row.style.display = (keyMatch && columnMatch) ? '' : 'none';
    });
}

document.addEventListener('click', function (e) {
    var dropdown = document.getElementById('lcColumnFilterDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('lcColumnFilterMenu').style.display = 'none';
    }
});

function lcViewRow(id) {
    fetch(`/sales-marketing/${id}`).then(r => r.json()).then(d => {
        var fmt = v => (v ?? '-'), fmtD = v => v ? new Date(v).toLocaleDateString('en-US', {month:'short', day:'2-digit', year:'numeric'}) : '-';
        var fmtP = v => v ? '₱' + parseFloat(v).toLocaleString('en-US', {minimumFractionDigits:2}) : '-';
        var fields = [
            ["Developer's Name", fmt(d.developer_name)],
            ['Project Name', fmt(d.project_name)],
            ['Block & Lot Number', fmt(d.block_lot_number)],
            ["Client's Name", fmt(d.client_name)],
            ['Lot Area', d.lot_area ? parseFloat(d.lot_area).toFixed(2) + ' sqm' : '-'],
            ['Price Per SQM', fmtP(d.price_sqm)],
            ['TCP', fmtP(d.tcp)],
            ['Discount', d.discount ? parseFloat(d.discount).toFixed(2) + '%' : '-'],
            ['Net TCP', fmtP(d.net_tcp)],
            ['Terms of Payment', fmt(d.terms_of_payment)],
            ['Reservation Date', fmtD(d.reservation_date)],
            ["Agent's Name", fmt(d.agent_name)],
            ['Client Status', fmt(d.client_status) || 'No Status'],
            ['Downpayment Status', fmt(d.downpayment_status) || '— Not Set —'],
            ['Downpayment Amount', fmtP(d.downpayment_amount)],
            ['Downpayment Terms', d.downpayment_terms ? d.downpayment_terms + ' month' + (d.downpayment_terms > 1 ? 's' : '') : '-'],
            ['Per Term Amount', fmtP(d.downpayment_per_term)],
            ['Date of Downpayment', fmtD(d.downpayment_date || d.date_of_downpayment)],
        ];
        document.getElementById('lcViewContent').innerHTML = fields.map(([l, v]) => `<div style="display:flex;flex-direction:column;gap:4px;min-width:0;"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">${l}</label><div style="font-size:14px;color:#374151;font-weight:500;padding:10px 14px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;overflow-wrap:break-word;word-break:break-word;">${v}</div></div>`).join('');
        document.getElementById('lcViewModal').style.display = 'flex';
    });
}

function openEdit(name, address) {
    document.getElementById('lc_name').value = name;
    document.getElementById('lc_name_display').value = name;
    document.getElementById('lc_address').value = address;
    // Check if client record exists
    document.getElementById('lcEditModal').classList.add('open');
}
</script>
@endsection
