@extends('layouts.dashboard')
@section('title', 'Client Database')
@section('content')
<style>
.cd-wrap{padding:0}
.cd-header{background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:28px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25)}
.cd-header-eyebrow{font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px}
.cd-header h1{font-size:28px;font-weight:700;color:white;margin:0 0 8px;position:relative;z-index:2}
.cd-header p{font-size:14px;color:rgba(255,255,255,0.75);margin:0;position:relative;z-index:2}
.add-commission-section{background:white;border-radius:12px;padding:32px;margin-bottom:30px;box-shadow:0 2px 8px rgba(0,0,0,0.08);border:2px solid #1e4575}
.section-header-commission{padding:0 0 12px;border-bottom:1px solid #d0d5dd;margin-bottom:24px}
.section-header-commission h2{color:#1e4575;font-size:18px;font-weight:700;margin:0;text-transform:uppercase}
.form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:12px}
.form-group{display:flex;flex-direction:column;gap:4px}
.form-group label{font-size:12px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:0.4px}
.required{color:#ef4444}
.form-group input,.form-group select{padding:12px 16px;border:2px solid #1e4575;border-radius:8px;font-size:14px;transition:all 0.3s;background:white;color:#344054;font-weight:500}
.form-group input:focus,.form-group select:focus{outline:none;border-color:#A37929;box-shadow:0 0 0 3px rgba(163,121,41,0.1)}
.section-title-bar{font-size:16px;font-weight:700;color:#A37929;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.6px;display:flex;align-items:center;gap:8px}
.section-title-bar::before{content:'';width:3px;height:20px;background:linear-gradient(180deg,#1e4575,#A37929);border-radius:2px}
.form-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:28px}
.btn-clear,.btn-submit{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:6px;font-weight:600;font-size:14px;cursor:pointer;transition:all 0.3s;border:none;min-height:44px}
.btn-clear{background:#f3f4f6;color:#374151;border:2px solid #d0d5dd}
.btn-clear:hover{background:#e5e7eb;transform:translateY(-2px)}
.btn-submit{background:#1e4575;color:white;box-shadow:0 2px 8px rgba(30,69,117,0.3)}
.btn-submit:hover{background:#152e4d;transform:translateY(-2px)}

/* Sticky checkbox + index columns for the records table */
.cd-sticky-col{position:sticky;background:#fff;z-index:2}
.cd-sticky-checkbox{left:0;width:40px;min-width:40px;max-width:40px;text-align:center}
.cd-sticky-index{left:40px;width:52px;min-width:52px;max-width:52px;text-align:center;box-shadow:2px 0 4px -2px rgba(0,0,0,0.15)}
tbody tr:hover .cd-sticky-col{background:#f8fafc}

/* Sticky header row (mirrors the sticky index column above, but for vertical scroll) */
.cd-records-table thead th{position:sticky;top:0;background:#1e4575;z-index:4;box-shadow:0 2px 4px -2px rgba(0,0,0,.25)}
.cd-records-table thead .cd-sticky-col{z-index:5}
.cd-bulk-btn{padding:9px 14px;border-radius:8px;border:none;font-size:13px;font-weight:700;cursor:pointer;background:#ef4444;color:#fff;transition:opacity .2s}
.cd-bulk-btn:disabled{opacity:.45;cursor:not-allowed}

/* Records table polish */
.cd-table-wrap{overflow-x:auto;overflow-y:hidden;border:1.5px solid #d0d5dd;border-radius:10px}
/* Hide the native scrollbar — replaced by the custom #cdScrollTrack bar below the table, which is fully JS-driven and guaranteed to work regardless of OS/browser scrollbar quirks. */
.cd-table-wrap::-webkit-scrollbar{display:none}
.cd-table-wrap{scrollbar-width:none;-ms-overflow-style:none}
.cd-scroll-track{position:relative;height:12px;background:#f1f5f9;border-radius:6px;margin-top:8px;cursor:pointer;display:none;user-select:none}
.cd-scroll-thumb{position:absolute;top:1px;left:0;height:10px;background:#94a3b8;border-radius:5px;cursor:grab;transition:background .15s}
.cd-scroll-thumb:hover{background:#64748b}
.cd-scroll-thumb.dragging{cursor:grabbing;background:#475569}
.cd-records-table{width:100%;border-collapse:collapse;font-size:13px}
.cd-records-table th,.cd-records-table td{border-right:1px solid #eef1f5}
.cd-records-table th:last-child,.cd-records-table td:last-child{border-right:none}
.cd-records-table thead th{border-bottom:2px solid #16345c}
.cd-records-table tbody tr:last-child td{border-bottom:none}
#cdTableBody tr:hover td{background:#f8fafc}

/* Mobile-responsive modals (restored from previous version) */
@media (max-width: 768px) {
    .cd-modal-overlay { padding: 0; }
    .cd-modal-box { width: 100% !important; max-width: 100% !important; height: 100%; max-height: 100%; overflow-y: auto !important; -webkit-overflow-scrolling: touch; border-radius: 0 !important; }
    .cd-modal-grid { grid-template-columns: 1fr !important; }
}
/* Downpayment modal — responsive on all sizes */

/* Desktop / tablet default: let rows wrap gracefully at any width instead
   of relying on a fixed min-width, and keep the toolbar from ever
   overflowing when the browser window itself is resized narrow. */
.dp-installment-row > * { flex: 1 1 auto; }
.dp-installment-row input[type="number"] { min-width: 120px; }
.dp-installment-toolbar-btn { white-space: nowrap; }

/* Small desktop windows / tablets — start stacking the toolbar before
   things get cramped, well above the phone breakpoint. */
@media (max-width: 900px) {
    .dp-installment-toolbar > div { flex: 1 1 140px; }
}

/* Phones — full-screen modal + fully stacked rows */
@media (max-width: 768px) {
    #dpModal { padding: 0; }
    #dpModal > div { width: 100% !important; max-width: 100% !important; height: 100% !important; max-height: 100% !important; border-radius: 0 !important; }
    .dp-installment-toolbar { flex-direction: column !important; align-items: stretch !important; gap: 10px !important; }
    .dp-installment-toolbar > div { width: 100% !important; }
    .dp-installment-toolbar-btn { width: 100%; }
    .dp-installment-row > * { flex: 1 1 100% !important; border-right: none !important; }
    .dp-installment-row > span:first-child { border-bottom: 1.5px solid #e2e8f0; }
}

/* ---- Filter dropdown + chips (matches Commission Monitoring / ARC Sales pattern) ---- */
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
.active-column-filters-row{display:flex;flex-wrap:wrap;align-items:center;gap:10px}
.column-filter-chip{display:flex;align-items:center;gap:6px;background:#f5f7fa;border:1.5px solid #d0d5dd;border-radius:8px;padding:6px 8px 6px 12px}
.column-filter-chip label{font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
.column-filter-chip input,.column-filter-chip select{font-size:13px;padding:6px 8px;border:1.5px solid #d0d5dd;border-radius:6px;color:#344054;min-width:130px}
.column-filter-chip .cfm-remove{background:none;border:none;color:#8a9bad;cursor:pointer;font-size:16px;line-height:1;padding:2px 4px}
.column-filter-chip .cfm-remove:hover{color:#dc2626}
.clear-column-filters-btn{font-size:12px;font-weight:600;color:#1e4575;background:#eef2f7;border:1px solid #d0d5dd;border-radius:6px;padding:8px 14px;cursor:pointer;white-space:nowrap}
@media (max-width:768px){
  .column-filter-menu{left:0;right:0;min-width:0;width:100%;box-sizing:border-box}
  .active-column-filters-row{flex-direction:column;align-items:stretch}
  .column-filter-chip{width:100%;flex-wrap:wrap;box-sizing:border-box}
  .column-filter-chip label{flex:1 1 100%}
  .column-filter-chip input,.column-filter-chip select{flex:1 1 auto;min-width:0;width:100%}
  .clear-column-filters-btn{width:100%;text-align:center}
}

/* Print Selected — same feature/fix as Departmental Expenses */
.cd-print-only{display:none}
@media print{
    /* #cdPrintArea is reparented to be a direct child of <body> by
       cdPrintSelectedRecords() right before printing. Hiding every OTHER
       direct child of body (display:none, not visibility:hidden) removes
       it from layout entirely, instead of just hiding it visually while
       it still reserves its full height — that reserved height was what
       produced several blank pages when only one row was selected. */
    body > *:not(.cd-print-only){
        display:none !important;
    }
    html, body{
        overflow:visible !important;
        height:auto !important;
        max-height:none !important;
    }
    .cd-print-only{
        display:block !important;
        position:static !important;
        width:100%;
    }
    .cd-print-header{margin-bottom:20px}
    .cd-print-header h2{margin:0 0 4px;font-size:18px;color:#1e4575}
    .cd-print-header p{margin:0;font-size:12px;color:#555}
    .cd-print-table{width:100%;border-collapse:collapse;font-size:11px}
    .cd-print-table th,.cd-print-table td{border:1px solid #999;padding:6px 8px;text-align:left}
    .cd-print-table th{background:#eef2f7 !important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .cd-print-table tr{page-break-inside:avoid}
    .cd-print-table thead{display:table-header-group}
    @page{size:landscape;margin:12mm}
}

</style>

<div class="cd-wrap">
    @if(session('error'))
    <div style="background:#fee2e2;color:#dc2626;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;">⚠ {{ session('error') }}</div>
    @endif
    @if(session('success'))
    <div style="background:#dcfce7;color:#166534;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;">✓ {{ session('success') }}</div>
    @endif
    <div class="cd-header">
        <div style="position:relative;z-index:2;">
            <div class="cd-header-eyebrow">Sales & Marketing</div>
            <h1>Clients</h1>
            <p>Manage client records and commission requests</p>
        </div>
        <div style="position:absolute;top:0;right:0;width:300px;height:100%;pointer-events:none;">
            <div style="position:absolute;width:220px;height:220px;top:-60px;right:-40px;border-radius:50%;background:rgba(255,255,255,.06);"></div>
            <div style="position:absolute;width:140px;height:140px;top:40px;right:120px;border-radius:50%;background:rgba(255,255,255,.04);"></div>
        </div>
    </div>

    <div class="add-commission-section">
        <div class="section-header-commission">
            <h2>ADD NEW CLIENT RECORD</h2>
        </div>
        <form id="commissionForm" action="{{ route('client-database.store') }}" method="POST">
            @csrf
            <div class="section-title-bar"><span>📋</span> COMMISSION REQUEST INFORMATION</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>DEVELOPER'S NAME <span class="required">*</span></label>
                    <div style="position:relative;">
                        <input type="text" name="developer_name" id="dev_name_input" placeholder="Type or select developer" autocomplete="off" required
                            onclick="toggleSearchDropdown('devDropdown')" oninput="filterSearchDropdown('devDropdown', this.value)"
                            style="width:100%;padding:12px 40px 12px 16px;border:2px solid #1e4575;border-radius:8px;font-size:14px;font-weight:500;background:white;color:#344054;box-sizing:border-box;">
                        <button type="button" onclick="toggleSearchDropdown('devDropdown')" style="position:absolute;right:2px;top:50%;transform:translateY(-50%);width:36px;height:calc(100% - 4px);background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:0 6px 6px 0;cursor:pointer;font-size:12px;">▼</button>
                        <div id="devDropdown" data-dropdown-input="dev_name_input" style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;background:white;border:2px solid #1e4575;border-radius:8px;max-height:200px;overflow-y:auto;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                            {{--
                                FIX: this used to be a @foreach over a $developers variable passed from the
                                controller. If that variable came back empty/undefined (e.g. a broken query,
                                a renamed relationship, or the variable simply not being passed to the view),
                                the loop silently rendered nothing and the whole dropdown looked "gone" even
                                though the surrounding HTML/JS was fine.

                                This version is defensive: it uses $developers if the controller provides it,
                                and otherwise falls back to the two known developers so the dropdown can never
                                render empty again. Add more names to the fallback array below, or — better —
                                make sure your controller passes a $developers array/collection to this view,
                                the same way it already does for something like $commissionRequests.
                            --}}
                            @php
                                $developerOptions = (isset($developers) && count($developers)) ? $developers : [
                                    '758 Real Estate Management',
                                    'Xceed Realty and Development Inc.',
                                ];
                            @endphp
                            @foreach($developerOptions as $dev)
                            <div onclick="selectSearchOption('dev_name_input','devDropdown','{{ $dev }}')" data-value="{{ $dev }}" style="padding:12px 16px;cursor:pointer;font-size:14px;color:#374151;font-weight:500;border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#e3f2fd'" onmouseout="this.style.background=this.dataset.selected==='true'?'#dbeafe':''">{{ $dev }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>PROJECT NAME <span class="required">*</span></label>
                    <input type="text" name="project_name" placeholder="Enter project name" required>
                </div>
                <div class="form-group">
                    <label>BLOCK & LOT NUMBER <span class="required">*</span></label>
                    <input type="text" name="block_lot_number" placeholder="e.g., Block 3 Lot 12" required>
                </div>
                <div class="form-group">
                    <label>CLIENT'S NAME <span class="required">*</span></label>
                    <input type="text" name="client_name" placeholder="Enter client name" required>
                </div>
                <div class="form-group">
                    <label>LOT AREA <span class="required">*</span></label>
                    <input type="number" name="lot_area" id="f_lot_area" placeholder="0.0000" step="0.0001" min="0" oninput="computeTCP()" required>
                </div>
                <div class="form-group">
                    <label>PRICE PER SQM <span class="required">*</span></label>
                    <input type="text" id="f_price_sqm_display" placeholder="0.00" oninput="onPriceSqmInput(this)" style="color:#374151;" required>
                    <input type="hidden" name="price_sqm" id="f_price_sqm">
                </div>
                <div class="form-group">
                    <label>TCP <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                    <input type="text" id="f_tcp_display" placeholder="0.00" readonly style="background:#f3f4f6;cursor:not-allowed;color:#374151;">
                    <input type="hidden" name="tcp" id="f_tcp">
                </div>
                <div class="form-group">
                    <label>DISCOUNT (%)</label>
                    <input type="number" name="discount" id="f_discount_pct" placeholder="0.00" step="0.0000000001" min="0" max="100" oninput="computeDiscount()">
                </div>
                <div class="form-group">
                    <label>DISCOUNT VALUE <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                    <input type="number" name="discount_value" id="f_discount_val" placeholder="0.00" step="0.01" min="0" oninput="computeDiscountFromValue()" style="color:#374151;">
                </div>
                <div class="form-group">
                    <label>NET TCP <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                    <input type="text" id="f_net_tcp_display" placeholder="0.00" readonly style="background:#f3f4f6;cursor:not-allowed;color:#374151;">
                    <input type="hidden" name="net_tcp" id="f_net_tcp">
                </div>
                <div class="form-group">
                    <label>TERMS OF PAYMENT <span class="required">*</span></label>
                    <div style="position:relative">
                        <input type="text" id="terms_of_payment" name="terms_of_payment" required autocomplete="off" placeholder="Type or select payment terms" onclick="toggleSearchDropdown('termsDropdown')" oninput="filterSearchDropdown('termsDropdown', this.value)" style="width:100%;padding:12px 40px 12px 16px;border:2px solid #1e4575;border-radius:8px;font-size:14px;font-weight:500;background:white;color:#344054;box-sizing:border-box">
                        <button type="button" onclick="toggleSearchDropdown('termsDropdown')" style="position:absolute;right:2px;top:50%;transform:translateY(-50%);width:36px;height:calc(100% - 4px);background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:0 6px 6px 0;cursor:pointer;font-size:12px">▼</button>
                        <div id="termsDropdown" data-dropdown-input="terms_of_payment" style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;background:white;border:2px solid #1e4575;border-radius:8px;max-height:250px;overflow-y:auto;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.15)">
                            @php
                                // Shared list of standard payment terms — also reused by the
                                // Edit Modal's searchable dropdown further down this file.
                                $termsOptions = ['30% DP - 70% BAL 5 YRS','50% DP - 50% BAL 5 YRS','30% DP (6 MOS) - 70% BAL 54 MOS','30% DP (3 MOS) - 70% BAL 57 MOS','30% DP (9 MOS) - 70% BAL 36 MOS','30% DP (2 MOS) - 70% BAL 57 MOS','30% DP (2 MOS) - 70% BAL 5 YRS','STRAIGHT PAYMENT','30% DP - 70% BAL 3 YRS'];
                            @endphp
                            @foreach($termsOptions as $term)
                            <div onclick="selectSearchOption('terms_of_payment','termsDropdown','{{ $term }}')" data-value="{{ $term }}" style="padding:12px 16px;cursor:pointer;font-size:14px;color:#374151;font-weight:500;border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#e3f2fd'" onmouseout="this.style.background=this.dataset.selected==='true'?'#dbeafe':''">{{ $term }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>RESERVATION DATE <span class="required">*</span></label>
                    <input type="date" name="reservation_date" id="f_reservation_date" required onchange="validateDownpaymentDate()">
                </div>
                <div class="form-group">
                    <label>NUMBER OF UNITS <span class="required">*</span></label>
                    <input type="number" name="number_of_units" min="1" value="1" placeholder="1" required>
                </div>
                <div class="form-group">
                    <label>DATE OF DOWNPAYMENT <span class="required">*</span></label>
                    <input type="date" name="date_of_downpayment" id="f_date_of_downpayment" required onchange="validateDownpaymentDate()">
                </div>
                <div class="form-group">
                    <label>AGENT'S NAME <span class="required">*</span></label>
                    <input type="text" name="agent_name" placeholder="Enter agent name" required>
                </div>
                <div class="form-group">
                    <label>CLIENT STATUS</label>
                    <select name="status" style="width:100%;padding:12px 16px;border:2px solid #1e4575;border-radius:8px;font-size:14px;font-weight:500;background:white;color:#344054;">
                        <option value="">No Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    {{-- "Done" is intentionally not offered here — a brand new record can't have a completed downpayment yet. Done is set automatically once the client finishes paying. --}}
                </div>
            </div>
            <input type="hidden" name="date_requested" value="{{ date('Y-m-d') }}">

            <input type="hidden" name="property_details" value="">
            <input type="hidden" name="commission" value="">
            <input type="hidden" name="remarks" value="">
            <input type="hidden" name="commission_percent" value="">
            <div class="form-actions">
                <button type="button" class="btn-clear" id="commissionClearBtn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear
                </button>
                <button type="submit" class="btn-submit">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Submit Request
                </button>
            </div>
        </form>

        <!-- Duplicate Record Confirmation Modal (restored from previous version) -->
        <div id="duplicateRecordModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
          <div style="background:white;border-radius:12px;padding:28px;max-width:420px;width:90%;box-shadow:0 10px 40px rgba(0,0,0,0.2);">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <h3 style="margin:0;font-size:17px;color:#111827;">Can't be submitted</h3>
            </div>
            <p style="color:#4b5563;font-size:14px;margin:0 0 20px;">There's an existing data like this on the database.</p>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
              <button type="button" onclick="closeDuplicateModal()" style="padding:9px 18px;border:1.5px solid #e2e8f0;background:white;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;color:#374151;">Close</button>
              <button type="button" onclick="goToDuplicateRecord()" style="padding:9px 18px;border:none;background:#1e4575;color:white;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">Go to Same Record</button>
            </div>
          </div>
        </div>
    </div>

    <!-- Table -->
    <div style="background:white;border-radius:12px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.08);border:2px solid #1e4575;margin-top:30px">
        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:2px solid #e5e7eb;">
            <h3 style="font-size:20px;font-weight:700;color:#1e4575;margin:0;text-transform:uppercase">Client Database Records</h3>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <div class="column-filter-dropdown" id="cdColumnFilterDropdown">
                    <button type="button" class="column-filter-btn" onclick="toggleCdColumnFilterMenu(event)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        <span>Filter</span>
                        <span id="cdFilterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="cdColumnFilterMenu" class="column-filter-menu" style="display:none;"></div>
                </div>
                <div class="cd-search-wrap" style="position:relative;">
                    <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#6b7280" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="cdSearch" placeholder="Search request..." style="width:340px;max-width:100%;padding:9px 12px 9px 36px;border:2px solid #d0d5dd;border-radius:8px;font-size:13px;box-sizing:border-box;outline:none;" oninput="cdFilter()">
                </div>
                <button id="cdPrintSelectedBtn" class="cd-bulk-btn" style="background:#1e4575;" onclick="cdPrintSelectedRecords()">Print Selected</button>
                <button id="cdBulkDeleteBtn" class="cd-bulk-btn" disabled onclick="cdDeleteSelected()">Delete Selected (0)</button>
                <span id="cdCount" style="font-size:12px;color:#94a3b8;white-space:nowrap;"></span>
                <button onclick="cdClearAll()" style="padding:9px 14px;background:#f1f5f9;border:2px solid #d0d5dd;border-radius:8px;font-size:13px;color:#64748b;cursor:pointer;">Clear</button>
            </div>
            <div id="cdActiveColumnFiltersRow" class="active-column-filters-row" style="display:none;"></div>
        </div>
        <div class="cd-table-wrap">
            <table class="cd-records-table js-sort-table">
                <thead style="background:linear-gradient(135deg,#1e4575,#2563eb)">
                    <tr>
                        <th class="cd-sticky-col cd-sticky-checkbox" style="padding:14px 8px">
                            <input type="checkbox" id="cdSelectAll" onchange="cdToggleSelectAll(this)" title="Select all">
                        </th>
                        <th class="cd-sticky-col cd-sticky-index" style="padding:14px 8px;color:white;text-transform:uppercase;font-size:11px;">#</th>
                        @foreach(['Control Number','Developer','Project','Block & Lot','Client','Lot Area','Price/SQM','TCP','Discount (%)','Discount Value','Net TCP','Terms','Reservation Date','Units','Downpayment Date','Agent','Client Status','DP Stage','Downpayment Status','Actions'] as $h)
                        <th style="padding:14px 12px;text-align:left;font-weight:600;color:white;text-transform:uppercase;font-size:11px;white-space:nowrap">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="cdTableBody">
                    @forelse($commissionRequests ?? [] as $req)
                    @php $discVal = $req->tcp && $req->discount ? $req->tcp * ($req->discount / 100) : null; @endphp
                    <tr data-id="{{ $req->id }}"
                        data-search="{{ strtolower($req->control_number ?? '') }} {{ strtolower($req->client_name ?? '') }} {{ strtolower($req->agent_name ?? '') }} {{ strtolower($req->project_name ?? '') }} {{ strtolower($req->developer_name ?? '') }} {{ strtolower($req->block_lot_number ?? '') }}"
                        data-control="{{ strtolower($req->control_number ?? '') }}"
                        data-developer="{{ strtolower($req->developer_name ?? '') }}"
                        data-project="{{ strtolower($req->project_name ?? '') }}"
                        data-block-lot="{{ strtolower($req->block_lot_number ?? '') }}"
                        data-client="{{ strtolower($req->client_name ?? '') }}"
                        data-lot-area="{{ $req->lot_area ?? '' }}"
                        data-price-sqm="{{ $req->price_sqm ?? '' }}"
                        data-tcp="{{ $req->tcp ?? '' }}"
                        data-discount="{{ $req->discount ?? '' }}"
                        data-discount-value="{{ $discVal ?? '' }}"
                        data-net-tcp="{{ $req->net_tcp ?? '' }}"
                        data-terms="{{ $req->terms_of_payment ?? '' }}"
                        data-reservation-date="{{ $req->reservation_date ? $req->reservation_date->format('Y-m-d') : '' }}"
                        data-units="{{ $req->number_of_units ?? '' }}"
                        data-downpayment-date="{{ $req->date_of_downpayment ? $req->date_of_downpayment->format('Y-m-d') : '' }}"
                        data-agent="{{ strtolower($req->agent_name ?? '') }}"
                        data-client-status="{{ strtolower($req->client_status ?? '') }}"
                        data-downpayment-stage="{{ ($req->downpayment_stage ?? 0).'/'.($req->downpayment_stage_total ?? 1) }}"
                        data-downpayment-status="{{ strtolower($req->downpayment_status ?? '') }}"
                        style="border-bottom:1px solid #e5e7eb">
                        <td class="cd-sticky-col cd-sticky-checkbox" style="padding:14px 8px">
                            <input type="checkbox" class="cd-row-checkbox" value="{{ $req->id }}" onchange="cdUpdateBulkBar()">
                        </td>
                        <td class="cd-sticky-col cd-sticky-index" style="padding:14px 8px;color:#374151;font-weight:600">{{ $loop->iteration }}</td>
                        <td style="padding:14px 12px;white-space:nowrap"><span style="font-family:monospace;background:#f1f5f9;padding:2px 8px;border-radius:6px;font-size:12px;color:#1e4575;font-weight:600;">{{ $req->control_number ?? '-' }}</span></td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->developer_name ?? '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->project_name ?? '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->block_lot_number ?? '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->client_name ?? '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->lot_area ? number_format($req->lot_area,2).' sqm' : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->price_sqm ? '₱'.number_format($req->price_sqm,2) : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->tcp ? '₱'.number_format($req->tcp,2) : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->discount !== null ? number_format($req->discount, 2).'%' : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">
                            @php $discVal = $req->tcp && $req->discount ? $req->tcp * ($req->discount / 100) : null; @endphp
                            {{ $discVal ? '₱'.number_format($discVal, 2) : '-' }}
                        </td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->net_tcp ? '₱'.number_format($req->net_tcp,2) : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->terms_of_payment ?? '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->reservation_date ? $req->reservation_date->format('M d, Y') : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap;text-align:center;">{{ $req->number_of_units ?? '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->date_of_downpayment ? $req->date_of_downpayment->format('M d, Y') : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->agent_name ?? '-' }}</td>
                        <td style="padding:10px 12px;white-space:nowrap">
                            <form method="POST" action="{{ route('client-database.status', $req->id) }}">
                                @csrf @method('PATCH')
                                <select id="csSel_{{ $req->id }}" name="client_status" onchange="this.form.submit()"
                                    data-client-status="{{ strtolower($req->client_status ?? '') }}"
                                    style="padding:5px 10px;border-radius:20px;font-size:12px;font-weight:600;border:none;cursor:pointer;outline:none;
                                    background:{{ $req->client_status === 'Done' ? '#dcfce7' : ($req->client_status === 'Cancelled' ? '#fee2e2' : ($req->client_status === 'Pending' ? '#fef3c7' : '#f1f5f9')) }};
                                    color:{{ $req->client_status === 'Done' ? '#166534' : ($req->client_status === 'Cancelled' ? '#991b1b' : ($req->client_status === 'Pending' ? '#92400e' : '#64748b')) }};">
                                    <option value="" {{ !$req->client_status ? 'selected' : '' }}>— Set Status —</option>
                                    <option value="Pending" {{ $req->client_status === 'Pending' ? 'selected' : '' }} style="background:#fef3c7;color:#92400e;">Pending</option>
                                    {{-- Done is system-driven only (set automatically once the full downpayment is paid) — kept as a disabled option so it still displays correctly on records that are already Done, but cannot be manually selected. --}}
                                    <option value="Done" disabled {{ $req->client_status === 'Done' ? 'selected' : '' }} style="background:#dcfce7;color:#166534;">Done (auto)</option>
                                    <option value="Cancelled" {{ $req->client_status === 'Cancelled' ? 'selected' : '' }} style="background:#fee2e2;color:#991b1b;">Cancelled</option>
                                </select>
                            </form>
                        </td>
                        @php
                            $latestCommissionRecord = $req->commissionRequests->first();
                            $latestSalesRequest = $req->commissionStageRequests->first();
                            $latestCommissionStage = (int) ($latestCommissionRecord->commission_stage ?? 0);
                            $latestSalesStage = (int) ($latestSalesRequest->commission_stage ?? 0);

                            if ($latestCommissionRecord && $latestCommissionStage >= $latestSalesStage) {
                                $dpStageStatus = $latestCommissionRecord->status === 'Not Released'
                                    ? 'Not Yet Released'
                                    : ($latestCommissionRecord->status ?: 'Not Yet Released');
                            } elseif ($latestSalesRequest) {
                                $dpStageStatus = 'Requested';
                            } elseif ($req->status === 'For Request') {
                                $dpStageStatus = 'Ready to request';
                            } else {
                                $dpStageStatus = null;
                            }
                        @endphp
                        <td id="dpStage_{{ $req->id }}" style="padding:10px 12px;white-space:nowrap;text-align:center;font-weight:700;color:#1e4575">
                            <div id="dpStageValue_{{ $req->id }}">{{ ($req->downpayment_stage ?? 0).'/'.($req->downpayment_stage_total ?? 1) }}</div>
                            <div id="dpStageStatus_{{ $req->id }}" style="display:{{ $dpStageStatus ? 'block' : 'none' }};margin-top:3px;font-size:9px;font-weight:800;text-transform:uppercase;color:{{ $dpStageStatus === 'Released' ? '#166534' : ($dpStageStatus === 'Not Yet Released' ? '#92400e' : ($dpStageStatus === 'Requested' ? '#1d4ed8' : '#A37929')) }};">
                                {{ $dpStageStatus ?? '' }}
                            </div>
                        </td>
                        <td style="padding:10px 12px;white-space:nowrap">
                            <button id="dpBtn_{{ $req->id }}" onclick="openDPModalFromBtn(this)"
                                data-id="{{ $req->id }}"
                                data-amount="{{ $req->downpayment_amount ?? 0 }}"
                                data-terms="{{ $req->downpayment_terms ?? 1 }}"
                                data-per-term="{{ $req->downpayment_per_term ?? 0 }}"
                                data-status="{{ addslashes($req->downpayment_status ?? '') }}"
                                data-dp-date="{{ $req->downpayment_date ? $req->downpayment_date->format('Y-m-d') : '' }}"
                                data-net-tcp="{{ $req->net_tcp ?? 0 }}"
                                data-terms-label="{{ addslashes($req->terms_of_payment ?? '') }}"
                                data-stage="{{ $req->downpayment_stage ?? 0 }}"
                                data-stage-total="{{ $req->downpayment_stage_total ?? 1 }}"
                                style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;border:none;cursor:pointer;
                                background:{{ $req->downpayment_status === 'Paid' || $req->downpayment_status === 'Spot Paid' ? '#dcfce7' : ($req->downpayment_status && $req->downpayment_status !== '— Set —' ? '#fef3c7' : '#f1f5f9') }};
                                color:{{ $req->downpayment_status === 'Paid' || $req->downpayment_status === 'Spot Paid' ? '#166534' : ($req->downpayment_status && $req->downpayment_status !== '— Set —' ? '#92400e' : '#64748b') }};">
                                {{ $req->downpayment_status ?: '— Set —' }}
                            </button>
                        </td>
                        <td style="padding:14px 12px;white-space:nowrap">
                            <div style="display:flex;gap:6px">
                                <button onclick="viewRow({{ $req->id }})" style="width:60px;height:28px;background:#1e4575;color:white;border:none;border-radius:5px;font-size:11px;font-weight:700;cursor:pointer">VIEW</button>
                                @if(auth()->user()->isAdmin())
                                <button onclick="editRow({{ $req->id }})" style="width:60px;height:28px;background:#f59e0b;color:white;border:none;border-radius:5px;font-size:11px;font-weight:700;cursor:pointer">EDIT</button>
                                <form action="{{ route('client-database.destroy', $req->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this record?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="width:60px;height:28px;background:#ef4444;color:white;border:none;border-radius:5px;font-size:11px;font-weight:700;cursor:pointer">DELETE</button>
                                </form>
                                @else
                                @php $dpLocked = in_array($req->downpayment_status, ['Paid', 'Spot Paid']) && $req->downpayment_amount > 0; @endphp
                                @if($dpLocked)
                                <button disabled title="Locked — downpayment has been paid. Only admin can edit." style="width:60px;height:28px;background:#9ca3af;color:white;border:none;border-radius:5px;font-size:11px;font-weight:700;cursor:not-allowed;opacity:0.7;">🔒 EDIT</button>
                                @else
                                <button onclick="staffEditRow({{ $req->id }}, '{{ addslashes($req->client_name ?? '') }} - {{ addslashes($req->project_name ?? '') }}')" style="width:60px;height:28px;background:#f59e0b;color:white;border:none;border-radius:5px;font-size:11px;font-weight:700;cursor:pointer">EDIT</button>
                                @endif
                                <form action="{{ route('client-database.destroy', $req->id) }}" method="POST" style="display:inline" onsubmit="return staffDeleteConfirm(event, {{ $req->id }}, '{{ addslashes($req->client_name ?? '') }} - {{ addslashes($req->project_name ?? '') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="width:60px;height:28px;background:#ef4444;color:white;border:none;border-radius:5px;font-size:11px;font-weight:700;cursor:pointer">DELETE</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="22" style="text-align:center;padding:40px;color:#6b7280">No client records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="cdScrollTrack" class="cd-scroll-track">
            <div id="cdScrollThumb" class="cd-scroll-thumb"></div>
        </div>
        <div id="cdPrintArea" class="cd-print-only"></div>
    </div>
</div>

<!-- Permission Modal -->
<div id="permissionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this)closeLocalPermModal()">
    <div style="background:white;border-radius:16px;max-width:460px;width:90%;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.2);">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);padding:18px 22px;display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div style="flex:1;">
                <div style="color:rgba(255,255,255,.7);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;">Permission Required</div>
                <div id="localPermTitle" style="color:white;font-size:15px;font-weight:700;margin-top:1px;">Request to Edit Record</div>
            </div>
            <button onclick="closeLocalPermModal()" style="background:rgba(255,255,255,.15);border:none;color:white;width:28px;height:28px;border-radius:6px;cursor:pointer;font-size:18px;line-height:1;">&times;</button>
        </div>
        <div style="padding:20px 22px;">
            <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;margin-bottom:16px;border:1px solid #e2e8f0;">
                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Record</div>
                <div id="localPermRecord" style="font-size:13px;font-weight:600;color:#1e293b;">—</div>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Reason for Request <span style="color:#dc2626;">*</span></label>
                <textarea id="localPermReason" rows="4" placeholder="Please explain why you need to perform this action..." style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-family:inherit;resize:none;outline:none;box-sizing:border-box;" onfocus="this.style.borderColor='#1e4575'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
                <div id="localPermError" style="color:#dc2626;font-size:11px;margin-top:4px;display:none;">Please provide a reason (at least 5 characters).</div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button onclick="closeLocalPermModal()" style="padding:9px 18px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;">Cancel</button>
                <button onclick="submitLocalPermRequest()" id="localPermBtn" style="padding:9px 20px;background:#1e4575;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Send Request</button>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="cd-modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
    <div class="cd-modal-box" style="background:white;border-radius:16px;width:95%;max-width:960px;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);color:white;padding:20px 24px;border-radius:16px 16px 0 0;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;font-size:18px;font-weight:700">Commission Request Details</h3>
            <button onclick="document.getElementById('viewModal').style.display='none'" style="background:rgba(255,255,255,0.2);border:none;color:white;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:18px">✕</button>
        </div>
        <div style="padding:24px">
            <div class="cd-modal-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px" id="viewContent"></div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end">
            <button onclick="document.getElementById('viewModal').style.display='none'" style="padding:10px 20px;background:#f3f4f6;color:#374151;border:2px solid #d0d5dd;border-radius:8px;font-weight:600;cursor:pointer">Close</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="cd-modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
    <div class="cd-modal-box" style="background:white;border-radius:16px;width:95%;max-width:960px;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);color:white;padding:20px 24px;border-radius:16px 16px 0 0;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;font-size:18px;font-weight:700">Edit Commission Request</h3>
            <button onclick="document.getElementById('editModal').style.display='none'" style="background:rgba(255,255,255,0.2);border:none;color:white;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:18px">✕</button>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <input type="hidden" id="edit_id" name="id">
            <input type="hidden" id="edit_date_requested" name="date_requested">
            <div class="cd-modal-grid" style="padding:24px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
                <div style="display:flex;flex-direction:column;gap:4px">
                    <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Developer's Name</label>
                    <div style="position:relative;">
                        <input type="text" name="developer_name" id="edit_developer_name" placeholder="Type or select developer" autocomplete="off"
                            onclick="toggleSearchDropdown('edit_devDropdown')" oninput="filterSearchDropdown('edit_devDropdown', this.value)"
                            style="width:100%;padding:10px 40px 10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;background:white;color:#374151;box-sizing:border-box;">
                        <button type="button" onclick="toggleSearchDropdown('edit_devDropdown')" style="position:absolute;right:2px;top:50%;transform:translateY(-50%);width:34px;height:calc(100% - 4px);background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:0 6px 6px 0;cursor:pointer;font-size:12px;">▼</button>
                        <div id="edit_devDropdown" data-dropdown-input="edit_developer_name" style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;background:white;border:2px solid #1e4575;border-radius:8px;max-height:200px;overflow-y:auto;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                            @foreach($developerOptions as $dev)
                            <div onclick="selectSearchOption('edit_developer_name','edit_devDropdown','{{ $dev }}')" data-value="{{ $dev }}" style="padding:12px 16px;cursor:pointer;font-size:14px;color:#374151;font-weight:500;border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#e3f2fd'" onmouseout="this.style.background=this.dataset.selected==='true'?'#dbeafe':''">{{ $dev }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Project Name *</label><input type="text" id="edit_project_name" name="project_name" required style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Block & Lot Number</label><input type="text" id="edit_block_lot_number" name="block_lot_number" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Client's Name *</label><input type="text" id="edit_client_name" name="client_name" required style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Lot Area</label><input type="number" id="edit_lot_area" name="lot_area" step="0.0001" min="0" oninput="computeTCP('edit')" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Price Per SQM</label><input type="number" id="edit_price_sqm" name="price_sqm" step="0.01" min="0" oninput="computeTCP('edit')" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">TCP <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label><input type="number" id="edit_tcp" name="tcp" step="0.01" readonly style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;background:#f3f4f6;cursor:not-allowed;color:#374151;"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Discount (%)</label><input type="number" id="edit_discount" name="discount" step="0.01" min="0" max="100" oninput="computeDiscount('edit')" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Discount Value <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label><input type="number" id="edit_discount_value" name="discount_value" step="0.01" min="0" readonly style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;background:#f3f4f6;cursor:not-allowed;color:#374151;"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Net TCP <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label><input type="number" id="edit_net_tcp" name="net_tcp" step="0.01" readonly style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;background:#f3f4f6;cursor:not-allowed;color:#374151;"></div>
                <div style="display:flex;flex-direction:column;gap:4px">
                    <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Terms of Payment *</label>
                    <div style="position:relative">
                        <input type="text" id="edit_terms_of_payment" name="terms_of_payment" required autocomplete="off" placeholder="Type or select payment terms" onclick="toggleSearchDropdown('edit_termsDropdown')" oninput="filterSearchDropdown('edit_termsDropdown', this.value)" style="width:100%;padding:10px 40px 10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;background:white;color:#374151;box-sizing:border-box">
                        <button type="button" onclick="toggleSearchDropdown('edit_termsDropdown')" style="position:absolute;right:2px;top:50%;transform:translateY(-50%);width:34px;height:calc(100% - 4px);background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:0 6px 6px 0;cursor:pointer;font-size:12px">▼</button>
                        <div id="edit_termsDropdown" data-dropdown-input="edit_terms_of_payment" style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;background:white;border:2px solid #1e4575;border-radius:8px;max-height:250px;overflow-y:auto;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.15)">
                            @foreach($termsOptions as $term)
                            <div onclick="selectSearchOption('edit_terms_of_payment','edit_termsDropdown','{{ $term }}')" data-value="{{ $term }}" style="padding:12px 16px;cursor:pointer;font-size:14px;color:#374151;font-weight:500;border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#e3f2fd'" onmouseout="this.style.background=this.dataset.selected==='true'?'#dbeafe':''">{{ $term }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Reservation Date</label><input type="date" id="edit_reservation_date" name="reservation_date" onchange="validateEditDownpaymentDate()" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Number of Units</label><input type="number" id="edit_number_of_units" name="number_of_units" min="1" value="1" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Date of Downpayment</label><input type="date" id="edit_date_of_downpayment" name="date_of_downpayment" onchange="validateEditDownpaymentDate()" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Agent's Name *</label><input type="text" id="edit_agent_name" name="agent_name" required style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Client Status</label>
                    <select id="edit_client_status" name="status" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px">
                        <option value="">No Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Done" id="edit_status_done_option" disabled>Done (auto)</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div style="padding:16px 24px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:12px">
                <button type="button" onclick="document.getElementById('editModal').style.display='none'" style="padding:10px 20px;background:#f3f4f6;color:#374151;border:2px solid #d0d5dd;border-radius:8px;font-weight:600;cursor:pointer">Cancel</button>
                <button type="button" id="editSaveBtn" onclick="submitEditForm()" style="padding:10px 24px;background:#1e4575;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer">Save Changes</button>
            </div>
            <div id="editFormError" style="display:none;margin:0 24px 16px;background:#fee2e2;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;font-weight:600;"></div>
        </form>
    </div>
</div>

<!-- Bulk Delete Confirm Modal -->
<div id="cdBulkDeleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this)cdCancelBulkDelete()">
    <div style="background:white;border-radius:16px;max-width:420px;width:90%;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.2);">
        <div style="background:linear-gradient(135deg,#dc2626,#ef4444);padding:18px 22px;display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div style="flex:1;">
                <div style="color:rgba(255,255,255,.75);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;">Confirm Deletion</div>
                <div style="color:white;font-size:15px;font-weight:700;margin-top:1px;">Delete Selected Records</div>
            </div>
        </div>
        <div style="padding:20px 22px;">
            <p style="font-size:14px;color:#374151;margin:0 0 4px;">Delete <strong id="cdBulkDeleteCount">0</strong> selected record(s)?</p>
            <p style="font-size:12px;color:#94a3b8;margin:0 0 18px;">This action cannot be undone.</p>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button onclick="cdCancelBulkDelete()" style="padding:9px 18px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;">No, Cancel</button>
                <button onclick="cdConfirmBulkDelete()" style="padding:9px 20px;background:#dc2626;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
const IS_ADMIN = {{ (auth()->check() && auth()->user()->isAdmin()) ? 'true' : 'false' }};

let _localPermAction = '', _localPermModule = 'Client Database', _localPermRecordId = null, _localPermRecordLabel = '';
let _pendingDeleteForm = null;

function requireAdmin(cb, recordId, recordLabel, action) {
    if (IS_ADMIN) { if (cb) cb(); return; }
    var btn = document.getElementById('editSaveBtn');
    // Check if already approved for this specific record+action
    fetch(`/api/permission-requests/check?action=${action || 'edit'}&record_id=${recordId}`)
        .then(r => r.json())
        .then(data => {
            if (data.approved) {
                if (cb) cb();
            } else {
                _localPermAction = action || 'edit';
                _localPermRecordId = recordId || null;
                _localPermRecordLabel = recordLabel || '';
                document.getElementById('localPermTitle').textContent = 'Request to ' + (_localPermAction.charAt(0).toUpperCase() + _localPermAction.slice(1)) + ' Record';
                document.getElementById('localPermRecord').textContent = recordLabel || 'Record #' + recordId;
                document.getElementById('localPermReason').value = '';
                document.getElementById('localPermError').style.display = 'none';
                document.getElementById('permissionModal').style.display = 'flex';
                setTimeout(() => document.getElementById('localPermReason').focus(), 100);
            }
        })
        .catch(() => {
            // On fetch error, still show permission request modal
            _localPermAction = action || 'edit';
            _localPermRecordId = recordId || null;
            _localPermRecordLabel = recordLabel || '';
            document.getElementById('localPermTitle').textContent = 'Request to ' + (_localPermAction.charAt(0).toUpperCase() + _localPermAction.slice(1)) + ' Record';
            document.getElementById('localPermRecord').textContent = recordLabel || 'Record #' + recordId;
            document.getElementById('localPermReason').value = '';
            document.getElementById('localPermError').style.display = 'none';
            document.getElementById('permissionModal').style.display = 'flex';
        });
}

function requireAdminSync(e, recordId, recordLabel) {
    if (IS_ADMIN) {
        return confirm('Delete this record?');
    }
    e.preventDefault();
    // Check if already approved
    fetch(`/api/permission-requests/check?action=delete&record_id=${recordId}`)
        .then(r => r.json())
        .then(data => {
            if (data.approved) {
                if (confirm('Delete this record?')) {
                    // Find and submit the delete form for this record
                    var form = document.querySelector('tr[data-id="' + recordId + '"] form[action*="DELETE"], tr[data-id="' + recordId + '"] form[method="POST"]');
                    // Find the delete form specifically (has DELETE method input)
                    var allForms = document.querySelectorAll('tr[data-id="' + recordId + '"] form');
                    for (var f of allForms) {
                        var methodInput = f.querySelector('input[name="_method"]');
                        if (methodInput && methodInput.value === 'DELETE') {
                            f.submit();
                            return;
                        }
                    }
                }
            } else {
                _localPermAction = 'delete';
                _localPermRecordId = recordId || null;
                _localPermRecordLabel = recordLabel || '';
                document.getElementById('localPermTitle').textContent = 'Request to Delete Record';
                document.getElementById('localPermRecord').textContent = recordLabel || 'Record #' + recordId;
                document.getElementById('localPermReason').value = '';
                document.getElementById('localPermError').style.display = 'none';
                document.getElementById('permissionModal').style.display = 'flex';
                setTimeout(() => document.getElementById('localPermReason').focus(), 100);
            }
        });
    return false;
}

function closeLocalPermModal() {
    document.getElementById('permissionModal').style.display = 'none';
}

function submitLocalPermRequest() {
    const reason = document.getElementById('localPermReason').value.trim();
    if (reason.length < 5) { document.getElementById('localPermError').style.display = 'block'; return; }
    document.getElementById('localPermError').style.display = 'none';
    const btn = document.getElementById('localPermBtn');
    btn.disabled = true; btn.textContent = 'Sending...';
    fetch('/api/permission-requests', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ action: _localPermAction, module: _localPermModule, record_id: _localPermRecordId, record_label: _localPermRecordLabel, reason })
    })
    .then(r => r.json())
    .then(() => {
        closeLocalPermModal();
        btn.disabled = false; btn.textContent = 'Send Request';
        if (typeof showToast === 'function') showToast('Your request has been sent to admin for approval.', 'success', 'Request Sent');
        if (typeof pollNotifications === 'function') pollNotifications();
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Send Request'; });
}

// ── Searchable dropdown widget (shared by Add form and Edit modal) ──
// Every "type or select" field (Developer's Name, Terms of Payment — in both
// the Add form and the Edit modal) uses the same three generic functions
// instead of one copy-pasted set of functions per field. Each dropdown
// container carries data-dropdown-input="<id of its text input>" so a single
// outside-click listener can close whichever ones are open.

function toggleSearchDropdown(dropdownId) {
    var d = document.getElementById(dropdownId);
    if (!d) return;
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
}

function filterSearchDropdown(dropdownId, value) {
    var d = document.getElementById(dropdownId);
    if (!d) return;
    var items = d.children, f = (value || '').toUpperCase(), has = false;
    for (var i of items) {
        var show = i.textContent.toUpperCase().includes(f);
        i.style.display = show ? '' : 'none';
        if (show) has = true;
    }
    d.style.display = has ? 'block' : 'none';
}

function selectSearchOption(inputId, dropdownId, value) {
    var input = document.getElementById(inputId);
    if (input) input.value = value;
    var d = document.getElementById(dropdownId);
    if (d) d.style.display = 'none';
    highlightSelectedOption(dropdownId, value);
}

// Closes any open searchable dropdown when a click lands outside its input,
// toggle button, and option list (all three live together in the same
// position:relative wrapper, so "outside the wrapper" == "outside the field").
document.addEventListener('click', function (e) {
    document.querySelectorAll('[data-dropdown-input]').forEach(function (dropdown) {
        var input = document.getElementById(dropdown.dataset.dropdownInput);
        var wrapper = input ? input.parentElement : null;
        if (wrapper && !wrapper.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
});

// Marks the option matching `value` as selected (persistent highlight, survives
// mouseout) inside the given dropdown, and clears the highlight on the rest.
function highlightSelectedOption(dropdownId, value) {
    var d = document.getElementById(dropdownId);
    if (!d) return;
    for (var child of d.children) {
        if (value && child.dataset.value === value) {
            child.dataset.selected = 'true';
            child.style.background = '#dbeafe';
            child.style.fontWeight = '700';
        } else {
            child.dataset.selected = 'false';
            child.style.background = '';
            child.style.fontWeight = '500';
        }
    }
}

function fmtComma(n) {
    if (!n && n !== 0) return '';
    return parseFloat(n).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:10});
}

function onPriceSqmInput(el) {
    // Strip commas, keep only digits and decimal
    var raw = el.value.replace(/,/g, '');
    var num = parseFloat(raw);
    // Store raw numeric in hidden field
    document.getElementById('f_price_sqm').value = isNaN(num) ? '' : num;
    el.setCustomValidity((!isNaN(num) && num < 0) ? 'Price per SQM cannot be negative.' : '');
    // Format display with commas (only if user has finished typing a valid number)
    // Use a small delay so cursor doesn't jump while typing
    clearTimeout(el._fmt);
    el._fmt = setTimeout(function() {
        if (!isNaN(num) && raw !== '') {
            // Comma-separate thousands, but never force trailing zeroes —
            // only show the decimals the user actually typed.
            el.value = num.toLocaleString('en-US', { maximumFractionDigits: 10 });
        }
    }, 800);
    computeTCP();
}

// ── Downpayment Date must be on/after Reservation Date ──
// Uses setCustomValidity so the browser shows the same native tooltip
// style as `required`/`min`, instead of an alert() or custom banner.
function validateDownpaymentDate() {
    var resInput = document.getElementById('f_reservation_date');
    var dpInput  = document.getElementById('f_date_of_downpayment');

    if (!resInput.value || !dpInput.value) {
        dpInput.setCustomValidity('');
        return;
    }
    // Date input values are "YYYY-MM-DD", so string comparison works directly.
    if (dpInput.value < resInput.value) {
        dpInput.setCustomValidity('Date of Downpayment cannot be earlier than the Reservation Date.');
    } else {
        dpInput.setCustomValidity('');
    }
}

// Same rule, same technique, for the Edit modal's date fields — kept as a
// separate function (rather than parameterizing validateDownpaymentDate)
// since the Edit modal isn't a native form submit, so this gets invoked
// explicitly from submitEditForm() via form.reportValidity() as well as
// on each date's onchange.
function validateEditDownpaymentDate() {
    var resInput = document.getElementById('edit_reservation_date');
    var dpInput  = document.getElementById('edit_date_of_downpayment');

    if (!resInput.value || !dpInput.value) {
        dpInput.setCustomValidity('');
        return;
    }
    if (dpInput.value < resInput.value) {
        dpInput.setCustomValidity('Date of Downpayment cannot be earlier than the Reservation Date.');
    } else {
        dpInput.setCustomValidity('');
    }
}


// ctx selects which form's fields to read/write: 'f' (default) is the Add
// New Client Record form, 'edit' is the Edit Client Record modal. Both forms
// share the same TCP / Discount Value / Net TCP formulas — only the element
// ids (and whether there's a separate comma-formatted display field) differ.
function computeTCP(ctx){
    ctx = ctx || 'f';
    if (ctx === 'edit') {
        var area = parseFloat(document.getElementById('edit_lot_area').value) || 0;
        var psqm = parseFloat(document.getElementById('edit_price_sqm').value) || 0;
        var tcp  = area * psqm;
        document.getElementById('edit_tcp').value = tcp ? tcp.toFixed(2) : '';
        computeDiscount('edit');
        return;
    }
    var area = parseFloat(document.getElementById('f_lot_area').value) || 0;
    var psqm = parseFloat(document.getElementById('f_price_sqm').value) || 0;
    var tcp  = area * psqm;
    document.getElementById('f_tcp').value = tcp ? tcp.toFixed(2) : '';
    document.getElementById('f_tcp_display').value = tcp ? fmtComma(tcp) : '';
    computeDiscount('f');
}
function computeDiscount(ctx){
    ctx = ctx || 'f';
    if (ctx === 'edit') {
        var tcp = parseFloat(document.getElementById('edit_tcp').value) || 0;
        var pct = parseFloat(document.getElementById('edit_discount').value) || 0;
        var val = tcp * (pct / 100);
        var net = tcp - val;
        document.getElementById('edit_discount_value').value = val ? val.toFixed(2) : '';
        document.getElementById('edit_net_tcp').value = net ? net.toFixed(2) : '';
        return;
    }
    var tcp  = parseFloat(document.getElementById('f_tcp').value) || 0;
    var pct  = parseFloat(document.getElementById('f_discount_pct').value) || 0;
    var val  = tcp * (pct / 100);
    var net  = tcp - val;
    document.getElementById('f_discount_val').value = val ? val.toFixed(2) : '';
    document.getElementById('f_net_tcp').value = net ? net.toFixed(2) : '';
    document.getElementById('f_net_tcp_display').value = net ? fmtComma(net) : '';
}
function computeDiscountFromValue(){
    var tcp = parseFloat(document.getElementById('f_tcp').value) || 0;
    var val = parseFloat(document.getElementById('f_discount_val').value) || 0;
    var pct = tcp > 0 ? (val / tcp) * 100 : 0;
    var net = tcp - val;
    document.getElementById('f_discount_pct').value = pct ? pct.toFixed(10).replace(/\.?0+$/, '') : '';
    document.getElementById('f_net_tcp').value = net ? net.toFixed(2) : '';
    document.getElementById('f_net_tcp_display').value = net ? fmtComma(net) : '';
}

function viewRow(id){
    fetch(`/sales-marketing/${id}`).then(r=>r.json()).then(d=>{
        var fmt=v=>(v??'-'), fmtD=v=>v?new Date(v).toLocaleDateString('en-US',{month:'short',day:'2-digit',year:'numeric'}):'-';
        var fmtP=v=>v?'₱'+parseFloat(v).toLocaleString('en-US',{minimumFractionDigits:2}):'-';
        var fields=[
            ["Developer's Name",fmt(d.developer_name)],
            ['Project Name',fmt(d.project_name)],
            ['Block & Lot Number',fmt(d.block_lot_number)],
            ["Client's Name",fmt(d.client_name)],
            ['Lot Area',d.lot_area?parseFloat(d.lot_area).toFixed(2)+' sqm':'-'],
            ['Price Per SQM',fmtP(d.price_sqm)],
            ['TCP',fmtP(d.tcp)],
            ['Discount',d.discount?parseFloat(d.discount).toFixed(2)+'%':'-'],
            ['Net TCP',fmtP(d.net_tcp)],
            ['Terms of Payment',fmt(d.terms_of_payment)],
            ['Reservation Date',fmtD(d.reservation_date)],
            ["Agent's Name",fmt(d.agent_name)],
            ['Client Status',fmt(d.client_status)||'No Status'],
            ['Downpayment Status',fmt(d.downpayment_status)||'— Not Set —'],
            ['Downpayment Amount',fmtP(d.downpayment_amount)],
            ['Downpayment Terms',d.downpayment_terms?d.downpayment_terms+' month'+(d.downpayment_terms>1?'s':''):'-'],
            ['DP Stage',(d.downpayment_stage ?? 0)+'/'+(d.downpayment_stage_total ?? 1)],
            ['Commission Status',fmt(d.status || 'Not Yet Released')],
            ['Date of Downpayment',fmtD(d.downpayment_date || d.date_of_downpayment)],
        ];
        document.getElementById('viewContent').innerHTML=fields.map(([l,v])=>`<div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">${l}</label><div style="font-size:14px;color:#374151;font-weight:500;padding:10px 14px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb">${v}</div></div>`).join('');
        document.getElementById('viewModal').style.display='flex';
    });
}
function editRow(id){
    fetch(`/sales-marketing/${id}`).then(r=>r.json()).then(d=>{
        document.getElementById('edit_id').value=d.id;
        document.getElementById('edit_developer_name').value=d.developer_name??'';
        highlightSelectedOption('edit_devDropdown', d.developer_name??'');
        document.getElementById('edit_project_name').value=d.project_name??'';
        document.getElementById('edit_block_lot_number').value=d.block_lot_number??'';
        document.getElementById('edit_client_name').value=d.client_name??'';
        document.getElementById('edit_lot_area').value=d.lot_area??'';
        document.getElementById('edit_price_sqm').value=d.price_sqm??'';
        document.getElementById('edit_discount').value=d.discount??'';
        // TCP, Discount Value, and Net TCP are read-only/derived fields — recompute
        // them from Lot Area, Price/SQM, and Discount % instead of trusting the
        // stored tcp/net_tcp columns, so the modal never opens showing figures
        // that don't match the record's own inputs.
        computeTCP('edit');
        document.getElementById('edit_terms_of_payment').value=d.terms_of_payment??'';
        highlightSelectedOption('edit_termsDropdown', d.terms_of_payment??'');
        document.getElementById('edit_reservation_date').value=d.reservation_date?(d.reservation_date+'').split('T')[0]:'';
        document.getElementById('edit_number_of_units').value=d.number_of_units??1;
        document.getElementById('edit_date_of_downpayment').value=d.date_of_downpayment?(d.date_of_downpayment+'').split('T')[0]:'';
        // The Edit modal's fields are reused across rows, so any leftover custom
        // validity message from a previous record must be cleared before
        // re-checking against this record's own dates.
        document.getElementById('edit_date_of_downpayment').setCustomValidity('');
        validateEditDownpaymentDate();
        document.getElementById('edit_agent_name').value=d.agent_name??'';
        document.getElementById('edit_client_status').value=d.status??'';
        document.getElementById('edit_date_requested').value=d.date_requested?(d.date_requested+'').split('T')[0]:'';
        document.getElementById('editForm').action=`/client-database/${d.id}`;
        document.getElementById('editFormError').innerHTML='';
        document.getElementById('editFormError').style.display='none';
        document.getElementById('edit_devDropdown').style.display='none';
        document.getElementById('edit_termsDropdown').style.display='none';
        document.getElementById('editModal').style.display='flex';
    }).catch(err=>{
        alert('Failed to load record. Please try again.');
        console.error(err);
    });
}

// Staff edit — non-admins can edit directly
function staffEditRow(id, label) {
    editRow(id);
}

// Staff delete — plain confirmation, no permission gate
function staffDeleteConfirm(e, id, label) {
    e.preventDefault();
    if (confirm('Delete this record?')) {
        var rows = document.querySelectorAll('tr[data-id="' + id + '"] form');
        for (var f of rows) {
            var m = f.querySelector('input[name="_method"]');
            if (m && m.value === 'DELETE') { f.submit(); return; }
        }
    }
    return false;
}

// Renders one or more error messages inside the Edit modal's banner, using
// the same bulleted-list convention the rest of the app uses for
// $errors->all() (see tripping.blade.php / commission-monitoring.blade.php),
// so a failed Edit submission reads exactly like a failed Add submission.
function renderEditFormErrors(messages) {
    var errEl = document.getElementById('editFormError');
    var list = (Array.isArray(messages) ? messages : [messages]).filter(Boolean);
    if (!list.length) list = ['Failed to save. Please try again.'];
    errEl.innerHTML = list.length === 1
        ? list[0]
        : list.map(function (m) { return '<div>• ' + m + '</div>'; }).join('');
    errEl.style.display = 'block';
}

function submitEditForm() {
    var form = document.getElementById('editForm');
    var btn  = document.getElementById('editSaveBtn');
    var errEl= document.getElementById('editFormError');

    // Reset any error state left over from a previous attempt.
    errEl.innerHTML = '';
    errEl.style.display = 'none';

    // The Save button is type="button", not type="submit", so the browser
    // never runs native constraint validation (required, min/max on Lot
    // Area / Price per SQM / Discount, and the custom Reservation-Date-
    // before-Downpayment-Date rule) the way it does for the Add form's real
    // submit button. Run it explicitly here — reportValidity() shows the
    // browser's standard inline tooltip on whichever field is invalid.
    validateEditDownpaymentDate();
    if (!form.reportValidity()) {
        return;
    }

    // Basic client-side validation
    var projectName = document.getElementById('edit_project_name').value.trim();
    var clientName  = document.getElementById('edit_client_name').value.trim();
    var agentName   = document.getElementById('edit_agent_name').value.trim();
    var terms       = document.getElementById('edit_terms_of_payment').value.trim();
    if (!projectName || !clientName || !agentName || !terms) {
        renderEditFormErrors('Please fill in all required fields (Project Name, Client Name, Agent Name, Terms of Payment).');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Saving...';

    var formData = new FormData(form);
    var action   = form.action;

    fetch(action, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: formData,
    }).then(r => {
        if (r.redirected || r.ok) {
            // Success — reload page
            window.location.reload();
        } else {
            return r.text().then(text => {
                btn.disabled = false;
                btn.textContent = 'Save Changes';
                // Try to parse a structured JSON error response first (this is what
                // update() returns on validation failure: {error, errors}). Fall
                // back to a generic, status-coded message for anything else
                // (auth/permission errors, a 500 HTML error page, etc.)
                try {
                    var json = JSON.parse(text);
                    if (Array.isArray(json.errors) && json.errors.length) {
                        renderEditFormErrors(json.errors);
                    } else {
                        renderEditFormErrors(json.error || json.message || 'Failed to save. Please try again.');
                    }
                } catch (e) {
                    renderEditFormErrors('Failed to save. Please try again. (Status: ' + r.status + ')');
                }
            });
        }
    }).catch(() => {
        btn.disabled = false;
        btn.textContent = 'Save Changes';
        renderEditFormErrors('Network error. Please try again.');
    });
}

document.addEventListener('DOMContentLoaded', function () {
    cdFilter();
    cdInitScrollbar();

    // Force recalculation after the page fully renders
    setTimeout(function () {
        window.dispatchEvent(new Event('resize'));
    }, 100);

    setTimeout(function () {
        window.dispatchEvent(new Event('resize'));
    }, 500);

    // Highlight row from permission notification
    const params = new URLSearchParams(window.location.search);
    const highlightId = params.get('highlight');
    const hlStatus = params.get('status');
    const hlAction = params.get('action');
    if (highlightId) {
        // Strip highlight params from the URL now that we've read them, so a
        // browser refresh loads a clean URL and the highlight does not reappear.
        window.history.replaceState({}, '', window.location.pathname);

        // Wait for full page render then scroll
        function doHighlight() {
            const row = document.querySelector('tr[data-id="' + highlightId + '"]');
            if (!row) return;

            // Force show the row even if filtered
            row.style.display = '';

            const isApproved = hlStatus === 'approved';
            const isPending  = hlStatus === 'pending';
            const isDuplicate= hlStatus === 'duplicate';
            const bgColor    = isApproved ? 'rgba(22,163,74,.15)' : (isPending ? 'rgba(234,179,8,.15)' : 'rgba(220,38,38,.12)');
            const borderColor= isApproved ? '#16a34a' : (isPending ? '#d97706' : '#dc2626');
            const badgeColor = isApproved ? '#16a34a' : (isPending ? '#d97706' : '#dc2626');
            const badgeText  = isApproved ? '✓ Approved — Can ' + (hlAction||'edit')
                             : (isPending  ? '👁 ' + (hlAction||'edit') + ' requested'
                             : '✕ Rejected');

            // Duplicate records: no highlight styling — the View modal (below)
            // takes its place instead.
            if (!isDuplicate) {
                row.style.background   = bgColor;
                row.style.outline      = '2px solid ' + borderColor;
                row.style.outlineOffset= '-1px';
                row.style.transition   = 'all .3s';

                // Badge goes on the index cell (2nd cell) — 1st cell is the select checkbox
                const cells = row.querySelectorAll('td');
                const badgeTd = cells[1] || cells[0];
                if (badgeTd && !badgeTd.querySelector('.hl-badge')) {
                    const badge = document.createElement('span');
                    badge.className = 'hl-badge';
                    badge.style.cssText = 'display:inline-block;margin-left:6px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:' + badgeColor + ';color:white;vertical-align:middle;';
                    badge.textContent = badgeText;
                    badgeTd.appendChild(badge);
                }
            }

            // Find the scrollable container and scroll to row
            const scroller = document.querySelector('.page-content');
            if (scroller) {
                const rowRect = row.getBoundingClientRect();
                const scrollerRect = scroller.getBoundingClientRect();
                const scrollTo = scroller.scrollTop + rowRect.top - scrollerRect.top - 100;
                scroller.scrollTo({ top: scrollTo, behavior: 'smooth' });
            } else {
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Duplicate records: open the View modal for this record once the
            // scroll settles, instead of the old highlight-only behavior.
            if (isDuplicate) {
                setTimeout(function() {
                    viewRow(parseInt(highlightId));
                }, 600);
            }


            // If approved + edit action → auto-open edit modal
            if (isApproved && hlAction === 'edit') {
                setTimeout(function() {
                    editRow(parseInt(highlightId));
                }, 600);
            }

            // If approved + delete action → auto-trigger delete confirm
            if (isApproved && hlAction === 'delete') {
                setTimeout(function() {
                    if (confirm('Your delete request was approved. Delete this record now?')) {
                        var allForms = document.querySelectorAll('tr[data-id="' + highlightId + '"] form');
                        for (var f of allForms) {
                            var m = f.querySelector('input[name="_method"]');
                            if (m && m.value === 'DELETE') { f.submit(); return; }
                        }
                    }
                }, 700);
            }

            if (!isDuplicate) {
                setTimeout(function() {
                    row.style.background   = '';
                    row.style.outline      = '';
                    const badge = row.querySelector('.hl-badge');
                    if (badge) badge.remove();
                }, 10000);
                // Click anywhere on row to remove highlight immediately
                row.addEventListener('click', function() {
                    row.style.background = '';
                    row.style.outline    = '';
                    const badge = row.querySelector('.hl-badge');
                    if (badge) badge.remove();
                }, { once: true });
            }
        }

        // Try multiple times to ensure table is rendered
        setTimeout(doHighlight, 800);
        setTimeout(doHighlight, 1500);
    }

    @if(session('duplicate_error'))
    showDuplicateModal({{ (int) session('duplicate_id') }});
    @endif
});

const MONTH_ALIASES = {
    'january':'jan','february':'feb','march':'mar','april':'apr',
    'may':'may','june':'jun','july':'jul','august':'aug',
    'september':'sep','october':'oct','november':'nov','december':'dec',
    'jan':'jan','feb':'feb','mar':'mar','apr':'apr',
    'jun':'jun','jul':'jul','aug':'aug','sep':'sep',
    'oct':'oct','nov':'nov','dec':'dec'
};

function cdInitScrollbar() {
    var wrap  = document.querySelector('.cd-table-wrap');
    var track = document.getElementById('cdScrollTrack');
    var thumb = document.getElementById('cdScrollThumb');
    if (!wrap || !track || !thumb) return;

    function update() {
        var scrollable = wrap.scrollWidth > wrap.clientWidth + 1;
        track.style.display = scrollable ? 'block' : 'none';
        if (!scrollable) return;
        var trackWidth = track.clientWidth;
        var thumbWidth = Math.max(30, (wrap.clientWidth / wrap.scrollWidth) * trackWidth);
        thumb.style.width = thumbWidth + 'px';
        var maxThumbLeft  = trackWidth - thumbWidth;
        var maxScrollLeft = wrap.scrollWidth - wrap.clientWidth;
        var ratio = maxScrollLeft > 0 ? wrap.scrollLeft / maxScrollLeft : 0;
        thumb.style.left = (ratio * maxThumbLeft) + 'px';
    }

    function scrollToThumbLeft(newLeft) {
        var trackWidth   = track.clientWidth;
        var thumbWidth   = thumb.offsetWidth;
        var maxThumbLeft = trackWidth - thumbWidth;
        newLeft = Math.min(maxThumbLeft, Math.max(0, newLeft));
        thumb.style.left = newLeft + 'px';
        var maxScrollLeft = wrap.scrollWidth - wrap.clientWidth;
        wrap.scrollLeft = maxThumbLeft > 0 ? (newLeft / maxThumbLeft) * maxScrollLeft : 0;
    }

    update();
    window.addEventListener('resize', update);
    wrap.addEventListener('scroll', update);

    var dragging = false, startX = 0, startLeft = 0;

    thumb.addEventListener('mousedown', function(e) {
        dragging = true;
        thumb.classList.add('dragging');
        startX = e.clientX;
        startLeft = thumb.offsetLeft;
        e.preventDefault();
    });
    document.addEventListener('mousemove', function(e) {
        if (!dragging) return;
        scrollToThumbLeft(startLeft + (e.clientX - startX));
    });
    document.addEventListener('mouseup', function() {
        if (dragging) { dragging = false; thumb.classList.remove('dragging'); }
    });

    // Click on the track (not the thumb itself) jumps to that position
    track.addEventListener('click', function(e) {
        if (e.target === thumb) return;
        var rect = track.getBoundingClientRect();
        scrollToThumbLeft((e.clientX - rect.left) - (thumb.offsetWidth / 2));
    });

    // Touch support
    thumb.addEventListener('touchstart', function(e) {
        dragging = true;
        startX = e.touches[0].clientX;
        startLeft = thumb.offsetLeft;
    }, { passive: true });
    document.addEventListener('touchmove', function(e) {
        if (!dragging) return;
        scrollToThumbLeft(startLeft + (e.touches[0].clientX - startX));
    }, { passive: true });
    document.addEventListener('touchend', function() { dragging = false; });
}

/* ---- Filter dropdown + chips ---- */
var CD_FILTERABLE_FIELDS = [
    { key: 'control',            label: 'Control Number',     dataAttr: 'data-control',           type: 'text'   },
    { key: 'developer',          label: 'Developer',          dataAttr: 'data-developer',        type: 'text'   },
    { key: 'project',            label: 'Project',            dataAttr: 'data-project',           type: 'text'   },
    { key: 'block-lot',          label: 'Block & Lot',        dataAttr: 'data-block-lot',         type: 'text'   },
    { key: 'client',             label: 'Client',             dataAttr: 'data-client',            type: 'text'   },
    { key: 'lot-area',           label: 'Lot Area',           dataAttr: 'data-lot-area',          type: 'numrange'   },
    { key: 'price-sqm',          label: 'Price/SQM',          dataAttr: 'data-price-sqm',         type: 'numrange'   },
    { key: 'tcp',                label: 'TCP',                dataAttr: 'data-tcp',               type: 'numrange'   },
    { key: 'discount',           label: 'Discount',           dataAttr: 'data-discount',          type: 'text'   },
    { key: 'discount-value',     label: 'Discount Value',     dataAttr: 'data-discount-value',    type: 'numrange'   },
    { key: 'net-tcp',            label: 'Net TCP',            dataAttr: 'data-net-tcp',           type: 'numrange'   },
    { key: 'terms',              label: 'Terms',              dataAttr: 'data-terms',             type: 'select', options: ['30% DP - 70% BAL 5 YRS','50% DP - 50% BAL 5 YRS','30% DP (6 MOS) - 70% BAL 54 MOS','30% DP (3 MOS) - 70% BAL 57 MOS','30% DP (9 MOS) - 70% BAL 36 MOS','30% DP (2 MOS) - 70% BAL 57 MOS','30% DP (2 MOS) - 70% BAL 5 YRS','STRAIGHT PAYMENT','30% DP - 70% BAL 3 YRS'] },
    { key: 'reservation-date',   label: 'Reservation Date',   dataAttr: 'data-reservation-date',  type: 'daterange' },
    { key: 'units',              label: 'Units',              dataAttr: 'data-units',             type: 'text'   },
    { key: 'downpayment-date',   label: 'Downpayment Date',   dataAttr: 'data-downpayment-date',  type: 'daterange' },
    { key: 'agent',              label: 'Agent',              dataAttr: 'data-agent',             type: 'text'   },
    { key: 'status',             label: 'Client Status',      dataAttr: 'data-client-status',     type: 'select', options: ['Pending','Done','Cancelled'] },
    { key: 'downpayment-stage',  label: 'DP Stage',           dataAttr: 'data-downpayment-stage', type: 'text'   },
    { key: 'downpayment-status', label: 'Downpayment Status', dataAttr: 'data-downpayment-status',type: 'text'   },
];

var cdColumnFilters = {};

function cdFieldConfig(key) {
    return CD_FILTERABLE_FIELDS.find(function (f) { return f.key === key; });
}

function toggleCdColumnFilterMenu(e) {
    e.stopPropagation();
    var menu = document.getElementById('cdColumnFilterMenu');
    if (menu.style.display === 'block') { menu.style.display = 'none'; return; }
    renderCdColumnFilterMenu();
    menu.style.display = 'block';
}

function renderCdColumnFilterMenu() {
    var menu = document.getElementById('cdColumnFilterMenu');
    menu.innerHTML = '';
    CD_FILTERABLE_FIELDS.forEach(function (f) {
        var item = document.createElement('div');
        item.className = 'column-filter-menu-item' + (cdColumnFilters.hasOwnProperty(f.key) ? ' is-active' : '');
        item.innerHTML = '<span class="cfm-check">✓</span><span>' + f.label + '</span>';
        item.onclick = function (ev) { ev.stopPropagation(); cdToggleColumnFilter(f.key); };
        menu.appendChild(item);
    });
}

function cdToggleColumnFilter(key) {
    if (cdColumnFilters.hasOwnProperty(key)) {
        delete cdColumnFilters[key];
    } else {
        var f = cdFieldConfig(key);
        cdColumnFilters[key] = (f && (f.type === 'daterange' || f.type === 'numrange')) ? { from: '', to: '' } : '';
    }
    renderCdColumnFilterMenu();
    renderCdActiveColumnFilters();
    updateCdFilterBadge();
    cdFilter();
    document.getElementById('cdColumnFilterMenu').style.display = 'none';
}

function cdRemoveColumnFilter(key) {
    delete cdColumnFilters[key];
    renderCdActiveColumnFilters();
    updateCdFilterBadge();
    cdFilter();
}

function updateCdFilterBadge() {
    var badge = document.getElementById('cdFilterCountBadge');
    var count = Object.keys(cdColumnFilters).length;
    badge.style.display = count > 0 ? 'inline-flex' : 'none';
    badge.textContent = count;
}

function renderCdActiveColumnFilters() {
    var row = document.getElementById('cdActiveColumnFiltersRow');
    var keys = Object.keys(cdColumnFilters);
    row.innerHTML = '';
    if (keys.length === 0) { row.style.display = 'none'; return; }
    row.style.display = 'flex';

    keys.forEach(function (key) {
        var f = cdFieldConfig(key);
        if (!f) return;
        var chip = document.createElement('div');
        chip.className = 'column-filter-chip';
        var label = document.createElement('label');
        label.textContent = f.label;
        chip.appendChild(label);

        var input;
        if (f.type === 'daterange') {
            if (!cdColumnFilters[key] || typeof cdColumnFilters[key] !== 'object') {
                cdColumnFilters[key] = { from: '', to: '' };
            }
            var range = cdColumnFilters[key];

            input = document.createElement('span');
            input.style.display = 'flex';
            input.style.alignItems = 'center';
            input.style.gap = '6px';

            var fromInput = document.createElement('input');
            fromInput.type = 'date';
            fromInput.value = range.from || '';
            fromInput.onchange = function () { range.from = this.value; cdFilter(); };

            var toLabel = document.createElement('span');
            toLabel.textContent = 'to';
            toLabel.style.cssText = 'color:#8a9bad;font-size:12px;';

            var toInput = document.createElement('input');
            toInput.type = 'date';
            toInput.value = range.to || '';
            toInput.onchange = function () { range.to = this.value; cdFilter(); };

            input.appendChild(fromInput);
            input.appendChild(toLabel);
            input.appendChild(toInput);
        } else if (f.type === 'numrange') {
            if (!cdColumnFilters[key] || typeof cdColumnFilters[key] !== 'object') {
                cdColumnFilters[key] = { from: '', to: '' };
            }
            var numRange = cdColumnFilters[key];

            input = document.createElement('span');
            input.style.display = 'flex';
            input.style.alignItems = 'center';
            input.style.gap = '6px';

            var numFromInput = document.createElement('input');
            numFromInput.type = 'number';
            numFromInput.step = 'any';
            numFromInput.placeholder = 'Min';
            numFromInput.style.width = '100px';
            numFromInput.value = numRange.from || '';
            numFromInput.oninput = numFromInput.onchange = function () { numRange.from = this.value; cdFilter(); };

            var numToLabel = document.createElement('span');
            numToLabel.textContent = 'to';
            numToLabel.style.cssText = 'color:#8a9bad;font-size:12px;';

            var numToInput = document.createElement('input');
            numToInput.type = 'number';
            numToInput.step = 'any';
            numToInput.placeholder = 'Max';
            numToInput.style.width = '100px';
            numToInput.value = numRange.to || '';
            numToInput.oninput = numToInput.onchange = function () { numRange.to = this.value; cdFilter(); };

            input.appendChild(numFromInput);
            input.appendChild(numToLabel);
            input.appendChild(numToInput);
        } else if (f.type === 'select') {
            input = document.createElement('select');
            var optAll = document.createElement('option');
            optAll.value = ''; optAll.textContent = 'All';
            input.appendChild(optAll);
            f.options.forEach(function (o) {
                var opt = document.createElement('option');
                opt.value = o; opt.textContent = o;
                if (cdColumnFilters[key] === o) opt.selected = true;
                input.appendChild(opt);
            });
            input.onchange = function () { cdColumnFilters[key] = this.value; cdFilter(); };
        } else {
            input = document.createElement('input');
            input.type = f.type === 'date' ? 'date' : 'text';
            input.placeholder = 'Search ' + f.label.toLowerCase() + '...';
            input.value = cdColumnFilters[key];
            input.oninput = function () { cdColumnFilters[key] = this.value; cdFilter(); };
        }
        chip.appendChild(input);

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'cfm-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function () { cdRemoveColumnFilter(key); };
        chip.appendChild(removeBtn);

        row.appendChild(chip);
    });

    var clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.className = 'clear-column-filters-btn';
    clearBtn.textContent = 'Clear Filters';
    clearBtn.onclick = function () {
        cdColumnFilters = {};
        renderCdActiveColumnFilters();
        updateCdFilterBadge();
        cdFilter();
    };
    row.appendChild(clearBtn);
}

function cdMatchesColumnFilters(row) {
    for (var key in cdColumnFilters) {
        var f = cdFieldConfig(key);
        if (!f) continue;

        if (f.type === 'daterange') {
            var range = cdColumnFilters[key];
            if (!range || (!range.from && !range.to)) continue;
            var rowDate = (row.getAttribute(f.dataAttr) || '').toString();
            if (!rowDate) return false;
            if (range.from && rowDate < range.from) return false;
            if (range.to && rowDate > range.to) return false;
            continue;
        }

        if (f.type === 'numrange') {
            var numRangeVal = cdColumnFilters[key];
            if (!numRangeVal || (numRangeVal.from === '' && numRangeVal.to === '')) continue;
            var rawVal = (row.getAttribute(f.dataAttr) || '').toString().replace(/[^0-9.\-]/g, '');
            var rowNum = rawVal === '' ? NaN : parseFloat(rawVal);
            if (isNaN(rowNum)) return false;
            if (numRangeVal.from !== '' && rowNum < parseFloat(numRangeVal.from)) return false;
            if (numRangeVal.to !== '' && rowNum > parseFloat(numRangeVal.to)) return false;
            continue;
        }

        var filterVal = (cdColumnFilters[key] || '').toString().trim().toLowerCase();
        if (!filterVal) continue;
        var rowVal = (row.getAttribute(f.dataAttr) || '').toString().toLowerCase();

        if (f.type === 'date' || f.type === 'select') {
            if (rowVal !== filterVal) return false;
        } else {
            if (!rowVal.includes(filterVal)) return false;
        }
    }
    return true;
}

document.addEventListener('click', function (e) {
    var dropdown = document.getElementById('cdColumnFilterDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('cdColumnFilterMenu').style.display = 'none';
    }
});

function cdClearAll() {
    document.getElementById('cdSearch').value = '';
    cdColumnFilters = {};
    renderCdColumnFilterMenu();
    renderCdActiveColumnFilters();
    updateCdFilterBadge();
    cdFilter();
}

function cdFilter() {
    var raw = (document.getElementById('cdSearch')?.value || '').toLowerCase().trim();
    var keywords = raw ? raw.split(/\s+/).filter(k => k.length > 0).map(k => MONTH_ALIASES[k] || k) : [];

    var rows = document.querySelectorAll('#cdTableBody tr');
    var visible = 0;
    rows.forEach(function(r) {
        var cells = r.querySelectorAll('td');
        if (!cells.length) { return; } // skip "no records" placeholder row

        // Keyword search — matches Name, Agent, Project, Client, Block & Lot
        // (stored on the row as data-search) so it stays accurate regardless of column order.
        var searchText = (r.dataset.search || '').toLowerCase();
        var keyMatch = keywords.every(k => searchText.includes(k));

        var columnMatch = cdMatchesColumnFilters(r);

        var show = keyMatch && columnMatch;
        r.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    var countEl = document.getElementById('cdCount');
    if (countEl) countEl.textContent = visible + ' record(s) shown';

    cdUpdateBulkBar();
}

// ── Multi-select / bulk delete ──
function cdToggleSelectAll(source) {
    var rows = document.querySelectorAll('#cdTableBody tr');
    rows.forEach(function(r) {
        if (r.style.display === 'none') return; // only affect currently visible/filtered rows
        var cb = r.querySelector('.cd-row-checkbox');
        if (cb) cb.checked = source.checked;
    });
    cdUpdateBulkBar();
}

function cdUpdateBulkBar() {
    var checked = document.querySelectorAll('.cd-row-checkbox:checked');
    var btn = document.getElementById('cdBulkDeleteBtn');
    if (btn) {
        btn.textContent = 'Delete Selected (' + checked.length + ')';
        btn.disabled = checked.length === 0;
    }

    // Keep the "select all" checkbox in sync with the visible rows
    var selectAll = document.getElementById('cdSelectAll');
    if (selectAll) {
        var visibleCheckboxes = Array.from(document.querySelectorAll('#cdTableBody tr'))
            .filter(function(r) { return r.style.display !== 'none'; })
            .map(function(r) { return r.querySelector('.cd-row-checkbox'); })
            .filter(Boolean);
        selectAll.checked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(function(cb) { return cb.checked; });
        selectAll.indeterminate = !selectAll.checked && visibleCheckboxes.some(function(cb) { return cb.checked; });
    }
}

function cdGetSelectedIds() {
    return Array.from(document.querySelectorAll('.cd-row-checkbox:checked')).map(function(cb) { return cb.value; });
}

function cdDeleteSelected() {
    var ids = cdGetSelectedIds();
    if (!ids.length) {
        alert('Please select at least one record first (use the checkboxes on the left).');
        return;
    }

    if (!IS_ADMIN) {
        alert('Bulk delete is only available to admins. Please delete records individually — each will go through the usual permission request flow.');
        return;
    }

    // Open the custom confirm modal instead of relying on the native confirm(),
    // which browsers can silently suppress after repeated dialogs on a page.
    document.getElementById('cdBulkDeleteCount').textContent = ids.length;
    document.getElementById('cdBulkDeleteModal').style.display = 'flex';
}

function cdCancelBulkDelete() {
    document.getElementById('cdBulkDeleteModal').style.display = 'none';
}

function cdConfirmBulkDelete() {
    var ids = cdGetSelectedIds();
    document.getElementById('cdBulkDeleteModal').style.display = 'none';
    if (!ids.length) return;

    var btn = document.getElementById('cdBulkDeleteBtn');
    btn.disabled = true;
    btn.textContent = 'Deleting...';

    var csrfMeta = document.querySelector('meta[name=csrf-token]');
    if (!csrfMeta) {
        console.error('[cdConfirmBulkDelete] No <meta name="csrf-token"> found on this page — cannot send authenticated requests.');
        alert('Missing CSRF token meta tag on this page. Cannot delete records — check the console for details.');
        btn.disabled = false;
        btn.textContent = 'Delete Selected (' + ids.length + ')';
        return;
    }
    var csrf = csrfMeta.content;

    Promise.all(ids.map(function(id) {
        return fetch(`/client-database/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });
    })).then(function() {
        window.location.reload();
    }).catch(function(err) {
        console.error('[cdConfirmBulkDelete] error during delete:', err);
        alert('Some records may not have been deleted. Reloading the page...');
        window.location.reload();
    });
}

// ── Print Selected ──
function cdGetSelectedPrintRows() {
    return Array.from(document.querySelectorAll('.cd-row-checkbox:checked'))
        .map(function(cb) { return cb.closest('tr'); })
        .filter(function(row) { return row.style.display !== 'none'; });
}

// Most cells are plain text, but Client Status (a <select>) and DP Stage
// (two stacked <div>s) aren't — textContent on those would include every
// option or run the two divs together, so they're pulled out explicitly.
function cdGetPrintCellText(row, index) {
    const cells = row.cells;
    if (index === 18) { // Client Status
        const sel = cells[18].querySelector('select');
        if (sel) return sel.value || '— No Status —';
        return cells[18].textContent.trim();
    }
    if (index === 19) { // DP Stage
        const valueEl = cells[19].querySelector('div[id^="dpStageValue_"]');
        const statusEl = cells[19].querySelector('div[id^="dpStageStatus_"]');
        const val = valueEl ? valueEl.textContent.trim() : '';
        const status = (statusEl && statusEl.style.display !== 'none') ? statusEl.textContent.trim() : '';
        return status ? (val + ' (' + status + ')') : val;
    }
    return cells[index].textContent.trim();
}

function cdPrintSelectedRecords() {
    const rows = cdGetSelectedPrintRows();
    if (rows.length === 0) {
        if (typeof showToast === 'function') {
            showToast('Please select at least one record to print.', 'warning', 'No Selection');
        } else {
            alert('Please select at least one record to print.');
        }
        return;
    }

    const headers = ['Control Number','Developer','Project','Block & Lot','Client','Lot Area','Price/SQM','TCP','Discount (%)','Discount Value','Net TCP','Terms','Reservation Date','Units','Downpayment Date','Agent','Client Status','DP Stage','Downpayment Status'];

    let tableHtml = '<table class="cd-print-table"><thead><tr>';
    headers.forEach(function(h) { tableHtml += '<th>' + h + '</th>'; });
    tableHtml += '</tr></thead><tbody>';

    rows.forEach(function(row) {
        tableHtml += '<tr>';
        // cells 0=checkbox, 1=#, 2..20=data columns above, 21=Actions (skipped)
        for (let i = 2; i <= 20; i++) {
            tableHtml += '<td>' + cdGetPrintCellText(row, i) + '</td>';
        }
        tableHtml += '</tr>';
    });
    tableHtml += '</tbody></table>';

    const now = new Date();
    const dateStr = now.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

    const printArea = document.getElementById('cdPrintArea');
    printArea.innerHTML = `
        <div class="cd-print-header">
            <h2>Client Database Report</h2>
            <p>Generated on ${dateStr} — ${rows.length} record(s)</p>
        </div>
        ${tableHtml}
    `;

    // Reparent out of the clipped ancestor chain for the duration of the
    // print, same fix as Departmental Expenses — the @media print overflow
    // overrides above handle the rest.
    const printAreaAnchor = document.createComment('cdPrintArea-anchor');
    printArea.parentNode.insertBefore(printAreaAnchor, printArea);
    document.body.appendChild(printArea);

    function restoreCdPrintArea() {
        printAreaAnchor.parentNode.insertBefore(printArea, printAreaAnchor);
        printAreaAnchor.remove();
        window.removeEventListener('afterprint', restoreCdPrintArea);
    }
    window.addEventListener('afterprint', restoreCdPrintArea);

    window.print();
}



// ── Clear button — uses the app's real confirm modal, since window.confirm ──
// is globally overridden (always returns true) in layouts/dashboard.blade.php.
document.getElementById('commissionClearBtn').addEventListener('click', function () {
    window.showConfirmModal('Clear all entered data on this form?').then(function (confirmed) {
        if (!confirmed) return;
        document.getElementById('commissionForm').reset();
    });
});

// ── Duplicate client record check (restored from previous version) ──
document.getElementById('commissionForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var form = this;


    window.showConfirmModal('Submit this client record?').then(function (confirmed) {
        if (!confirmed) return;

        var clientName    = (form.querySelector('[name="client_name"]').value || '').trim();
        var projectName   = (form.querySelector('[name="project_name"]').value || '').trim();
        var developerName = (form.querySelector('[name="developer_name"]').value || '').trim();
        var blockLot      = (form.querySelector('[name="block_lot_number"]').value || '').trim();

        var params = new URLSearchParams({
            client_name: clientName,
            project_name: projectName,
            developer_name: developerName,
            block_lot_number: blockLot
        });

        fetch('/api/client-database/check-duplicate?' + params.toString())
            .then(r => r.json())
            .then(data => {
                if (data.duplicate) {
                    showDuplicateModal(data.id);
                } else {
                    form.submit();
                }
            })
            .catch(() => {
                form.submit();
            });
    });
});

function showDuplicateModal(recordId) {
    var modal = document.getElementById('duplicateRecordModal');
    modal.dataset.recordId = recordId;
    modal.style.display = 'flex';
}
function closeDuplicateModal() {
    document.getElementById('duplicateRecordModal').style.display = 'none';
}
function goToDuplicateRecord() {
    var modal = document.getElementById('duplicateRecordModal');
    var id = modal.dataset.recordId;
    window.location.href = '{{ route("client-database") }}?highlight=' + id + '&status=duplicate';
}

// ── Prefill from site visit Reserve button ──
(function() {
    const p = new URLSearchParams(window.location.search);
    const client    = p.get('prefill_client');
    const project   = p.get('prefill_project');
    const agent     = p.get('prefill_agent');
    const date      = p.get('prefill_date');
    const developer = p.get('prefill_developer');
    // Handle ?view= param from sidebar sub-links
    if (!client && !project) return;

    document.addEventListener('DOMContentLoaded', function() {
        const set = (name, val) => {
            const el = document.querySelector('form [name="' + name + '"]');
            if (el && val) el.value = val;
        };
        set('client_name',    client);
        set('project_name',   project);
        set('agent_name',     agent);
        set('reservation_date', date);
        if (developer) {
            // developer_name uses a custom dropdown input
            const devInput = document.getElementById('dev_name_input');
            if (devInput) devInput.value = developer;
        }

        // Scroll to form and highlight it
        const form = document.querySelector('form[action*="client-database"]');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            form.style.transition = 'box-shadow .4s';
            form.style.boxShadow  = '0 0 0 3px #7c3aed, 0 8px 32px rgba(124,58,237,.15)';
            setTimeout(() => { form.style.boxShadow = ''; }, 2500);
        }

        // Toast
        const toast = document.createElement('div');
        toast.textContent = '✓ Form pre-filled from site visit data';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#7c3aed;color:white;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.2)';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);

        window.history.replaceState({}, '', window.location.pathname);
    });
})();
</script>
<!-- Downpayment Installment Modal -->
<div id="dpModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:white;border-radius:16px;width:90%;max-width:580px;box-shadow:0 20px 60px rgba(0,0,0,0.3);max-height:90vh;display:flex;flex-direction:column;overflow-y:auto;overflow-x:hidden;">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);color:white;padding:20px 24px;display:flex;justify-content:space-between;align-items:center;flex-shrink:0">
            <h3 style="margin:0;font-size:18px;font-weight:700">Downpayment</h3>
            <button onclick="document.getElementById('dpModal').style.display='none'" style="background:rgba(255,255,255,0.2);border:none;color:white;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:18px">✕</button>
        </div>

        {{-- Read-only summary header: Terms of Payment / TCP / Total DP / Remaining Balance.
             These are all derived values — never directly editable by the user. --}}
        <div id="dp_summary_header" style="padding:16px 24px;background:#f8fafc;border-bottom:1.5px solid #e5e7eb;display:flex;flex-direction:column;gap:10px;">
            <div>
                <label style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Terms of Payment</label>
                <div id="dp_summary_terms" style="font-size:13px;font-weight:700;color:#1e4575;">—</div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Net TCP</label>
                    <div id="dp_summary_tcp" style="font-size:14px;font-weight:700;color:#374151;">₱0.00</div>
                </div>
                <div>
                    <label style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Total Downpayment</label>
                    <div id="dp_summary_total" style="font-size:14px;font-weight:700;color:#374151;">₱0.00</div>
                </div>
            </div>
            <div style="border-top:1px dashed #d0d5dd;padding-top:8px">
                <label style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Remaining DP Balance</label>
                <div id="dp_summary_remaining" style="font-size:16px;font-weight:700;color:#A37929;">₱0.00</div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;border-top:1px dashed #d0d5dd;padding-top:8px">
                <div>
                    <label style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Payment Stage</label>
                    <div id="dp_summary_stage" style="font-size:15px;font-weight:800;color:#1e4575;">0/1</div>
                </div>
                <div>
                    <label style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Next Commission Stage</label>
                    <div id="dp_summary_next_stage" style="font-size:15px;font-weight:800;color:#A37929;">—</div>
                </div>
            </div>
            <div style="border-top:1px dashed #d0d5dd;padding-top:8px">
                <label style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:7px">Commission Request Progress</label>
                <div id="dp_commission_progress" style="display:flex;flex-direction:column;gap:6px;">
                    <div style="font-size:12px;color:#94a3b8;">Loading stage records…</div>
                </div>
            </div>
        </div>

        {{-- Step 1: Choose type --}}
        <div id="dp_step_type" style="padding:24px;display:flex;flex-direction:column;gap:12px;flex-shrink:0">
            <p style="font-size:13px;color:#64748b;margin:0;">Select the payment mode. TCP, total downpayment, and installment count are calculated automatically from the client record.</p>
            <div style="display:flex;gap:12px">
                <button onclick="selectDPType('spot')" style="flex:1;padding:14px;border:2px solid #e2e8f0;border-radius:10px;background:white;cursor:pointer;font-size:14px;font-weight:600;color:#374151;transition:all .2s" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#1e4575'" onmouseout="this.style.background='white';this.style.borderColor='#e2e8f0'">
                    💰 Spot Downpayment
                </button>
                <button onclick="selectDPType('installment')" style="flex:1;padding:14px;border:2px solid #e2e8f0;border-radius:10px;background:white;cursor:pointer;font-size:14px;font-weight:600;color:#374151;transition:all .2s" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#1e4575'" onmouseout="this.style.background='white';this.style.borderColor='#e2e8f0'">
                    📅 Installment Downpayment
                </button>
            </div>
            <div style="display:flex;gap:12px">
                <button onclick="selectDPType('others')" style="flex:1;padding:14px;border:2px solid #e2e8f0;border-radius:10px;background:white;cursor:pointer;font-size:14px;font-weight:600;color:#374151;transition:all .2s" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#1e4575'" onmouseout="this.style.background='white';this.style.borderColor='#e2e8f0'">
                    📝 Others
                </button>
            </div>
        </div>

        {{-- Spot DP --}}
        <div id="dp_spot_section" style="display:none;padding:0 24px 24px;flex-direction:column;gap:12px">
            <div id="dp_spot_locked_notice" style="display:none;background:#fef3c7;border-left:3px solid #f59e0b;padding:10px 14px;border-radius:6px;font-size:12px;color:#92400e;">
                🔒 This downpayment has been finalized. Only admin can modify it.
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px">Amount</label>
                <div style="display:flex;align-items:center;border:2px solid #d0d5dd;border-radius:8px;overflow:hidden;background:white;">
                    <input type="number" id="dp_spot_amount" step="0.01" min="0" placeholder="0.00" readonly
                        style="flex:1;padding:10px 12px;border:none;outline:none;font-size:14px;background:#f3f4f6;cursor:not-allowed;font-weight:700;color:#374151;">
                    <button id="dp_spot_paid_btn" onclick="saveSpotDP()" style="padding:10px 16px;background:linear-gradient(135deg,#A37929,#d4a03a);color:white;border:none;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">Paid</button>
                </div>
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px">Date of Payment</label>
                <input type="date" id="dp_spot_date" style="width:100%;padding:10px 12px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;box-sizing:border-box;">
            </div>
        </div>

        {{-- Installment DP --}}
        <div id="dp_installment_section" style="display:none;flex-direction:column;">
            <div class="dp-installment-toolbar" style="padding:16px 24px;border-bottom:1px solid #e5e7eb;display:flex;gap:12px;align-items:flex-end;flex-shrink:0;flex-wrap:wrap">
                <div style="flex:1">
                    <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px">Total Amount</label>
                    <input type="number" id="dp_total_amount" step="0.01" min="0" placeholder="0.00" readonly
                        style="width:100%;padding:9px 12px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;box-sizing:border-box;background:#f3f4f6;cursor:not-allowed;font-weight:700;color:#374151">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px">Terms</label>
                    <select id="dp_terms_select" disabled style="padding:9px 12px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;background:#f3f4f6;cursor:not-allowed;color:#374151;width:100%;box-sizing:border-box;">
                        @for($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <button onclick="setupInstallments()" class="dp-installment-toolbar-btn" style="padding:9px 16px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap">Generate Terms</button>
            </div>
            <div id="dp_installments_list" style="padding:16px 24px;display:flex;flex-direction:column;gap:10px;">
                <div style="text-align:center;color:#94a3b8;padding:20px;font-size:13px;">Set amount and terms, then click "Set Terms".</div>
            </div>
        </div>

        {{-- Others DP --}}
        <div id="dp_others_section" style="display:none;padding:0 24px 24px;flex-direction:column;gap:12px">
            <div style="background:#fef3c7;border-left:3px solid #f59e0b;padding:10px 14px;border-radius:6px;font-size:12px;color:#92400e;margin-bottom:4px;">
                For more than 6 terms — enter total amount and number of terms, then click Set Terms.
            </div>
            <div style="display:flex;gap:12px;align-items:flex-end;">
                <div style="flex:1">
                    <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px">Total Amount</label>
                    <input type="number" id="dp_others_amount" step="0.01" min="0" placeholder="0.00"
                        style="width:100%;padding:9px 12px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;box-sizing:border-box">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px">No. of Terms</label>
                    <input type="number" id="dp_others_terms" min="7" max="120" placeholder="e.g. 12"
                        style="width:90px;padding:9px 12px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;box-sizing:border-box">
                </div>
                <button onclick="setupOthersInstallments()" style="padding:9px 16px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap">Set Terms</button>
            </div>
            <div id="dp_others_list" style="margin-top:12px;display:flex;flex-direction:column;gap:8px;"></div>
        </div>

        <div style="padding:16px 24px;border-top:1px solid #e5e7eb;flex-shrink:0;position:sticky;bottom:0;background:white;z-index:10;">
            <div id="dp_footer_type" style="display:flex">
            </div>
            <div id="dp_footer_spot" style="display:none;gap:10px">
                <button onclick="selectDPType('spot'); document.getElementById('dp_step_type').style.display='flex';" style="flex:1;padding:10px;background:#f1f5f9;color:#374151;border:1.5px solid #e2e8f0;border-radius:8px;font-weight:600;cursor:pointer">Back</button>
                <button id="dp_footer_spot_save" onclick="saveSpotDP()" style="flex:1;padding:10px;background:linear-gradient(135deg,#A37929,#d4a03a);color:white;border:none;border-radius:8px;font-weight:700;cursor:pointer">Save</button>
            </div>
            <div id="dp_footer_installment" style="display:none;gap:10px">
                <button onclick="document.getElementById('dp_step_type').style.display='flex';document.getElementById('dp_installment_section').style.display='none';" style="flex:1;padding:10px;background:#f1f5f9;color:#374151;border:1.5px solid #e2e8f0;border-radius:8px;font-weight:600;cursor:pointer">Back</button>
                <button onclick="document.getElementById('dpModal').style.display='none'" style="flex:1;padding:10px;background:linear-gradient(135deg,#A37929,#d4a03a);color:white;border:none;border-radius:8px;font-weight:700;cursor:pointer">Done</button>
            </div>
            <div id="dp_footer_others" style="display:none;gap:10px">
                <button onclick="document.getElementById('dp_step_type').style.display='flex';document.getElementById('dp_others_section').style.display='none';document.getElementById('dp_footer_others').style.display='none';document.getElementById('dp_footer_type').style.display='flex';" style="flex:1;padding:10px;background:#f1f5f9;color:#374151;border:1.5px solid #e2e8f0;border-radius:8px;font-weight:600;cursor:pointer">Back</button>
                <button onclick="saveOthersDP()" style="flex:1;padding:10px;background:linear-gradient(135deg,#A37929,#d4a03a);color:white;border:none;border-radius:8px;font-weight:700;cursor:pointer">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Commission Request Ready Notification Modal -->
<div id="commissionReadyModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:10000;align-items:center;justify-content:center" onclick="if(event.target===this)dismissCommissionReadyModal()">
    <div style="background:white;border-radius:16px;max-width:440px;width:90%;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.3);">
        <div style="background:linear-gradient(135deg,#A37929,#d4a03a);padding:20px 22px;display:flex;align-items:center;gap:12px;">
            <div style="width:38px;height:38px;background:rgba(255,255,255,.2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px;">💰</div>
            <div style="flex:1;">
                <div style="color:rgba(255,255,255,.75);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;">Commission Update</div>
                <div style="color:white;font-size:15px;font-weight:700;margin-top:1px;">Commission Ready to Request</div>
            </div>
        </div>
        <div style="padding:22px;">
            <p id="commissionReadyText" style="font-size:14px;color:#374151;margin:0 0 20px;line-height:1.5;">
                The agent's commission for this client is now ready to request.
            </p>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button onclick="dismissCommissionReadyModal()" style="padding:9px 18px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;">Not Now</button>
                <button id="commissionRequestBtn" onclick="requestCommissionStage()" style="padding:9px 20px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;">Request</button>
            </div>
        </div>
    </div>
</div>

<script>
let _dpRecordId = null;
let _dpNetTcp = 0;
let _dpTermsLabel = '';
let _dpTotalAmount = 0; // total downpayment amount, computed from Net TCP x DP%
let _dpPaidAmount = 0;
let _dpRemainingBalance = 0;
let _dpStage = 0;
let _dpStageTotal = 1;
let _dpNextCommissionStage = null;
let _dpCommissionReady = false;
let _dpAllCommissionStagesRequested = false;
let _dpCommissionStages = [];
let _dpHasFiledCommissionStage = false;
let _dpPendingReload = false; // set true when a page reload was deferred so the commission-ready popup could show first
const _dpCsrf = document.querySelector('meta[name=csrf-token]')?.content || '';
const _isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};

function openDPModalFromBtn(btn) {
    openDPModal(
        parseInt(btn.dataset.id),
        parseFloat(btn.dataset.amount) || 0,
        parseInt(btn.dataset.terms) || 1,
        parseFloat(btn.dataset.perTerm) || 0,
        btn.dataset.status || '',
        btn.dataset.dpDate || '',
        parseFloat(btn.dataset.netTcp) || 0,
        btn.dataset.termsLabel || '',
        parseInt(btn.dataset.stage) || 0,
        parseInt(btn.dataset.stageTotal) || 1
    );
}

// Parses the DP percentage out of a terms-of-payment label.
// Examples: "30% DP - 70% BAL 5 YRS" -> 0.30, "50% DP - 50% BAL 5 YRS" -> 0.50,
// "STRAIGHT PAYMENT" -> 1 (the full Net TCP is due, no separate "DP" concept).
// Falls back to 0.30 if nothing recognizable is found, since 30% DP is the
// most common plan — this keeps the header from showing ₱0.00 on odd/legacy labels.
function parseDPPercent(termsLabel) {
    if (!termsLabel) return 0;
    var label = termsLabel.toUpperCase();
    if (label.includes('STRAIGHT')) return 1;
    var m = label.match(/(\d+(?:\.\d+)?)\s*%\s*DP/);
    return m ? parseFloat(m[1]) / 100 : 0;
}

// Reads the installment count from labels such as "30% DP (6 MOS) - 70% BAL 54 MOS".
// Plans without an explicit DP month count are treated as a single/spot downpayment.
function parseDPMonths(termsLabel) {
    if (!termsLabel) return 1;
    var label = termsLabel.toUpperCase();
    if (label.includes('STRAIGHT')) return 1;
    var m = label.match(/DP\s*\(\s*(\d+)\s*MOS?\s*\)/);
    return m ? Math.max(1, parseInt(m[1], 10)) : 1;
}

function fmtPeso(n) {
    n = parseFloat(n) || 0;
    return '₱' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function renderCommissionStageProgress(stages) {
    var container = document.getElementById('dp_commission_progress');
    if (!container) return;

    if (!Array.isArray(stages) || !stages.length) {
        container.innerHTML = '<div style="font-size:12px;color:#94a3b8;">No DP stage information available.</div>';
        return;
    }

    container.innerHTML = stages.map(function (item) {
        var status = item.status || 'waiting_payment';
        var label = item.status_label || 'Waiting for payment';
        var bg = '#f8fafc';
        var border = '#e2e8f0';
        var color = '#64748b';
        var icon = '○';

        if (status === 'requested') {
            bg = '#eff6ff'; border = '#bfdbfe'; color = '#1d4ed8'; icon = '↗';
        } else if (status === 'not_yet_released') {
            bg = '#fffbeb'; border = '#fde68a'; color = '#92400e'; icon = '◷';
        } else if (status === 'released') {
            bg = '#f0fdf4'; border = '#bbf7d0'; color = '#166534'; icon = '✓';
        } else if (status === 'ready') {
            bg = '#fffbeb'; border = '#fde68a'; color = '#92400e'; icon = '●';
        } else if (status === 'eligible_waiting') {
            bg = '#eff6ff'; border = '#bfdbfe'; color = '#1d4ed8'; icon = '↗';
        }

        return '<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:7px 9px;border:1px solid ' + border + ';background:' + bg + ';border-radius:8px;">'
            + '<div style="display:flex;align-items:center;gap:7px;min-width:0;">'
            + '<span style="font-size:12px;font-weight:800;color:' + color + ';">' + icon + '</span>'
            + '<div style="min-width:0;">'
            + '<div style="font-size:12px;font-weight:800;color:#1e4575;">DP Stage ' + item.label + '</div>'
            + '<div style="font-size:10px;color:#64748b;">Threshold: ' + fmtPeso(item.threshold_amount) + '</div>'
            + '</div></div>'
            + '<span style="font-size:10px;font-weight:800;color:' + color + ';text-align:right;white-space:nowrap;">' + label + '</span>'
            + '</div>';
    }).join('');
}

function refreshDPStageSummary(summary) {
    if (summary) {
        _dpStage = parseInt(summary.downpayment_stage) || 0;
        _dpStageTotal = parseInt(summary.downpayment_stage_total) || 1;
        _dpNextCommissionStage = summary.next_commission_stage === null
            || summary.next_commission_stage === undefined
            ? null
            : parseInt(summary.next_commission_stage);
        _dpCommissionReady = !!summary.commission_ready;
        _dpAllCommissionStagesRequested = !!summary.all_commission_stages_requested;
        _dpCommissionStages = Array.isArray(summary.commission_stages)
            ? summary.commission_stages
            : [];
        _dpHasFiledCommissionStage = Array.isArray(summary.filed_stages)
            ? summary.filed_stages.length > 0
            : _dpCommissionStages.some(function (stage) { return !!stage.is_requested; });
    }

    var stageEl = document.getElementById('dp_summary_stage');
    var nextEl = document.getElementById('dp_summary_next_stage');
    var rowStageValueEl = document.getElementById('dpStageValue_' + _dpRecordId);
    var rowStageStatusEl = document.getElementById('dpStageStatus_' + _dpRecordId);
    var button = document.getElementById('dpBtn_' + _dpRecordId);

    var stageText = _dpStage + '/' + _dpStageTotal;
    if (stageEl) stageEl.textContent = stageText;
    if (rowStageValueEl) rowStageValueEl.textContent = stageText;

    if (rowStageStatusEl) {
        var rowStatus = '';
        for (var statusIndex = _dpCommissionStages.length - 1; statusIndex >= 0; statusIndex--) {
            if (_dpCommissionStages[statusIndex].is_requested) {
                rowStatus = _dpCommissionStages[statusIndex].status_label || 'Requested';
                break;
            }
        }
        if (!rowStatus && _dpCommissionReady) {
            rowStatus = 'Ready to request';
        }

        rowStageStatusEl.textContent = rowStatus;
        rowStageStatusEl.style.display = rowStatus ? 'block' : 'none';
        rowStageStatusEl.style.color = rowStatus === 'Released'
            ? '#166534'
            : (rowStatus === 'Not Yet Released'
                ? '#92400e'
                : (rowStatus === 'Requested' ? '#1d4ed8' : '#A37929'));
    }

    if (button) {
        button.dataset.stage = _dpStage;
        button.dataset.stageTotal = _dpStageTotal;
    }

    if (nextEl) {
        if (_dpAllCommissionStagesRequested) {
            nextEl.textContent = 'All requested';
            nextEl.style.color = '#166534';
        } else if (_dpNextCommissionStage) {
            nextEl.textContent = _dpNextCommissionStage + '/' + _dpStageTotal
                + (_dpCommissionReady ? ' — Ready' : '');
            nextEl.style.color = _dpCommissionReady ? '#A37929' : '#1e4575';
        } else {
            nextEl.textContent = '—';
            nextEl.style.color = '#A37929';
        }
    }

    renderCommissionStageProgress(_dpCommissionStages);
}

async function loadDownpaymentSummary(showReadyPopup) {
    if (!_dpRecordId) return null;

    try {
        var response = await fetch('/api/client-database/' + _dpRecordId + '/downpayment-summary', {
            headers: { 'Accept': 'application/json' }
        });
        var summary = await response.json();

        if (!response.ok || !summary.success) return null;

        _dpTotalAmount = Number(summary.total_downpayment) || _dpTotalAmount;
        refreshDPSummaryHeader(summary.paid_total);
        refreshDPStageSummary(summary);

        // Re-render paid rows after the request state arrives so the Admin undo
        // action disappears as soon as any commission stage has been filed.
        if (_dpHasFiledCommissionStage) loadInstallments();

        if (showReadyPopup) handleCommissionTrigger(summary);
        return summary;
    } catch (error) {
        return null;
    }
}

// Recomputes and redraws the read-only summary header (Terms / Net TCP / Total DP / Remaining).
// paidSum = however much of the total DP has actually been paid so far
// (sum of paid installment amounts, or the full spot amount once paid).
function refreshDPSummaryHeader(paidSum) {
    paidSum = Number(paidSum);

    if (!Number.isFinite(paidSum) || paidSum < 0) {
        paidSum = 0;
    }

    _dpPaidAmount = paidSum;
    _dpRemainingBalance = Math.max(0, _dpTotalAmount - _dpPaidAmount);

    document.getElementById('dp_summary_terms').textContent = _dpTermsLabel || '—';
    document.getElementById('dp_summary_tcp').textContent = fmtPeso(_dpNetTcp);
    document.getElementById('dp_summary_total').textContent = fmtPeso(_dpTotalAmount);
    document.getElementById('dp_summary_remaining').textContent = fmtPeso(_dpRemainingBalance);
}

// ---- Commission-request threshold notification ----
// The threshold must be calculated and persisted by the controller on every payment save.
// The frontend only reacts to the server response so the notification works across browsers
// and cannot be duplicated or bypassed through localStorage.
function handleCommissionTrigger(response) {
    if (!response || !response.commission_ready) return false;

    var stageText = response.next_commission_stage
        ? response.next_commission_stage + '/' + (response.downpayment_stage_total || _dpStageTotal)
        : '';
    var text = response.message || (stageText
        ? 'Commission stage ' + stageText + ' is ready to request.'
        : "The agent's commission for this client is now ready to request.");
    if (response.paid_total !== undefined && response.threshold_amount !== undefined) {
        text += ' Paid: ' + fmtPeso(response.paid_total) + ' / Threshold: ' + fmtPeso(response.threshold_amount) + '.';
    }
    document.getElementById('commissionReadyText').textContent = text;
    showCommissionReadyModal();
    return true;
}

function showCommissionReadyModal() {
    document.getElementById('commissionReadyModal').style.display = 'flex';
}
function dismissCommissionReadyModal() {
    document.getElementById('commissionReadyModal').style.display = 'none';
    if (_dpPendingReload) { _dpPendingReload = false; window.location.reload(); }
}
function requestCommissionStage() {
    if (!_dpRecordId || !_dpCommissionReady) {
        if (typeof showToast === 'function') {
            showToast('This commission stage is not ready to request yet.', 'error', 'Not Ready');
        }
        return;
    }

    var stageText = _dpNextCommissionStage
        ? _dpNextCommissionStage + '/' + _dpStageTotal
        : 'the next eligible stage';

    var submitRequest = function () {
        var button = document.getElementById('commissionRequestBtn');
        if (button) {
            button.disabled = true;
            button.textContent = 'Sending...';
        }

        fetch('/api/client-database/' + _dpRecordId + '/commission-request', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': _dpCsrf
            },
            body: JSON.stringify({})
        })
        .then(async function (response) {
            var data = await response.json().catch(function () { return {}; });
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to send the commission request.');
            }

            _dpPendingReload = false;
            refreshDPStageSummary(data);
            document.getElementById('commissionReadyModal').style.display = 'none';

            if (typeof showToast === 'function') {
                showToast(data.message, 'success', 'Request Sent');
            }

            setTimeout(function () { window.location.reload(); }, 700);
        })
        .catch(function (error) {
            if (button) {
                button.disabled = false;
                button.textContent = 'Request';
            }

            if (typeof showToast === 'function') {
                showToast(error.message, 'error', 'Request Failed');
            } else {
                alert(error.message);
            }
        });
    };

    var message = 'Are you sure you want to request commission for DP stage ' + stageText + '? Finance will be notified and this stage cannot be requested again.';

    if (window.showConfirmModal) {
        window.showConfirmModal(message).then(function (confirmed) {
            if (confirmed) submitRequest();
        });
    } else if (confirm(message)) {
        submitRequest();
    }
}

function updateClientStatusSelect(id, clientStatus) {
    const sel = document.getElementById('csSel_' + id);
    if (!sel) return;
    sel.value = clientStatus || '';
    sel.dataset.clientStatus = (clientStatus || '').toLowerCase();
    const bg  = clientStatus === 'Done' ? '#dcfce7' : (clientStatus === 'Cancelled' ? '#fee2e2' : (clientStatus === 'Pending' ? '#fef3c7' : '#f1f5f9'));
    const col = clientStatus === 'Done' ? '#166534' : (clientStatus === 'Cancelled' ? '#991b1b' : (clientStatus === 'Pending' ? '#92400e' : '#64748b'));
    sel.style.background = bg;
    sel.style.color = col;
}

function updateDPStatusBadge(id, status, terms, amount) {
    const btn = document.getElementById('dpBtn_' + id);
    if (!btn) return;
    status = status || '';
    const isPaid = status === 'Paid' || status === 'Spot Paid';
    const hasStatus = status && status !== '— Set —';
    btn.style.background = isPaid ? '#dcfce7' : (hasStatus ? '#fef3c7' : '#f1f5f9');
    btn.style.color      = isPaid ? '#166534' : (hasStatus ? '#92400e' : '#64748b');
    btn.textContent = status || '— Set —';
    btn.dataset.status = status;
    if (terms  !== undefined) btn.dataset.terms  = terms;
    if (amount !== undefined) btn.dataset.amount = amount;
}

function openDPModal(id, amount, terms, perTerm, status, dpDate, netTcp, termsLabel, stage, stageTotal) {
    _dpRecordId    = id;
    _dpNetTcp      = parseFloat(netTcp) || 0;
    _dpTermsLabel  = termsLabel || '';
    _dpTotalAmount = _dpNetTcp * parseDPPercent(_dpTermsLabel);
    _dpStage = parseInt(stage) || 0;
    _dpStageTotal = parseInt(stageTotal) || 1;
    _dpNextCommissionStage = null;
    _dpCommissionReady = false;
    _dpAllCommissionStagesRequested = false;
    _dpCommissionStages = [];
    _dpHasFiledCommissionStage = false;
    refreshDPStageSummary();

    const isSpotPaid = status === 'Spot Paid';
    const locked     = isSpotPaid && !_isAdmin;

    // Paid-so-far for the header's Remaining Balance:
    // - Spot Paid means the whole thing was paid in one go -> paidSum = _dpTotalAmount.
    // - Otherwise (installments not yet loaded) start at 0; renderInstallments()
    //   will correct this once the real per-term paid amounts come back.
    refreshDPSummaryHeader(isSpotPaid ? _dpTotalAmount : 0);

    // Populate fields
    document.getElementById('dp_total_amount').value  = _dpTotalAmount > 0 ? _dpTotalAmount.toFixed(2) : '';

    // The quick picker only has options 1–6 built in. If this plan has more
    // terms (created via "Others"), add a matching option on the fly so the
    // dropdown actually reflects the real count instead of clamping to 6.
    var termsSelect = document.getElementById('dp_terms_select');
    var parsedTerms  = parseDPMonths(_dpTermsLabel);
    var actualTerms  = parsedTerms > 0 ? parsedTerms : (terms || 1);
    if (actualTerms > 6 && !termsSelect.querySelector('option[value="' + actualTerms + '"]')) {
        var opt = document.createElement('option');
        opt.value = actualTerms;
        opt.textContent = actualTerms;
        termsSelect.appendChild(opt);
    }
    termsSelect.value = actualTerms;
    document.getElementById('dp_spot_amount').value   = _dpTotalAmount > 0 ? _dpTotalAmount.toFixed(2) : '';
    document.getElementById('dp_spot_date').value     = dpDate || '';
    document.getElementById('dp_others_amount').value = _dpTotalAmount > 0 ? _dpTotalAmount.toFixed(2) : '';
    document.getElementById('dp_others_terms').value  = '';

    // Apply lock/unlock to spot DP fields
    var amountEl  = document.getElementById('dp_spot_amount');
    var dateEl    = document.getElementById('dp_spot_date');
    var paidBtn   = document.getElementById('dp_spot_paid_btn');
    var saveBtn   = document.getElementById('dp_footer_spot_save');
    var lockNotice= document.getElementById('dp_spot_locked_notice');

    amountEl.readOnly  = locked;
    amountEl.style.background = locked ? '#f3f4f6' : 'transparent';
    amountEl.style.cursor     = locked ? 'not-allowed' : '';
    dateEl.disabled    = locked;
    dateEl.style.background   = locked ? '#f3f4f6' : '';
    dateEl.style.cursor       = locked ? 'not-allowed' : '';
    paidBtn.style.display     = locked ? 'none' : '';
    saveBtn.style.display     = locked ? 'none' : '';
    lockNotice.style.display  = locked ? 'block' : 'none';

    // Reset all sections
    document.getElementById('dp_step_type').style.display          = 'flex';
    document.getElementById('dp_spot_section').style.display       = 'none';
    document.getElementById('dp_installment_section').style.display= 'none';
    document.getElementById('dp_others_section').style.display     = 'none';
    document.getElementById('dp_footer_type').style.display        = 'flex';
    document.getElementById('dp_footer_spot').style.display        = 'none';
    document.getElementById('dp_footer_installment').style.display = 'none';
    document.getElementById('dp_footer_others').style.display      = 'none';

    // Route to correct view
    if (isSpotPaid) {
        selectDPType('spot');
    } else if (status && (status.includes('month') || status === 'Partial' || status === 'Paid')) {
        // Already has installments set up (any term count, including >6 from "Others") — show the paid/unpaid list
        selectDPType('installment');
        loadInstallments();
    } else if (terms > 1) {
        selectDPType('installment');
        loadInstallments();
    } else if (parseDPMonths(_dpTermsLabel) > 1) {
        selectDPType('installment');
    }

    document.getElementById('dpModal').style.display = 'flex';
    loadDownpaymentSummary(true);
}

function selectDPType(type) {
    document.getElementById('dp_step_type').style.display = 'none';
    document.getElementById('dp_footer_type').style.display = 'none';
    document.getElementById('dp_others_section').style.display = 'none';
    document.getElementById('dp_footer_others').style.display = 'none';
    if (type === 'spot') {
        document.getElementById('dp_spot_section').style.display = 'flex';
        document.getElementById('dp_installment_section').style.display = 'none';
        document.getElementById('dp_footer_spot').style.display = 'flex';
        document.getElementById('dp_footer_installment').style.display = 'none';
    } else if (type === 'others') {
        document.getElementById('dp_spot_section').style.display = 'none';
        document.getElementById('dp_installment_section').style.display = 'none';
        document.getElementById('dp_others_section').style.display = 'flex';
        document.getElementById('dp_footer_spot').style.display = 'none';
        document.getElementById('dp_footer_installment').style.display = 'none';
        document.getElementById('dp_footer_others').style.display = 'flex';
    } else {
        document.getElementById('dp_spot_section').style.display = 'none';
        document.getElementById('dp_installment_section').style.display = 'flex';
        document.getElementById('dp_footer_spot').style.display = 'none';
        document.getElementById('dp_footer_installment').style.display = 'flex';
        loadInstallments();
    }
}

function saveSpotDP() {
    const amount = _dpTotalAmount;
    const date   = document.getElementById('dp_spot_date').value;
    if (!amount || parseFloat(amount) <= 0) {
        alert('The total downpayment could not be calculated from Net TCP and Terms of Payment.');
        return;
    }
    if (!date) {
        alert('Please select the date of payment.');
        return;
    }
    fetch(`/client-database/${_dpRecordId}/downpayment-status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _dpCsrf },
        body: JSON.stringify({ downpayment_status: 'Spot Paid', downpayment_amount: amount, downpayment_date: date })
    }).then(r => r.json()).then(d => {
        refreshDPSummaryHeader(parseFloat(d.paid_total ?? amount) || 0);
        document.getElementById('dpModal').style.display = 'none';
        updateDPStatusBadge(_dpRecordId, d.status || 'Spot Paid');
        updateClientStatusSelect(_dpRecordId, d.client_status || 'Done');
        if (handleCommissionTrigger(d)) {
            _dpPendingReload = true;
        } else {
            window.location.reload();
        }
    }).catch(() => {
        const form = document.createElement('form');
        form.method = 'POST'; form.action = `/client-database/${_dpRecordId}/downpayment-status`;
        form.innerHTML = `<input name="_token" value="${_dpCsrf}"><input name="_method" value="PATCH"><input name="downpayment_status" value="Spot Paid"><input name="downpayment_amount" value="${amount}"><input name="downpayment_date" value="${date}">`;
        document.body.appendChild(form); form.submit();
    });
}

function saveOthersDP() {
    const amount = document.getElementById('dp_others_amount').value;
    const terms  = document.getElementById('dp_others_terms').value;
    if (!terms || parseInt(terms) < 1) { alert('Please enter number of terms.'); return; }
    setupOthersInstallments();
}

function setupOthersInstallments() {
    const amount = parseFloat(document.getElementById('dp_others_amount').value) || 0;
    const terms  = parseInt(document.getElementById('dp_others_terms').value) || 0;
    if (!terms || terms < 1) { alert('Please enter number of terms.'); return; }
    fetch(`/api/client-database/${_dpRecordId}/installments/setup`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _dpCsrf },
        body: JSON.stringify({ terms, total_amount: amount })
    }).then(r => r.json()).then(data => {
        // Sync the installment view's own header fields so they reflect
        // what was just entered here, instead of showing stale 0.00 / 1
        document.getElementById('dp_total_amount').value = amount > 0 ? amount : '';
        var termsSelect = document.getElementById('dp_terms_select');
        if (!termsSelect.querySelector('option[value="' + terms + '"]')) {
            var opt = document.createElement('option');
            opt.value = terms;
            opt.textContent = terms;
            termsSelect.appendChild(opt);
        }
        termsSelect.value = terms;

        // Switch to installment view to show the terms
        selectDPType('installment');
        renderInstallments(data);
        const status = terms + ' month' + (terms > 1 ? 's' : '');
        updateDPStatusBadge(_dpRecordId, status, terms, amount);
        updateClientStatusSelect(_dpRecordId, 'Pending');
    });
}   

function loadInstallments() {
    fetch(`/api/client-database/${_dpRecordId}/installments`)
        .then(r => r.json()).then(data => renderInstallments(data));
}

function setupInstallments() {
    const terms  = parseDPMonths(_dpTermsLabel);
    const amount = _dpTotalAmount;
    if (!terms || terms < 2) {
        alert('This payment plan does not contain an installment DP month count. Please use Spot Downpayment.');
        return;
    }
    if (!amount || amount <= 0) {
        alert('The total downpayment could not be calculated from Net TCP and Terms of Payment.');
        return;
    }
    fetch(`/api/client-database/${_dpRecordId}/installments/setup`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _dpCsrf },
        body: JSON.stringify({ terms, total_amount: amount })
    }).then(r => r.json()).then(data => {
        renderInstallments(data);
        const status = terms + ' month' + (terms > 1 ? 's' : '');
        updateDPStatusBadge(_dpRecordId, status, terms, amount);
        updateClientStatusSelect(_dpRecordId, 'Pending');
    });
}

function renderInstallments(list) {
    const container = document.getElementById('dp_installments_list');

    // Recompute how much of the total DP has actually been paid, and refresh
    // the read-only summary header (Remaining Balance + commission threshold check).
    const paidSum = list.reduce((sum, inst) => sum + (inst.is_paid ? (parseFloat(inst.amount) || 0) : 0), 0);
    refreshDPSummaryHeader(paidSum);

    if (!list.length) {
        container.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:20px;font-size:13px;">Set amount and terms, then click "Set Terms".</div>';
        return;
    }
    container.innerHTML = list.map(inst => {
        const border = inst.is_paid ? '#bbf7d0' : '#e2e8f0';
        const bg     = inst.is_paid ? '#f0fdf4' : '#f8fafc';
        const paidDate = inst.paid_date ? `<span style="font-size:10px;color:#16a34a;margin-left:6px;">${inst.paid_date}</span>` : '';

        const actionBtn = inst.is_paid
            ? (_isAdmin && !_dpHasFiledCommissionStage
                ? `<button onclick="unmarkPaid(${inst.id})" style="padding:10px 14px;background:#dcfce7;color:#166534;border:none;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;border-left:1.5px solid #bbf7d0;" title="Click to undo">✓ Paid ↩</button>`
                : `<span style="padding:10px 14px;background:#dcfce7;color:#166534;font-size:12px;font-weight:700;white-space:nowrap;border-left:1.5px solid #bbf7d0;" title="${_dpHasFiledCommissionStage ? 'Locked because a commission request has been recorded' : 'Paid'}">✓ Paid${_dpHasFiledCommissionStage ? ' 🔒' : ''}</span>`)
            : `<button onclick="markPaidWithDate(${inst.id}, this)" style="padding:10px 16px;background:linear-gradient(135deg,#A37929,#d4a03a);color:white;border:none;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">Paid</button>`;

        // Amount input — editable for both admin and staff BEFORE paid; locked after paid
        const amountInput = inst.is_paid
            ? `<span style="flex:1;padding:10px 12px;font-size:13px;color:#166534;font-weight:600;">₱${Number(inst.amount||0).toLocaleString()}${paidDate}</span>`
            : `<input type="number" id="inst_amount_${inst.id}" value="${inst.amount || ''}" placeholder="Enter amount" step="0.01" min="0.01" max="${_dpRemainingBalance.toFixed(2)}" inputmode="decimal"
                onkeydown="return !['e','E','+','-'].includes(event.key)"
                onblur="saveInstallmentAmount(${inst.id}, true)"
                style="flex:1;padding:10px 12px;border:none;outline:none;font-size:13px;background:transparent;">`;

        // Date input — only show when not yet paid
        const dateInput = inst.is_paid ? '' :
            `<input type="date" id="inst_date_${inst.id}" style="padding:8px 10px;border:none;border-left:1.5px solid #e2e8f0;outline:none;font-size:12px;background:transparent;color:#374151;" title="Date of payment">`;

        return `
            <div class="dp-installment-row" style="display:flex;align-items:center;flex-wrap:wrap;gap:0;border:1.5px solid ${border};border-radius:10px;overflow:hidden;background:${bg};">
                <span style="font-size:13px;font-weight:700;color:#1e4575;padding:10px 14px;white-space:nowrap;border-right:1.5px solid ${border};">Term ${inst.term_number}</span>
                ${amountInput}
                ${dateInput}
                ${actionBtn}
            </div>`;
    }).join('');
}

function getInstallmentApiError(data, fallback) {
    if (data && data.message) return data.message;
    if (data && data.error) return data.error;

    if (data && data.errors) {
        var firstKey = Object.keys(data.errors)[0];
        if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) {
            return data.errors[firstKey][0];
        }
    }

    return fallback || 'Unable to save the installment.';
}

function readInstallmentAmount(instId, silent) {
    var amountEl = document.getElementById('inst_amount_' + instId);
    var rawAmount = amountEl ? amountEl.value.trim() : '';
    var amount = Number(rawAmount);

    function showAmountError(message) {
        if (!amountEl || silent) return;
        amountEl.setCustomValidity(message);
        amountEl.reportValidity();
        amountEl.focus();
        amountEl.addEventListener('input', function clearV() {
            amountEl.setCustomValidity('');
            amountEl.removeEventListener('input', clearV);
        }, { once: true });
    }

    if (!rawAmount || !Number.isFinite(amount) || amount <= 0) {
        showAmountError('Enter a valid finite payment amount greater than zero.');
        return null;
    }

    if (_dpRemainingBalance > 0 && amount > _dpRemainingBalance + 0.01) {
        showAmountError(
            'The payment cannot exceed the remaining DP balance of '
            + fmtPeso(_dpRemainingBalance) + '.'
        );
        return null;
    }

    return {
        raw: rawAmount,
        value: amount,
        element: amountEl
    };
}

async function saveInstallmentAmount(instId, silent) {
    var payment = readInstallmentAmount(instId, silent);
    if (!payment) return false;
    try {
        var response = await fetch(`/api/installments/${instId}/amount`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': _dpCsrf
            },
            body: JSON.stringify({ amount: payment.raw })
        });

        var data = await response.json().catch(function () { return {}; });

        if (!response.ok || !data.success) {
            throw new Error(getInstallmentApiError(
                data,
                'Unable to save the installment amount.'
            ));
        }

        return true;
    } catch (error) {
        payment.element.setCustomValidity(error.message);
        payment.element.reportValidity();
        payment.element.setCustomValidity('');

        if (!silent) {
            if (typeof showToast === 'function') {
                showToast(error.message, 'error', 'Payment Error');
            } else {
                alert(error.message);
            }
        }
        return false;
    }
}

function markPaid(instId) {
    var btn = document.querySelector(
        `button[onclick="markPaidWithDate(${instId}, this)"]`
    );

    markPaidWithDate(instId, btn);
}

function showFieldTooltip(el, message) {
    var existing = document.getElementById('_fieldTooltip');
    if (existing) existing.remove();

    var rect = el.getBoundingClientRect();
    var tip = document.createElement('div');
    tip.id = '_fieldTooltip';
    tip.style.cssText = 'position:fixed;top:' + (rect.bottom + 8) + 'px;left:' + rect.left + 'px;'
        + 'z-index:99999;background:white;border:1px solid #d1d5db;border-radius:6px;'
        + 'box-shadow:0 4px 12px rgba(0,0,0,.15);padding:8px 12px;display:flex;align-items:center;'
        + 'gap:8px;font-size:13px;color:#1f2937;max-width:280px;';
    tip.innerHTML =
        '<span style="flex-shrink:0;width:18px;height:18px;background:#dc2626;color:white;'
        + 'border-radius:3px;display:flex;align-items:center;justify-content:center;font-weight:700;'
        + 'font-size:12px;">!</span><span>' + message + '</span>'
        + '<div style="position:absolute;top:-6px;left:16px;width:12px;height:12px;background:white;'
        + 'border-left:1px solid #d1d5db;border-top:1px solid #d1d5db;transform:rotate(45deg);"></div>';

    document.body.appendChild(tip);
    el.focus();

    function remove() {
        tip.remove();
        el.removeEventListener('input', remove);
        document.removeEventListener('click', onOutsideClick);
    }
    function onOutsideClick(e) {
        if (!tip.contains(e.target) && e.target !== el) remove();
    }
    el.addEventListener('input', remove, { once: true });
    setTimeout(function() { document.addEventListener('click', onOutsideClick); }, 0);
    setTimeout(remove, 4000);
}

async function markPaidWithDate(instId, btn) {
    var dateEl = document.getElementById('inst_date_' + instId);
    var date = dateEl ? dateEl.value : '';

    if (!date) {
        if (dateEl) showFieldTooltip(dateEl, 'Date of payment is required.');
        return;
    }

    var payment = readInstallmentAmount(instId);
    if (!payment) return;

    if (!confirm('Mark this term as paid for ' + fmtPeso(payment.value) + '?')) {
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Saving...';
    }

    try {
        var response = await fetch(`/api/installments/${instId}/paid`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': _dpCsrf
            },
            body: JSON.stringify({
                amount: payment.raw,
                paid_date: date
            })
        });

        var res = await response.json().catch(function () { return {}; });

        if (!response.ok || !res.success) {
            throw new Error(getInstallmentApiError(
                res,
                'Failed to mark the installment as paid.'
            ));
        }

        await loadInstallments();

        if (res.status) updateDPStatusBadge(_dpRecordId, res.status);

        if (res.client_status) {
            updateClientStatusSelect(_dpRecordId, res.client_status);
        } else if (res.status === 'Paid') {
            updateClientStatusSelect(_dpRecordId, 'Done');
        } else {
            updateClientStatusSelect(_dpRecordId, 'Pending');
        }

        refreshDPStageSummary(res);
        handleCommissionTrigger(res);
    } catch (error) {
        if (typeof showToast === 'function') {
            showToast(error.message, 'error', 'Payment Error');
        } else {
            alert(error.message);
        }

        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Paid';
        }
    }
}

function unmarkPaid(instId) {
    if (!confirm('Undo this payment? This will mark the term as unpaid.')) return;
    fetch(`/api/installments/${instId}/unpaid`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _dpCsrf },
        body: JSON.stringify({})
    }).then(async r => {
        const res = await r.json().catch(() => ({}));
        if (!r.ok || !res.success) throw new Error(res.message || 'Unable to undo this payment.');
        return res;
    }).then(res => {
        loadInstallments();
        updateDPStatusBadge(_dpRecordId, res.status || '');
        updateClientStatusSelect(_dpRecordId, res.client_status || 'Pending');
        refreshDPStageSummary(res);
    }).catch(error => {
        if (typeof showToast === 'function') {
            showToast(error.message, 'error', 'Undo Failed');
        } else {
            alert(error.message);
        }
    });
}
</script>

@endsection