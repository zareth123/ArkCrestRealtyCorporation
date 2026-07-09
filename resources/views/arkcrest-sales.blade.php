@extends('layouts.dashboard')
@section('title', 'ARC Sales')
@section('content')
<style>
.arc-wrap{padding:24px 30px;overflow-x:hidden}
.arc-header{background:linear-gradient(135deg,#1e4575 0%,#2563eb 100%);border-radius:16px;padding:36px 40px;margin-bottom:24px;color:white}
.arc-header h1{font-size:24px;font-weight:700;margin:0 0 4px}
.arc-header p{font-size:13px;color:rgba(255,255,255,.7);margin:0}
.arc-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
.arc-card{background:white;border-radius:12px;padding:20px;border:1px solid #e8ecf0;box-shadow:0 1px 4px rgba(0,0,0,.05)}
.arc-card-label{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px}
.arc-card-value{font-size:22px;font-weight:800;color:#0f172a}
.arc-table-wrap{background:white;border-radius:12px;border:1px solid #e8ecf0;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05)}
.arc-table-scroll{overflow-x:auto !important;overflow-y:visible !important;max-height:none !important;}
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

/* ---- Search + Column Filter bar (matches the Commission Monitoring / All Expenses pattern) ---- */
.arc-filters-bar{display:flex;flex-direction:column;gap:14px;margin-bottom:20px}
.arc-filters-row{display:flex;align-items:center;gap:10px;width:100%;max-width:560px}
.column-filter-dropdown{position:relative}
.column-filter-btn{display:inline-flex;align-items:center;gap:6px;white-space:nowrap;font-size:13px;font-weight:600;color:#1e4575;background:white;border:2px solid #1e4575;border-radius:8px;padding:9px 14px;cursor:pointer;height:40px;box-sizing:border-box;transition:all .2s ease}
.column-filter-btn:hover{background:#eef2f7}
.filter-count-badge{background:#A37929;color:white;font-size:11px;font-weight:700;border-radius:999px;min-width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;padding:0 5px}
.column-filter-menu{position:absolute;top:calc(100% + 6px);left:0;min-width:220px;max-height:320px;overflow-y:auto;background:white;border:1.5px solid #d0d5dd;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:500;padding:6px}
.column-filter-menu-item{display:flex;align-items:center;gap:8px;padding:9px 10px;font-size:13px;font-weight:500;color:#344054;border-radius:6px;cursor:pointer;white-space:nowrap}
.column-filter-menu-item:hover{background:#eef2f7}
.column-filter-menu-item .cfm-check{width:14px;color:#A37929;font-weight:700;visibility:hidden}
.column-filter-menu-item.is-active .cfm-check{visibility:visible}
.column-filter-menu-item.is-active{color:#1e4575;font-weight:700}
.search-box-inline{position:relative;display:flex;align-items:center;width:100%}
.search-box-inline svg{position:absolute;left:10px;width:16px;height:16px;color:#9ca3af;pointer-events:none}
.search-box-inline input{padding:8px 12px 8px 34px;border:1.5px solid #d0d5dd;border-radius:8px;font-size:13px;width:100%;transition:border-color .2s;color:#374151}
.search-box-inline input:focus{outline:none;border-color:#1e4575;box-shadow:0 0 0 3px rgba(30,69,117,.1)}
.active-column-filters-row{display:flex;flex-wrap:wrap;align-items:center;gap:10px}
.column-filter-chip{display:flex;align-items:center;gap:6px;background:#f5f7fa;border:1.5px solid #d0d5dd;border-radius:8px;padding:6px 8px 6px 12px}
.column-filter-chip label{font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
.column-filter-chip input,.column-filter-chip select{font-size:13px;padding:6px 8px;border:1.5px solid #d0d5dd;border-radius:6px;color:#344054;min-width:130px}
.column-filter-chip .cfm-remove{background:none;border:none;color:#8a9bad;cursor:pointer;font-size:16px;line-height:1;padding:2px 4px}
.column-filter-chip .cfm-remove:hover{color:#dc2626}
.clear-column-filters-btn{font-size:12px;font-weight:600;color:#1e4575;background:#eef2f7;border:1px solid #d0d5dd;border-radius:6px;padding:8px 14px;cursor:pointer;white-space:nowrap}

@media (max-width:768px){
  .arc-filters-row{max-width:100%;flex-direction:column;align-items:stretch}
  .column-filter-dropdown{width:100%}
  .column-filter-btn{width:100%;justify-content:center}
  .column-filter-menu{left:0;right:0;min-width:0;width:100%;box-sizing:border-box}
  .active-column-filters-row{flex-direction:column;align-items:stretch}
  .column-filter-chip{width:100%;flex-wrap:wrap;box-sizing:border-box}
  .column-filter-chip label{flex:1 1 100%}
  .column-filter-chip input,.column-filter-chip select{flex:1 1 auto;min-width:0;width:100%}
  .clear-column-filters-btn{width:100%;text-align:center}
}
</style>

<div class="arc-wrap">

    {{-- Header --}}
    <div style="background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:28px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25);">
        <div style="position:absolute;top:-40px;right:-40px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.06);"></div>
        <div style="position:absolute;top:40px;right:120px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,.04);"></div>
        <div style="position:relative;z-index:2;">
            <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;">Finance</div>
            <h1 style="font-size:28px;font-weight:700;color:white;margin:0 0 8px;">ARC Sales</h1>
            <p style="font-size:14px;color:rgba(255,255,255,.75);margin:0;">ArkCrest commission income from released agent commissions</p>
        </div>
    </div>
    {{-- Summary Cards --}}
    <div class="arc-cards">
        <div class="arc-card">
            <div class="arc-card-label">Released Commissions</div>
            <div class="arc-card-value">{{ $released->count() }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">released transactions</div>
        </div>
        <div class="arc-card">
            <div class="arc-card-label">Total Net TCP</div>
            <div class="arc-card-value" style="color:#1e4575;">₱{{ number_format($totalNetTcp, 2) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">from released commissions</div>
        </div>
        <div class="arc-card">
            <div class="arc-card-label">ARC Gross Sales</div>
            <div class="arc-card-value" style="color:#16a34a;" id="arcTotalDisplay">₱{{ number_format($totalArkcrestCommission, 2) }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">ArkCrest commission income</div>
        </div>
    </div>


    {{-- Search + Column Filter --}}
    <div class="arc-filters-bar">
        <div class="arc-filters-row">
            <div class="column-filter-dropdown" id="columnFilterDropdown">
                <button type="button" id="columnFilterBtn" class="column-filter-btn" onclick="toggleColumnFilterMenu(event)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Filter</span>
                    <span id="filterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div id="columnFilterMenu" class="column-filter-menu" style="display:none;"></div>
            </div>
            <div class="search-box-inline">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="arcSalesSearch" placeholder="Search requests...">
            </div>
        </div>
        <div id="activeColumnFiltersRow" class="active-column-filters-row" style="display:none;"></div>
    </div>

    
    {{-- Table --}}
    <div class="arc-table-wrap">
        @if($released->isEmpty())
        <div style="padding:40px;text-align:center;color:#94a3b8;font-size:14px;">No released commissions for this period.</div>
        @else
        <div class="arc-table-scroll" style="overflow-x:auto;">
        <table class="arc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date Released</th>
                    <th>Client</th>
                    <th>Project</th>
                    <th>Agent</th>
                    <th>Net TCP</th>
                    <th>Commission Terms</th>
                    <th>ARC % </th>
                    <th>ARC Commission</th>
                </tr>
            </thead>
            <tbody>
            @foreach($released as $i => $r)
            @php $rate = $rates->get($r->id); @endphp
            <tr id="row-{{ $r->id }}"
                data-date-released="{{ $r->date_released ? $r->date_released->format('Y-m-d') : '' }}"
                data-client="{{ $r->client_name ?? '' }}"
                data-project="{{ $r->project_name ?? '' }}"
                data-agent="{{ $r->agent_name ?? '' }}"
                data-net-tcp="{{ $r->net_tcp ?? 0 }}"
                data-commission-terms="{{ $r->payment_type ?? '' }}"
                data-arc-percent="{{ $rate ? $rate->arkcrest_percent : '' }}"
                data-arc-commission="{{ $rate ? $rate->arkcrest_commission : '' }}">
                <td style="color:#cbd5e1;font-weight:600;">{{ $i + 1 }}</td>
                <td style="white-space:nowrap;color:#059669;font-weight:600;">{{ $r->date_released ? $r->date_released->format('M d, Y') : '—' }}</td>
                <td style="font-weight:600;color:#0f172a;">{{ $r->client_name ?? '—' }}</td>
                <td style="color:#64748b;">{{ $r->project_name ?? '—' }}</td>
                <td>{{ $r->agent_name ?? '—' }}</td>
                <td style="font-weight:600;color:#1e4575;">₱{{ number_format($r->net_tcp ?? 0, 2) }}</td>
                <td>
                    <select id="terms-{{ $r->id }}" onchange="onTermsChange({{ $r->id }}, {{ $r->net_tcp ?? 0 }})"
                        style="padding:5px 8px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;color:#374151;background:#fff;outline:none;cursor:pointer;">
                        <option value="">— Select —</option>
                        <option value="Full Payment" {{ ($r->payment_type ?? '') == 'Full Payment' ? 'selected' : '' }}>Full Payment</option>
                        <option value="2 Months Commission" {{ ($r->payment_type ?? '') == '2 Months Commission' ? 'selected' : '' }}>2 Months Commission</option>
                        <option value="3 Months Commission" {{ ($r->payment_type ?? '') == '3 Months Commission' ? 'selected' : '' }}>3 Months Commission</option>
                    </select>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <input type="number" class="arc-pct-input" id="pct-{{ $r->id }}"
                            value="{{ $rate ? $rate->arkcrest_percent : '' }}"
                            placeholder="0.00" step="0.01" min="0" max="100">
                        <span style="font-size:12px;color:#94a3b8;">%</span>
                        <button class="arc-save-btn" onclick="saveRate({{ $r->id }}, {{ $r->net_tcp ?? 0 }})">Save</button>
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
                    <td colspan="8" style="padding:12px 14px;font-size:13px;font-weight:700;color:#0f172a;text-align:right;">ARC Gross Sales Total:</td>
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

function onTermsChange(id, netTcp) {
    const terms = document.getElementById('terms-' + id).value;
    if (!terms) return;
    const pct = parseFloat(document.getElementById('pct-' + id).value) || 0;
    fetch('/api/arkcrest-sales/' + id + '/rate', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body: JSON.stringify({arkcrest_percent: pct, payment_type: terms})
    }).then(r => r.json()).then(data => {
        if (data.success) {
            document.getElementById('arc-' + id).textContent = data.formatted;
            arcTotals[id] = data.arkcrest_commission;
            updateTotal();
            var rowEl = document.getElementById('row-' + id);
            if (rowEl) {
                rowEl.dataset.commissionTerms = terms;
                rowEl.dataset.arcPercent = pct;
                rowEl.dataset.arcCommission = data.arkcrest_commission;
                applyArcFilters();
            }
        }
    });
}

function saveRate(id, netTcp) {
    const pct   = parseFloat(document.getElementById('pct-' + id).value) || 0;
    const terms = document.getElementById('terms-' + id)?.value || '';
    fetch('/api/arkcrest-sales/' + id + '/rate', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body: JSON.stringify({arkcrest_percent: pct, payment_type: terms})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('arc-' + id).textContent = data.formatted;
            arcTotals[id] = data.arkcrest_commission;
            updateTotal();
            var rowEl = document.getElementById('row-' + id);
            if (rowEl) {
                rowEl.dataset.commissionTerms = terms;
                rowEl.dataset.arcPercent = pct;
                rowEl.dataset.arcCommission = data.arkcrest_commission;
                applyArcFilters();
            }
        }
    });
}

function updateTotal() {
    const total = Object.values(arcTotals).reduce((a, b) => a + b, 0);
    const fmt = '₱' + total.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById('arcTotalDisplay').textContent = fmt;
    document.getElementById('arcFooterTotal').textContent = fmt;
}

/* ---- Filter dropdown + Search logic ---- */

var FILTERABLE_COLUMNS = [
    { key: 'date-released',     label: 'Date Released',     type: 'daterange',   data: 'dateReleased' },
    { key: 'client',            label: 'Client',             type: 'text',   data: 'client' },
    { key: 'project',           label: 'Project',            type: 'text',   data: 'project' },
    { key: 'agent',             label: 'Agent',               type: 'text',   data: 'agent' },
    { key: 'net-tcp',           label: 'Net TCP',            type: 'number', data: 'netTcp' },
    { key: 'commission-terms',  label: 'Commission Terms',   type: 'select', data: 'commissionTerms',
        options: ['Full Payment', '2 Months Commission', '3 Months Commission'] },
    { key: 'arc-percent',       label: 'ARC %',               type: 'number', data: 'arcPercent' },
    { key: 'arc-commission',    label: 'ARC Commission',     type: 'number', data: 'arcCommission' }
];

var activeArcFilters = {}; // key -> current value

function toggleColumnFilterMenu(e) {
    e.stopPropagation();
    var menu = document.getElementById('columnFilterMenu');
    if (menu.style.display === 'block') { menu.style.display = 'none'; return; }
    renderColumnFilterMenu();
    menu.style.display = 'block';
}

function renderColumnFilterMenu() {
    var menu = document.getElementById('columnFilterMenu');
    menu.innerHTML = '';
    FILTERABLE_COLUMNS.forEach(function (col) {
        var item = document.createElement('div');
        item.className = 'column-filter-menu-item' + (activeArcFilters.hasOwnProperty(col.key) ? ' is-active' : '');
        item.innerHTML = '<span class="cfm-check">✓</span><span>' + col.label + '</span>';
        item.onclick = function (ev) { ev.stopPropagation(); toggleArcFilterColumn(col.key); };
        menu.appendChild(item);
    });
}

function toggleArcFilterColumn(key) {
    if (activeArcFilters.hasOwnProperty(key)) {
        delete activeArcFilters[key];
    } else {
        activeArcFilters[key] = '';
    }
    renderColumnFilterMenu();
    renderActiveFilterChips();
    updateFilterBadge();
    applyArcFilters();
    document.getElementById('columnFilterMenu').style.display = 'none';
}

function removeArcFilterColumn(key) {
    delete activeArcFilters[key];
    renderActiveFilterChips();
    updateFilterBadge();
    applyArcFilters();
}

function updateFilterBadge() {
    var badge = document.getElementById('filterCountBadge');
    var count = Object.keys(activeArcFilters).length;
    if (count > 0) { badge.style.display = 'inline-flex'; badge.textContent = count; }
    else { badge.style.display = 'none'; }
}

function renderActiveFilterChips() {
    var row = document.getElementById('activeColumnFiltersRow');
    var keys = Object.keys(activeArcFilters);
    row.innerHTML = '';
    if (keys.length === 0) { row.style.display = 'none'; return; }
    row.style.display = 'flex';

    keys.forEach(function (key) {
        var col = FILTERABLE_COLUMNS.find(function (c) { return c.key === key; });
        if (!col) return;

        var chip = document.createElement('div');
        chip.className = 'column-filter-chip';

        var label = document.createElement('label');
        label.textContent = col.label;
        chip.appendChild(label);

        var input;
        if (col.type === 'daterange') {
            if (!activeArcFilters[key] || typeof activeArcFilters[key] !== 'object') {
                activeArcFilters[key] = { from: '', to: '' };
            }
            var range = activeArcFilters[key];

            input = document.createElement('span');
            input.style.display = 'flex';
            input.style.alignItems = 'center';
            input.style.gap = '6px';

            var fromInput = document.createElement('input');
            fromInput.type = 'date';
            fromInput.value = range.from || '';
            fromInput.onchange = function () { range.from = this.value; applyArcFilters(); };

            var toLabel = document.createElement('span');
            toLabel.textContent = 'to';
            toLabel.style.cssText = 'color:#8a9bad;font-size:12px;';

            var toInput = document.createElement('input');
            toInput.type = 'date';
            toInput.value = range.to || '';
            toInput.onchange = function () { range.to = this.value; applyArcFilters(); };

            input.appendChild(fromInput);
            input.appendChild(toLabel);
            input.appendChild(toInput);
        } else if (col.type === 'select') {
            input = document.createElement('select');
            var optAll = document.createElement('option');
            optAll.value = ''; optAll.textContent = 'All';
            input.appendChild(optAll);
            col.options.forEach(function (o) {
                var opt = document.createElement('option');
                opt.value = o; opt.textContent = o;
                if (activeArcFilters[key] === o) opt.selected = true;
                input.appendChild(opt);
            });
            input.oninput = input.onchange = function () {
                activeArcFilters[key] = this.value;
                applyArcFilters();
            };
        } else {
            input = document.createElement('input');
            input.type = col.type === 'date' ? 'date' : 'text';
            input.placeholder = 'Search ' + col.label.toLowerCase() + '...';
            input.value = activeArcFilters[key];
            input.oninput = input.onchange = function () {
                activeArcFilters[key] = this.value;
                applyArcFilters();
            };
        }
        chip.appendChild(input);

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'cfm-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function () { removeArcFilterColumn(key); };
        chip.appendChild(removeBtn);

        row.appendChild(chip);
    });

    var clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.className = 'clear-column-filters-btn';
    clearBtn.textContent = 'Clear Filters';
    clearBtn.onclick = clearAllArcFilters;
    row.appendChild(clearBtn);
}

function clearAllArcFilters() {
    activeArcFilters = {};
    renderActiveFilterChips();
    updateFilterBadge();
    applyArcFilters();
}

function applyArcFilters() {
    var globalSearch = (document.getElementById('arcSalesSearch').value || '').toLowerCase().trim();
    var rows = document.querySelectorAll('.arc-table tbody tr');

    rows.forEach(function (row) {
        var visible = true;

        for (var key in activeArcFilters) {
            var col = FILTERABLE_COLUMNS.find(function (c) { return c.key === key; });
            if (!col) continue;

            if (col.type === 'daterange') {
                var range = activeArcFilters[key];
                if (!range || (!range.from && !range.to)) continue;
                var cellDate = (row.dataset[col.data] || '').toString();
                if (!cellDate) { visible = false; break; }
                if (range.from && cellDate < range.from) { visible = false; break; }
                if (range.to && cellDate > range.to) { visible = false; break; }
                continue;
            }

            var val = (activeArcFilters[key] || '').toString().trim();
            if (!val) continue;
            var cellVal = (row.dataset[col.data] || '').toString();

            if (col.type === 'date') {
                if (cellVal !== val) { visible = false; break; }
            } else if (col.type === 'number') {
                var cleanCell = cellVal.replace(/[^0-9.]/g, '');
                var cleanVal = val.replace(/[^0-9.]/g, '');
                if (!cleanCell.includes(cleanVal)) { visible = false; break; }
            } else if (col.type === 'select') {
                if (cellVal !== val) { visible = false; break; }
            } else {
                if (!cellVal.toLowerCase().includes(val.toLowerCase())) { visible = false; break; }
            }
        }

        if (visible && globalSearch) {
            var haystack = Object.values(row.dataset).join(' ').toLowerCase();
            if (!haystack.includes(globalSearch)) visible = false;
        }

        row.style.display = visible ? '' : 'none';
    });
}

document.addEventListener('click', function (e) {
    var dropdown = document.getElementById('columnFilterDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('columnFilterMenu').style.display = 'none';
    }
});

document.getElementById('arcSalesSearch').addEventListener('input', applyArcFilters);
</script>
@endsection
