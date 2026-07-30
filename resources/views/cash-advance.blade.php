\@extends('layouts.dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/departmental-expenses-enhanced.css') }}?v={{ time() }}">
<div class="ca-container">

    <!-- Welcome Banner -->
    <div class="ca-banner">
        <div class="ca-banner-content">
            <div class="ca-eyebrow">Finance</div>
            <h1 class="ca-title">Employee Cash Advance</h1>
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
                        <div style="position:relative;">
                            <input type="text" id="ca_employee_search" class="form-control" placeholder="Type or select employee..." autocomplete="off" required
                                style="padding-right:36px;box-sizing:border-box;width:100%;"
                                onclick="caToggleEmployeeDropdown()" oninput="caFilterEmployeeDropdown(this.value)">
                            <input type="hidden" id="ca_employee_id" name="employee_id">
                            <button type="button" onclick="caToggleEmployeeDropdown()" style="position:absolute;right:2px;top:2px;bottom:2px;width:32px;background:transparent;border:none;color:#8A9BAD;cursor:pointer;font-size:11px;">▼</button>
                            <div id="caEmployeeDropdown" style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;background:#fff;border:1.5px solid #d0d5dd;border-radius:8px;max-height:220px;overflow-y:auto;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,.15);">
                                @foreach($employees as $emp)
                                @php $empLabel = $emp->name . ($emp->position ? ' — ' . $emp->position : ''); @endphp
                                <div class="ca-employee-option" onclick="caSelectEmployee({{ $emp->id }}, '{{ addslashes($empLabel) }}')"
                                    style="padding:10px 14px;cursor:pointer;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background=''">
                                    {{ $empLabel }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <span class="ca-error" id="err_employee_search"></span>
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
                        <input type="number" id="ca_installment_terms_other" class="form-control" min="1" step="1" placeholder="Enter number of terms" style="display:none;">
                        <span class="ca-hint" id="ca_terms_hint">Each term is one installment. Maximum of 6 terms.</span>
                        <span class="ca-error" id="err_installment_terms"></span>
                    </div>
                </div>
            </div>

            <div class="form-actions-right">
                <button type="submit" class="btn-submit" id="caSubmitBtn">Create Cash Advance Form</button>
            </div>
        </form>
    </div>

    <!-- Cash Advance Records -->
    <div class="ca-card ca-records-card">
        <div class="ca-records-header">
            <h3 class="ca-card-title">Cash Advance Records</h3>
            <div style="display:flex;align-items:center;gap:10px;">
                <button type="button" class="ca-bulk-delete-btn" id="caBulkDeleteBtn" disabled onclick="caDeleteSelected()">Delete Selected (0)</button>
                <span class="ca-records-count" id="caRecordsCount">{{ $totalRecords }} total</span>
            </div>
        </div>

        <div class="ca-filter-toolbar">
            <div class="ca-search-wrap">
                <svg class="ca-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="caGlobalSearch" class="ca-search-input" placeholder="Search all columns..." oninput="caFilter()">
            </div>
            <div class="column-filter-dropdown" id="caColumnFilterDropdown">
                <button type="button" class="column-filter-btn" onclick="toggleCaColumnFilterMenu(event)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Filter</span>
                    <span id="caFilterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div id="caColumnFilterMenu" class="column-filter-menu" style="display:none;"></div>
            </div>
            <button type="button" class="clear-column-filters-btn" onclick="caClearAllColumnFilters()">Clear Filters</button>
        </div>
        <div id="caActiveColumnFiltersRow" class="active-column-filters-row" style="display:none;"></div>

        <div class="ca-table-wrap">
            <table class="ca-table js-sort-table js-sort-dropdown" id="caTable">
                <thead>
                    <tr>
                        <th class="ca-sticky-col ca-sticky-checkbox">
                            <input type="checkbox" id="caSelectAll" onchange="caToggleSelectAll(this)" title="Select all">
                        </th>
                        <th class="ca-sticky-col ca-sticky-id">Cash Advance No.</th>
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
                    @php
                        $termsLabel = ($r->installment_terms ?? '—') . ' term' . (($r->installment_terms ?? 0) == 1 ? '' : 's');
                        $termsEditable = in_array($r->status, ['APPROVED', 'COMPLETED']);
                    @endphp
                    <tr id="ca-row-{{ $r->id }}" data-amount="{{ $r->amount }}"
                        data-control="{{ strtolower($r->control_number ?? '') }}"
                        data-employee="{{ strtolower($r->employee_name ?? '') }}"
                        data-department="{{ strtolower($r->department ?? '') }}"
                        data-date-requested="{{ optional($r->date_requested)->format('Y-m-d') ?? optional($r->created_at)->format('Y-m-d') }}"
                        data-date-needed="{{ optional($r->date_needed)->format('Y-m-d') ?? '' }}"
                        data-repayment-type="{{ strtolower($r->repayment_type ?? '') }}"
                        data-status="{{ strtolower($r->display_status ?? '') }}"
                        data-date-added="{{ $r->created_at?->timestamp }}"
                        data-date-modified="{{ $r->updated_at?->timestamp }}">
                        <td class="ca-sticky-col ca-sticky-checkbox">
                            <input type="checkbox" class="ca-row-checkbox" value="{{ $r->id }}" onchange="caUpdateBulkBar()">
                        </td>
                        <td class="ca-id ca-sticky-col ca-sticky-id">{{ $r->control_number }}</td>
                        <td>
                            <div class="ca-employee-name">{{ $r->employee_name }}</div>
                           
                        </td>
                        <td>{{ $r->department ?? '—' }}</td>
                        <td>₱{{ number_format($r->amount, 2) }}</td>
                        <td>{{ optional($r->date_requested)->format('Y-m-d') ?? optional($r->created_at)->format('Y-m-d') }}</td>
                        <td>{{ optional($r->date_needed)->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $r->repayment_type === 'OTHERS' ? 'Others' : 'Installment' }}</td>
                        <td>
                            @if($termsEditable)
                                <button type="button" class="ca-btn-terms ca-btn-terms-{{ strtolower($r->display_status) }}"
                                    title="Manage repayment" onclick="caOpenEdit({{ $r->id }}, '{{ $r->control_number }}')">{{ $termsLabel }}</button>
                            @else
                                <span>{{ $termsLabel }}</span>
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
                                <button type="button" class="ca-btn-delete" title="Delete record" onclick="caDelete({{ $r->id }}, '{{ $r->control_number }}')">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="caEmptyRow">
                        <td colspan="13" class="ca-empty">No cash advance records yet.</td>
                    </tr>
                    @endforelse
                    <tr id="caNoMatchRow" style="display:none;">
                        <td colspan="13" class="ca-empty">No records match the current filters.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @php
        // Each repayment term/installment gets its own row below, so this is a
        // flat count across every cash advance's individual repayment records —
        // not the number of cash advance requests itself.
        $totalRepaymentRecords = $records->sum(function ($r) { return $r->repayments->count(); });
    @endphp

    <!-- Repayment Records -->
    <div class="ca-card ca-repayment-card">
        <div class="ca-records-header">
            <h3 class="ca-card-title">Repayment Records</h3>
            <div style="display:flex;align-items:center;gap:10px;">
                <button type="button" class="ca-bulk-delete-btn" id="caRepayBulkDeleteBtn" disabled onclick="caRepayDeleteSelected()">Delete Selected (0)</button>
                <span class="ca-records-count" id="caRepaymentRecordsCount">{{ $totalRepaymentRecords }} record{{ $totalRepaymentRecords == 1 ? '' : 's' }}</span>
            </div>
        </div>

        <div class="ca-filter-toolbar">
            <div class="ca-search-wrap">
                <svg class="ca-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="caRepayGlobalSearch" class="ca-search-input" placeholder="Search all columns..." oninput="caRepayFilter()">
            </div>
            <div class="column-filter-dropdown" id="caRepayColumnFilterDropdown">
                <button type="button" class="column-filter-btn" onclick="toggleCaRepayColumnFilterMenu(event)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Filter</span>
                    <span id="caRepayFilterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div id="caRepayColumnFilterMenu" class="column-filter-menu" style="display:none;"></div>
            </div>
            <button type="button" class="clear-column-filters-btn" onclick="caRepayClearAllColumnFilters()">Clear Filters</button>
        </div>
        <div id="caRepayActiveColumnFiltersRow" class="active-column-filters-row" style="display:none;"></div>

        <div class="ca-table-wrap">
            <table class="ca-table js-sort-table" id="caRepaymentsTable">
                <thead>
                    <tr>
                        <th class="ca-sticky-col ca-sticky-checkbox">
                            <input type="checkbox" id="caRepaySelectAll" onchange="caRepayToggleSelectAll(this)" title="Select all">
                        </th>
                        <th class="ca-sticky-col ca-sticky-id">Cash Advance No.</th>
                        <th>Employee</th>
                        <th>Repayment Term</th>
                        <th>Amount</th>
                        <th>Payment Stage</th>
                        <th>Status</th>
                        <th>Date Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $r)
                        @foreach($r->repayments as $rep)
                        @php
                            $repAmount = $rep->amount;
                            $repTermLabel = 'term ' . $rep->term_number;
                            $repStatusLabel = $rep->status === 'PAID' ? 'paid' : 'partial';
                        @endphp
                        <tr data-id="{{ $rep->id }}"
                            data-amount="{{ $repAmount }}"
                            data-control="{{ strtolower($r->control_number ?? '') }}"
                            data-employee="{{ strtolower($r->employee_name ?? '') }}"
                            data-term="{{ $repTermLabel }}"
                            data-status="{{ $repStatusLabel }}"
                            data-date-paid="{{ optional($rep->date_paid)->format('Y-m-d') ?? '' }}">
                            <td class="ca-sticky-col ca-sticky-checkbox">
                                <input type="checkbox" class="ca-repay-row-checkbox" value="{{ $rep->id }}" onchange="caRepayUpdateBulkBar()">
                            </td>
                            <td class="ca-id ca-sticky-col ca-sticky-id">{{ $r->control_number }}</td>
                            <td>{{ $r->employee_name }}</td>
                            <td>Term {{ $rep->term_number }}</td>
                            <td>₱{{ number_format($repAmount, 2) }}</td>
                            <td>{{ $rep->term_number }}/{{ $r->total_terms }}</td>
                            <td>
                                <span class="ca-badge ca-badge-{{ $rep->status === 'PAID' ? 'completed' : 'active' }}">{{ $rep->status === 'PAID' ? 'Paid' : 'Partial' }}</span>
                            </td>
                            <td>{{ optional($rep->date_paid)->format('Y-m-d') ?? '—' }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                    @if($totalRepaymentRecords === 0)
                    <tr id="caRepaymentsEmptyRow">
                        <td colspan="8" class="ca-empty">No repayment records yet.</td>
                    </tr>
                    @endif
                    <tr id="caRepayNoMatchRow" style="display:none;">
                        <td colspan="8" class="ca-empty">No repayment records match the current filters.</td>
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

<!-- Bulk Delete Confirm Modal: Cash Advance Records -->
<div id="caBulkDeleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this) caCancelBulkDelete()">
    <div style="background:white;border-radius:16px;max-width:420px;width:90%;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.2);">
        <div style="background:linear-gradient(135deg,#dc2626,#ef4444);padding:18px 22px;display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div style="flex:1;">
                <div style="color:rgba(255,255,255,.75);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;">Confirm Deletion</div>
                <div style="color:white;font-size:15px;font-weight:700;margin-top:1px;">Delete Selected Cash Advance Records</div>
            </div>
        </div>
        <div style="padding:20px 22px;">
            <p style="font-size:14px;color:#374151;margin:0 0 4px;">Delete <strong id="caBulkDeleteCount">0</strong> selected record(s)?</p>
            <p style="font-size:12px;color:#94a3b8;margin:0 0 18px;">This action cannot be undone.</p>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button onclick="caCancelBulkDelete()" style="padding:9px 18px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;">No, Cancel</button>
                <button onclick="caConfirmBulkDelete()" style="padding:9px 20px;background:#dc2626;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Confirm Modal: Repayment Records -->
<div id="caRepayBulkDeleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this) caRepayCancelBulkDelete()">
    <div style="background:white;border-radius:16px;max-width:420px;width:90%;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.2);">
        <div style="background:linear-gradient(135deg,#dc2626,#ef4444);padding:18px 22px;display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div style="flex:1;">
                <div style="color:rgba(255,255,255,.75);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;">Confirm Deletion</div>
                <div style="color:white;font-size:15px;font-weight:700;margin-top:1px;">Delete Selected Repayment Records</div>
            </div>
        </div>
        <div style="padding:20px 22px;">
            <p style="font-size:14px;color:#374151;margin:0 0 4px;">Delete <strong id="caRepayBulkDeleteCount">0</strong> selected record(s)?</p>
            <p style="font-size:12px;color:#94a3b8;margin:0 0 18px;">This action cannot be undone.</p>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button onclick="caRepayCancelBulkDelete()" style="padding:9px 18px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;">No, Cancel</button>
                <button onclick="caRepayConfirmBulkDelete()" style="padding:9px 20px;background:#dc2626;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<style>


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

.ca-records-header { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px; }
.ca-records-count { font-size: 12px; color: #8A9BAD; font-weight: 600; }

/* Bulk delete button used by both tables' header bars */
.ca-bulk-delete-btn {
    padding: 8px 14px; border-radius: 8px; border: none; font-size: 12px; font-weight: 700;
    cursor: pointer; background: #ef4444; color: #fff; transition: opacity .2s;
}
.ca-bulk-delete-btn:disabled { opacity: .45; cursor: not-allowed; }

/* ---- Column filter dropdown + chips (matches Client Database pattern) ---- */
.ca-filter-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 14px; }
.ca-search-wrap { position: relative; flex: 1 1 220px; min-width: 180px; max-width: 320px; }
.ca-search-input { width: 100%; box-sizing: border-box; padding: 8px 12px 8px 34px; border: 1.5px solid #d0d5dd; border-radius: 8px; font-size: 12.5px; color: #344054; height: 36px; outline: none; transition: border-color .15s; }
.ca-search-input:focus { border-color: #1e4575; }
.ca-search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #94a3b8; pointer-events: none; }
.column-filter-dropdown { position: relative; }
.column-filter-btn { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; font-size: 12.5px; font-weight: 700; color: #1e4575; background: #fff; border: 1.5px solid #1e4575; border-radius: 8px; padding: 8px 13px; cursor: pointer; height: 36px; box-sizing: border-box; transition: all .2s ease; }
.column-filter-btn:hover { background: #eef2f7; }
.column-filter-btn svg { width: 14px; height: 14px; }
.filter-count-badge { background: #A37929; color: white; font-size: 10.5px; font-weight: 700; border-radius: 999px; min-width: 17px; height: 17px; display: inline-flex; align-items: center; justify-content: center; padding: 0 5px; }
.column-filter-menu { position: absolute; top: calc(100% + 6px); left: 0; min-width: 210px; max-height: 300px; overflow-y: auto; background: white; border: 1.5px solid #d0d5dd; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 500; padding: 6px; }
.column-filter-menu-item { display: flex; align-items: center; gap: 8px; padding: 8px 10px; font-size: 12.5px; font-weight: 500; color: #344054; border-radius: 6px; cursor: pointer; white-space: nowrap; }
.column-filter-menu-item:hover { background: #eef2f7; }
.column-filter-menu-item .cfm-check { width: 14px; color: #A37929; font-weight: 700; visibility: hidden; }
.column-filter-menu-item.is-active .cfm-check { visibility: visible; }
.column-filter-menu-item.is-active { color: #1e4575; font-weight: 700; }
.active-column-filters-row { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 14px; }
.column-filter-chip { display: flex; align-items: center; gap: 6px; background: #f5f7fa; border: 1.5px solid #d0d5dd; border-radius: 8px; padding: 6px 8px 6px 12px; }
.column-filter-chip label { font-size: 10.5px; font-weight: 700; color: #1e4575; text-transform: uppercase; letter-spacing: .3px; white-space: nowrap; }
.column-filter-chip input, .column-filter-chip select { font-size: 12.5px; padding: 6px 8px; border: 1.5px solid #d0d5dd; border-radius: 6px; color: #344054; min-width: 120px; }
.column-filter-chip .cfm-remove { background: none; border: none; color: #8a9bad; cursor: pointer; font-size: 16px; line-height: 1; padding: 2px 4px; }
.column-filter-chip .cfm-remove:hover { color: #dc2626; }
.clear-column-filters-btn { font-size: 11.5px; font-weight: 600; color: #1e4575; background: #eef2f7; border: 1px solid #d0d5dd; border-radius: 6px; padding: 7px 13px; cursor: pointer; white-space: nowrap; }
@media (max-width: 768px) {
    .column-filter-menu { left: 0; right: 0; min-width: 0; width: 100%; box-sizing: border-box; }
    .active-column-filters-row { flex-direction: column; align-items: stretch; }
    .column-filter-chip { width: 100%; flex-wrap: wrap; box-sizing: border-box; }
    .column-filter-chip label { flex: 1 1 100%; }
    .column-filter-chip input, .column-filter-chip select { flex: 1 1 auto; min-width: 0; width: 100%; }
    .clear-column-filters-btn { width: 100%; text-align: center; }
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

/* Sticky "Cash Advance No." column — used by both the Cash Advance Records
   table and the Repayment Records table below it, so the control number
   stays visible while the rest of the row scrolls horizontally. A sticky
   checkbox column sits to its left for row selection. */
.ca-sticky-col {
    position: sticky;
    left: 0;
    z-index: 2;
    background: #fff;
    box-shadow: 2px 0 4px -2px rgba(0,0,0,.15);
}
.ca-table thead th.ca-sticky-col {
    z-index: 3;
    background: #fff;
}
.ca-sticky-checkbox { left: 0; width: 40px; min-width: 40px; max-width: 40px; text-align: center; box-shadow: none; }
.ca-sticky-id { left: 40px; }
.ca-table thead th.ca-sticky-checkbox { z-index: 3; background: #fff; }

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

/* Terms button — styled like the Downpayment status pill in Client Database:
   a rounded pill, colored by status, clickable to open repayment tracking. */
.ca-btn-terms {
    display: inline-block; padding: 5px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600; border: none; cursor: pointer;
    transition: opacity .15s; white-space: nowrap;
}
.ca-btn-terms:hover { opacity: .85; }
.ca-btn-terms-pending   { background: #eef2ff; color: #4338ca; }
.ca-btn-terms-approved  { background: #dcfce7; color: #166534; }
.ca-btn-terms-rejected  { background: #fee2e2; color: #991b1b; }
.ca-btn-terms-active    { background: #dbeafe; color: #1d4ed8; }
.ca-btn-terms-completed { background: #dcfce7; color: #166534; }
.ca-btn-terms-overdue   { background: #fee2e2; color: #991b1b; }

.ca-actions { display: flex; gap: 6px; align-items: center; flex-wrap: nowrap; }
.ca-btn-approve, .ca-btn-reject, .ca-btn-view {
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
.ca-term-amount-input {
    width: 110px; padding: 8px 10px; border: none; border-left: 1.5px solid #e2e8f0;
    outline: none; font-size: 12px; background: transparent; color: #374151; flex: 1 1 auto;
}
.ca-term-amount-input:focus { background: #fff; }
.ca-term-date-input { padding: 8px 10px; border: none; border-left: 1.5px solid #e2e8f0; outline: none; font-size: 12px; background: transparent; color: #374151; }
.ca-btn-mark-paid {
    padding: 10px 16px; border: none; font-size: 12px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .3px; cursor: pointer; white-space: nowrap;
    background: linear-gradient(135deg,#A37929,#d4a03a); color: #fff;
}
.ca-btn-divide-equally {
    display: block; width: 100%; margin-bottom: 10px; padding: 9px 14px;
    border: 1.5px dashed #A37929; border-radius: 8px; background: #fff7ea;
    color: #A37929; font-size: 12px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .3px; cursor: pointer; transition: all .15s;
}
.ca-btn-divide-equally:hover { background: #fdecc8; }
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

    if (!form || !dateRequestedInput || !dateNeededInput) {
        console.error('[cash-advance] init aborted: expected form elements not found on page', {
            form: !!form, dateRequestedInput: !!dateRequestedInput,
            dateNeededInput: !!dateNeededInput,
        });
        return;
    }

    // Default Date Requested to today, and keep Date Needed from being
    // picked earlier than its logical predecessor.
    dateRequestedInput.value = todayStr;

    function syncMinDates() {
        dateNeededInput.setAttribute('min', dateRequestedInput.value || todayStr);
        if (dateNeededInput.value && dateRequestedInput.value && dateNeededInput.value < dateRequestedInput.value) {
            dateNeededInput.value = '';
        }
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

        if (!data.employee_id && !(data.employee_name && data.employee_name.trim())) {
            setError('ca_employee_search', 'Please enter or select an employee.');
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

        const termsEl = data.repayment_type === 'OTHERS'
            ? document.getElementById('ca_installment_terms_other')
            : document.getElementById('ca_installment_terms');
        const termsErrEl = document.getElementById('err_installment_terms');
        const termsVal = parseInt(data.installment_terms, 10);
        if (!data.installment_terms || isNaN(termsVal) || termsVal < 1) {
            if (termsEl) termsEl.classList.add('ca-invalid');
            if (termsErrEl) termsErrEl.textContent = 'Please enter the number of terms.';
            valid = false;
        } else if (data.repayment_type === 'INSTALLMENT' && termsVal > 6) {
            if (termsEl) termsEl.classList.add('ca-invalid');
            if (termsErrEl) termsErrEl.textContent = 'A maximum of 6 terms is allowed for Installment.';
            valid = false;
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

    function caCopyBlock(label, data, employeeLabel, controlHtml) {
    const amount = parseFloat(data.amount) || 0;
    const terms = parseInt(data.installment_terms, 10) || 0;
    const perTerm = terms > 0 ? (amount / terms) : 0;
    const repaymentRows =
        '<tr><td style="padding:4px 0;width:190px;color:#555;">Repayment Type</td><td style="padding:4px 0;">' + (data.repayment_type === 'OTHERS' ? 'Others' : 'Installment') + '</td></tr>' +
        '<tr><td style="padding:4px 0;color:#555;">Number of Terms</td><td style="padding:4px 0;">' + terms + ' installment' + (terms === 1 ? '' : 's') + '</td></tr>' +
        '<tr><td style="padding:4px 0;color:#555;">Amount per Term</td><td style="padding:4px 0;">₱ ' + money(perTerm) + '</td></tr>';

    return '<div style="page-break-inside:avoid;">'
        + '<p style="font-style:italic;margin:0 0 4px;font-size:10px;">' + label + '</p>'
        + '<div style="display:flex;align-items:center;gap:8px;border-bottom:2px solid #111;padding-bottom:8px;margin-bottom:10px;">'
+           '<img src="{{ asset('images/ArkCrest_Logo.png') }}" style="width:34px;height:34px;object-fit:contain;">'        +   '<div>'
        +     '<div style="font-size:16px;font-weight:700;letter-spacing:.5px;">ArkCrest — Cash Advance Request Form</div>'
        +     '<div style="font-size:11px;color:#555;">' + controlHtml + '</div>'
        +   '</div>'
        + '</div>'
        + '<table style="width:100%;border-collapse:collapse;font-size:12px;">'
        +   '<tr><td style="padding:4px 0;width:190px;color:#555;">Employee</td><td style="padding:4px 0;font-weight:600;">' + escapeHtml(employeeLabel) + '</td></tr>'
        +   '<tr><td style="padding:4px 0;color:#555;">Department</td><td style="padding:4px 0;">' + escapeHtml(data.department) + '</td></tr>'
        +   '<tr><td style="padding:4px 0;color:#555;">Amount Requested</td><td style="padding:4px 0;font-weight:700;">₱ ' + money(amount) + '</td></tr>'
        +   '<tr><td style="padding:4px 0;color:#555;">Date Requested</td><td style="padding:4px 0;">' + fmtDate(data.date_requested) + '</td></tr>'
        +   '<tr><td style="padding:4px 0;color:#555;">Date Needed</td><td style="padding:4px 0;">' + fmtDate(data.date_needed) + '</td></tr>'
        +   '<tr><td style="padding:4px 0;vertical-align:top;color:#555;">Purpose</td><td style="padding:4px 0;">' + escapeHtml(data.purpose) + '</td></tr>'
        +   repaymentRows
        + '</table>'
        + '<table class="nb" style="font-size:11px;margin-top:12px;width:100%;"><tr>'
        +   '<td style="width:50%;padding-top:12px;border-top:1px solid #111;">Requested by</td>'
        +   '<td style="padding-top:12px;border-top:1px solid #111;">Approved by</td>'
        + '</tr></table>'
        + '</div>';
}

function buildPreviewHtml(data) {
    const employeeLabel = document.getElementById('ca_employee_search').value || '';
    const controlHtml = 'Control No.: <em>To be assigned upon submission</em>';
    return caCopyBlock('Company Copy', data, employeeLabel, controlHtml)
        + '<hr style="margin:10px 0;border:none;border-top:1px dashed #999;">'
        + caCopyBlock("Employee's Copy", data, employeeLabel, controlHtml);
}

    function handleCaSubmit(e) {
        e.preventDefault();
        clearErrors();

        const data = {
            employee_id: document.getElementById('ca_employee_id').value,
            employee_name: document.getElementById('ca_employee_search').value,
            department: document.getElementById('ca_department').value,
            amount: document.getElementById('ca_amount').value,
            purpose: document.getElementById('ca_purpose').value,
            date_requested: document.getElementById('ca_date_requested').value,
            date_needed: document.getElementById('ca_date_needed').value,
            repayment_type: document.getElementById('ca_repayment_type').value,
            installment_terms: document.getElementById('ca_repayment_type').value === 'OTHERS'
                ? document.getElementById('ca_installment_terms_other').value
                : document.getElementById('ca_installment_terms').value,
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
        const selectEl = document.getElementById('ca_installment_terms');
        const otherEl = document.getElementById('ca_installment_terms_other');
        const termsHint = document.getElementById('ca_terms_hint');

        if (type === 'OTHERS') {
            selectEl.style.display = 'none';
            selectEl.removeAttribute('required');
            otherEl.style.display = '';
            otherEl.setAttribute('required', 'required');
            termsHint.textContent = 'Enter how many terms this cash advance should be repaid in.';
        } else {
            otherEl.style.display = 'none';
            otherEl.removeAttribute('required');
            selectEl.style.display = '';
            selectEl.setAttribute('required', 'required');
            termsHint.textContent = 'Each term is one installment. Maximum of 6 terms.';
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
        html = html.split('<em>To be assigned upon submission</em>').join(controlNumber);
    }
    const win = window.open('', '_blank');
    const printHtml = '<html><head><title>Cash Advance Form</title><style>@page{size:letter;margin:.75in}body{font-family:"Times New Roman",serif;font-size:12px;color:#111;margin:0}<' + '/style><' + 'head><body>'
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

function caCopyBlockView(label, data) {
    const amount = parseFloat(data.amount) || 0;

    const terms = parseInt(data.installment_terms, 10) || 0;
    const perTerm = data.amount_per_term != null ? parseFloat(data.amount_per_term) : (terms > 0 ? amount / terms : 0);
    const repaymentRows =
        '<tr><td style="padding:4px 0;width:190px;color:#555;">Repayment Type</td><td style="padding:4px 0;">' + (data.repayment_type === 'OTHERS' ? 'Others' : 'Installment') + '</td></tr>' +
        '<tr><td style="padding:4px 0;color:#555;">Number of Terms</td><td style="padding:4px 0;">' + terms + ' installment' + (terms === 1 ? '' : 's') + '</td></tr>' +
        '<tr><td style="padding:4px 0;color:#555;">Amount per Term</td><td style="padding:4px 0;">₱ ' + caMoney(perTerm) + '</td></tr>' +
        '<tr><td style="padding:4px 0;color:#555;">Payment Stage</td><td style="padding:4px 0;">' + caEscapeHtml(data.payment_stage_label) + '</td></tr>';

    return '<div style="page-break-inside:avoid;">'
        + '<p style="font-style:italic;margin:0 0 4px;font-size:10px;">' + label + '</p>'
        + '<div style="display:flex;align-items:center;gap:8px;border-bottom:2px solid #111;padding-bottom:8px;margin-bottom:10px;">'
+           '<img src="{{ asset('images/ArkCrest_Logo.png') }}" style="width:34px;height:34px;object-fit:contain;">'        +   '<div>'
        +     '<div style="font-size:16px;font-weight:700;letter-spacing:.5px;">ArkCrest — Cash Advance Request Form</div>'
        +     '<div style="font-size:11px;color:#555;">Control No.: ' + caEscapeHtml(data.control_number) + '</div>'
        +   '</div>'
        + '</div>'
        + '<table style="width:100%;border-collapse:collapse;font-size:12px;">'
        +   '<tr><td style="padding:4px 0;width:190px;color:#555;">Employee</td><td style="padding:4px 0;font-weight:600;">' + caEscapeHtml(data.employee_name) + '</td></tr>'
        +   '<tr><td style="padding:4px 0;color:#555;">Department</td><td style="padding:4px 0;">' + caEscapeHtml(data.department) + '</td></tr>'
        +   '<tr><td style="padding:4px 0;color:#555;">Amount Requested</td><td style="padding:4px 0;font-weight:700;">₱ ' + caMoney(amount) + '</td></tr>'
        +   '<tr><td style="padding:4px 0;color:#555;">Date Requested</td><td style="padding:4px 0;">' + caFmtDate(data.date_requested) + '</td></tr>'
        +   '<tr><td style="padding:4px 0;color:#555;">Date Needed</td><td style="padding:4px 0;">' + caFmtDate(data.date_needed) + '</td></tr>'
        +   '<tr><td style="padding:4px 0;vertical-align:top;color:#555;">Purpose</td><td style="padding:4px 0;">' + caEscapeHtml(data.purpose) + '</td></tr>'
        +   repaymentRows
        +   '<tr><td style="padding:4px 0;color:#555;">Status</td><td style="padding:4px 0;">' + caEscapeHtml(data.display_status) + '</td></tr>'
        + '</table>'
        + '<table class="nb" style="font-size:11px;margin-top:12px;width:100%;"><tr>'
        +   '<td style="width:50%;padding-top:12px;border-top:1px solid #111;">Requested by</td>'
        +   '<td style="padding-top:12px;border-top:1px solid #111;">Approved by</td>'
        + '</tr></table>'
        + '</div>';
}

function caBuildViewHtml(data) {
    return caCopyBlockView('Company Copy', data)
        + '<hr style="margin:10px 0;border:none;border-top:1px dashed #999;">'
        + caCopyBlockView("Employee's Copy", data);
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
}

function caPrintView() {
    const html = document.getElementById('caViewContent').innerHTML;
    const win = window.open('', '_blank');
    const printHtml = '<html><head><title>Cash Advance Form</title><style>@page{size:letter;margin:.75in}body{font-family:"Times New Roman",serif;font-size:12px;color:#111;margin:0}<' + '/style><' + 'head><body>'
        + html + '</body></html>';
    win.document.write(printHtml);
    win.document.close();
    win.focus();
    setTimeout(function() { win.print(); }, 400);
}

const CA_IS_ADMIN = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
var _caEditHasPaidLockedByAdminRule = false; // reserved if you later gate unmark by an external event (e.g. payroll run)

var _caEditData = null;

function caRenderEditContent(id, data) {
    _caEditData = data;
    const totalAmount = parseFloat(data.amount) || 0;
    const paidAmount = data.terms.reduce((sum, t) => sum + (t.status === 'PAID' ? (parseFloat(t.amount) || 0) : 0), 0);
    const remaining = Math.max(0, totalAmount - paidAmount);
    const unpaidTerms = data.terms.filter(function(t) { return t.status !== 'PAID'; });

    let rowsHtml = '';

    function termAmountFallback(t) {
        if (t.amount != null) return parseFloat(t.amount) || 0;
        return parseFloat(data.amount_per_term) || 0;
    }

    data.terms.forEach(function(t) {
        const isPaid = t.status === 'PAID';
        const amt = termAmountFallback(t);
        // If the balance already hit zero before this term's turn (e.g. the
        // whole advance was paid off at term 2 of 6), there's nothing left
        // to collect for it — show it as waived instead of still offering
        // an amount/date input and a "Paid" button for it.
        const isWaived = !isPaid && remaining <= 0;
        rowsHtml += '<div class="ca-term-row' + (isPaid ? ' is-paid' : (isWaived ? ' is-waived' : '')) + '">'
            + '<span class="ca-term-label">Term ' + t.term_number + '</span>'
            + (isPaid
                ? '<span class="ca-term-amount">₱' + caMoney(amt) + '</span>'
                  + '<span class="ca-term-badge-paid' + (CA_IS_ADMIN ? ' is-clickable' : '') + '"' + (CA_IS_ADMIN ? ' onclick="caUnmarkTermPaid(' + t.term_number + ')" title="Click to undo"' : '') + '>✓ Paid — ' + caFmtDate(t.date_paid) + '</span>'
                : (isWaived
                    ? '<span class="ca-term-badge-waived" style="display:inline-block;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;background:#e2e8f0;color:#475569;">Balance already fully paid — no payment needed</span>'
                    : '<input type="number" step="0.01" min="0.01" id="ca_term_amount_' + t.term_number + '" class="ca-term-amount-input" placeholder="₱' + amt.toFixed(2) + '">'
                      + '<input type="date" id="ca_term_date_' + t.term_number + '" class="ca-term-date-input">'
                      + '<button type="button" class="ca-btn-mark-paid" onclick="caMarkTermPaid(' + t.term_number + ')">Paid</button>'))
            + '</div>';
    });

    var divideBtnHtml = '';
    if (unpaidTerms.length > 1 && remaining > 0) {
        divideBtnHtml = '<button type="button" class="ca-btn-divide-equally" onclick="caDivideEqually()">Divide Equally by '
            + unpaidTerms.length + ' Term' + (unpaidTerms.length === 1 ? '' : 's') + '</button>';
    }

    // Flag when the advance was paid off ahead of its original term schedule
    // so the still-"unpaid" terms don't look like an outstanding balance.
    var fullyPaidEarlyNoteHtml = '';
    if (remaining <= 0 && unpaidTerms.length > 0) {
        fullyPaidEarlyNoteHtml = '<div class="ca-edit-fully-paid-note" style="margin-top:8px;padding:8px 12px;border-radius:8px;background:#dcfce7;color:#166534;font-size:12.5px;font-weight:600;">'
            + '✓ Fully paid ahead of schedule — the remaining term' + (unpaidTerms.length === 1 ? '' : 's') + ' below no longer require payment.</div>';
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
        +   fullyPaidEarlyNoteHtml
        + '</div>'
        + divideBtnHtml
        + rowsHtml;
}

// Splits the *remaining* balance evenly across whatever terms are still
// unpaid (not the original total ÷ total terms) so it stays correct even
// after some terms were paid with custom, uneven amounts.
function caDivideEqually() {
    if (!_caEditData) return;
    var data = _caEditData;
    var totalAmount = parseFloat(data.amount) || 0;
    var paidAmount = data.terms.reduce(function(sum, t) {
        return sum + (t.status === 'PAID' ? (parseFloat(t.amount) || 0) : 0);
    }, 0);
    var remaining = Math.max(0, totalAmount - paidAmount);
    var unpaidTerms = data.terms.filter(function(t) { return t.status !== 'PAID'; });
    if (!unpaidTerms.length) return;
    if (remaining <= 0) return; // already fully paid off early — nothing left to divide

    var split = remaining / unpaidTerms.length;
    unpaidTerms.forEach(function(t) {
        var input = document.getElementById('ca_term_amount_' + t.term_number);
        if (input) input.value = split.toFixed(2);
    });
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

function caMarkTermPaid(termNumber) {
    const dateInput = document.getElementById('ca_term_date_' + termNumber);
    const amountInput = document.getElementById('ca_term_amount_' + termNumber);
    const datePaid = dateInput ? dateInput.value : '';
    const amount = amountInput ? parseFloat(amountInput.value) : NaN;

    if (!datePaid) {
        showToast('Please select the date paid.', 'error', 'Validation Failed');
        return;
    }
    if (!amount || amount <= 0) {
        showToast('Please enter a valid amount.', 'error', 'Validation Failed');
        return;
    }

    // Guard against paying a term that's no longer owed because the full
    // balance was already settled early (e.g. paid off at term 2 of 6).
    // Without this check, staff could keep "paying" terms with nothing left
    // to collect, and could also overpay past what's actually remaining.
    if (_caEditData) {
        var totalAmount = parseFloat(_caEditData.amount) || 0;
        var paidSoFar = _caEditData.terms.reduce(function(sum, t) {
            return sum + (t.status === 'PAID' ? (parseFloat(t.amount) || 0) : 0);
        }, 0);
        var remainingBalance = Math.max(0, totalAmount - paidSoFar);

        if (remainingBalance <= 0) {
            showToast('This cash advance is already fully paid. No further payments are needed.', 'error', 'Already Fully Paid');
            return;
        }
        if (amount > remainingBalance + 0.01) {
            showToast('Amount exceeds the remaining balance of ₱' + caMoney(remainingBalance) + '.', 'error', 'Validation Failed');
            return;
        }
    }

    fetch('/cash-advance/' + _caEditCashAdvanceId + '/repayments/' + termNumber + '/pay', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ date_paid: datePaid, amount: amount }),
    })
    .then(r => r.json().then(json => ({ status: r.status, json })))
    .then(({ status, json }) => {
        if (status === 200 && json.success) {
            showToast(json.message, 'success', 'Saved');

            // The Repayment Records table further down the page is rendered
            // server-side only (unlike the Cash Advance Records row and this
            // modal, which we already patch live above) — without a reload it
            // keeps showing the pre-payment status and a blank Date Paid even
            // though the payment was recorded successfully. Reload so it picks
            // up the real saved values.
            setTimeout(() => location.reload(), 900);
        } else {
            showToast(json.message || 'Could not record this payment.', 'error', 'Error');
        }
    })
    .catch(() => showToast('Network error. Please try again.', 'error', 'Error'));
}

function caUnmarkTermPaid(termNumber) {
    if (!CA_IS_ADMIN) return;
    showConfirm('Undo this payment? This will mark the term as unpaid.', function() {
        fetch('/cash-advance/' + _caEditCashAdvanceId + '/repayments/' + termNumber + '/unpay', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        })
        .then(r => r.json().then(json => ({ status: r.status, json })))
        .then(({ status, json }) => {
            if (status === 200 && json.success) {
                showToast(json.message || 'Term reverted to pending.', 'success', 'Undone');
                // Same reason as caMarkTermPaid — reload so the server-rendered
                // Repayment Records table reflects the real (now-unpaid) state
                // instead of staying stale.
                setTimeout(() => location.reload(), 900);
            } else {
                showToast(json.message || 'Could not undo this payment.', 'error', 'Error');
            }
        })
        .catch(() => showToast('Network error. Please try again.', 'error', 'Error'));
    }, 'Undo Payment');
}

// ==== Column filter dropdown + chips: Cash Advance Records table ====
// Field types: 'text' (substring match), 'select' (exact match against a
// fixed option list), 'numrange' (min/max, for Amount), 'daterange' (from/to,
// for the two date columns). Mirrors the pattern used on Client Database.
var CA_FILTERABLE_FIELDS = [
    { key: 'control',         label: 'Cash Advance No.', dataAttr: 'data-control',         type: 'text' },
    { key: 'employee',        label: 'Employee',         dataAttr: 'data-employee',         type: 'text' },
    { key: 'department',      label: 'Department',       dataAttr: 'data-department',       type: 'select', options: [@foreach($departments as $dept)'{{ addslashes($dept) }}', @endforeach] },
    { key: 'amount',          label: 'Amount',           dataAttr: 'data-amount',            type: 'numrange' },
    { key: 'date-requested',  label: 'Date Requested',   dataAttr: 'data-date-requested',    type: 'daterange' },
    { key: 'date-needed',     label: 'Date Needed',      dataAttr: 'data-date-needed',       type: 'daterange' },
    { key: 'repayment-type',  label: 'Repayment Type',   dataAttr: 'data-repayment-type',    type: 'select', options: ['Installment', 'Others'] },
    { key: 'status',          label: 'Status',           dataAttr: 'data-status',            type: 'select', options: ['Pending', 'Approved', 'Rejected', 'Active', 'Completed', 'Overdue'] },
];

var caColumnFilters = {};

function caFieldConfig(key) {
    return CA_FILTERABLE_FIELDS.find(function (f) { return f.key === key; });
}

function toggleCaColumnFilterMenu(e) {
    e.stopPropagation();
    var menu = document.getElementById('caColumnFilterMenu');
    if (menu.style.display === 'block') { menu.style.display = 'none'; return; }
    renderCaColumnFilterMenu();
    menu.style.display = 'block';
}

function renderCaColumnFilterMenu() {
    var menu = document.getElementById('caColumnFilterMenu');
    menu.innerHTML = '';
    CA_FILTERABLE_FIELDS.forEach(function (f) {
        var item = document.createElement('div');
        item.className = 'column-filter-menu-item' + (caColumnFilters.hasOwnProperty(f.key) ? ' is-active' : '');
        item.innerHTML = '<span class="cfm-check">✓</span><span>' + f.label + '</span>';
        item.onclick = function (ev) { ev.stopPropagation(); caToggleColumnFilter(f.key); };
        menu.appendChild(item);
    });
}

function caToggleColumnFilter(key) {
    if (caColumnFilters.hasOwnProperty(key)) {
        delete caColumnFilters[key];
    } else {
        var f = caFieldConfig(key);
        caColumnFilters[key] = (f && (f.type === 'daterange' || f.type === 'numrange')) ? { from: '', to: '' } : '';
    }
    renderCaColumnFilterMenu();
    renderCaActiveColumnFilters();
    updateCaFilterBadge();
    caFilter();
    document.getElementById('caColumnFilterMenu').style.display = 'none';
}

function caRemoveColumnFilter(key) {
    delete caColumnFilters[key];
    renderCaActiveColumnFilters();
    updateCaFilterBadge();
    caFilter();
}

function updateCaFilterBadge() {
    var badge = document.getElementById('caFilterCountBadge');
    var count = Object.keys(caColumnFilters).length;
    badge.style.display = count > 0 ? 'inline-flex' : 'none';
    badge.textContent = count;
}

function caClearAllColumnFilters() {
    caColumnFilters = {};
    renderCaColumnFilterMenu();
    renderCaActiveColumnFilters();
    updateCaFilterBadge();
    caFilter();
}

function renderCaActiveColumnFilters() {
    var row = document.getElementById('caActiveColumnFiltersRow');
    var keys = Object.keys(caColumnFilters);
    row.innerHTML = '';
    if (keys.length === 0) { row.style.display = 'none'; return; }
    row.style.display = 'flex';

    keys.forEach(function (key) {
        var f = caFieldConfig(key);
        if (!f) return;
        var chip = document.createElement('div');
        chip.className = 'column-filter-chip';
        var label = document.createElement('label');
        label.textContent = f.label;
        chip.appendChild(label);

        var input;
        if (f.type === 'daterange') {
            if (!caColumnFilters[key] || typeof caColumnFilters[key] !== 'object') caColumnFilters[key] = { from: '', to: '' };
            var range = caColumnFilters[key];
            input = document.createElement('span');
            input.style.cssText = 'display:flex;align-items:center;gap:6px;';
            var fromInput = document.createElement('input');
            fromInput.type = 'date';
            fromInput.value = range.from || '';
            fromInput.onchange = function () { range.from = this.value; caFilter(); };
            var toLabel = document.createElement('span');
            toLabel.textContent = 'to';
            toLabel.style.cssText = 'color:#8a9bad;font-size:12px;';
            var toInput = document.createElement('input');
            toInput.type = 'date';
            toInput.value = range.to || '';
            toInput.onchange = function () { range.to = this.value; caFilter(); };
            input.appendChild(fromInput);
            input.appendChild(toLabel);
            input.appendChild(toInput);
        } else if (f.type === 'numrange') {
            if (!caColumnFilters[key] || typeof caColumnFilters[key] !== 'object') caColumnFilters[key] = { from: '', to: '' };
            var numRange = caColumnFilters[key];
            input = document.createElement('span');
            input.style.cssText = 'display:flex;align-items:center;gap:6px;';
            var numFrom = document.createElement('input');
            numFrom.type = 'number'; numFrom.step = 'any'; numFrom.placeholder = 'Min'; numFrom.style.width = '90px';
            numFrom.value = numRange.from || '';
            numFrom.oninput = numFrom.onchange = function () { numRange.from = this.value; caFilter(); };
            var numToLabel = document.createElement('span');
            numToLabel.textContent = 'to';
            numToLabel.style.cssText = 'color:#8a9bad;font-size:12px;';
            var numTo = document.createElement('input');
            numTo.type = 'number'; numTo.step = 'any'; numTo.placeholder = 'Max'; numTo.style.width = '90px';
            numTo.value = numRange.to || '';
            numTo.oninput = numTo.onchange = function () { numRange.to = this.value; caFilter(); };
            input.appendChild(numFrom);
            input.appendChild(numToLabel);
            input.appendChild(numTo);
        } else if (f.type === 'select') {
            input = document.createElement('select');
            var optAll = document.createElement('option');
            optAll.value = ''; optAll.textContent = 'All';
            input.appendChild(optAll);
            f.options.forEach(function (o) {
                var opt = document.createElement('option');
                opt.value = o; opt.textContent = o;
                if (caColumnFilters[key] === o) opt.selected = true;
                input.appendChild(opt);
            });
            input.onchange = function () { caColumnFilters[key] = this.value; caFilter(); };
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.placeholder = 'Search ' + f.label.toLowerCase() + '...';
            input.value = caColumnFilters[key];
            input.oninput = function () { caColumnFilters[key] = this.value; caFilter(); };
        }
        chip.appendChild(input);

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'cfm-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function () { caRemoveColumnFilter(key); };
        chip.appendChild(removeBtn);

        row.appendChild(chip);
    });
}

function caMatchesColumnFilters(row) {
    for (var key in caColumnFilters) {
        var f = caFieldConfig(key);
        if (!f) continue;

        if (f.type === 'daterange') {
            var range = caColumnFilters[key];
            if (!range || (!range.from && !range.to)) continue;
            var rowDate = (row.getAttribute(f.dataAttr) || '').toString();
            if (!rowDate) return false;
            if (range.from && rowDate < range.from) return false;
            if (range.to && rowDate > range.to) return false;
            continue;
        }

        if (f.type === 'numrange') {
            var numRangeVal = caColumnFilters[key];
            if (!numRangeVal || (numRangeVal.from === '' && numRangeVal.to === '')) continue;
            var rawVal = (row.getAttribute(f.dataAttr) || '').toString().replace(/[^0-9.\-]/g, '');
            var rowNum = rawVal === '' ? NaN : parseFloat(rawVal);
            if (isNaN(rowNum)) return false;
            if (numRangeVal.from !== '' && rowNum < parseFloat(numRangeVal.from)) return false;
            if (numRangeVal.to !== '' && rowNum > parseFloat(numRangeVal.to)) return false;
            continue;
        }

        var filterVal = (caColumnFilters[key] || '').toString().trim().toLowerCase();
        if (!filterVal) continue;
        var rowVal = (row.getAttribute(f.dataAttr) || '').toString().toLowerCase();

        if (f.type === 'select') {
            if (rowVal !== filterVal) return false;
        } else {
            if (!rowVal.includes(filterVal)) return false;
        }
    }
    return true;
}

function caFilter() {
    var rows = document.querySelectorAll('#caTable tbody tr[data-amount]');
    var visibleCount = 0;
    var searchEl = document.getElementById('caGlobalSearch');
    var searchQ = searchEl ? searchEl.value.trim().toLowerCase() : '';

    rows.forEach(function (row) {
        var show = caMatchesColumnFilters(row) && (!searchQ || row.textContent.toLowerCase().includes(searchQ));
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    var hasFilter = Object.keys(caColumnFilters).length > 0 || !!searchQ;
    var noMatchRow = document.getElementById('caNoMatchRow');
    if (noMatchRow) {
        noMatchRow.style.display = (hasFilter && rows.length > 0 && visibleCount === 0) ? '' : 'none';
    }

    var countEl = document.getElementById('caRecordsCount');
    if (countEl) {
        countEl.textContent = hasFilter ? (visibleCount + ' of ' + rows.length + ' shown') : ({{ $totalRecords }} + ' total');
    }

    // Re-sync the select-all / bulk bar since filtering can hide selected rows.
    caUpdateBulkBar();
}

document.addEventListener('click', function (e) {
    var dropdown = document.getElementById('caColumnFilterDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('caColumnFilterMenu').style.display = 'none';
    }
});

// ==== Column filter dropdown + chips: Repayment Records table ====
var CA_REPAY_FILTERABLE_FIELDS = [
    { key: 'control',    label: 'Cash Advance No.', dataAttr: 'data-control',    type: 'text' },
    { key: 'employee',   label: 'Employee',         dataAttr: 'data-employee',   type: 'text' },
    { key: 'term',       label: 'Repayment Term',   dataAttr: 'data-term',       type: 'text' },
    { key: 'amount',     label: 'Amount',           dataAttr: 'data-amount',     type: 'numrange' },
    { key: 'status',     label: 'Status',           dataAttr: 'data-status',     type: 'select', options: ['Paid', 'Partial'] },
    { key: 'date-paid',  label: 'Date Paid',        dataAttr: 'data-date-paid',  type: 'daterange' },
];

var caRepayColumnFilters = {};

function caRepayFieldConfig(key) {
    return CA_REPAY_FILTERABLE_FIELDS.find(function (f) { return f.key === key; });
}

function toggleCaRepayColumnFilterMenu(e) {
    e.stopPropagation();
    var menu = document.getElementById('caRepayColumnFilterMenu');
    if (menu.style.display === 'block') { menu.style.display = 'none'; return; }
    renderCaRepayColumnFilterMenu();
    menu.style.display = 'block';
}

function renderCaRepayColumnFilterMenu() {
    var menu = document.getElementById('caRepayColumnFilterMenu');
    menu.innerHTML = '';
    CA_REPAY_FILTERABLE_FIELDS.forEach(function (f) {
        var item = document.createElement('div');
        item.className = 'column-filter-menu-item' + (caRepayColumnFilters.hasOwnProperty(f.key) ? ' is-active' : '');
        item.innerHTML = '<span class="cfm-check">✓</span><span>' + f.label + '</span>';
        item.onclick = function (ev) { ev.stopPropagation(); caRepayToggleColumnFilter(f.key); };
        menu.appendChild(item);
    });
}

function caRepayToggleColumnFilter(key) {
    if (caRepayColumnFilters.hasOwnProperty(key)) {
        delete caRepayColumnFilters[key];
    } else {
        var f = caRepayFieldConfig(key);
        caRepayColumnFilters[key] = (f && (f.type === 'daterange' || f.type === 'numrange')) ? { from: '', to: '' } : '';
    }
    renderCaRepayColumnFilterMenu();
    renderCaRepayActiveColumnFilters();
    updateCaRepayFilterBadge();
    caRepayFilter();
    document.getElementById('caRepayColumnFilterMenu').style.display = 'none';
}

function caRepayRemoveColumnFilter(key) {
    delete caRepayColumnFilters[key];
    renderCaRepayActiveColumnFilters();
    updateCaRepayFilterBadge();
    caRepayFilter();
}

function updateCaRepayFilterBadge() {
    var badge = document.getElementById('caRepayFilterCountBadge');
    var count = Object.keys(caRepayColumnFilters).length;
    badge.style.display = count > 0 ? 'inline-flex' : 'none';
    badge.textContent = count;
}

function caRepayClearAllColumnFilters() {
    caRepayColumnFilters = {};
    renderCaRepayColumnFilterMenu();
    renderCaRepayActiveColumnFilters();
    updateCaRepayFilterBadge();
    caRepayFilter();
}

function renderCaRepayActiveColumnFilters() {
    var row = document.getElementById('caRepayActiveColumnFiltersRow');
    var keys = Object.keys(caRepayColumnFilters);
    row.innerHTML = '';
    if (keys.length === 0) { row.style.display = 'none'; return; }
    row.style.display = 'flex';

    keys.forEach(function (key) {
        var f = caRepayFieldConfig(key);
        if (!f) return;
        var chip = document.createElement('div');
        chip.className = 'column-filter-chip';
        var label = document.createElement('label');
        label.textContent = f.label;
        chip.appendChild(label);

        var input;
        if (f.type === 'daterange') {
            if (!caRepayColumnFilters[key] || typeof caRepayColumnFilters[key] !== 'object') caRepayColumnFilters[key] = { from: '', to: '' };
            var range = caRepayColumnFilters[key];
            input = document.createElement('span');
            input.style.cssText = 'display:flex;align-items:center;gap:6px;';
            var fromInput = document.createElement('input');
            fromInput.type = 'date';
            fromInput.value = range.from || '';
            fromInput.onchange = function () { range.from = this.value; caRepayFilter(); };
            var toLabel = document.createElement('span');
            toLabel.textContent = 'to';
            toLabel.style.cssText = 'color:#8a9bad;font-size:12px;';
            var toInput = document.createElement('input');
            toInput.type = 'date';
            toInput.value = range.to || '';
            toInput.onchange = function () { range.to = this.value; caRepayFilter(); };
            input.appendChild(fromInput);
            input.appendChild(toLabel);
            input.appendChild(toInput);
        } else if (f.type === 'numrange') {
            if (!caRepayColumnFilters[key] || typeof caRepayColumnFilters[key] !== 'object') caRepayColumnFilters[key] = { from: '', to: '' };
            var numRange = caRepayColumnFilters[key];
            input = document.createElement('span');
            input.style.cssText = 'display:flex;align-items:center;gap:6px;';
            var numFrom = document.createElement('input');
            numFrom.type = 'number'; numFrom.step = 'any'; numFrom.placeholder = 'Min'; numFrom.style.width = '90px';
            numFrom.value = numRange.from || '';
            numFrom.oninput = numFrom.onchange = function () { numRange.from = this.value; caRepayFilter(); };
            var numToLabel = document.createElement('span');
            numToLabel.textContent = 'to';
            numToLabel.style.cssText = 'color:#8a9bad;font-size:12px;';
            var numTo = document.createElement('input');
            numTo.type = 'number'; numTo.step = 'any'; numTo.placeholder = 'Max'; numTo.style.width = '90px';
            numTo.value = numRange.to || '';
            numTo.oninput = numTo.onchange = function () { numRange.to = this.value; caRepayFilter(); };
            input.appendChild(numFrom);
            input.appendChild(numToLabel);
            input.appendChild(numTo);
        } else if (f.type === 'select') {
            input = document.createElement('select');
            var optAll = document.createElement('option');
            optAll.value = ''; optAll.textContent = 'All';
            input.appendChild(optAll);
            f.options.forEach(function (o) {
                var opt = document.createElement('option');
                opt.value = o; opt.textContent = o;
                if (caRepayColumnFilters[key] === o) opt.selected = true;
                input.appendChild(opt);
            });
            input.onchange = function () { caRepayColumnFilters[key] = this.value; caRepayFilter(); };
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.placeholder = 'Search ' + f.label.toLowerCase() + '...';
            input.value = caRepayColumnFilters[key];
            input.oninput = function () { caRepayColumnFilters[key] = this.value; caRepayFilter(); };
        }
        chip.appendChild(input);

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'cfm-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function () { caRepayRemoveColumnFilter(key); };
        chip.appendChild(removeBtn);

        row.appendChild(chip);
    });
}

function caRepayMatchesColumnFilters(row) {
    for (var key in caRepayColumnFilters) {
        var f = caRepayFieldConfig(key);
        if (!f) continue;

        if (f.type === 'daterange') {
            var range = caRepayColumnFilters[key];
            if (!range || (!range.from && !range.to)) continue;
            var rowDate = (row.getAttribute(f.dataAttr) || '').toString();
            if (!rowDate) return false;
            if (range.from && rowDate < range.from) return false;
            if (range.to && rowDate > range.to) return false;
            continue;
        }

        if (f.type === 'numrange') {
            var numRangeVal = caRepayColumnFilters[key];
            if (!numRangeVal || (numRangeVal.from === '' && numRangeVal.to === '')) continue;
            var rawVal = (row.getAttribute(f.dataAttr) || '').toString().replace(/[^0-9.\-]/g, '');
            var rowNum = rawVal === '' ? NaN : parseFloat(rawVal);
            if (isNaN(rowNum)) return false;
            if (numRangeVal.from !== '' && rowNum < parseFloat(numRangeVal.from)) return false;
            if (numRangeVal.to !== '' && rowNum > parseFloat(numRangeVal.to)) return false;
            continue;
        }

        var filterVal = (caRepayColumnFilters[key] || '').toString().trim().toLowerCase();
        if (!filterVal) continue;
        var rowVal = (row.getAttribute(f.dataAttr) || '').toString().toLowerCase();

        if (f.type === 'select') {
            if (rowVal !== filterVal) return false;
        } else {
            if (!rowVal.includes(filterVal)) return false;
        }
    }
    return true;
}

function caRepayFilter() {
    var rows = document.querySelectorAll('#caRepaymentsTable tbody tr[data-id]');
    var visibleCount = 0;
    var searchEl = document.getElementById('caRepayGlobalSearch');
    var searchQ = searchEl ? searchEl.value.trim().toLowerCase() : '';

    rows.forEach(function (row) {
        var show = caRepayMatchesColumnFilters(row) && (!searchQ || row.textContent.toLowerCase().includes(searchQ));
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    var hasFilter = Object.keys(caRepayColumnFilters).length > 0 || !!searchQ;
    var noMatchRow = document.getElementById('caRepayNoMatchRow');
    if (noMatchRow) {
        noMatchRow.style.display = (hasFilter && rows.length > 0 && visibleCount === 0) ? '' : 'none';
    }

    var countEl = document.getElementById('caRepaymentRecordsCount');
    if (countEl) {
        countEl.textContent = hasFilter
            ? (visibleCount + ' of ' + rows.length + ' shown')
            : ({{ $totalRepaymentRecords }} + ' record' + ({{ $totalRepaymentRecords }} == 1 ? '' : 's'));
    }

    // Re-sync the select-all / bulk bar since filtering can hide selected rows.
    caRepayUpdateBulkBar();
}

document.addEventListener('click', function (e) {
    var dropdown = document.getElementById('caRepayColumnFilterDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('caRepayColumnFilterMenu').style.display = 'none';
    }
});

// ---- Employee typable/searchable dropdown ----
function caToggleEmployeeDropdown() {
    var d = document.getElementById('caEmployeeDropdown');
    if (!d) return;
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
}

function caFilterEmployeeDropdown(value) {
    var d = document.getElementById('caEmployeeDropdown');
    if (!d) return;
    var filter = (value || '').toLowerCase();
    var has = false;
    Array.from(d.children).forEach(function(opt) {
        var match = opt.textContent.toLowerCase().includes(filter);
        opt.style.display = match ? '' : 'none';
        if (match) has = true;
    });
    d.style.display = has ? 'block' : 'none';

    // Typing invalidates whatever was previously picked until the user
    // selects an option again, so validation doesn't silently keep a
    // stale employee_id that no longer matches the visible text.
    var idField = document.getElementById('ca_employee_id');
    if (idField) idField.value = '';
}

function caSelectEmployee(id, label) {
    var searchField = document.getElementById('ca_employee_search');
    var idField = document.getElementById('ca_employee_id');
    if (searchField) {
        searchField.value = label;
        searchField.classList.remove('ca-invalid');
    }
    if (idField) idField.value = id;
    var dropdown = document.getElementById('caEmployeeDropdown');
    if (dropdown) dropdown.style.display = 'none';
    var err = document.getElementById('err_employee_search');
    if (err) err.textContent = '';
}

document.addEventListener('click', function(e) {
    var searchField = document.getElementById('ca_employee_search');
    var dropdown = document.getElementById('caEmployeeDropdown');
    if (!searchField || !dropdown) return;
    if (!searchField.parentElement.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

// ---- Select / Bulk Delete: Cash Advance Records ----
function caToggleSelectAll(source) {
    document.querySelectorAll('#caTable tbody .ca-row-checkbox').forEach(function(cb) {
        var row = cb.closest('tr');
        if (row && row.style.display === 'none') return; // respect amount filter
        cb.checked = source.checked;
    });
    caUpdateBulkBar();
}

function caUpdateBulkBar() {
    var checked = document.querySelectorAll('#caTable tbody .ca-row-checkbox:checked');
    var btn = document.getElementById('caBulkDeleteBtn');
    if (btn) {
        btn.textContent = 'Delete Selected (' + checked.length + ')';
        btn.disabled = checked.length === 0;
    }
    var selectAll = document.getElementById('caSelectAll');
    if (selectAll) {
        var visible = Array.from(document.querySelectorAll('#caTable tbody tr[data-amount]'))
            .filter(function(r) { return r.style.display !== 'none'; })
            .map(function(r) { return r.querySelector('.ca-row-checkbox'); })
            .filter(Boolean);
        selectAll.checked = visible.length > 0 && visible.every(function(cb) { return cb.checked; });
        selectAll.indeterminate = !selectAll.checked && visible.some(function(cb) { return cb.checked; });
    }
}

function caGetSelectedIds() {
    return Array.from(document.querySelectorAll('#caTable tbody .ca-row-checkbox:checked')).map(function(cb) { return cb.value; });
}

function caDeleteSelected() {
    var ids = caGetSelectedIds();
    if (!ids.length) return;
    document.getElementById('caBulkDeleteCount').textContent = ids.length;
    document.getElementById('caBulkDeleteModal').style.display = 'flex';
}

function caCancelBulkDelete() {
    document.getElementById('caBulkDeleteModal').style.display = 'none';
}

function caConfirmBulkDelete() {
    var ids = caGetSelectedIds();
    document.getElementById('caBulkDeleteModal').style.display = 'none';
    if (!ids.length) return;

    var btn = document.getElementById('caBulkDeleteBtn');
    btn.disabled = true;
    btn.textContent = 'Deleting...';

    Promise.all(ids.map(function(id) {
        return fetch('/cash-advance/' + id, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        });
    })).then(function() {
        showToast('Selected records deleted.', 'success', 'Deleted');
        setTimeout(function() { location.reload(); }, 900);
    }).catch(function() {
        showToast('Some records may not have been deleted.', 'error', 'Error');
        setTimeout(function() { location.reload(); }, 900);
    });
}

// ---- Select / Bulk Delete: Repayment Records ----
function caRepayToggleSelectAll(source) {
    document.querySelectorAll('#caRepaymentsTable tbody .ca-repay-row-checkbox').forEach(function(cb) {
        var row = cb.closest('tr');
        if (row && row.style.display === 'none') return; // respect active filters
        cb.checked = source.checked;
    });
    caRepayUpdateBulkBar();
}

function caRepayUpdateBulkBar() {
    var checked = document.querySelectorAll('#caRepaymentsTable tbody .ca-repay-row-checkbox:checked');
    var btn = document.getElementById('caRepayBulkDeleteBtn');
    if (btn) {
        btn.textContent = 'Delete Selected (' + checked.length + ')';
        btn.disabled = checked.length === 0;
    }
    var selectAll = document.getElementById('caRepaySelectAll');
    if (selectAll) {
        var visible = Array.from(document.querySelectorAll('#caRepaymentsTable tbody tr[data-id]'))
            .filter(function(r) { return r.style.display !== 'none'; })
            .map(function(r) { return r.querySelector('.ca-repay-row-checkbox'); })
            .filter(Boolean);
        selectAll.checked = visible.length > 0 && visible.every(function(cb) { return cb.checked; });
        selectAll.indeterminate = !selectAll.checked && visible.some(function(cb) { return cb.checked; });
    }
}

function caRepayGetSelectedIds() {
    return Array.from(document.querySelectorAll('#caRepaymentsTable tbody .ca-repay-row-checkbox:checked')).map(function(cb) { return cb.value; });
}

function caRepayDeleteSelected() {
    var ids = caRepayGetSelectedIds();
    if (!ids.length) return;
    document.getElementById('caRepayBulkDeleteCount').textContent = ids.length;
    document.getElementById('caRepayBulkDeleteModal').style.display = 'flex';
}

function caRepayCancelBulkDelete() {
    document.getElementById('caRepayBulkDeleteModal').style.display = 'none';
}

function caRepayConfirmBulkDelete() {
    var ids = caRepayGetSelectedIds();
    document.getElementById('caRepayBulkDeleteModal').style.display = 'none';
    if (!ids.length) return;

    var btn = document.getElementById('caRepayBulkDeleteBtn');
    btn.disabled = true;
    btn.textContent = 'Deleting...';

    Promise.all(ids.map(function(id) {
        return fetch('/cash-advance-repayments/' + id, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        });
    })).then(function() {
        showToast('Selected repayment records deleted.', 'success', 'Deleted');
        setTimeout(function() { location.reload(); }, 900);
    }).catch(function() {
        showToast('Some records may not have been deleted.', 'error', 'Error');
        setTimeout(function() { location.reload(); }, 900);
    });
}
</script>
@endsection