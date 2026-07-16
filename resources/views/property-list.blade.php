@extends('layouts.dashboard')
@section('title', 'List of Properties')
@section('content')
<style>
.pl-header{background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:16px;padding:28px 32px;margin-bottom:24px;position:relative;overflow:hidden;box-shadow:0 6px 24px rgba(30,69,117,.2)}
.pl-header h1{font-size:22px;font-weight:700;color:white;margin:0 0 4px;position:relative;z-index:2}
.pl-header p{font-size:13px;color:rgba(255,255,255,.7);margin:0;position:relative;z-index:2}
.pl-deco{position:absolute;top:-40px;right:-40px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.05)}
.pl-card{background:white;border-radius:12px;border:1px solid #e8ecf0;box-shadow:0 1px 4px rgba(0,0,0,.05);overflow:hidden}
.pl-toolbar{padding:14px 18px;display:flex;align-items:center;gap:10px;border-bottom:1px solid #f1f5f9;flex-wrap:wrap}
.pl-search{position:relative;flex:1;min-width:200px}
.pl-search input{width:100%;padding:8px 12px 8px 34px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;color:#374151;background:#f8fafc}
.pl-search input:focus{outline:none;border-color:#1e4575;background:white}
.pl-search svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#94a3b8}
.pl-select{padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;color:#374151;background:white;cursor:pointer}
.pl-table{width:100%;border-collapse:collapse}
.pl-table thead tr{background:#1e4575}
.pl-table thead th{padding:11px 16px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.7px;white-space:nowrap;position:sticky;top:0;background:#1e4575;z-index:4;box-shadow:0 2px 4px -2px rgba(0,0,0,.25)}
.pl-table tbody tr{border-bottom:1px solid #f1f5f9;transition:background .15s}
.pl-table tbody tr:hover{background:#f8fafc}
.pl-table tbody tr:last-child{border-bottom:none}
.pl-table td{padding:11px 16px;font-size:13px;color:#374151;vertical-align:middle;white-space:nowrap}
.pl-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700}
.pl-badge-released{background:#dcfce7;color:#166534}
.pl-badge-pending{background:#fef3c7;color:#92400e}
.pl-badge-cancelled{background:#fee2e2;color:#991b1b}
.pl-empty{text-align:center;padding:48px;color:#94a3b8;font-size:13px}
.pl-count{font-size:12px;color:#94a3b8;margin-left:auto}
.pl-table-scroll{overflow-x:auto !important;overflow-y:visible !important;max-height:none !important;}

/* ---- Filter dropdown + chips (matches other pages' pattern) ---- */
.column-filter-dropdown{position:relative}
.column-filter-btn{display:inline-flex;align-items:center;gap:6px;white-space:nowrap;font-size:13px;font-weight:600;color:#1e4575;background:white;border:2px solid #1e4575;border-radius:8px;padding:8px 13px;cursor:pointer;height:36px;box-sizing:border-box;transition:all .2s ease}
.column-filter-btn:hover{background:#eef2f7}
.filter-count-badge{background:#A37929;color:white;font-size:11px;font-weight:700;border-radius:999px;min-width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;padding:0 5px}
.column-filter-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:200px;max-height:300px;overflow-y:auto;background:white;border:1.5px solid #d0d5dd;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:500;padding:6px}
.column-filter-menu-item{display:flex;align-items:center;gap:8px;padding:8px 10px;font-size:12px;font-weight:500;color:#344054;border-radius:6px;cursor:pointer;white-space:nowrap}
.column-filter-menu-item:hover{background:#eef2f7}
.column-filter-menu-item .cfm-check{width:14px;color:#A37929;font-weight:700;visibility:hidden}
.column-filter-menu-item.is-active .cfm-check{visibility:visible}
.column-filter-menu-item.is-active{color:#1e4575;font-weight:700}
.active-column-filters-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:0 18px 14px;}
.column-filter-chip{display:flex;align-items:center;gap:6px;background:#f5f7fa;border:1.5px solid #d0d5dd;border-radius:8px;padding:5px 6px 5px 10px}
.column-filter-chip label{font-size:10px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
.column-filter-chip input{font-size:12px;padding:5px 7px;border:1.5px solid #d0d5dd;border-radius:6px;color:#344054;min-width:120px}
.column-filter-chip .cfm-remove{background:none;border:none;color:#8a9bad;cursor:pointer;font-size:15px;line-height:1;padding:2px 4px}
.column-filter-chip .cfm-remove:hover{color:#dc2626}
.clear-column-filters-btn{font-size:11px;font-weight:600;color:#1e4575;background:#eef2f7;border:1px solid #d0d5dd;border-radius:6px;padding:7px 12px;cursor:pointer;white-space:nowrap}
@media (max-width:768px){
  .column-filter-menu{left:0;right:0;min-width:0;width:100%;box-sizing:border-box}
  .active-column-filters-row{flex-direction:column;align-items:stretch}
  .column-filter-chip{width:100%;flex-wrap:wrap;box-sizing:border-box}
  .column-filter-chip label{flex:1 1 100%}
  .column-filter-chip input{flex:1 1 auto;min-width:0;width:100%}
  .clear-column-filters-btn{width:100%;text-align:center}
}
</style>

<div class="pl-header">
    <div class="pl-deco"></div>
    <h1>List of Properties</h1>
    <p>Reserved properties from the client database</p>
</div>

<div class="pl-card">
    <div class="pl-toolbar">
        <div class="column-filter-dropdown" id="plColumnFilterDropdown">
            <button type="button" class="column-filter-btn" onclick="togglePlColumnFilterMenu(event)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                <span>Filter</span>
                <span id="plFilterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div id="plColumnFilterMenu" class="column-filter-menu" style="display:none;"></div>
        </div>
        <div class="pl-search">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="plSearch" placeholder="Search property, client, developer..." oninput="plApplyFilters()">
        </div>
        <select class="pl-select" id="plStatus" style="display:none;"></select>
        <span class="pl-count" id="plCount">{{ $properties->count() }} records</span>
    </div>
    <div id="plActiveColumnFiltersRow" class="active-column-filters-row" style="display:none;"></div>
    <div class="pl-table-scroll" style="overflow-x:auto;">
    <table class="pl-table js-sort-table" id="plTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Project Name</th>
                <th>Developer</th>
                <th>Block / Lot</th>
                <th>Lot Area (sqm)</th>
                <th>Client Name</th>
            </tr>
        </thead>
        <tbody id="plBody">
            @forelse($properties as $i => $p)
            <tr
                data-project="{{ strtolower($p->project_name ?? '') }}"
                data-developer="{{ strtolower($p->developer_name ?? '') }}"
                data-block-lot="{{ strtolower($p->block_lot_number ?? '') }}"
                data-lot-area="{{ $p->lot_area ?? '' }}"    
                data-client="{{ strtolower($p->client_name ?? '') }}">  
                <td style="color:#cbd5e1;font-weight:600;text-align:center;">{{ $i + 1 }}</td>
                <td style="font-weight:600;color:#0f172a;">{{ $p->project_name }}</td>
                <td style="color:#64748b;">{{ $p->developer_name ?: '—' }}</td>
                <td>{{ $p->block_lot_number ?: '—' }}</td>
                <td>{{ $p->lot_area ? number_format($p->lot_area, 2) : '—' }}</td>
                <td style="font-weight:600;color:#1e4575;">{{ $p->client_name }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="pl-empty">No properties on record.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<script>
var PL_FILTERABLE_FIELDS = [
    { key: 'project',   label: 'Project Name',     dataAttr: 'data-project',   type: 'text' },
    { key: 'developer', label: 'Developer',        dataAttr: 'data-developer', type: 'text' },
    { key: 'block-lot',  label: 'Block/Lot',        dataAttr: 'data-block-lot', type: 'text' },
    { key: 'lot-area',  label: 'Lot Area (sqm)',   dataAttr: 'data-lot-area',  type: 'text' },
    { key: 'client',    label: 'Client Name',      dataAttr: 'data-client',    type: 'text' },
];
var plColumnFilters = {};

function plFieldConfig(key) {
    return PL_FILTERABLE_FIELDS.find(function (f) { return f.key === key; });
}

function togglePlColumnFilterMenu(e) {
    e.stopPropagation();
    var menu = document.getElementById('plColumnFilterMenu');
    if (menu.style.display === 'block') { menu.style.display = 'none'; return; }
    renderPlColumnFilterMenu();
    menu.style.display = 'block';
}

function renderPlColumnFilterMenu() {
    var menu = document.getElementById('plColumnFilterMenu');
    menu.innerHTML = '';
    PL_FILTERABLE_FIELDS.forEach(function (f) {
        var item = document.createElement('div');
        item.className = 'column-filter-menu-item' + (plColumnFilters.hasOwnProperty(f.key) ? ' is-active' : '');
        item.innerHTML = '<span class="cfm-check">✓</span><span>' + f.label + '</span>';
        item.onclick = function (ev) { ev.stopPropagation(); plToggleColumnFilter(f.key); };
        menu.appendChild(item);
    });
}

function plToggleColumnFilter(key) {
    if (plColumnFilters.hasOwnProperty(key)) delete plColumnFilters[key];
    else plColumnFilters[key] = '';
    renderPlColumnFilterMenu();
    renderPlActiveColumnFilters();
    updatePlFilterBadge();
    plApplyFilters();
    document.getElementById('plColumnFilterMenu').style.display = 'none';
}

function plRemoveColumnFilter(key) {
    delete plColumnFilters[key];
    renderPlActiveColumnFilters();
    updatePlFilterBadge();
    plApplyFilters();
}

function updatePlFilterBadge() {
    var badge = document.getElementById('plFilterCountBadge');
    var count = Object.keys(plColumnFilters).length;
    badge.style.display = count > 0 ? 'inline-flex' : 'none';
    badge.textContent = count;
}

function renderPlActiveColumnFilters() {
    var row = document.getElementById('plActiveColumnFiltersRow');
    var keys = Object.keys(plColumnFilters);
    row.innerHTML = '';
    if (keys.length === 0) { row.style.display = 'none'; return; }
    row.style.display = 'flex';

    keys.forEach(function (key) {
        var f = plFieldConfig(key);
        if (!f) return;
        var chip = document.createElement('div');
        chip.className = 'column-filter-chip';
        var label = document.createElement('label');
        label.textContent = f.label;
        chip.appendChild(label);

        var input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Search ' + f.label.toLowerCase() + '...';
        input.value = plColumnFilters[key];
        input.oninput = function () { plColumnFilters[key] = this.value; plApplyFilters(); };
        chip.appendChild(input);

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'cfm-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function () { plRemoveColumnFilter(key); };
        chip.appendChild(removeBtn);

        row.appendChild(chip);
    });

    var clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.className = 'clear-column-filters-btn';
    clearBtn.textContent = 'Clear Filters';
    clearBtn.onclick = function () {
        plColumnFilters = {};
        renderPlActiveColumnFilters();
        updatePlFilterBadge();
        plApplyFilters();
    };
    row.appendChild(clearBtn);
}

function plMatchesColumnFilters(row) {
    for (var key in plColumnFilters) {
        var f = plFieldConfig(key);
        if (!f) continue;
        var filterVal = (plColumnFilters[key] || '').toString().trim().toLowerCase();
        if (!filterVal) continue;
        var rowVal = (row.getAttribute(f.dataAttr) || '').toString().toLowerCase();
        if (!rowVal.includes(filterVal)) return false;
    }
    return true;
}

function plApplyFilters() {
    var raw = (document.getElementById('plSearch').value || '').toLowerCase().trim();
    var keywords = raw ? raw.split(/\s+/).filter(function (k) { return k.length > 0; }) : [];
    var visible = 0;

    document.querySelectorAll('#plBody tr').forEach(function (row) {
        if (!row.hasAttribute('data-project')) return; // skip empty-state row
        var text = row.textContent.toLowerCase();
        var keyMatch = keywords.length === 0 || keywords.every(function (k) { return text.includes(k); });
        var columnMatch = plMatchesColumnFilters(row);
        var show = keyMatch && columnMatch;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    var countEl = document.getElementById('plCount');
    if (countEl) countEl.textContent = visible + ' record' + (visible !== 1 ? 's' : '');
}

document.addEventListener('click', function (e) {
    var dropdown = document.getElementById('plColumnFilterDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('plColumnFilterMenu').style.display = 'none';
    }
});
</script>
@endsection
