@extends('layouts.dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/departmental-expenses-enhanced.css') }}?v={{ time() }}">
<div class="ca-container">

    <!-- Welcome Banner -->
    <div class="ca-banner">
        <div class="ca-banner-content">
            <div class="ca-eyebrow">Finance</div>
            <h1 class="ca-title">Cash Advance</h1>
            <p class="ca-subtitle">
                <svg class="ca-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Submit a new cash advance request and manage existing records.
            </p>
        </div>
        <div class="ca-decoration">
            <div class="ca-circle ca-circle-1"></div>
            <div class="ca-circle ca-circle-2"></div>
            <div class="ca-circle ca-circle-3"></div>
        </div>
    </div>

    <!-- Stats -->
    <div class="ca-stats-grid">
        <div class="ca-stat-card">
            <div class="ca-stat-icon ca-stat-icon-records">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="ca-stat-label">Total Records</div>
                <div class="ca-stat-value" id="caStatTotalRecords">{{ $totalRecords }}</div>
            </div>
        </div>
        <div class="ca-stat-card">
            <div class="ca-stat-icon ca-stat-icon-pending">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ca-stat-label">Pending</div>
                <div class="ca-stat-value" id="caStatPending">{{ $pendingCount }}</div>
            </div>
        </div>
        <div class="ca-stat-card">
            <div class="ca-stat-icon ca-stat-icon-requested">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ca-stat-label">Total Requested</div>
                <div class="ca-stat-value" id="caStatTotalRequested">₱{{ number_format($totalRequested, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- New Request Form (styled like Departmental Expenses → Add New Expense) -->
    <div class="request-form-container">
        <h3 class="form-title">New Request</h3>
        <form id="caForm" class="request-form" novalidate>
            @csrf

            <!-- Request Information Section -->
            <div class="form-section">
                <h4 class="section-label">Request Information</h4>

                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Employee <span class="required">*</span></label>
                        <select id="ca_employee_id" name="employee_id" class="form-control" required>
                            <option value="" disabled selected>Select employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}@if($emp->position) — {{ $emp->position }}@endif</option>
                            @endforeach
                        </select>
                        <span class="ca-error" id="err_employee_id"></span>
                    </div>

                    <div class="form-group">
                        <label>Department <span class="required">*</span></label>
                        <select id="ca_department" name="department" class="form-control" required>
                            <option value="" disabled selected>Select department...</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                        <span class="ca-error" id="err_department"></span>
                    </div>

                    <div class="form-group">
                        <label>Amount Requested (₱) <span class="required">*</span></label>
                        <input type="number" id="ca_amount" name="amount" class="form-control" min="1" step="0.01" placeholder="0.00" required>
                        <span class="ca-error" id="err_amount"></span>
                    </div>
                </div>

                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Date Requested <span class="required">*</span></label>
                        <input type="date" id="ca_date_requested" name="date_requested" class="form-control" required>
                        <span class="ca-error" id="err_date_requested"></span>
                    </div>

                    <div class="form-group">
                        <label>Date Needed <span class="required">*</span></label>
                        <input type="date" id="ca_date_needed" name="date_needed" class="form-control" required>
                        <span class="ca-error" id="err_date_needed"></span>
                    </div>
                </div>

                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Purpose <span class="required">*</span></label>
                        <textarea id="ca_purpose" name="purpose" class="form-control" rows="3" placeholder="e.g. Medical emergency" required></textarea>
                        <span class="ca-error" id="err_purpose"></span>
                    </div>
                </div>
            </div>

            <!-- Repayment Details Section -->
            <div class="form-section">
                <h4 class="section-label">Repayment Details</h4>

                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Repayment Type <span class="required">*</span></label>
                        <select id="ca_repayment_type" name="repayment_type" class="form-control" required onchange="caToggleRepaymentType()">
                            <option value="INSTALLMENT">Installment</option>
                            <option value="OTHERS">Others</option>
                        </select>
                        <span class="ca-error" id="err_repayment_type"></span>
                    </div>

                    <div class="form-group" id="ca_terms_group">
                        <label>Number of Terms <span class="required">*</span></label>
                        <select id="ca_installment_terms" name="installment_terms" class="form-control">
                            <option value="" disabled selected>Select terms...</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                        </select>
                        <span class="ca-hint">Each term is one salary deduction. Maximum of 6 terms.</span>
                        <span class="ca-error" id="err_installment_terms"></span>
                    </div>

                    <div class="form-group" id="ca_repay_date_group" style="display:none;">
                        <label>Repayment Date <span class="required">*</span></label>
                        <input type="date" id="ca_repayment_date" name="repayment_date" class="form-control">
                        <span class="ca-hint">One-time payment date.</span>
                        <span class="ca-error" id="err_repayment_date"></span>
                    </div>
                </div>
            </div>

            <div class="form-actions-right">
                <button type="submit" class="btn-submit" id="caSubmitBtn">Create Cash Advance Form</button>
            </div>
        </form>
    </div>

    <!-- Records -->
    <div class="ca-card ca-records-card">
        <div class="ca-records-header">
            <h3 class="ca-card-title">Records</h3>
            <span class="ca-records-count" id="caRecordsCount">{{ $totalRecords }} total</span>
        </div>

        <div class="ca-filter-row" id="caFilterRow">
            <label class="ca-filter-label">Filter by Amount</label>
            <div class="ca-filter-inputs">
                <span class="ca-filter-currency">₱</span>
                <input type="number" step="any" id="caAmountFrom" placeholder="Min" class="ca-filter-input">
                <span class="ca-filter-to">to</span>
                <span class="ca-filter-currency">₱</span>
                <input type="number" step="any" id="caAmountTo" placeholder="Max" class="ca-filter-input">
                <button type="button" class="ca-filter-clear" id="caFilterClearBtn" onclick="caClearAmountFilter()" style="display:none;">Clear</button>
            </div>
        </div>

        <div class="ca-table-wrap">
            <table class="ca-table" id="caTable">
                <thead>
                    <tr>
                        <th>Cash Advance No.</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Amount</th>
                        <th>Date Requested</th>
                        <th>Date Needed</th>
                        <th>Repayment Type</th>
                        <th>Terms</th>
                        <th>Payment Stage</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $r)
                    <tr id="ca-row-{{ $r->id }}" data-amount="{{ $r->amount }}">
                        <td class="ca-id">{{ $r->control_number }}</td>
                        <td>
                            <div class="ca-employee-name">{{ $r->employee_name }}</div>
                            <div class="ca-employee-reason">{{ $r->purpose ?? $r->reason }}</div>
                        </td>
                        <td>{{ $r->department ?? '—' }}</td>
                        <td>₱{{ number_format($r->amount, 2) }}</td>
                        <td>{{ optional($r->date_requested)->format('Y-m-d') ?? optional($r->created_at)->format('Y-m-d') }}</td>
                        <td>{{ optional($r->date_needed)->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $r->repayment_type === 'OTHERS' ? 'Others' : 'Installment' }}</td>
                        <td>
                            @if($r->repayment_type === 'OTHERS')
                                One-time — {{ optional($r->repayment_date)->format('Y-m-d') ?? '—' }}
                            @else
                                {{ $r->installment_terms ?? '—' }} term{{ ($r->installment_terms ?? 0) == 1 ? '' : 's' }}
                            @endif
                        </td>
                        <td id="ca-stage-{{ $r->id }}">{{ $r->payment_stage_label }}</td>
                        <td id="ca-status-{{ $r->id }}">
                            <span class="ca-badge ca-badge-{{ strtolower($r->display_status) }}">{{ $r->display_status }}</span>
                        </td>
                        <td>{{ optional($r->created_at)->format('Y-m-d') }}</td>
                        <td>
                            <div class="ca-actions">
                                @if($r->status === 'PENDING')
                                <button type="button" class="ca-btn-approve" onclick="caApprove({{ $r->id }}, '{{ $r->control_number }}')">Approve</button>
                                <button type="button" class="ca-btn-reject" onclick="caReject({{ $r->id }}, '{{ $r->control_number }}')">Reject</button>
                                @endif
                                <button type="button" class="ca-btn-view" title="View / Print" onclick="caOpenView({{ $r->id }})">View</button>
                                @if(in_array($r->status, ['APPROVED', 'COMPLETED']))
                                <button type="button" class="ca-btn-edit" title="Manage repayment" onclick="caOpenEdit({{ $r->id }}, '{{ $r->control_number }}')">Edit</button>
                                @endif
                                <button type="button" class="ca-btn-delete" title="Delete record" onclick="caDelete({{ $r->id }}, '{{ $r->control_number }}')">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="caEmptyRow">
                        <td colspan="12" class="ca-empty">No cash advance records yet.</td>
                    </tr>
                    @endforelse
                    <tr id="caNoMatchRow" style="display:none;">
                        <td colspan="12" class="ca-empty">No records match this amount range.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Printable Form Preview Modal -->
<div id="caPreviewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this) caClosePreview()">
    <div style="background:#fff;border-radius:14px;width:95vw;max-width:820px;max-height:90vh;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;display:flex;flex-direction:column;">
        <div style="padding:16px 22px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);flex-shrink:0;">
            <span style="font-size:14px;font-weight:700;color:#fff;">Cash Advance Form — Preview</span>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <button type="button" onclick="caClosePreview()" style="padding:7px 14px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">Back to Edit</button>
                <button type="button" id="caConfirmPrintBtn" onclick="caConfirmAndPrint()" style="padding:7px 16px;background:#A37929;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Confirm &amp; Print</button>
            </div>
        </div>
        <div id="caPreviewContent" style="padding:30px 36px;font-family:'Times New Roman',serif;font-size:13px;color:#111;flex:1;overflow-y:auto;"></div>
    </div>
</div>

<!-- View (read-only, printable) Modal -->
<div id="caViewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this) caCloseView()">
    <div style="background:#fff;border-radius:14px;width:95vw;max-width:820px;max-height:90vh;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;display:flex;flex-direction:column;">
        <div style="padding:16px 22px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);flex-shrink:0;">
            <span style="font-size:14px;font-weight:700;color:#fff;">Cash Advance Form — View</span>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <button type="button" onclick="caCloseView()" style="padding:7px 14px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">Close</button>
                <button type="button" onclick="caPrintView()" style="padding:7px 16px;background:#A37929;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Print</button>
            </div>
        </div>
        <div id="caViewContent" style="padding:30px 36px;font-family:'Times New Roman',serif;font-size:13px;color:#111;flex:1;overflow-y:auto;"></div>
    </div>
</div>

<!-- Edit (repayment tracking) Modal -->
<div id="caEditModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this) caCloseEdit()">
    <div style="background:#fff;border-radius:14px;width:95vw;max-width:600px;max-height:90vh;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;display:flex;flex-direction:column;">
        <div style="padding:16px 22px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);flex-shrink:0;">
            <span id="caEditTitle" style="font-size:14px;font-weight:700;color:#fff;">Repayment Tracking</span>
            <button type="button" onclick="caCloseEdit()" style="padding:7px 14px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">Close</button>
        </div>
        <div id="caEditContent" style="padding:22px 26px;font-size:13px;color:#111;flex:1;overflow-y:auto;"></div>
    </div>
</div>

<style>
.ca-container { max-width: 1300px; }

.ca-banner {
    background: linear-gradient(135deg, #1e4575 0%, #2563eb 60%, #1e4575 100%);
    border-radius: 20px;
    padding: 32px 36px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(30,69,117,.25);
}
.ca-banner-content { position: relative; z-index: 2; }
.ca-eyebrow { font-size: 11px; font-weight: 700; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px; }
.ca-title { font-size: 26px; font-weight: 700; color: #fff; margin: 0 0 8px; }
.ca-subtitle { font-size: 13.5px; color: rgba(255,255,255,.8); margin: 0; display: flex; align-items: center; gap: 8px; }
.ca-icon-sm { width: 15px; height: 15px; flex-shrink: 0; }
.ca-decoration { position: absolute; top: 0; right: 0; width: 300px; height: 100%; pointer-events: none; }
.ca-circle { position: absolute; border-radius: 50%; background: rgba(163,121,41,0.18); }
.ca-circle-1 { width: 200px; height: 200px; top: -50px; right: -50px; }
.ca-circle-2 { width: 140px; height: 140px; top: 50px; right: 110px; }
.ca-circle-3 { width: 90px; height: 90px; bottom: -25px; right: 60px; }

.ca-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 24px; }
.ca-stat-card { background: #fff; border-radius: 14px; padding: 18px 20px; box-shadow: 0 2px 10px rgba(0,0,0,.06); border: 1px solid #eef1f5; display: flex; align-items: center; gap: 14px; }
.ca-stat-icon { width: 42px; height: 42px; border-radius: 11px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ca-stat-icon svg { width: 22px; height: 22px; }
.ca-stat-icon-records { background: #eef2ff; color: #4338ca; }
.ca-stat-icon-pending { background: #fff7ed; color: #c2410c; }
.ca-stat-icon-requested { background: #ecfdf5; color: #059669; }
.ca-stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #8A9BAD; margin-bottom: 4px; }
.ca-stat-value { font-size: 24px; font-weight: 700; color: #1e2a3a; }

/* Mobile responsiveness for stat cards, matching the 768px breakpoint
   convention used elsewhere on the site (e.g. departmental-expenses). The
   3-column grid is too cramped once icons were added, so stack to one
   column and let the icon/label/value row breathe on narrow screens. */
@media (max-width: 768px) {
    .ca-stats-grid {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
    }
    .ca-stat-card {
        padding: 14px 16px !important;
    }
    .ca-stat-icon {
        width: 38px !important;
        height: 38px !important;
    }
    .ca-stat-icon svg {
        width: 20px !important;
        height: 20px !important;
    }
    .ca-stat-value {
        font-size: 20px !important;
    }
}

.ca-card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,.06); border: 1px solid #eef1f5; min-width: 0; margin-top: 24px; }
.ca-card-title { font-size: 16px; font-weight: 700; color: #1e2a3a; margin: 0 0 4px; }
.ca-card-sub { font-size: 12.5px; color: #8A9BAD; margin: 0 0 18px; }

/* Request form fields reuse the shared .form-control / .form-group styling
   from departmental-expenses-enhanced.css (Add New Expense) — this just
   adds the bits that component doesn't already define: inline error text,
   an invalid state, and hint copy under the Installment/Others fields. */
.ca-hint { display: block; font-size: 11px; color: #8A9BAD; margin-top: 2px; }
.ca-error { display: block; font-size: 11.5px; color: #dc2626; margin-top: 4px; min-height: 14px; }
.form-control.ca-invalid { border-color: #dc2626 !important; background: #fef2f2; }

.ca-records-header { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 14px; }
.ca-records-count { font-size: 12px; color: #8A9BAD; font-weight: 600; }

.ca-filter-row {
    display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
    padding: 10px 14px; margin-bottom: 14px; background: #f8fafc;
    border: 1px solid #eef1f5; border-radius: 10px;
}
.ca-filter-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #1e4575; white-space: nowrap; }
.ca-filter-inputs { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.ca-filter-currency { font-size: 12.5px; color: #8A9BAD; font-weight: 600; }
.ca-filter-to { font-size: 12px; color: #8a9bad; }
.ca-filter-input {
    width: 100px; padding: 7px 10px; border: 1.5px solid #d0d5dd; border-radius: 7px;
    font-size: 13px; font-family: inherit; color: #1e2a3a; background: #fff; transition: border-color .15s;
}
.ca-filter-input:focus { outline: none; border-color: #1e4575; }
.ca-filter-clear {
    padding: 6px 12px; border: 1.5px solid #d0d5dd; border-radius: 7px; background: #fff;
    font-size: 11.5px; font-weight: 700; color: #6b7280; cursor: pointer; transition: all .15s;
}
.ca-filter-clear:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
@media (max-width: 480px) {
    .ca-filter-row { flex-direction: column; align-items: stretch; }
    .ca-filter-inputs { justify-content: space-between; }
    .ca-filter-input { flex: 1; min-width: 0; }
}

/* The layout's global auto-scrollbar script tags this wrapper with .tbl-scroll,
   which pulls in an extra overflow-y:auto + max-height rule from optimized-global.css
   on top of the forced overflow-x:scroll rule from dashboard.css — the two competing
   scroll axes end up painting two stacked scrollbar tracks. Pin everything down to a
   single horizontal-only scrollbar here, at higher specificity than those global rules. */
.ca-table-wrap,
.ca-table-wrap.tbl-scroll,
.ca-table-wrap.auto-scroll-wrap {
    overflow-x: auto !important;
    overflow-y: hidden !important;
    max-height: none !important;
    padding-bottom: 0 !important;
}
.ca-table-wrap::-webkit-scrollbar,
.ca-table-wrap.tbl-scroll::-webkit-scrollbar {
    height: 8px !important;
    width: 0 !important;
}
.ca-table-wrap::-webkit-scrollbar-track,
.ca-table-wrap.tbl-scroll::-webkit-scrollbar-track {
    background: #f1f5f9 !important;
    border-radius: 4px;
}
.ca-table-wrap::-webkit-scrollbar-thumb,
.ca-table-wrap.tbl-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1 !important;
    border-radius: 4px;
}
.ca-table { width: 100%; border-collapse: collapse; }
.ca-table thead th {
    text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    color: #8A9BAD; padding: 8px 10px; border-bottom: 1.5px solid #eef1f5; white-space: nowrap;
}
.ca-table tbody td { padding: 14px 10px; border-bottom: 1px solid #f1f3f6; font-size: 13px; color: #374151; vertical-align: top; }
.ca-table tbody tr:last-child td { border-bottom: none; }
.ca-id { font-weight: 600; color: #1e2a3a; white-space: nowrap; }
.ca-employee-name { font-weight: 600; color: #1e2a3a; }
.ca-employee-reason { font-size: 11.5px; color: #8A9BAD; margin-top: 2px; max-width: 220px; }
.ca-empty { text-align: center; color: #8A9BAD; padding: 30px 0 !important; }

.ca-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; white-space: nowrap; }
.ca-badge-pending { background: #eef2ff; color: #4338ca; }
.ca-badge-approved { background: #dcfce7; color: #166534; }
.ca-badge-rejected { background: #fee2e2; color: #991b1b; }
.ca-badge-active { background: #dbeafe; color: #1d4ed8; }
.ca-badge-completed { background: #dcfce7; color: #166534; }
.ca-badge-overdue { background: #fee2e2; color: #991b1b; }

.ca-actions { display: flex; gap: 6px; align-items: center; flex-wrap: nowrap; }
.ca-btn-approve, .ca-btn-reject, .ca-btn-view, .ca-btn-edit {
    padding: 6px 12px; border: 1.5px solid; border-radius: 7px; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .3px; cursor: pointer; background: #fff; white-space: nowrap;
    transition: all .15s;
}
.ca-btn-approve { color: #166534; border-color: #bbf7d0; }
.ca-btn-approve:hover { background: #f0fdf4; }
.ca-btn-reject { color: #991b1b; border-color: #fecaca; }
.ca-btn-reject:hover { background: #fef2f2; }
.ca-btn-view { color: #1e4575; border-color: #bfdbfe; }
.ca-btn-view:hover { background: #eff6ff; }
.ca-btn-edit { color: #A37929; border-color: #fde3a7; }
.ca-btn-edit:hover { background: #fffbeb; }
.ca-btn-delete {
    display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px;
    border: none; background: transparent; color: #9ca3af; cursor: pointer; border-radius: 7px; transition: all .15s;
}
.ca-btn-delete svg { width: 15px; height: 15px; }
.ca-btn-delete:hover { background: #fef2f2; color: #dc2626; }

.ca-term-row {
    display: flex; align-items: center; flex-wrap: wrap; gap: 0;
    border: 1.5px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 10px; background: #f8fafc;
}
.ca-term-row:last-child { margin-bottom: 0; }
.ca-term-row.is-paid { border-color: #bbf7d0; background: #f0fdf4; }
.ca-term-label { font-size: 13px; font-weight: 700; color: #1e4575; padding: 10px 14px; white-space: nowrap; border-right: 1.5px solid #e2e8f0; }
.ca-term-amount { flex: 1 1 auto; padding: 10px 12px; font-size: 13px; color: #374151; }
.ca-term-date-input { padding: 8px 10px; border: none; border-left: 1.5px solid #e2e8f0; outline: none; font-size: 12px; background: transparent; color: #374151; }
.ca-btn-mark-paid {
    padding: 10px 16px; border: none; font-size: 12px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .3px; cursor: pointer; white-space: nowrap;
    background: linear-gradient(135deg,#A37929,#d4a03a); color: #fff;
}
.ca-btn-mark-paid:hover { opacity: .92; }
.ca-btn-mark-paid:disabled { opacity: .5; cursor: not-allowed; }
.ca-term-badge-paid {
    padding: 10px 14px; background: #dcfce7; color: #166534; font-size: 12px; font-weight: 700;
    white-space: nowrap; border-left: 1.5px solid #bbf7d0;
}
.ca-term-badge-paid.is-clickable { cursor: pointer; }
.ca-edit-summary {
    background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px 16px; margin-bottom: 18px;
    display: flex; flex-direction: column; gap: 10px;
}
.ca-edit-summary-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.ca-edit-summary-item label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 2px; }
.ca-edit-summary-item div { font-size: 14px; font-weight: 700; color: #374151; }
.ca-edit-summary-remaining { border-top: 1px dashed #d0d5dd; padding-top: 8px; }
.ca-edit-summary-remaining label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 2px; }
.ca-edit-summary-remaining div { font-size: 16px; font-weight: 700; color: #A37929; }
.ca-edit-summary-stage { border-top: 1px dashed #d0d5dd; padding-top: 8px; }
.ca-edit-summary-stage div { font-size: 15px; font-weight: 800; color: #1e4575; }
</style>

<script>
var _caPendingData = null; // holds the validated request data between "Create Cash Advance Form" and "Confirm & Print"

(function() {
  try {
    const todayStr = new Date().toISOString().split('T')[0];
    const form = document.getElementById('caForm');
    const dateRequestedInput = document.getElementById('ca_date_requested');
    const dateNeededInput = document.getElementById('ca_date_needed');
    const repaymentDateInput = document.getElementById('ca_repayment_date');

    if (!form || !dateRequestedInput || !dateNeededInput || !repaymentDateInput) {
        console.error('[cash-advance] init aborted: expected form elements not found on page', {
            form: !!form, dateRequestedInput: !!dateRequestedInput,
            dateNeededInput: !!dateNeededInput, repaymentDateInput: !!repaymentDateInput,
        });
        return;
    }

    // Default Date Requested to today, and keep Date Needed / Repayment Date
    // from being picked earlier than their logical predecessor.
    dateRequestedInput.value = todayStr;

    function syncMinDates() {
        dateNeededInput.setAttribute('min', dateRequestedInput.value || todayStr);
        if (dateNeededInput.value && dateRequestedInput.value && dateNeededInput.value < dateRequestedInput.value) {
            dateNeededInput.value = '';
        }
        repaymentDateInput.setAttribute('min', dateNeededInput.value || dateRequestedInput.value || todayStr);
    }
    dateRequestedInput.addEventListener('change', syncMinDates);
    dateNeededInput.addEventListener('change', syncMinDates);
    syncMinDates();

    function clearErrors() {
        form.querySelectorAll('.ca-error').forEach(el => el.textContent = '');
        form.querySelectorAll('.ca-invalid').forEach(el => el.classList.remove('ca-invalid'));
    }

    function setError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const err = document.getElementById('err_' + fieldId.replace('ca_', ''));
        if (field) field.classList.add('ca-invalid');
        if (err) err.textContent = message;
    }

    function validateForm(data) {
        let valid = true;

        if (!data.employee_id) {
            setError('ca_employee_id', 'Please select an employee.');
            valid = false;
        }

        if (!data.department) {
            setError('ca_department', 'Please select a department.');
            valid = false;
        }

        const amount = parseFloat(data.amount);
        if (!data.amount || isNaN(amount) || amount <= 0) {
            setError('ca_amount', 'Amount must be a positive number greater than 0.');
            valid = false;
        }

        if (!data.purpose || !data.purpose.trim()) {
            setError('ca_purpose', 'Please enter a purpose.');
            valid = false;
        }

        if (!data.date_requested) {
            setError('ca_date_requested', 'Please select the date requested.');
            valid = false;
        }

        if (!data.date_needed) {
            setError('ca_date_needed', 'Please select the date needed.');
            valid = false;
        } else if (data.date_requested && data.date_needed < data.date_requested) {
            setError('ca_date_needed', 'Date needed cannot be earlier than the date requested.');
            valid = false;
        }

        if (data.repayment_type === 'INSTALLMENT') {
            if (!data.installment_terms) {
                setError('ca_installment_terms', 'Please select the number of terms.');
                valid = false;
            }
        } else if (data.repayment_type === 'OTHERS') {
            if (!data.repayment_date) {
                setError('ca_repayment_date', 'Please select a repayment date.');
                valid = false;
            } else if (data.date_needed && data.repayment_date < data.date_needed) {
                setError('ca_repayment_date', 'Repayment date cannot be earlier than the date needed.');
                valid = false;
            }
        }

        return valid;
    }

    // ---- Printable preview helpers ----
    function fmtDate(v) {
        if (!v) return '—';
        const parts = v.split('-');
        if (parts.length !== 3) return v;
        const d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        if (isNaN(d.getTime())) return v;
        return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function money(v) {
        const n = parseFloat(v);
        if (isNaN(n)) return '0.00';
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function escapeHtml(s) {
        return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function buildPreviewHtml(data) {
        const employeeSelect = document.getElementById('ca_employee_id');
        const chosenOption = employeeSelect.options[employeeSelect.selectedIndex];
        const employeeLabel = chosenOption ? chosenOption.text : '';
        const amount = parseFloat(data.amount) || 0;

        let repaymentRows;
        if (data.repayment_type === 'INSTALLMENT') {
            const terms = parseInt(data.installment_terms, 10) || 0;
            const perTerm = terms > 0 ? (amount / terms) : 0;
            repaymentRows =
                '<tr><td style="padding:6px 0;width:190px;color:#555;">Repayment Type</td><td style="padding:6px 0;">Installment</td></tr>' +
                '<tr><td style="padding:6px 0;color:#555;">Number of Terms</td><td style="padding:6px 0;">' + terms + ' salary deduction' + (terms === 1 ? '' : 's') + '</td></tr>' +
                '<tr><td style="padding:6px 0;color:#555;">Amount per Term</td><td style="padding:6px 0;">₱ ' + money(perTerm) + '</td></tr>';
        } else {
            repaymentRows =
                '<tr><td style="padding:6px 0;width:190px;color:#555;">Repayment Type</td><td style="padding:6px 0;">Others — One-time Payment</td></tr>' +
                '<tr><td style="padding:6px 0;color:#555;">Repayment Date</td><td style="padding:6px 0;">' + fmtDate(data.repayment_date) + '</td></tr>';
        }

        return ''
            + '<div style="display:flex;align-items:center;gap:12px;border-bottom:2px solid #111;padding-bottom:14px;margin-bottom:18px;">'
            +   '<img src="{{ asset('images/ArkCrest_Logo.png') }}" style="width:44px;height:44px;object-fit:contain;">'
            +   '<div>'
            +     '<div style="font-size:16px;font-weight:700;letter-spacing:.5px;">ArkCrest — Cash Advance Request Form</div>'
            +     '<div style="font-size:11px;color:#555;" data-control-number>Control No.: <em>To be assigned upon submission</em></div>'
            +   '</div>'
            + '</div>'
            + '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
            +   '<tr><td style="padding:6px 0;width:190px;color:#555;">Employee</td><td style="padding:6px 0;font-weight:600;">' + escapeHtml(employeeLabel) + '</td></tr>'
            +   '<tr><td style="padding:6px 0;color:#555;">Department</td><td style="padding:6px 0;">' + escapeHtml(data.department) + '</td></tr>'
            +   '<tr><td style="padding:6px 0;color:#555;">Amount Requested</td><td style="padding:6px 0;font-weight:700;">₱ ' + money(amount) + '</td></tr>'
            +   '<tr><td style="padding:6px 0;color:#555;">Date Requested</td><td style="padding:6px 0;">' + fmtDate(data.date_requested) + '</td></tr>'
            +   '<tr><td style="padding:6px 0;color:#555;">Date Needed</td><td style="padding:6px 0;">' + fmtDate(data.date_needed) + '</td></tr>'
            +   '<tr><td style="padding:6px 0;vertical-align:top;color:#555;">Purpose</td><td style="padding:6px 0;">' + escapeHtml(data.purpose) + '</td></tr>'
            +   repaymentRows
            + '</table>'
            + '<div style="margin-top:36px;display:grid;grid-template-columns:1fr 1fr;gap:40px;">'
            +   '<div><div style="border-top:1px solid #111;padding-top:6px;font-size:12px;">Employee Signature</div></div>'
            +   '<div><div style="border-top:1px solid #111;padding-top:6px;font-size:12px;">Approved By</div></div>'
            + '</div>';
    }

    function handleCaSubmit(e) {
        e.preventDefault();
        clearErrors();

        const data = {
            employee_id: document.getElementById('ca_employee_id').value,
            department: document.getElementById('ca_department').value,
            amount: document.getElementById('ca_amount').value,
            purpose: document.getElementById('ca_purpose').value,
            date_requested: document.getElementById('ca_date_requested').value,
            date_needed: document.getElementById('ca_date_needed').value,
            repayment_type: document.getElementById('ca_repayment_type').value,
            installment_terms: document.getElementById('ca_installment_terms').value,
            repayment_date: document.getElementById('ca_repayment_date').value,
        };

        if (!validateForm(data)) {
            showToast('Please fix the highlighted fields.', 'error', 'Validation Failed');
            return;
        }

        // Do NOT save yet — hand off to the printable preview. The record
        // is only created once the user confirms from that preview.
        _caPendingData = data;
        document.getElementById('caPreviewContent').innerHTML = buildPreviewHtml(data);
        document.getElementById('caPreviewModal').style.display = 'flex';

        const confirmBtn = document.getElementById('caConfirmPrintBtn');
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm & Print';
    }

    form.addEventListener('submit', handleCaSubmit);

    // Fallback: if for any reason the native 'submit' event doesn't fire
    // as expected (e.g. a duplicate #caForm id elsewhere on the page), the
    // button's own click still triggers the same logic and is prevented
    // from bubbling into a real form submission.
    const submitBtn = document.getElementById('caSubmitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleCaSubmit(e);
        });
    }

    window.caToggleRepaymentType = function() {
        const type = document.getElementById('ca_repayment_type').value;
        const termsGroup = document.getElementById('ca_terms_group');
        const dateGroup = document.getElementById('ca_repay_date_group');
        const termsInput = document.getElementById('ca_installment_terms');
        const dateInput = document.getElementById('ca_repayment_date');

        if (type === 'OTHERS') {
            termsGroup.style.display = 'none';
            dateGroup.style.display = '';
            termsInput.value = '';
            termsInput.removeAttribute('required');
            dateInput.setAttribute('required', 'required');
        } else {
            dateGroup.style.display = 'none';
            termsGroup.style.display = '';
            dateInput.value = '';
            dateInput.removeAttribute('required');
            termsInput.setAttribute('required', 'required');
        }
    };

    // Initialize visibility to match the default "Installment" selection.
    window.caToggleRepaymentType();
  } catch (err) {
    console.error('[cash-advance] init failed — form will not submit via AJAX until this is fixed:', err);
  }
})();

function caClosePreview() {
    document.getElementById('caPreviewModal').style.display = 'none';
}

function caConfirmAndPrint() {
    if (!_caPendingData) return;

    const confirmBtn = document.getElementById('caConfirmPrintBtn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Submitting...';

    fetch('{{ route('cash-advance.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify(_caPendingData),
    })
    .then(r => r.json().then(json => ({ status: r.status, json })))
    .then(({ status, json }) => {
        if (status === 200 && json.success) {
            showToast(json.message, 'success', 'Request Submitted');
            _caPrintPreview(json.data && json.data.control_number);
            _caPendingData = null;
            setTimeout(() => location.reload(), 900);
        } else {
            showToast(json.message || 'Something went wrong. Please try again.', 'error', 'Submission Failed');
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm & Print';
        }
    })
    .catch(() => {
        showToast('Network error. Please try again.', 'error', 'Submission Failed');
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm & Print';
    });
}

// Opens the browser print dialog on a clean copy of the preview, swapping
// in the real control number now that the record has actually been saved.
function _caPrintPreview(controlNumber) {
    const source = document.getElementById('caPreviewContent');
    let html = source.innerHTML;
    if (controlNumber) {
        html = html.replace('<em>To be assigned upon submission</em>', controlNumber);
    }
    const win = window.open('', '_blank');
    const printHtml = '<html><head><title>Cash Advance Form</title><style>@page{size:letter;margin:.75in}body{font-family:"Times New Roman",serif;font-size:13px;color:#111;margin:0}<' + '/style><' + 'head><body>'
        + html + '</body></html>';
    win.document.write(printHtml);
    win.document.close();
    win.focus();
    setTimeout(function() { win.print(); }, 400);
}

function caApprove(id, controlNumber) {
    showConfirm('Approve cash advance ' + controlNumber + '?', function() {
        fetch('/cash-advance/' + id + '/approve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        })
        .then(r => r.json().then(json => ({ status: r.status, json })))
        .then(({ status, json }) => {
            if (status === 200 && json.success) {
                showToast(json.message, 'success', 'Approved');
                setTimeout(() => location.reload(), 900);
            } else {
                showToast(json.message || 'Could not approve this request.', 'error', 'Error');
            }
        })
        .catch(() => showToast('Network error. Please try again.', 'error', 'Error'));
    }, 'Approve Request');
}

function caReject(id, controlNumber) {
    showConfirm('Reject cash advance ' + controlNumber + '? This will remove its amount from Total Requested.', function() {
        fetch('/cash-advance/' + id + '/reject', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        })
        .then(r => r.json().then(json => ({ status: r.status, json })))
        .then(({ status, json }) => {
            if (status === 200 && json.success) {
                showToast(json.message, 'success', 'Rejected');
                setTimeout(() => location.reload(), 900);
            } else {
                showToast(json.message || 'Could not reject this request.', 'error', 'Error');
            }
        })
        .catch(() => showToast('Network error. Please try again.', 'error', 'Error'));
    }, 'Reject Request');
}

function caDelete(id, controlNumber) {
    showConfirm('Delete cash advance ' + controlNumber + '? This cannot be undone.', function() {
        fetch('/cash-advance/' + id, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        })
        .then(r => r.json().then(json => ({ status: r.status, json })))
        .then(({ status, json }) => {
            if (status === 200 && json.success) {
                showToast(json.message, 'success', 'Deleted');
                setTimeout(() => location.reload(), 900);
            } else {
                showToast(json.message || 'Could not delete this record.', 'error', 'Error');
            }
        })
        .catch(() => showToast('Network error. Please try again.', 'error', 'Error'));
    }, 'Delete Record');
}

// ==== View (read-only, printable) ====
function caFmtDate(v) {
    if (!v) return '—';
    const parts = String(v).split('T')[0].split('-');
    if (parts.length !== 3) return v;
    const d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
    if (isNaN(d.getTime())) return v;
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function caMoney(v) {
    const n = parseFloat(v);
    if (isNaN(n)) return '0.00';
    return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function caEscapeHtml(s) {
    return (s || '').toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function caBuildViewHtml(data) {
    const amount = parseFloat(data.amount) || 0;

    let repaymentRows;
    if (data.repayment_type === 'INSTALLMENT') {
        const terms = parseInt(data.installment_terms, 10) || 0;
        const perTerm = data.amount_per_term != null ? parseFloat(data.amount_per_term) : (terms > 0 ? amount / terms : 0);
        repaymentRows =
            '<tr><td style="padding:6px 0;width:190px;color:#555;">Repayment Type</td><td style="padding:6px 0;">Installment</td></tr>' +
            '<tr><td style="padding:6px 0;color:#555;">Number of Terms</td><td style="padding:6px 0;">' + terms + ' salary deduction' + (terms === 1 ? '' : 's') + '</td></tr>' +
            '<tr><td style="padding:6px 0;color:#555;">Amount per Term</td><td style="padding:6px 0;">₱ ' + caMoney(perTerm) + '</td></tr>' +
            '<tr><td style="padding:6px 0;color:#555;">Payment Stage</td><td style="padding:6px 0;">' + caEscapeHtml(data.payment_stage_label) + '</td></tr>';
    } else {
        repaymentRows =
            '<tr><td style="padding:6px 0;width:190px;color:#555;">Repayment Type</td><td style="padding:6px 0;">Others — One-time Payment</td></tr>' +
            '<tr><td style="padding:6px 0;color:#555;">Repayment Date</td><td style="padding:6px 0;">' + caFmtDate(data.repayment_date) + '</td></tr>' +
            '<tr><td style="padding:6px 0;color:#555;">Payment Stage</td><td style="padding:6px 0;">' + caEscapeHtml(data.payment_stage_label) + '</td></tr>';
    }

    return ''
        + '<div style="display:flex;align-items:center;gap:12px;border-bottom:2px solid #111;padding-bottom:14px;margin-bottom:18px;">'
        +   '<img src="{{ asset('images/ArkCrest_Logo.png') }}" style="width:44px;height:44px;object-fit:contain;">'
        +   '<div>'
        +     '<div style="font-size:16px;font-weight:700;letter-spacing:.5px;">ArkCrest — Cash Advance Request Form</div>'
        +     '<div style="font-size:11px;color:#555;">Control No.: ' + caEscapeHtml(data.control_number) + '</div>'
        +   '</div>'
        + '</div>'
        + '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
        +   '<tr><td style="padding:6px 0;width:190px;color:#555;">Employee</td><td style="padding:6px 0;font-weight:600;">' + caEscapeHtml(data.employee_name) + '</td></tr>'
        +   '<tr><td style="padding:6px 0;color:#555;">Department</td><td style="padding:6px 0;">' + caEscapeHtml(data.department) + '</td></tr>'
        +   '<tr><td style="padding:6px 0;color:#555;">Amount Requested</td><td style="padding:6px 0;font-weight:700;">₱ ' + caMoney(amount) + '</td></tr>'
        +   '<tr><td style="padding:6px 0;color:#555;">Date Requested</td><td style="padding:6px 0;">' + caFmtDate(data.date_requested) + '</td></tr>'
        +   '<tr><td style="padding:6px 0;color:#555;">Date Needed</td><td style="padding:6px 0;">' + caFmtDate(data.date_needed) + '</td></tr>'
        +   '<tr><td style="padding:6px 0;vertical-align:top;color:#555;">Purpose</td><td style="padding:6px 0;">' + caEscapeHtml(data.purpose) + '</td></tr>'
        +   repaymentRows
        +   '<tr><td style="padding:6px 0;color:#555;">Status</td><td style="padding:6px 0;">' + caEscapeHtml(data.display_status) + '</td></tr>'
        + '</table>'
        + '<div style="margin-top:36px;display:grid;grid-template-columns:1fr 1fr;gap:40px;">'
        +   '<div><div style="border-top:1px solid #111;padding-top:6px;font-size:12px;">Employee Signature</div></div>'
        +   '<div><div style="border-top:1px solid #111;padding-top:6px;font-size:12px;">Approved By</div></div>'
        + '</div>';
}

function caOpenView(id) {
    fetch('/cash-advance/' + id, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(json => {
            if (!json.success) {
                showToast('Could not load this record.', 'error', 'Error');
                return;
            }
            document.getElementById('caViewContent').innerHTML = caBuildViewHtml(json.data);
            document.getElementById('caViewModal').style.display = 'flex';
        })
        .catch(() => showToast('Network error. Please try again.', 'error', 'Error'));
}
function caCloseView() {
    document.getElementById('caViewModal').style.display = 'none';
    _caEditCashAdvanceId = null;
}

function caCloseView() {
    document.getElementById('caViewModal').style.display = 'none';
}

function caPrintView() {
    const html = document.getElementById('caViewContent').innerHTML;
    const win = window.open('', '_blank');
    const printHtml = '<html><head><title>Cash Advance Form</title><style>@page{size:letter;margin:.75in}body{font-family:"Times New Roman",serif;font-size:13px;color:#111;margin:0}<' + '/style><' + 'head><body>'
        + html + '</body></html>';
    win.document.write(printHtml);
    win.document.close();
    win.focus();
    setTimeout(function() { win.print(); }, 400);
}

const CA_IS_ADMIN = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
var _caEditHasPaidLockedByAdminRule = false; // reserved if you later gate unmark by an external event (e.g. payroll run)

function caRenderEditContent(id, data) {
    const totalAmount = parseFloat(data.amount) || 0;
    const paidAmount = data.terms.reduce((sum, t) => sum + (t.status === 'PAID' ? (data.repayment_type === 'OTHERS' ? totalAmount : (parseFloat(data.amount_per_term) || 0)) : 0), 0);
    const remaining = Math.max(0, totalAmount - paidAmount);

    let rowsHtml = '';

    if (data.repayment_type === 'OTHERS') {
        const t = data.terms[0] || {};
        const isPaid = t.status === 'PAID';
        rowsHtml = '<div class="ca-term-row' + (isPaid ? ' is-paid' : '') + '">'
            + '<span class="ca-term-label">Repayment</span>'
            + '<span class="ca-term-amount">₱' + caMoney(totalAmount) + '</span>'
            + (isPaid
                ? '<span class="ca-term-badge-paid' + (CA_IS_ADMIN ? ' is-clickable' : '') + '"' + (CA_IS_ADMIN ? ' onclick="caUnmarkTermPaid(' + t.id + ')" title="Click to undo"' : '') + '>✓ Paid — ' + caFmtDate(t.date_paid) + '</span>'
                : '<input type="date" id="ca_term_date_' + t.id + '" class="ca-term-date-input">'
                  + '<button type="button" class="ca-btn-mark-paid" onclick="caMarkTermPaid(' + t.id + ')">Paid</button>');
        rowsHtml += '</div>';
    } else {
        data.terms.forEach(function(t) {
            const isPaid = t.status === 'PAID';
            rowsHtml += '<div class="ca-term-row' + (isPaid ? ' is-paid' : '') + '">'
                + '<span class="ca-term-label">Term ' + t.term_number + '</span>'
                + '<span class="ca-term-amount">₱' + caMoney(data.amount_per_term) + '</span>'
                + (isPaid
                    ? '<span class="ca-term-badge-paid' + (CA_IS_ADMIN ? ' is-clickable' : '') + '"' + (CA_IS_ADMIN ? ' onclick="caUnmarkTermPaid(' + t.id + ')" title="Click to undo"' : '') + '>✓ Paid — ' + caFmtDate(t.date_paid) + '</span>'
                    : '<input type="date" id="ca_term_date_' + t.id + '" class="ca-term-date-input">'
                      + '<button type="button" class="ca-btn-mark-paid" onclick="caMarkTermPaid(' + t.id + ')">Paid</button>')
                + '</div>';
        });
    }

    document.getElementById('caEditContent').innerHTML =
        '<div class="ca-edit-summary">'
        +   '<div><label style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Repayment Type</label>'
        +   '<div style="font-size:13px;font-weight:700;color:#1e4575;">' + (data.repayment_type === 'OTHERS' ? 'Others' : 'Installment') + '</div></div>'
        +   '<div class="ca-edit-summary-row">'
        +     '<div class="ca-edit-summary-item"><label>Total Amount</label><div>₱' + caMoney(totalAmount) + '</div></div>'
        +     '<div class="ca-edit-summary-item"><label>Paid So Far</label><div>₱' + caMoney(paidAmount) + '</div></div>'
        +   '</div>'
        +   '<div class="ca-edit-summary-remaining"><label>Remaining Balance</label><div>₱' + caMoney(remaining) + '</div></div>'
        +   '<div class="ca-edit-summary-stage"><label style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Payment Stage</label><div>' + caEscapeHtml(data.payment_stage_label) + '</div></div>'
        + '</div>'
        + rowsHtml;
}

function caOpenEdit(id, controlNumber) {
    _caEditCashAdvanceId = id;
    document.getElementById('caEditTitle').textContent = 'Repayment Tracking — ' + controlNumber;
    document.getElementById('caEditContent').innerHTML = '<div class="ca-empty">Loading...</div>';
    document.getElementById('caEditModal').style.display = 'flex';

    fetch('/cash-advance/' + id + '/repayments', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(json => {
            if (!json.success) {
                showToast('Could not load repayment terms.', 'error', 'Error');
                return;
            }
            caRenderEditContent(id, json.data);
        })
        .catch(() => showToast('Network error. Please try again.', 'error', 'Error'));
}
function caCloseEdit() {
    document.getElementById('caEditModal').style.display = 'none';
    _caEditCashAdvanceId = null;
}

function caMarkTermPaid(termId) {
    const dateInput = document.getElementById('ca_term_date_' + termId);
    const datePaid = dateInput ? dateInput.value : '';

    if (!datePaid) {
        showToast('Please select the date paid.', 'error', 'Validation Failed');
        return;
    }

    fetch('/cash-advance-repayments/' + termId + '/pay', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ date_paid: datePaid }),
    })
    .then(r => r.json().then(json => ({ status: r.status, json })))
    .then(({ status, json }) => {
        if (status === 200 && json.success) {
            showToast(json.message, 'success', 'Saved');

            const stageCell = document.getElementById('ca-stage-' + _caEditCashAdvanceId);
            if (stageCell) stageCell.textContent = json.payment_stage_label;

            const statusCell = document.getElementById('ca-status-' + _caEditCashAdvanceId);
            if (statusCell) {
                statusCell.innerHTML = '<span class="ca-badge ca-badge-' + json.display_status.toLowerCase() + '">' + json.display_status + '</span>';
            }

            if (_caEditCashAdvanceId) {
                fetch('/cash-advance/' + _caEditCashAdvanceId + '/repayments', { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(refreshed => {
                        if (refreshed.success) caRenderEditContent(_caEditCashAdvanceId, refreshed.data);
                    });
            }
        } else {
            showToast(json.message || 'Could not record this payment.', 'error', 'Error');
        }
    })
    .catch(() => showToast('Network error. Please try again.', 'error', 'Error'));
}

function caUnmarkTermPaid(termId) {
    if (!CA_IS_ADMIN) return;
    showConfirm('Undo this payment? This will mark the term as unpaid.', function() {
        fetch('/cash-advance-repayments/' + termId + '/unpay', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        })
        .then(r => r.json().then(json => ({ status: r.status, json })))
        .then(({ status, json }) => {
            if (status === 200 && json.success) {
                showToast(json.message || 'Term reverted to pending.', 'success', 'Undone');

                const stageCell = document.getElementById('ca-stage-' + _caEditCashAdvanceId);
                if (stageCell) stageCell.textContent = json.payment_stage_label;

                const statusCell = document.getElementById('ca-status-' + _caEditCashAdvanceId);
                if (statusCell) {
                    statusCell.innerHTML = '<span class="ca-badge ca-badge-' + json.display_status.toLowerCase() + '">' + json.display_status + '</span>';
                }

                fetch('/cash-advance/' + _caEditCashAdvanceId + '/repayments', { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(refreshed => {
                        if (refreshed.success) caRenderEditContent(_caEditCashAdvanceId, refreshed.data);
                    });
            } else {
                showToast(json.message || 'Could not undo this payment.', 'error', 'Error');
            }
        })
        .catch(() => showToast('Network error. Please try again.', 'error', 'Error'));
    }, 'Undo Payment');
}

// ---- Filter by Amount (range) ----
(function() {
    const fromInput = document.getElementById('caAmountFrom');
    const toInput = document.getElementById('caAmountTo');
    const clearBtn = document.getElementById('caFilterClearBtn');
    if (!fromInput || !toInput) return;

    function caApplyAmountFilter() {
        const fromVal = fromInput.value;
        const toVal = toInput.value;
        const from = fromVal === '' ? null : parseFloat(fromVal);
        const to = toVal === '' ? null : parseFloat(toVal);

        clearBtn.style.display = (fromVal !== '' || toVal !== '') ? 'inline-block' : 'none';

        const rows = document.querySelectorAll('#caTable tbody tr[data-amount]');
        let visibleCount = 0;

        rows.forEach(row => {
            const amount = parseFloat(row.getAttribute('data-amount'));
            let show = true;
            if (from !== null && amount < from) show = false;
            if (to !== null && amount > to) show = false;
            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        const noMatchRow = document.getElementById('caNoMatchRow');
        const hasFilter = (fromVal !== '' || toVal !== '');
        if (noMatchRow) {
            noMatchRow.style.display = (hasFilter && rows.length > 0 && visibleCount === 0) ? '' : 'none';
        }

        const countEl = document.getElementById('caRecordsCount');
        if (countEl) {
            countEl.textContent = hasFilter ? (visibleCount + ' of ' + rows.length + ' shown') : ({{ $totalRecords }} + ' total');
        }
    }

    fromInput.addEventListener('input', caApplyAmountFilter);
    toInput.addEventListener('input', caApplyAmountFilter);

    window.caClearAmountFilter = function() {
        fromInput.value = '';
        toInput.value = '';
        caApplyAmountFilter();
    };
})();
</script>
@endsection