@extends('layouts.dashboard')
@section('title', 'Employee Data')
@section('content')

<style>
.hr-banner { background:linear-gradient(135deg,#0f2444 0%,#1e4575 50%,#2563eb 100%);border-radius:20px;padding:36px 40px;margin-bottom:28px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25); }
.hr-banner-content { position:relative;z-index:2; }
.hr-banner-label { font-size:11px;font-weight:700;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px; }
.hr-banner h1 { font-size:30px;font-weight:800;color:white;margin:0 0 6px; }
.hr-banner p { font-size:14px;color:rgba(255,255,255,.7);margin:0; }
.hr-banner-deco { position:absolute;top:0;right:0;width:320px;height:100%;pointer-events:none; }
.hr-banner-deco span { position:absolute;border-radius:50%;background:rgba(255,255,255,.06); }

.hr-card { background:white;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);margin-bottom:20px;overflow:hidden; }
.hr-card-header { padding:12px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between; }
.hr-card-title { font-size:14px;font-weight:700;color:#0f172a; }
.hr-card-sub { font-size:12px;color:#94a3b8;margin-top:2px; }
.hr-card-body { padding:16px; }

.hr-form-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px; }
.hr-form-group label { display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px; }
.hr-input { width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;color:#0f172a;background:#fff;outline:none;transition:border-color .2s;box-sizing:border-box; }
.hr-input:focus { border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1); }

.hr-table { width:100%;border-collapse:collapse;font-size:13px; }
.hr-table thead tr { background:linear-gradient(135deg,#0f2444,#1e4575); }
.hr-table thead th { padding:8px 12px;text-align:left;font-size:11px;font-weight:700;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.6px; }
.hr-table tbody tr { border-bottom:1px solid #f1f5f9;transition:background .15s; }
.hr-table tbody tr:hover { background:#f8fafc; }
.hr-table tbody td { padding:9px 12px;color:#374151; }
.hr-table tbody tr.edit-row { background:#eff6ff; }

.hr-badge { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700; }
.hr-badge-admin { background:#dbeafe;color:#1e4575; }
.hr-badge-staff { background:#f1f5f9;color:#64748b; }
.hr-badge-active { background:#dcfce7;color:#166534; }
.hr-badge-pending { background:#fef3c7;color:#92400e; }

.hr-btn { padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;border:none;transition:all .2s; }
.hr-btn-primary { background:linear-gradient(135deg,#1e4575,#2563eb);color:white; }
.hr-btn-primary:hover { opacity:.9; }
.hr-btn-danger { background:#fee2e2;color:#991b1b; }
.hr-btn-danger:hover { background:#fecaca; }
.hr-btn-ghost { background:#f1f5f9;color:#374151; }
.hr-btn-ghost:hover { background:#e2e8f0; }
.hr-btn-lg { padding:10px 24px;font-size:13px; }

/* ---- Filter dropdown + search bar (matches Client Database / Reserved Clients pattern) ---- */
.emp-search{position:relative}
.emp-search svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#94a3b8}
.emp-search input{padding:8px 12px 8px 34px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;color:#111827;background:white;width:280px;transition:all .2s;box-sizing:border-box}
.emp-search input:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.column-filter-dropdown{position:relative}
.column-filter-btn{display:inline-flex;align-items:center;gap:6px;white-space:nowrap;font-size:12px;font-weight:600;color:#1e4575;background:white;border:2px solid #1e4575;border-radius:8px;padding:8px 13px;cursor:pointer;height:34px;box-sizing:border-box;transition:all .2s ease}
.column-filter-btn:hover{background:#eef2f7}
.filter-count-badge{background:#2563eb;color:white;font-size:10px;font-weight:700;border-radius:999px;min-width:16px;height:16px;display:inline-flex;align-items:center;justify-content:center;padding:0 5px}
.column-filter-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:200px;max-height:300px;overflow-y:auto;background:white;border:1.5px solid #d0d5dd;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:500;padding:6px}
.column-filter-menu-item{display:flex;align-items:center;gap:8px;padding:8px 10px;font-size:12px;font-weight:500;color:#344054;border-radius:6px;cursor:pointer;white-space:nowrap}
.column-filter-menu-item:hover{background:#eef2f7}
.column-filter-menu-item .cfm-check{width:14px;color:#2563eb;font-weight:700;visibility:hidden}
.column-filter-menu-item.is-active .cfm-check{visibility:visible}
.column-filter-menu-item.is-active{color:#1e4575;font-weight:700}
.active-column-filters-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:0 16px 14px;}
.column-filter-chip{display:flex;align-items:center;gap:6px;background:#f5f7fa;border:1.5px solid #d0d5dd;border-radius:8px;padding:5px 6px 5px 10px}
.column-filter-chip label{font-size:10px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
.column-filter-chip input,.column-filter-chip select{font-size:12px;padding:5px 7px;border:1.5px solid #d0d5dd;border-radius:6px;color:#344054;min-width:120px}
.column-filter-chip .cfm-remove{background:none;border:none;color:#8a9bad;cursor:pointer;font-size:15px;line-height:1;padding:2px 4px}
.column-filter-chip .cfm-remove:hover{color:#dc2626}
.clear-column-filters-btn{font-size:11px;font-weight:600;color:#1e4575;background:#eef2f7;border:1px solid #d0d5dd;border-radius:6px;padding:7px 12px;cursor:pointer;white-space:nowrap}
.hr-filters-bar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:0 16px 14px;}
@media (max-width:768px){
  .column-filter-menu{left:0;right:0;min-width:0;width:100%;box-sizing:border-box}
  .active-column-filters-row{flex-direction:column;align-items:stretch}
  .column-filter-chip{width:100%;flex-wrap:wrap;box-sizing:border-box}
  .column-filter-chip label{flex:1 1 100%}
  .column-filter-chip input,.column-filter-chip select{flex:1 1 auto;min-width:0;width:100%}
  .clear-column-filters-btn{width:100%;text-align:center}
  .emp-search input{width:100%}
}
</style>

<div class="hr-banner">
    <div class="hr-banner-content">
        <div class="hr-banner-label">Human Resource</div>
        <h1>Employee Data</h1>
        <p>Manage and update employment details for all active users</p>
    </div>
    <div class="hr-banner-deco">
        <span style="width:220px;height:220px;top:-60px;right:-40px;"></span>
        <span style="width:140px;height:140px;bottom:-30px;right:100px;"></span>
    </div>
</div>

@if(session('emp_success') || session('success'))
<div style="background:#d1fae5;color:#065f46;padding:12px 18px;border-radius:10px;margin-bottom:20px;font-weight:600;display:flex;align-items:center;gap:8px;">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('emp_success') ?? session('success') }}
</div>
@endif

@php $isAdmin = auth()->user()->isAdmin(); @endphp

{{-- Add New Employee --}}
<div class="hr-card">
    <div class="hr-card-header">
        <div>
            <div class="hr-card-title">Add New Employee</div>
            <div class="hr-card-sub">Pre-register an employee record</div>
        </div>
    </div>
    <div class="hr-card-body">
        <form method="POST" action="{{ route('settings.employee.add') }}">@csrf
            <div class="hr-form-grid">
                <div class="hr-form-group"><label>Name <span style="color:#ef4444;">*</span></label><input class="hr-input" type="text" name="name" required placeholder="Full name"></div>
                <div class="hr-form-group"><label>Position <span style="color:#ef4444;">*</span></label><input class="hr-input" type="text" name="position" required placeholder="Job title"></div>
                <div class="hr-form-group"><label>Employee ID <span style="color:#ef4444;">*</span></label><input class="hr-input" type="text" name="employee_id" required placeholder="e.g. 0050"></div>
                <div class="hr-form-group"><label>Date Hired <span style="color:#ef4444;">*</span></label><input class="hr-input" type="date" name="date_hired" required></div>
            </div>
            <div style="margin-top:18px;">
                <button type="submit" class="hr-btn hr-btn-primary hr-btn-lg">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Employee
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Employee Records --}}
<div class="hr-card">
    <div class="hr-card-header">
        <div>
            <div class="hr-card-title">Employee Records</div>
            <div class="hr-card-sub"><span id="empRecordCount">{{ $activeUsers->count() }}</span> employees on record</div>
        </div>
    </div>
    <div class="hr-filters-bar">
        <div class="column-filter-dropdown" id="empColumnFilterDropdown">
            <button type="button" class="column-filter-btn" onclick="toggleEmpColumnFilterMenu(event)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                <span>Filter</span>
                <span id="empFilterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div id="empColumnFilterMenu" class="column-filter-menu" style="display:none;"></div>
        </div>
        <div class="emp-search">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="empSearch" placeholder="Search by name, email, position, ID..." oninput="empApplyFilters()">
        </div>
    </div>
    <div id="empActiveColumnFiltersRow" class="active-column-filters-row" style="display:none;"></div>
    <div style="overflow-x:auto;">
        @if($activeUsers->isEmpty())
            <div style="padding:40px;text-align:center;color:#94a3b8;">No employees yet.</div>
        @else
        <table class="hr-table" id="empTable">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Email</th><th>Position</th><th>Employee ID</th><th>Date Hired</th><th>Role</th><th>Status</th>
                    @if($isAdmin)<th>Actions</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($activeUsers as $i => $u)
                <tr id="emp-view-{{ $u->id }}"
                    class="emp-row"
                    data-name="{{ strtolower($u->name ?? '') }}"
                    data-email="{{ strtolower($u->email ?? '') }}"
                    data-position="{{ strtolower($u->position ?? '') }}"
                    data-empid="{{ strtolower($u->employee_id ?? '') }}"
                    data-datehired="{{ strtolower($u->date_hired ? $u->date_hired->format('M d, Y') : '') }}"
                    data-role="{{ strtolower($u->role ?? 'staff') }}"
                    data-status="{{ strtolower($u->status ?? '') }}">
                    <td style="color:#cbd5e1;font-weight:600;">{{ $i + 1 }}</td>
                    <td style="font-weight:700;color:#0f172a;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#1e4575,#2563eb);display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700;flex-shrink:0;">{{ strtoupper(substr($u->name,0,1)) }}</div>
                            {{ $u->name }}
                        </div>
                    </td>
                    <td style="color:#64748b;font-size:12px;">{{ $u->email }}</td>
                    <td>{{ $u->position ?: '—' }}</td>
                    <td><span style="font-family:monospace;background:#f1f5f9;padding:2px 8px;border-radius:6px;font-size:12px;">{{ $u->employee_id ?: '—' }}</span></td>
                    <td>{{ $u->date_hired ? $u->date_hired->format('M d, Y') : '—' }}</td>
                    <td><span class="hr-badge {{ $u->role === 'admin' ? 'hr-badge-admin' : 'hr-badge-staff' }}">{{ ucfirst($u->role ?? 'staff') }}</span></td>
                    <td><span class="hr-badge {{ $u->status === 'active' ? 'hr-badge-active' : 'hr-badge-pending' }}">{{ ucfirst($u->status ?? '—') }}</span></td>
                    @if($isAdmin)
                    <td style="white-space:nowrap;">
                        <button type="button" class="hr-btn hr-btn-primary" onclick="toggleEmpEdit({{ $u->id }})">Edit</button>
                        @if($u->id !== auth()->id())
                        <form method="POST" action="{{ route('settings.users.remove', $u->id) }}" style="display:inline;" onsubmit="return confirm('Remove {{ addslashes($u->name) }}?')">@csrf @method('DELETE')
                            <button type="submit" class="hr-btn hr-btn-danger">Delete</button>
                        </form>
                        @endif
                    </td>
                    @endif
                </tr>
                <tr id="emp-edit-{{ $u->id }}" class="edit-row" style="display:none;">
                    <td colspan="{{ $isAdmin ? 9 : 8 }}" style="padding:16px 20px;">
                        <form method="POST" action="{{ route('settings.users.employee-info', $u->id) }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;background:#eff6ff;padding:16px;border-radius:10px;border:1.5px solid #bfdbfe;">@csrf
                            <div style="flex:1.5;min-width:130px;"><label style="font-size:11px;font-weight:700;color:#1e4575;display:block;margin-bottom:4px;">Position</label><input class="hr-input" type="text" name="position" value="{{ $u->position }}"></div>
                            <div style="flex:1;min-width:110px;"><label style="font-size:11px;font-weight:700;color:#1e4575;display:block;margin-bottom:4px;">Employee ID</label><input class="hr-input" type="text" name="employee_id" value="{{ $u->employee_id }}"></div>
                            <div style="flex:1;min-width:140px;"><label style="font-size:11px;font-weight:700;color:#1e4575;display:block;margin-bottom:4px;">Date Hired</label><input class="hr-input" type="date" name="date_hired" value="{{ $u->date_hired ? $u->date_hired->format('Y-m-d') : '' }}"></div>
                            <div style="display:flex;gap:8px;flex-shrink:0;">
                                <button type="submit" class="hr-btn hr-btn-primary">Save</button>
                                <button type="button" class="hr-btn hr-btn-ghost" onclick="toggleEmpEdit({{ $u->id }})">Cancel</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

<script>
function toggleEmpEdit(id) {
    var edit = document.getElementById('emp-edit-' + id);
    edit.style.display = edit.style.display === 'none' ? 'table-row' : 'none';
}

/* ---- Filter dropdown + search bar ---- */
var EMP_FILTERABLE_FIELDS = [
    { key: 'name',       label: 'Name',        dataAttr: 'data-name' },
    { key: 'email',      label: 'Email',       dataAttr: 'data-email' },
    { key: 'position',   label: 'Position',    dataAttr: 'data-position' },
    { key: 'empid',      label: 'Employee ID', dataAttr: 'data-empid' },
    { key: 'datehired',  label: 'Date Hired',  dataAttr: 'data-datehired' },
    { key: 'role',       label: 'Role',        dataAttr: 'data-role' },
    { key: 'status',     label: 'Status',      dataAttr: 'data-status' },
];
var empColumnFilters = {};

function empFieldConfig(key) {
    return EMP_FILTERABLE_FIELDS.find(function (f) { return f.key === key; });
}

function toggleEmpColumnFilterMenu(e) {
    e.stopPropagation();
    var menu = document.getElementById('empColumnFilterMenu');
    if (menu.style.display === 'block') { menu.style.display = 'none'; return; }
    renderEmpColumnFilterMenu();
    menu.style.display = 'block';
}

function renderEmpColumnFilterMenu() {
    var menu = document.getElementById('empColumnFilterMenu');
    menu.innerHTML = '';
    EMP_FILTERABLE_FIELDS.forEach(function (f) {
        var item = document.createElement('div');
        item.className = 'column-filter-menu-item' + (empColumnFilters.hasOwnProperty(f.key) ? ' is-active' : '');
        item.innerHTML = '<span class="cfm-check">✓</span><span>' + f.label + '</span>';
        item.onclick = function (ev) { ev.stopPropagation(); empToggleColumnFilter(f.key); };
        menu.appendChild(item);
    });
}

function empToggleColumnFilter(key) {
    if (empColumnFilters.hasOwnProperty(key)) delete empColumnFilters[key];
    else empColumnFilters[key] = '';
    renderEmpColumnFilterMenu();
    renderEmpActiveColumnFilters();
    updateEmpFilterBadge();
    empApplyFilters();
    document.getElementById('empColumnFilterMenu').style.display = 'none';
}

function empRemoveColumnFilter(key) {
    delete empColumnFilters[key];
    renderEmpActiveColumnFilters();
    updateEmpFilterBadge();
    empApplyFilters();
}

function updateEmpFilterBadge() {
    var badge = document.getElementById('empFilterCountBadge');
    var count = Object.keys(empColumnFilters).length;
    badge.style.display = count > 0 ? 'inline-flex' : 'none';
    badge.textContent = count;
}

function renderEmpActiveColumnFilters() {
    var row = document.getElementById('empActiveColumnFiltersRow');
    var keys = Object.keys(empColumnFilters);
    row.innerHTML = '';
    if (keys.length === 0) { row.style.display = 'none'; return; }
    row.style.display = 'flex';

    keys.forEach(function (key) {
        var f = empFieldConfig(key);
        if (!f) return;
        var chip = document.createElement('div');
        chip.className = 'column-filter-chip';
        var label = document.createElement('label');
        label.textContent = f.label;
        chip.appendChild(label);

        var input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Search ' + f.label.toLowerCase() + '...';
        input.value = empColumnFilters[key];
        input.oninput = function () { empColumnFilters[key] = this.value; empApplyFilters(); };
        chip.appendChild(input);

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'cfm-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function () { empRemoveColumnFilter(key); };
        chip.appendChild(removeBtn);

        row.appendChild(chip);
    });

    var clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.className = 'clear-column-filters-btn';
    clearBtn.textContent = 'Clear Filters';
    clearBtn.onclick = function () {
        empColumnFilters = {};
        renderEmpActiveColumnFilters();
        updateEmpFilterBadge();
        empApplyFilters();
    };
    row.appendChild(clearBtn);
}

function empMatchesColumnFilters(row) {
    for (var key in empColumnFilters) {
        var f = empFieldConfig(key);
        if (!f) continue;
        var filterVal = (empColumnFilters[key] || '').toString().trim().toLowerCase();
        if (!filterVal) continue;
        var rowVal = (row.getAttribute(f.dataAttr) || '').toString().toLowerCase();
        if (!rowVal.includes(filterVal)) return false;
    }
    return true;
}

function empApplyFilters() {
    var raw = (document.getElementById('empSearch').value || '').toLowerCase().trim();
    var keywords = raw ? raw.split(/\s+/).filter(function (k) { return k.length > 0; }) : [];
    var visibleCount = 0;

    document.querySelectorAll('#empTable tbody tr.emp-row').forEach(function (row) {
        var text = row.textContent.toLowerCase();
        var keyMatch = keywords.length === 0 || keywords.every(function (k) { return text.includes(k); });
        var columnMatch = empMatchesColumnFilters(row);
        var show = keyMatch && columnMatch;
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;

        var id = row.id ? row.id.replace('emp-view-', '') : null;
        if (id) {
            var editRow = document.getElementById('emp-edit-' + id);
            if (editRow && !show) editRow.style.display = 'none';
        }
    });

    var countEl = document.getElementById('empRecordCount');
    if (countEl) countEl.textContent = visibleCount;
}

document.addEventListener('click', function (e) {
    var dropdown = document.getElementById('empColumnFilterDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        var menu = document.getElementById('empColumnFilterMenu');
        if (menu) menu.style.display = 'none';
    }
});
</script>
@endsection
