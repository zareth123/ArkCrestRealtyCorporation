@extends('layouts.dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/departmental-expenses-enhanced.css') }}?v={{ time() }}">
<div class="aca-container">

    <!-- Welcome Banner -->
    <div class="aca-banner">
        <div class="aca-banner-content">
            <div class="aca-eyebrow">Finance</div>
            <h1 class="aca-title">Agent Cash Advance</h1>
            <p class="aca-subtitle">
                <svg class="aca-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Submit a new agent cash advance request and manage existing records.
            </p>
        </div>
        <div class="aca-decoration">
            <div class="aca-circle aca-circle-1"></div>
            <div class="aca-circle aca-circle-2"></div>
            <div class="aca-circle aca-circle-3"></div>
        </div>
    </div>

    <!-- Stats -->
    <div class="aca-stats-grid">
        <div class="aca-stat-card">
            <div class="aca-stat-icon aca-stat-icon-records">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="aca-stat-label">Total Records</div>
                <div class="aca-stat-value" id="acaStatTotalRecords">{{ $totalRecords }}</div>
            </div>
        </div>
        <div class="aca-stat-card">
            <div class="aca-stat-icon aca-stat-icon-pending">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="aca-stat-label">Pending</div>
                <div class="aca-stat-value" id="acaStatPending">{{ $pendingCount }}</div>
            </div>
        </div>
        <div class="aca-stat-card">
            <div class="aca-stat-icon aca-stat-icon-requested">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="aca-stat-label">Total Requested</div>
                <div class="aca-stat-value" id="acaStatTotalRequested">₱{{ number_format($totalRequested, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- New Request Form (styled like Departmental Expenses → Add New Expense) -->
    <div class="request-form-container">
        <h3 class="form-title">New Request</h3>
        <form id="acaForm" class="request-form" novalidate>
            @csrf

            <!-- Request Information Section -->
            <div class="form-section">
                <h4 class="section-label">Request Information</h4>

                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Agent <span class="required">*</span></label>
                        <div style="position:relative;">
                            <input type="text" id="aca_agent_search" class="form-control" placeholder="Type or select agent..." autocomplete="off" required
                                style="padding-right:36px;box-sizing:border-box;width:100%;"
                                onclick="acaToggleAgentDropdown()" oninput="acaFilterAgentDropdown(this.value)">
                            <input type="hidden" id="aca_agent_id" name="agent_id">
                            <button type="button" onclick="acaToggleAgentDropdown()" style="position:absolute;right:2px;top:2px;bottom:2px;width:32px;background:transparent;border:none;color:#8A9BAD;cursor:pointer;font-size:11px;">▼</button>
                            <div id="acaAgentDropdown" style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;background:#fff;border:1.5px solid #d0d5dd;border-radius:8px;max-height:220px;overflow-y:auto;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,.15);">
                                @foreach($agents as $agent)
                                @php $agentLabel = $agent->name . ($agent->team && $agent->team->team_name ? ' — ' . $agent->team->team_name : ''); @endphp
                                <div class="aca-agent-option" onclick="acaSelectAgent({{ $agent->id }}, '{{ addslashes($agentLabel) }}')"
                                    style="padding:10px 14px;cursor:pointer;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background=''">
                                    {{ $agentLabel }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <span class="aca-error" id="err_agent_search"></span>
                    </div>

                    <div class="form-group">
                        <label>Team <span class="required">*</span></label>
                        <select id="aca_team" name="team" class="form-control" required>
                            <option value="" disabled selected>Select team...</option>
                            @foreach($teams as $team)
                                <option value="{{ $team }}">{{ $team }}</option>
                            @endforeach
                        </select>
                        <span class="aca-error" id="err_team"></span>
                    </div>

                    <div class="form-group">
                        <label>Amount Requested (₱) <span class="required">*</span></label>
                        <input type="number" id="aca_amount" name="amount" class="form-control" min="1" step="0.01" placeholder="0.00" required>
                        <span class="aca-error" id="err_amount"></span>
                    </div>
                </div>

                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Date Requested <span class="required">*</span></label>
                        <input type="date" id="aca_date_requested" name="date_requested" class="form-control" required>
                        <span class="aca-error" id="err_date_requested"></span>
                    </div>

                    <div class="form-group">
                        <label>Date Needed <span class="required">*</span></label>
                        <input type="date" id="aca_date_needed" name="date_needed" class="form-control" required>
                        <span class="aca-error" id="err_date_needed"></span>
                    </div>
                </div>

                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Purpose <span class="required">*</span></label>
                        <textarea id="aca_purpose" name="purpose" class="form-control" rows="3" placeholder="e.g. Medical emergency" required></textarea>
                        <span class="aca-error" id="err_purpose"></span>
                    </div>
                </div>
            </div>

            <!-- Repayment Details Section -->
            <div class="form-section">
                <h4 class="section-label">Repayment Details</h4>

                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Repayment Type <span class="required">*</span></label>
                        <select id="aca_repayment_type" name="repayment_type" class="form-control" required onchange="acaToggleRepaymentType()">
                            <option value="INSTALLMENT">Installment</option>
                            <option value="OTHERS">Others</option>
                        </select>
                        <span class="aca-error" id="err_repayment_type"></span>
                    </div>

                    <div class="form-group" id="aca_terms_group">
                        <label>Number of Terms <span class="required">*</span></label>
                        <select id="aca_installment_terms" name="installment_terms" class="form-control">
                            <option value="" disabled selected>Select terms...</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                        </select>
                        <span class="aca-hint">Each term is one salary deduction. Maximum of 6 terms.</span>
                        <span class="aca-error" id="err_installment_terms"></span>
                    </div>

                    <div class="form-group" id="aca_repay_date_group" style="display:none;">
                        <label>Repayment Date <span class="required">*</span></label>
                        <input type="date" id="aca_repayment_date" name="repayment_date" class="form-control">
                        <span class="aca-hint">One-time payment date.</span>
                        <span class="aca-error" id="err_repayment_date"></span>
                    </div>
                </div>
            </div>

            <div class="form-actions-right">
                <button type="submit" class="btn-submit" id="acaSubmitBtn">Create Agent Cash Advance Form</button>
            </div>
        </form>
    </div>

    <!-- Agent Cash Advance Records -->
    <div class="aca-card aca-records-card">
        <div class="aca-records-header">
            <h3 class="aca-card-title">Agent Cash Advance Records</h3>
            <div style="display:flex;align-items:center;gap:10px;">
                <button type="button" class="aca-bulk-delete-btn" id="acaBulkDeleteBtn" disabled onclick="acaDeleteSelected()">Delete Selected (0)</button>
                <span class="aca-records-count" id="acaRecordsCount">{{ $totalRecords }} total</span>
            </div>
        </div>

        <div class="aca-filter-toolbar">
            <div class="column-filter-dropdown" id="acaColumnFilterDropdown">
                <button type="button" class="column-filter-btn" onclick="toggleCaColumnFilterMenu(event)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Filter</span>
                    <span id="acaFilterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div id="acaColumnFilterMenu" class="column-filter-menu" style="display:none;"></div>
            </div>
            <button type="button" class="clear-column-filters-btn" onclick="acaClearAllColumnFilters()">Clear Filters</button>
        </div>
        <div id="acaActiveColumnFiltersRow" class="active-column-filters-row" style="display:none;"></div>

        <div class="aca-table-wrap">
            <table class="aca-table" id="acaTable">
                <thead>
                    <tr>
                        <th class="aca-sticky-col aca-sticky-checkbox">
                            <input type="checkbox" id="acaSelectAll" onchange="acaToggleSelectAll(this)" title="Select all">
                        </th>
                        <th class="aca-sticky-col aca-sticky-id">Agent Cash Advance No.</th>
                        <th>Agent</th>
                        <th>Team</th>
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
                        $termsLabel = $r->repayment_type === 'OTHERS'
                            ? 'One-time — ' . (optional($r->repayment_date)->format('Y-m-d') ?? '—')
                            : ($r->installment_terms ?? '—') . ' term' . (($r->installment_terms ?? 0) == 1 ? '' : 's');
                        $termsEditable = in_array($r->status, ['APPROVED', 'COMPLETED']);
                    @endphp
                    <tr id="aca-row-{{ $r->id }}" data-amount="{{ $r->amount }}"
                        data-control="{{ strtolower($r->control_number ?? '') }}"
                        data-agent="{{ strtolower($r->agent_name ?? '') }}"
                        data-team="{{ strtolower($r->team ?? '') }}"
                        data-date-requested="{{ optional($r->date_requested)->format('Y-m-d') ?? optional($r->created_at)->format('Y-m-d') }}"
                        data-date-needed="{{ optional($r->date_needed)->format('Y-m-d') ?? '' }}"
                        data-repayment-type="{{ strtolower($r->repayment_type ?? '') }}"
                        data-status="{{ strtolower($r->display_status ?? '') }}">
                        <td class="aca-sticky-col aca-sticky-checkbox">
                            <input type="checkbox" class="aca-row-checkbox" value="{{ $r->id }}" onchange="acaUpdateBulkBar()">
                        </td>
                        <td class="aca-id aca-sticky-col aca-sticky-id">{{ $r->control_number }}</td>
                        <td>
                            <div class="aca-agent-name">{{ $r->agent_name }}</div>
                            <div class="aca-agent-reason">{{ $r->purpose ?? $r->reason }}</div>
                        </td>
                        <td>{{ $r->team ?? '—' }}</td>
                        <td>₱{{ number_format($r->amount, 2) }}</td>
                        <td>{{ optional($r->date_requested)->format('Y-m-d') ?? optional($r->created_at)->format('Y-m-d') }}</td>
                        <td>{{ optional($r->date_needed)->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $r->repayment_type === 'OTHERS' ? 'Others' : 'Installment' }}</td>
                        <td>
                            @if($termsEditable)
                                <button type="button" class="aca-btn-terms aca-btn-terms-{{ strtolower($r->display_status) }}"
                                    title="Manage repayment" onclick="acaOpenEdit({{ $r->id }}, '{{ $r->control_number }}')">{{ $termsLabel }}</button>
                            @else
                                <span>{{ $termsLabel }}</span>
                            @endif
                        </td>
                        <td id="aca-stage-{{ $r->id }}">{{ $r->payment_stage_label }}</td>
                        <td id="aca-status-{{ $r->id }}">
                            <span class="aca-badge aca-badge-{{ strtolower($r->display_status) }}">{{ $r->display_status }}</span>
                        </td>
                        <td>{{ optional($r->created_at)->format('Y-m-d') }}</td>
                        <td>
                            <div class="aca-actions">
                                @if($r->status === 'PENDING')
                                <button type="button" class="aca-btn-approve" onclick="acaApprove({{ $r->id }}, '{{ $r->control_number }}')">Approve</button>
                                <button type="button" class="aca-btn-reject" onclick="acaReject({{ $r->id }}, '{{ $r->control_number }}')">Reject</button>
                                @endif
                                <button type="button" class="aca-btn-view" title="View / Print" onclick="acaOpenView({{ $r->id }})">View</button>
                                <button type="button" class="aca-btn-delete" title="Delete record" onclick="acaDelete({{ $r->id }}, '{{ $r->control_number }}')">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="acaEmptyRow">
                        <td colspan="13" class="aca-empty">No agent cash advance records yet.</td>
                    </tr>
                    @endforelse
                    <tr id="acaNoMatchRow" style="display:none;">
                        <td colspan="13" class="aca-empty">No records match the current filters.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @php
        // Each repayment term/installment gets its own row below, so this is a
        // flat count across every agent cash advance's individual repayment records —
        // not the number of agent cash advance requests itself.
        $totalRepaymentRecords = $records->sum(function ($r) { return $r->repayments->count(); });
    @endphp

    <!-- Repayment Records -->
    <div class="aca-card aca-repayment-card">
        <div class="aca-records-header">
            <h3 class="aca-card-title">Repayment Records</h3>
            <div style="display:flex;align-items:center;gap:10px;">
                <button type="button" class="aca-bulk-delete-btn" id="acaRepayBulkDeleteBtn" disabled onclick="acaRepayDeleteSelected()">Delete Selected (0)</button>
                <span class="aca-records-count" id="acaRepaymentRecordsCount">{{ $totalRepaymentRecords }} record{{ $totalRepaymentRecords == 1 ? '' : 's' }}</span>
            </div>
        </div>

        <div class="aca-filter-toolbar">
            <div class="column-filter-dropdown" id="acaRepayColumnFilterDropdown">
                <button type="button" class="column-filter-btn" onclick="toggleCaRepayColumnFilterMenu(event)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Filter</span>
                    <span id="acaRepayFilterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div id="acaRepayColumnFilterMenu" class="column-filter-menu" style="display:none;"></div>
            </div>
            <button type="button" class="clear-column-filters-btn" onclick="acaRepayClearAllColumnFilters()">Clear Filters</button>
        </div>
        <div id="acaRepayActiveColumnFiltersRow" class="active-column-filters-row" style="display:none;"></div>

        <div class="aca-table-wrap">
            <table class="aca-table" id="acaRepaymentsTable">
                <thead>
                    <tr>
                        <th class="aca-sticky-col aca-sticky-checkbox">
                            <input type="checkbox" id="acaRepaySelectAll" onchange="acaRepayToggleSelectAll(this)" title="Select all">
                        </th>
                        <th class="aca-sticky-col aca-sticky-id">Agent Cash Advance No.</th>
                        <th>Agent</th>
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
                            $repAmount = $r->repayment_type === 'OTHERS' ? $r->amount : ($r->amount_per_term ?? 0);
                            $repTermLabel = $r->repayment_type === 'OTHERS' ? 'one-time payment' : ('term ' . $rep->term_number);
                            $repStatusLabel = $rep->status === 'PAID' ? 'paid' : 'partial';
                        @endphp
                        <tr data-id="{{ $rep->id }}"
                            data-amount="{{ $repAmount }}"
                            data-control="{{ strtolower($r->control_number ?? '') }}"
                            data-agent="{{ strtolower($r->agent_name ?? '') }}"
                            data-term="{{ $repTermLabel }}"
                            data-status="{{ $repStatusLabel }}"
                            data-date-paid="{{ optional($rep->date_paid)->format('Y-m-d') ?? '' }}">
                            <td class="aca-sticky-col aca-sticky-checkbox">
                                <input type="checkbox" class="aca-repay-row-checkbox" value="{{ $rep->id }}" onchange="acaRepayUpdateBulkBar()">
                            </td>
                            <td class="aca-id aca-sticky-col aca-sticky-id">{{ $r->control_number }}</td>
                            <td>{{ $r->agent_name }}</td>
                            <td>
                                @if($r->repayment_type === 'OTHERS')
                                    One-time Payment
                                @else
                                    Term {{ $rep->term_number }}
                                @endif
                            </td>
                            <td>₱{{ number_format($repAmount, 2) }}</td>
                            <td>{{ $rep->term_number }}/{{ $r->total_terms }}</td>
                            <td>
                                <span class="aca-badge aca-badge-{{ $rep->status === 'PAID' ? 'completed' : 'active' }}">{{ $rep->status === 'PAID' ? 'Paid' : 'Partial' }}</span>
                            </td>
                            <td>{{ optional($rep->date_paid)->format('Y-m-d') ?? '—' }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                    @if($totalRepaymentRecords === 0)
                    <tr id="acaRepaymentsEmptyRow">
                        <td colspan="8" class="aca-empty">No repayment records yet.</td>
                    </tr>
                    @endif
                    <tr id="acaRepayNoMatchRow" style="display:none;">
                        <td colspan="8" class="aca-empty">No repayment records match the current filters.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Printable Form Preview Modal -->
<div id="acaPreviewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this) acaClosePreview()">
    <div style="background:#fff;border-radius:14px;width:95vw;max-width:820px;max-height:90vh;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;display:flex;flex-direction:column;">
        <div style="padding:16px 22px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);flex-shrink:0;">
            <span style="font-size:14px;font-weight:700;color:#fff;">Agent Cash Advance Form — Preview</span>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <button type="button" onclick="acaClosePreview()" style="padding:7px 14px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">Back to Edit</button>
                <button type="button" id="acaConfirmPrintBtn" onclick="acaConfirmAndPrint()" style="padding:7px 16px;background:#A37929;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Confirm &amp; Print</button>
            </div>
        </div>
        <div id="acaPreviewContent" style="padding:30px 36px;font-family:'Times New Roman',serif;font-size:13px;color:#111;flex:1;overflow-y:auto;"></div>
    </div>
</div>

<!-- View (read-only, printable) Modal -->
<div id="acaViewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this) acaCloseView()">
    <div style="background:#fff;border-radius:14px;width:95vw;max-width:820px;max-height:90vh;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;display:flex;flex-direction:column;">
        <div style="padding:16px 22px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);flex-shrink:0;">
            <span style="font-size:14px;font-weight:700;color:#fff;">Agent Cash Advance Form — View</span>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <button type="button" onclick="acaCloseView()" style="padding:7px 14px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">Close</button>
                <button type="button" onclick="acaPrintView()" style="padding:7px 16px;background:#A37929;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Print</button>
            </div>
        </div>
        <div id="acaViewContent" style="padding:30px 36px;font-family:'Times New Roman',serif;font-size:13px;color:#111;flex:1;overflow-y:auto;"></div>
    </div>
</div>

<!-- Edit (repayment tracking) Modal -->
<div id="acaEditModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this) acaCloseEdit()">
    <div style="background:#fff;border-radius:14px;width:95vw;max-width:600px;max-height:90vh;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;display:flex;flex-direction:column;">
        <div style="padding:16px 22px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);flex-shrink:0;">
            <span id="acaEditTitle" style="font-size:14px;font-weight:700;color:#fff;">Repayment Tracking</span>
            <button type="button" onclick="acaCloseEdit()" style="padding:7px 14px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">Close</button>
        </div>
        <div id="acaEditContent" style="padding:22px 26px;font-size:13px;color:#111;flex:1;overflow-y:auto;"></div>
    </div>
</div>

<!-- Bulk Delete Confirm Modal: Agent Cash Advance Records -->
<div id="acaBulkDeleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this) acaCancelBulkDelete()">
    <div style="background:white;border-radius:16px;max-width:420px;width:90%;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.2);">
        <div style="background:linear-gradient(135deg,#dc2626,#ef4444);padding:18px 22px;display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div style="flex:1;">
                <div style="color:rgba(255,255,255,.75);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;">Confirm Deletion</div>
                <div style="color:white;font-size:15px;font-weight:700;margin-top:1px;">Delete Selected Agent Cash Advance Records</div>
            </div>
        </div>
        <div style="padding:20px 22px;">
            <p style="font-size:14px;color:#374151;margin:0 0 4px;">Delete <strong id="acaBulkDeleteCount">0</strong> selected record(s)?</p>
            <p style="font-size:12px;color:#94a3b8;margin:0 0 18px;">This action cannot be undone.</p>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button onclick="acaCancelBulkDelete()" style="padding:9px 18px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;">No, Cancel</button>
                <button onclick="acaConfirmBulkDelete()" style="padding:9px 20px;background:#dc2626;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Confirm Modal: Repayment Records -->
<div id="acaRepayBulkDeleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this) acaRepayCancelBulkDelete()">
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
            <p style="font-size:14px;color:#374151;margin:0 0 4px;">Delete <strong id="acaRepayBulkDeleteCount">0</strong> selected record(s)?</p>
            <p style="font-size:12px;color:#94a3b8;margin:0 0 18px;">This action cannot be undone.</p>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button onclick="acaRepayCancelBulkDelete()" style="padding:9px 18px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;">No, Cancel</button>
                <button onclick="acaRepayConfirmBulkDelete()" style="padding:9px 20px;background:#dc2626;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<style>


.aca-banner {
    background: linear-gradient(135deg, #1e4575 0%, #2563eb 60%, #1e4575 100%);
    border-radius: 20px;
    padding: 32px 36px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(30,69,117,.25);
}
.aca-banner-content { position: relative; z-index: 2; }
.aca-eyebrow { font-size: 11px; font-weight: 700; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px; }
.aca-title { font-size: 26px; font-weight: 700; color: #fff; margin: 0 0 8px; }
.aca-subtitle { font-size: 13.5px; color: rgba(255,255,255,.8); margin: 0; display: flex; align-items: center; gap: 8px; }
.aca-icon-sm { width: 15px; height: 15px; flex-shrink: 0; }
.aca-decoration { position: absolute; top: 0; right: 0; width: 300px; height: 100%; pointer-events: none; }
.aca-circle { position: absolute; border-radius: 50%; background: rgba(163,121,41,0.18); }
.aca-circle-1 { width: 200px; height: 200px; top: -50px; right: -50px; }
.aca-circle-2 { width: 140px; height: 140px; top: 50px; right: 110px; }
.aca-circle-3 { width: 90px; height: 90px; bottom: -25px; right: 60px; }

.aca-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-bottom: 24px; }
.aca-stat-card { background: #fff; border-radius: 14px; padding: 18px 20px; box-shadow: 0 2px 10px rgba(0,0,0,.06); border: 1px solid #eef1f5; display: flex; align-items: center; gap: 14px; }
.aca-stat-icon { width: 42px; height: 42px; border-radius: 11px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.aca-stat-icon svg { width: 22px; height: 22px; }
.aca-stat-icon-records { background: #eef2ff; color: #4338ca; }
.aca-stat-icon-pending { background: #fff7ed; color: #c2410c; }
.aca-stat-icon-requested { background: #ecfdf5; color: #059669; }
.aca-stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #8A9BAD; margin-bottom: 4px; }
.aca-stat-value { font-size: 24px; font-weight: 700; color: #1e2a3a; }

/* Mobile responsiveness for stat cards, matching the 768px breakpoint
   convention used elsewhere on the site (e.g. departmental-expenses). The
   3-column grid is too cramped once icons were added, so stack to one
   column and let the icon/label/value row breathe on narrow screens. */
@media (max-width: 768px) {
    .aca-stats-grid {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
    }
    .aca-stat-card {
        padding: 14px 16px !important;
    }
    .aca-stat-icon {
        width: 38px !important;
        height: 38px !important;
    }
    .aca-stat-icon svg {
        width: 20px !important;
        height: 20px !important;
    }
    .aca-stat-value {
        font-size: 20px !important;
    }
}

.aca-card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,.06); border: 1px solid #eef1f5; min-width: 0; margin-top: 24px; }
.aca-card-title { font-size: 16px; font-weight: 700; color: #1e2a3a; margin: 0 0 4px; }
.aca-card-sub { font-size: 12.5px; color: #8A9BAD; margin: 0 0 18px; }

/* Request form fields reuse the shared .form-control / .form-group styling
   from departmental-expenses-enhanced.css (Add New Expense) — this just
   adds the bits that component doesn't already define: inline error text,
   an invalid state, and hint copy under the Installment/Others fields. */
.aca-hint { display: block; font-size: 11px; color: #8A9BAD; margin-top: 2px; }
.aca-error { display: block; font-size: 11.5px; color: #dc2626; margin-top: 4px; min-height: 14px; }
.form-control.aca-invalid { border-color: #dc2626 !important; background: #fef2f2; }

.aca-records-header { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px; }
.aca-records-count { font-size: 12px; color: #8A9BAD; font-weight: 600; }

/* Bulk delete button used by both tables' header bars */
.aca-bulk-delete-btn {
    padding: 8px 14px; border-radius: 8px; border: none; font-size: 12px; font-weight: 700;
    cursor: pointer; background: #ef4444; color: #fff; transition: opacity .2s;
}
.aca-bulk-delete-btn:disabled { opacity: .45; cursor: not-allowed; }

/* ---- Column filter dropdown + chips (matches Client Database pattern) ---- */
.aca-filter-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 14px; }
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
.aca-table-wrap,
.aca-table-wrap.tbl-scroll,
.aca-table-wrap.auto-scroll-wrap {
    overflow-x: auto !important;
    overflow-y: hidden !important;
    max-height: none !important;
    padding-bottom: 0 !important;
}
.aca-table-wrap::-webkit-scrollbar,
.aca-table-wrap.tbl-scroll::-webkit-scrollbar {
    height: 8px !important;
    width: 0 !important;
}
.aca-table-wrap::-webkit-scrollbar-track,
.aca-table-wrap.tbl-scroll::-webkit-scrollbar-track {
    background: #f1f5f9 !important;
    border-radius: 4px;
}
.aca-table-wrap::-webkit-scrollbar-thumb,
.aca-table-wrap.tbl-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1 !important;
    border-radius: 4px;
}
.aca-table { width: 100%; border-collapse: collapse; }
.aca-table thead th {
    text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    color: #8A9BAD; padding: 8px 10px; border-bottom: 1.5px solid #eef1f5; white-space: nowrap;
}
.aca-table tbody td { padding: 14px 10px; border-bottom: 1px solid #f1f3f6; font-size: 13px; color: #374151; vertical-align: top; }
.aca-table tbody tr:last-child td { border-bottom: none; }
.aca-id { font-weight: 600; color: #1e2a3a; white-space: nowrap; }

/* Sticky "Agent Cash Advance No." column — used by both the Agent Cash Advance Records
   table and the Repayment Records table below it, so the control number
   stays visible while the rest of the row scrolls horizontally. A sticky
   checkbox column sits to its left for row selection. */
.aca-sticky-col {
    position: sticky;
    left: 0;
    z-index: 2;
    background: #fff;
    box-shadow: 2px 0 4px -2px rgba(0,0,0,.15);
}
.aca-table thead th.aca-sticky-col {
    z-index: 3;
    background: #fff;
}
.aca-sticky-checkbox { left: 0; width: 40px; min-width: 40px; max-width: 40px; text-align: center; box-shadow: none; }
.aca-sticky-id { left: 40px; }
.aca-table thead th.aca-sticky-checkbox { z-index: 3; background: #fff; }

.aca-agent-name { font-weight: 600; color: #1e2a3a; }
.aca-agent-reason { font-size: 11.5px; color: #8A9BAD; margin-top: 2px; max-width: 220px; }
.aca-empty { text-align: center; color: #8A9BAD; padding: 30px 0 !important; }

.aca-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; white-space: nowrap; }
.aca-badge-pending { background: #eef2ff; color: #4338ca; }
.aca-badge-approved { background: #dcfce7; color: #166534; }
.aca-badge-rejected { background: #fee2e2; color: #991b1b; }
.aca-badge-active { background: #dbeafe; color: #1d4ed8; }
.aca-badge-completed { background: #dcfce7; color: #166534; }
.aca-badge-overdue { background: #fee2e2; color: #991b1b; }

/* Terms button — styled like the Downpayment status pill in Client Database:
   a rounded pill, colored by status, clickable to open repayment tracking. */
.aca-btn-terms {
    display: inline-block; padding: 5px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600; border: none; cursor: pointer;
    transition: opacity .15s; white-space: nowrap;
}
.aca-btn-terms:hover { opacity: .85; }
.aca-btn-terms-pending   { background: #eef2ff; color: #4338ca; }
.aca-btn-terms-approved  { background: #dcfce7; color: #166534; }
.aca-btn-terms-rejected  { background: #fee2e2; color: #991b1b; }
.aca-btn-terms-active    { background: #dbeafe; color: #1d4ed8; }
.aca-btn-terms-completed { background: #dcfce7; color: #166534; }
.aca-btn-terms-overdue   { background: #fee2e2; color: #991b1b; }

.aca-actions { display: flex; gap: 6px; align-items: center; flex-wrap: nowrap; }
.aca-btn-approve, .aca-btn-reject, .aca-btn-view {
    padding: 6px 12px; border: 1.5px solid; border-radius: 7px; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .3px; cursor: pointer; background: #fff; white-space: nowrap;
    transition: all .15s;
}
.aca-btn-approve { color: #166534; border-color: #bbf7d0; }
.aca-btn-approve:hover { background: #f0fdf4; }
.aca-btn-reject { color: #991b1b; border-color: #fecaca; }
.aca-btn-reject:hover { background: #fef2f2; }
.aca-btn-view { color: #1e4575; border-color: #bfdbfe; }
.aca-btn-view:hover { background: #eff6ff; }
.aca-btn-delete {
    display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px;
    border: none; background: transparent; color: #9ca3af; cursor: pointer; border-radius: 7px; transition: all .15s;
}
.aca-btn-delete svg { width: 15px; height: 15px; }
.aca-btn-delete:hover { background: #fef2f2; color: #dc2626; }

.aca-term-row {
    display: flex; align-items: center; flex-wrap: wrap; gap: 0;
    border: 1.5px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 10px; background: #f8fafc;
}
.aca-term-row:last-child { margin-bottom: 0; }
.aca-term-row.is-paid { border-color: #bbf7d0; background: #f0fdf4; }
.aca-term-label { font-size: 13px; font-weight: 700; color: #1e4575; padding: 10px 14px; white-space: nowrap; border-right: 1.5px solid #e2e8f0; }
.aca-term-amount { flex: 1 1 auto; padding: 10px 12px; font-size: 13px; color: #374151; }
.aca-term-date-input { padding: 8px 10px; border: none; border-left: 1.5px solid #e2e8f0; outline: none; font-size: 12px; background: transparent; color: #374151; }
.aca-btn-mark-paid {
    padding: 10px 16px; border: none; font-size: 12px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .3px; cursor: pointer; white-space: nowrap;
    background: linear-gradient(135deg,#A37929,#d4a03a); color: #fff;
}
.aca-btn-mark-paid:hover { opacity: .92; }
.aca-btn-mark-paid:disabled { opacity: .5; cursor: not-allowed; }
.aca-term-badge-paid {
    padding: 10px 14px; background: #dcfce7; color: #166534; font-size: 12px; font-weight: 700;
    white-space: nowrap; border-left: 1.5px solid #bbf7d0;
}
.aca-term-badge-paid.is-clickable { cursor: pointer; }
.aca-edit-summary {
    background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px 16px; margin-bottom: 18px;
    display: flex; flex-direction: column; gap: 10px;
}
.aca-edit-summary-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.aca-edit-summary-item label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 2px; }
.aca-edit-summary-item div { font-size: 14px; font-weight: 700; color: #374151; }
.aca-edit-summary-remaining { border-top: 1px dashed #d0d5dd; padding-top: 8px; }
.aca-edit-summary-remaining label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 2px; }
.aca-edit-summary-remaining div { font-size: 16px; font-weight: 700; color: #A37929; }
.aca-edit-summary-stage { border-top: 1px dashed #d0d5dd; padding-top: 8px; }
.aca-edit-summary-stage div { font-size: 15px; font-weight: 800; color: #1e4575; }
</style>

<script>
var _acaPendingData = null; // holds the validated request data between "Create Agent Cash Advance Form" and "Confirm & Print"

(function() {
  try {
    const todayStr = new Date().toISOString().split('T')[0];
    const form = document.getElementById('acaForm');
    const dateRequestedInput = document.getElementById('aca_date_requested');
    const dateNeededInput = document.getElementById('aca_date_needed');
    const repaymentDateInput = document.getElementById('aca_repayment_date');

    if (!form || !dateRequestedInput || !dateNeededInput || !repaymentDateInput) {
        console.error('[agent-cash-advance] init aborted: expected form elements not found on page', {
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
        form.querySelectorAll('.aca-error').forEach(el => el.textContent = '');
        form.querySelectorAll('.aca-invalid').forEach(el => el.classList.remove('aca-invalid'));
    }

    function setError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const err = document.getElementById('err_' + fieldId.replace('aca_', ''));
        if (field) field.classList.add('aca-invalid');
        if (err) err.textContent = message;
    }

    function validateForm(data) {
        let valid = true;

        if (!data.agent_id) {
            setError('aca_agent_search', 'Please select an agent.');
            valid = false;
        }

        if (!data.team) {
            setError('aca_team', 'Please select a team.');
            valid = false;
        }

        const amount = parseFloat(data.amount);
        if (!data.amount || isNaN(amount) || amount <= 0) {
            setError('aca_amount', 'Amount must be a positive number greater than 0.');
            valid = false;
        }

        if (!data.purpose || !data.purpose.trim()) {
            setError('aca_purpose', 'Please enter a purpose.');
            valid = false;
        }

        if (!data.date_requested) {
            setError('aca_date_requested', 'Please select the date requested.');
            valid = false;
        }

        if (!data.date_needed) {
            setError('aca_date_needed', 'Please select the date needed.');
            valid = false;
        } else if (data.date_requested && data.date_needed < data.date_requested) {
            setError('aca_date_needed', 'Date needed cannot be earlier than the date requested.');
            valid = false;
        }

        if (data.repayment_type === 'INSTALLMENT') {
            if (!data.installment_terms) {
                setError('aca_installment_terms', 'Please select the number of terms.');
                valid = false;
            }
        } else if (data.repayment_type === 'OTHERS') {
            if (!data.repayment_date) {
                setError('aca_repayment_date', 'Please select a repayment date.');
                valid = false;
            } else if (data.date_needed && data.repayment_date < data.date_needed) {
                setError('aca_repayment_date', 'Repayment date cannot be earlier than the date needed.');
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
        const agentLabel = document.getElementById('aca_agent_search').value || '';
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
            +     '<div style="font-size:16px;font-weight:700;letter-spacing:.5px;">ArkCrest — Agent Cash Advance Request Form</div>'
            +     '<div style="font-size:11px;color:#555;" data-control-number>Control No.: <em>To be assigned upon submission</em></div>'
            +   '</div>'
            + '</div>'
            + '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
            +   '<tr><td style="padding:6px 0;width:190px;color:#555;">Agent</td><td style="padding:6px 0;font-weight:600;">' + escapeHtml(agentLabel) + '</td></tr>'
            +   '<tr><td style="padding:6px 0;color:#555;">Team</td><td style="padding:6px 0;">' + escapeHtml(data.team) + '</td></tr>'
            +   '<tr><td style="padding:6px 0;color:#555;">Amount Requested</td><td style="padding:6px 0;font-weight:700;">₱ ' + money(amount) + '</td></tr>'
            +   '<tr><td style="padding:6px 0;color:#555;">Date Requested</td><td style="padding:6px 0;">' + fmtDate(data.date_requested) + '</td></tr>'
            +   '<tr><td style="padding:6px 0;color:#555;">Date Needed</td><td style="padding:6px 0;">' + fmtDate(data.date_needed) + '</td></tr>'
            +   '<tr><td style="padding:6px 0;vertical-align:top;color:#555;">Purpose</td><td style="padding:6px 0;">' + escapeHtml(data.purpose) + '</td></tr>'
            +   repaymentRows
            + '</table>'
            + '<div style="margin-top:36px;display:grid;grid-template-columns:1fr 1fr;gap:40px;">'
            +   '<div><div style="border-top:1px solid #111;padding-top:6px;font-size:12px;">Agent Signature</div></div>'
            +   '<div><div style="border-top:1px solid #111;padding-top:6px;font-size:12px;">Approved By</div></div>'
            + '</div>';
    }

    function handleCaSubmit(e) {
        e.preventDefault();
        clearErrors();

        const data = {
            agent_id: document.getElementById('aca_agent_id').value,
            team: document.getElementById('aca_team').value,
            amount: document.getElementById('aca_amount').value,
            purpose: document.getElementById('aca_purpose').value,
            date_requested: document.getElementById('aca_date_requested').value,
            date_needed: document.getElementById('aca_date_needed').value,
            repayment_type: document.getElementById('aca_repayment_type').value,
            installment_terms: document.getElementById('aca_installment_terms').value,
            repayment_date: document.getElementById('aca_repayment_date').value,
        };

        if (!validateForm(data)) {
            showToast('Please fix the highlighted fields.', 'error', 'Validation Failed');
            return;
        }

        // Do NOT save yet — hand off to the printable preview. The record
        // is only created once the user confirms from that preview.
        _acaPendingData = data;
        document.getElementById('acaPreviewContent').innerHTML = buildPreviewHtml(data);
        document.getElementById('acaPreviewModal').style.display = 'flex';

        const confirmBtn = document.getElementById('acaConfirmPrintBtn');
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm & Print';
    }

    form.addEventListener('submit', handleCaSubmit);

    // Fallback: if for any reason the native 'submit' event doesn't fire
    // as expected (e.g. a duplicate #acaForm id elsewhere on the page), the
    // button's own click still triggers the same logic and is prevented
    // from bubbling into a real form submission.
    const submitBtn = document.getElementById('acaSubmitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleCaSubmit(e);
        });
    }

    window.acaToggleRepaymentType = function() {
        const type = document.getElementById('aca_repayment_type').value;
        const termsGroup = document.getElementById('aca_terms_group');
        const dateGroup = document.getElementById('aca_repay_date_group');
        const termsInput = document.getElementById('aca_installment_terms');
        const dateInput = document.getElementById('aca_repayment_date');

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
    window.acaToggleRepaymentType();
  } catch (err) {
    console.error('[agent-cash-advance] init failed — form will not submit via AJAX until this is fixed:', err);
  }
})();

function acaClosePreview() {
    document.getElementById('acaPreviewModal').style.display = 'none';
}

function acaConfirmAndPrint() {
    if (!_acaPendingData) return;

    const confirmBtn = document.getElementById('acaConfirmPrintBtn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Submitting...';

    fetch('{{ route('agent-cash-advance.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify(_acaPendingData),
    })
    .then(r => r.json().then(json => ({ status: r.status, json })))
    .then(({ status, json }) => {
        if (status === 200 && json.success) {
            showToast(json.message, 'success', 'Request Submitted');
            _acaPrintPreview(json.data && json.data.control_number);
            _acaPendingData = null;
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
function _acaPrintPreview(controlNumber) {
    const source = document.getElementById('acaPreviewContent');
    let html = source.innerHTML;
    if (controlNumber) {
        html = html.replace('<em>To be assigned upon submission</em>', controlNumber);
    }
    const win = window.open('', '_blank');
    const printHtml = '<html><head><title>Agent Cash Advance Form</title><style>@page{size:letter;margin:.75in}body{font-family:"Times New Roman",serif;font-size:13px;color:#111;margin:0}<' + '/style><' + 'head><body>'
        + html + '</body></html>';
    win.document.write(printHtml);
    win.document.close();
    win.focus();
    setTimeout(function() { win.print(); }, 400);
}

function acaApprove(id, controlNumber) {
    showConfirm('Approve agent cash advance ' + controlNumber + '?', function() {
        fetch('/agent-cash-advance/' + id + '/approve', {
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

function acaReject(id, controlNumber) {
    showConfirm('Reject agent cash advance ' + controlNumber + '? This will remove its amount from Total Requested.', function() {
        fetch('/agent-cash-advance/' + id + '/reject', {
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

function acaDelete(id, controlNumber) {
    showConfirm('Delete agent cash advance ' + controlNumber + '? This cannot be undone.', function() {
        fetch('/agent-cash-advance/' + id, {
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
function acaFmtDate(v) {
    if (!v) return '—';
    const parts = String(v).split('T')[0].split('-');
    if (parts.length !== 3) return v;
    const d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
    if (isNaN(d.getTime())) return v;
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function acaMoney(v) {
    const n = parseFloat(v);
    if (isNaN(n)) return '0.00';
    return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function acaEscapeHtml(s) {
    return (s || '').toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function acaBuildViewHtml(data) {
    const amount = parseFloat(data.amount) || 0;

    let repaymentRows;
    if (data.repayment_type === 'INSTALLMENT') {
        const terms = parseInt(data.installment_terms, 10) || 0;
        const perTerm = data.amount_per_term != null ? parseFloat(data.amount_per_term) : (terms > 0 ? amount / terms : 0);
        repaymentRows =
            '<tr><td style="padding:6px 0;width:190px;color:#555;">Repayment Type</td><td style="padding:6px 0;">Installment</td></tr>' +
            '<tr><td style="padding:6px 0;color:#555;">Number of Terms</td><td style="padding:6px 0;">' + terms + ' salary deduction' + (terms === 1 ? '' : 's') + '</td></tr>' +
            '<tr><td style="padding:6px 0;color:#555;">Amount per Term</td><td style="padding:6px 0;">₱ ' + acaMoney(perTerm) + '</td></tr>' +
            '<tr><td style="padding:6px 0;color:#555;">Payment Stage</td><td style="padding:6px 0;">' + acaEscapeHtml(data.payment_stage_label) + '</td></tr>';
    } else {
        repaymentRows =
            '<tr><td style="padding:6px 0;width:190px;color:#555;">Repayment Type</td><td style="padding:6px 0;">Others — One-time Payment</td></tr>' +
            '<tr><td style="padding:6px 0;color:#555;">Repayment Date</td><td style="padding:6px 0;">' + acaFmtDate(data.repayment_date) + '</td></tr>' +
            '<tr><td style="padding:6px 0;color:#555;">Payment Stage</td><td style="padding:6px 0;">' + acaEscapeHtml(data.payment_stage_label) + '</td></tr>';
    }

    return ''
        + '<div style="display:flex;align-items:center;gap:12px;border-bottom:2px solid #111;padding-bottom:14px;margin-bottom:18px;">'
        +   '<img src="{{ asset('images/ArkCrest_Logo.png') }}" style="width:44px;height:44px;object-fit:contain;">'
        +   '<div>'
        +     '<div style="font-size:16px;font-weight:700;letter-spacing:.5px;">ArkCrest — Agent Cash Advance Request Form</div>'
        +     '<div style="font-size:11px;color:#555;">Control No.: ' + acaEscapeHtml(data.control_number) + '</div>'
        +   '</div>'
        + '</div>'
        + '<table style="width:100%;border-collapse:collapse;font-size:13px;">'
        +   '<tr><td style="padding:6px 0;width:190px;color:#555;">Agent</td><td style="padding:6px 0;font-weight:600;">' + acaEscapeHtml(data.agent_name) + '</td></tr>'
        +   '<tr><td style="padding:6px 0;color:#555;">Team</td><td style="padding:6px 0;">' + acaEscapeHtml(data.team) + '</td></tr>'
        +   '<tr><td style="padding:6px 0;color:#555;">Amount Requested</td><td style="padding:6px 0;font-weight:700;">₱ ' + acaMoney(amount) + '</td></tr>'
        +   '<tr><td style="padding:6px 0;color:#555;">Date Requested</td><td style="padding:6px 0;">' + acaFmtDate(data.date_requested) + '</td></tr>'
        +   '<tr><td style="padding:6px 0;color:#555;">Date Needed</td><td style="padding:6px 0;">' + acaFmtDate(data.date_needed) + '</td></tr>'
        +   '<tr><td style="padding:6px 0;vertical-align:top;color:#555;">Purpose</td><td style="padding:6px 0;">' + acaEscapeHtml(data.purpose) + '</td></tr>'
        +   repaymentRows
        +   '<tr><td style="padding:6px 0;color:#555;">Status</td><td style="padding:6px 0;">' + acaEscapeHtml(data.display_status) + '</td></tr>'
        + '</table>'
        + '<div style="margin-top:36px;display:grid;grid-template-columns:1fr 1fr;gap:40px;">'
        +   '<div><div style="border-top:1px solid #111;padding-top:6px;font-size:12px;">Agent Signature</div></div>'
        +   '<div><div style="border-top:1px solid #111;padding-top:6px;font-size:12px;">Approved By</div></div>'
        + '</div>';
}

function acaOpenView(id) {
    fetch('/agent-cash-advance/' + id, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(json => {
            if (!json.success) {
                showToast('Could not load this record.', 'error', 'Error');
                return;
            }
            document.getElementById('acaViewContent').innerHTML = acaBuildViewHtml(json.data);
            document.getElementById('acaViewModal').style.display = 'flex';
        })
        .catch(() => showToast('Network error. Please try again.', 'error', 'Error'));
}


function acaCloseView() {
    document.getElementById('acaViewModal').style.display = 'none';
}

function acaPrintView() {
    const html = document.getElementById('acaViewContent').innerHTML;
    const win = window.open('', '_blank');
    const printHtml = '<html><head><title>Agent Cash Advance Form</title><style>@page{size:letter;margin:.75in}body{font-family:"Times New Roman",serif;font-size:13px;color:#111;margin:0}<' + '/style><' + 'head><body>'
        + html + '</body></html>';
    win.document.write(printHtml);
    win.document.close();
    win.focus();
    setTimeout(function() { win.print(); }, 400);
}

const ACA_IS_ADMIN = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
var _acaEditHasPaidLockedByAdminRule = false; // reserved if you later gate unmark by an external event (e.g. payroll run)

function acaRenderEditContent(id, data) {
    const totalAmount = parseFloat(data.amount) || 0;
    const paidAmount = data.terms.reduce((sum, t) => sum + (t.status === 'PAID' ? (data.repayment_type === 'OTHERS' ? totalAmount : (parseFloat(data.amount_per_term) || 0)) : 0), 0);
    const remaining = Math.max(0, totalAmount - paidAmount);

    let rowsHtml = '';

    if (data.repayment_type === 'OTHERS') {
        const t = data.terms[0] || {};
        const isPaid = t.status === 'PAID';
        rowsHtml = '<div class="aca-term-row' + (isPaid ? ' is-paid' : '') + '">'
            + '<span class="aca-term-label">Repayment</span>'
            + '<span class="aca-term-amount">₱' + acaMoney(totalAmount) + '</span>'
            + (isPaid
                ? '<span class="aca-term-badge-paid' + (ACA_IS_ADMIN ? ' is-clickable' : '') + '"' + (ACA_IS_ADMIN ? ' onclick="acaUnmarkTermPaid(' + t.id + ')" title="Click to undo"' : '') + '>✓ Paid — ' + acaFmtDate(t.date_paid) + '</span>'
                : '<input type="date" id="aca_term_date_' + t.id + '" class="aca-term-date-input">'
                  + '<button type="button" class="aca-btn-mark-paid" onclick="acaMarkTermPaid(' + t.id + ')">Paid</button>');
        rowsHtml += '</div>';
    } else {
        data.terms.forEach(function(t) {
            const isPaid = t.status === 'PAID';
            rowsHtml += '<div class="aca-term-row' + (isPaid ? ' is-paid' : '') + '">'
                + '<span class="aca-term-label">Term ' + t.term_number + '</span>'
                + '<span class="aca-term-amount">₱' + acaMoney(data.amount_per_term) + '</span>'
                + (isPaid
                    ? '<span class="aca-term-badge-paid' + (ACA_IS_ADMIN ? ' is-clickable' : '') + '"' + (ACA_IS_ADMIN ? ' onclick="acaUnmarkTermPaid(' + t.id + ')" title="Click to undo"' : '') + '>✓ Paid — ' + acaFmtDate(t.date_paid) + '</span>'
                    : '<input type="date" id="aca_term_date_' + t.id + '" class="aca-term-date-input">'
                      + '<button type="button" class="aca-btn-mark-paid" onclick="acaMarkTermPaid(' + t.id + ')">Paid</button>')
                + '</div>';
        });
    }

    document.getElementById('acaEditContent').innerHTML =
        '<div class="aca-edit-summary">'
        +   '<div><label style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Repayment Type</label>'
        +   '<div style="font-size:13px;font-weight:700;color:#1e4575;">' + (data.repayment_type === 'OTHERS' ? 'Others' : 'Installment') + '</div></div>'
        +   '<div class="aca-edit-summary-row">'
        +     '<div class="aca-edit-summary-item"><label>Total Amount</label><div>₱' + acaMoney(totalAmount) + '</div></div>'
        +     '<div class="aca-edit-summary-item"><label>Paid So Far</label><div>₱' + acaMoney(paidAmount) + '</div></div>'
        +   '</div>'
        +   '<div class="aca-edit-summary-remaining"><label>Remaining Balance</label><div>₱' + acaMoney(remaining) + '</div></div>'
        +   '<div class="aca-edit-summary-stage"><label style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:2px">Payment Stage</label><div>' + acaEscapeHtml(data.payment_stage_label) + '</div></div>'
        + '</div>'
        + rowsHtml;
}

function acaOpenEdit(id, controlNumber) {
    _acaEditCashAdvanceId = id;
    document.getElementById('acaEditTitle').textContent = 'Repayment Tracking — ' + controlNumber;
    document.getElementById('acaEditContent').innerHTML = '<div class="aca-empty">Loading...</div>';
    document.getElementById('acaEditModal').style.display = 'flex';

    fetch('/agent-cash-advance/' + id + '/repayments', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(json => {
            if (!json.success) {
                showToast('Could not load repayment terms.', 'error', 'Error');
                return;
            }
            acaRenderEditContent(id, json.data);
        })
        .catch(() => showToast('Network error. Please try again.', 'error', 'Error'));
}
function acaCloseEdit() {
    document.getElementById('acaEditModal').style.display = 'none';
    _acaEditCashAdvanceId = null;
}

function acaMarkTermPaid(termId) {
    const dateInput = document.getElementById('aca_term_date_' + termId);
    const datePaid = dateInput ? dateInput.value : '';

    if (!datePaid) {
        showToast('Please select the date paid.', 'error', 'Validation Failed');
        return;
    }

    fetch('/agent-cash-advance-repayments/' + termId + '/pay', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ date_paid: datePaid }),
    })
    .then(r => r.json().then(json => ({ status: r.status, json })))
    .then(({ status, json }) => {
        if (status === 200 && json.success) {
            showToast(json.message, 'success', 'Saved');

            // The Repayment Records table further down the page is rendered
            // server-side only (unlike the Agent Cash Advance Records row and this
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

function acaUnmarkTermPaid(termId) {
    if (!ACA_IS_ADMIN) return;
    showConfirm('Undo this payment? This will mark the term as unpaid.', function() {
        fetch('/agent-cash-advance-repayments/' + termId + '/unpay', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        })
        .then(r => r.json().then(json => ({ status: r.status, json })))
        .then(({ status, json }) => {
            if (status === 200 && json.success) {
                showToast(json.message || 'Term reverted to pending.', 'success', 'Undone');
                // Same reason as acaMarkTermPaid — reload so the server-rendered
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

// ==== Column filter dropdown + chips: Agent Cash Advance Records table ====
// Field types: 'text' (substring match), 'select' (exact match against a
// fixed option list), 'numrange' (min/max, for Amount), 'daterange' (from/to,
// for the two date columns). Mirrors the pattern used on Client Database.
var ACA_FILTERABLE_FIELDS = [
    { key: 'control',         label: 'Agent Cash Advance No.', dataAttr: 'data-control',         type: 'text' },
    { key: 'agent',        label: 'Agent',         dataAttr: 'data-agent',         type: 'text' },
    { key: 'team',      label: 'Team',       dataAttr: 'data-team',       type: 'select', options: [@foreach($teams as $team)'{{ addslashes($team) }}', @endforeach] },
    { key: 'amount',          label: 'Amount',           dataAttr: 'data-amount',            type: 'numrange' },
    { key: 'date-requested',  label: 'Date Requested',   dataAttr: 'data-date-requested',    type: 'daterange' },
    { key: 'date-needed',     label: 'Date Needed',      dataAttr: 'data-date-needed',       type: 'daterange' },
    { key: 'repayment-type',  label: 'Repayment Type',   dataAttr: 'data-repayment-type',    type: 'select', options: ['Installment', 'Others'] },
    { key: 'status',          label: 'Status',           dataAttr: 'data-status',            type: 'select', options: ['Pending', 'Approved', 'Rejected', 'Active', 'Completed', 'Overdue'] },
];

var acaColumnFilters = {};

function acaFieldConfig(key) {
    return ACA_FILTERABLE_FIELDS.find(function (f) { return f.key === key; });
}

function toggleCaColumnFilterMenu(e) {
    e.stopPropagation();
    var menu = document.getElementById('acaColumnFilterMenu');
    if (menu.style.display === 'block') { menu.style.display = 'none'; return; }
    renderCaColumnFilterMenu();
    menu.style.display = 'block';
}

function renderCaColumnFilterMenu() {
    var menu = document.getElementById('acaColumnFilterMenu');
    menu.innerHTML = '';
    ACA_FILTERABLE_FIELDS.forEach(function (f) {
        var item = document.createElement('div');
        item.className = 'column-filter-menu-item' + (acaColumnFilters.hasOwnProperty(f.key) ? ' is-active' : '');
        item.innerHTML = '<span class="cfm-check">✓</span><span>' + f.label + '</span>';
        item.onclick = function (ev) { ev.stopPropagation(); acaToggleColumnFilter(f.key); };
        menu.appendChild(item);
    });
}

function acaToggleColumnFilter(key) {
    if (acaColumnFilters.hasOwnProperty(key)) {
        delete acaColumnFilters[key];
    } else {
        var f = acaFieldConfig(key);
        acaColumnFilters[key] = (f && (f.type === 'daterange' || f.type === 'numrange')) ? { from: '', to: '' } : '';
    }
    renderCaColumnFilterMenu();
    renderCaActiveColumnFilters();
    updateCaFilterBadge();
    acaFilter();
    document.getElementById('acaColumnFilterMenu').style.display = 'none';
}

function acaRemoveColumnFilter(key) {
    delete acaColumnFilters[key];
    renderCaActiveColumnFilters();
    updateCaFilterBadge();
    acaFilter();
}

function updateCaFilterBadge() {
    var badge = document.getElementById('acaFilterCountBadge');
    var count = Object.keys(acaColumnFilters).length;
    badge.style.display = count > 0 ? 'inline-flex' : 'none';
    badge.textContent = count;
}

function acaClearAllColumnFilters() {
    acaColumnFilters = {};
    renderCaColumnFilterMenu();
    renderCaActiveColumnFilters();
    updateCaFilterBadge();
    acaFilter();
}

function renderCaActiveColumnFilters() {
    var row = document.getElementById('acaActiveColumnFiltersRow');
    var keys = Object.keys(acaColumnFilters);
    row.innerHTML = '';
    if (keys.length === 0) { row.style.display = 'none'; return; }
    row.style.display = 'flex';

    keys.forEach(function (key) {
        var f = acaFieldConfig(key);
        if (!f) return;
        var chip = document.createElement('div');
        chip.className = 'column-filter-chip';
        var label = document.createElement('label');
        label.textContent = f.label;
        chip.appendChild(label);

        var input;
        if (f.type === 'daterange') {
            if (!acaColumnFilters[key] || typeof acaColumnFilters[key] !== 'object') acaColumnFilters[key] = { from: '', to: '' };
            var range = acaColumnFilters[key];
            input = document.createElement('span');
            input.style.cssText = 'display:flex;align-items:center;gap:6px;';
            var fromInput = document.createElement('input');
            fromInput.type = 'date';
            fromInput.value = range.from || '';
            fromInput.onchange = function () { range.from = this.value; acaFilter(); };
            var toLabel = document.createElement('span');
            toLabel.textContent = 'to';
            toLabel.style.cssText = 'color:#8a9bad;font-size:12px;';
            var toInput = document.createElement('input');
            toInput.type = 'date';
            toInput.value = range.to || '';
            toInput.onchange = function () { range.to = this.value; acaFilter(); };
            input.appendChild(fromInput);
            input.appendChild(toLabel);
            input.appendChild(toInput);
        } else if (f.type === 'numrange') {
            if (!acaColumnFilters[key] || typeof acaColumnFilters[key] !== 'object') acaColumnFilters[key] = { from: '', to: '' };
            var numRange = acaColumnFilters[key];
            input = document.createElement('span');
            input.style.cssText = 'display:flex;align-items:center;gap:6px;';
            var numFrom = document.createElement('input');
            numFrom.type = 'number'; numFrom.step = 'any'; numFrom.placeholder = 'Min'; numFrom.style.width = '90px';
            numFrom.value = numRange.from || '';
            numFrom.oninput = numFrom.onchange = function () { numRange.from = this.value; acaFilter(); };
            var numToLabel = document.createElement('span');
            numToLabel.textContent = 'to';
            numToLabel.style.cssText = 'color:#8a9bad;font-size:12px;';
            var numTo = document.createElement('input');
            numTo.type = 'number'; numTo.step = 'any'; numTo.placeholder = 'Max'; numTo.style.width = '90px';
            numTo.value = numRange.to || '';
            numTo.oninput = numTo.onchange = function () { numRange.to = this.value; acaFilter(); };
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
                if (acaColumnFilters[key] === o) opt.selected = true;
                input.appendChild(opt);
            });
            input.onchange = function () { acaColumnFilters[key] = this.value; acaFilter(); };
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.placeholder = 'Search ' + f.label.toLowerCase() + '...';
            input.value = acaColumnFilters[key];
            input.oninput = function () { acaColumnFilters[key] = this.value; acaFilter(); };
        }
        chip.appendChild(input);

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'cfm-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function () { acaRemoveColumnFilter(key); };
        chip.appendChild(removeBtn);

        row.appendChild(chip);
    });
}

function acaMatchesColumnFilters(row) {
    for (var key in acaColumnFilters) {
        var f = acaFieldConfig(key);
        if (!f) continue;

        if (f.type === 'daterange') {
            var range = acaColumnFilters[key];
            if (!range || (!range.from && !range.to)) continue;
            var rowDate = (row.getAttribute(f.dataAttr) || '').toString();
            if (!rowDate) return false;
            if (range.from && rowDate < range.from) return false;
            if (range.to && rowDate > range.to) return false;
            continue;
        }

        if (f.type === 'numrange') {
            var numRangeVal = acaColumnFilters[key];
            if (!numRangeVal || (numRangeVal.from === '' && numRangeVal.to === '')) continue;
            var rawVal = (row.getAttribute(f.dataAttr) || '').toString().replace(/[^0-9.\-]/g, '');
            var rowNum = rawVal === '' ? NaN : parseFloat(rawVal);
            if (isNaN(rowNum)) return false;
            if (numRangeVal.from !== '' && rowNum < parseFloat(numRangeVal.from)) return false;
            if (numRangeVal.to !== '' && rowNum > parseFloat(numRangeVal.to)) return false;
            continue;
        }

        var filterVal = (acaColumnFilters[key] || '').toString().trim().toLowerCase();
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

function acaFilter() {
    var rows = document.querySelectorAll('#acaTable tbody tr[data-amount]');
    var visibleCount = 0;

    rows.forEach(function (row) {
        var show = acaMatchesColumnFilters(row);
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    var hasFilter = Object.keys(acaColumnFilters).length > 0;
    var noMatchRow = document.getElementById('acaNoMatchRow');
    if (noMatchRow) {
        noMatchRow.style.display = (hasFilter && rows.length > 0 && visibleCount === 0) ? '' : 'none';
    }

    var countEl = document.getElementById('acaRecordsCount');
    if (countEl) {
        countEl.textContent = hasFilter ? (visibleCount + ' of ' + rows.length + ' shown') : ({{ $totalRecords }} + ' total');
    }

    // Re-sync the select-all / bulk bar since filtering can hide selected rows.
    acaUpdateBulkBar();
}

document.addEventListener('click', function (e) {
    var dropdown = document.getElementById('acaColumnFilterDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('acaColumnFilterMenu').style.display = 'none';
    }
});

// ==== Column filter dropdown + chips: Repayment Records table ====
var ACA_REPAY_FILTERABLE_FIELDS = [
    { key: 'control',    label: 'Agent Cash Advance No.', dataAttr: 'data-control',    type: 'text' },
    { key: 'agent',   label: 'Agent',         dataAttr: 'data-agent',   type: 'text' },
    { key: 'term',       label: 'Repayment Term',   dataAttr: 'data-term',       type: 'text' },
    { key: 'amount',     label: 'Amount',           dataAttr: 'data-amount',     type: 'numrange' },
    { key: 'status',     label: 'Status',           dataAttr: 'data-status',     type: 'select', options: ['Paid', 'Partial'] },
    { key: 'date-paid',  label: 'Date Paid',        dataAttr: 'data-date-paid',  type: 'daterange' },
];

var acaRepayColumnFilters = {};

function acaRepayFieldConfig(key) {
    return ACA_REPAY_FILTERABLE_FIELDS.find(function (f) { return f.key === key; });
}

function toggleCaRepayColumnFilterMenu(e) {
    e.stopPropagation();
    var menu = document.getElementById('acaRepayColumnFilterMenu');
    if (menu.style.display === 'block') { menu.style.display = 'none'; return; }
    renderCaRepayColumnFilterMenu();
    menu.style.display = 'block';
}

function renderCaRepayColumnFilterMenu() {
    var menu = document.getElementById('acaRepayColumnFilterMenu');
    menu.innerHTML = '';
    ACA_REPAY_FILTERABLE_FIELDS.forEach(function (f) {
        var item = document.createElement('div');
        item.className = 'column-filter-menu-item' + (acaRepayColumnFilters.hasOwnProperty(f.key) ? ' is-active' : '');
        item.innerHTML = '<span class="cfm-check">✓</span><span>' + f.label + '</span>';
        item.onclick = function (ev) { ev.stopPropagation(); acaRepayToggleColumnFilter(f.key); };
        menu.appendChild(item);
    });
}

function acaRepayToggleColumnFilter(key) {
    if (acaRepayColumnFilters.hasOwnProperty(key)) {
        delete acaRepayColumnFilters[key];
    } else {
        var f = acaRepayFieldConfig(key);
        acaRepayColumnFilters[key] = (f && (f.type === 'daterange' || f.type === 'numrange')) ? { from: '', to: '' } : '';
    }
    renderCaRepayColumnFilterMenu();
    renderCaRepayActiveColumnFilters();
    updateCaRepayFilterBadge();
    acaRepayFilter();
    document.getElementById('acaRepayColumnFilterMenu').style.display = 'none';
}

function acaRepayRemoveColumnFilter(key) {
    delete acaRepayColumnFilters[key];
    renderCaRepayActiveColumnFilters();
    updateCaRepayFilterBadge();
    acaRepayFilter();
}

function updateCaRepayFilterBadge() {
    var badge = document.getElementById('acaRepayFilterCountBadge');
    var count = Object.keys(acaRepayColumnFilters).length;
    badge.style.display = count > 0 ? 'inline-flex' : 'none';
    badge.textContent = count;
}

function acaRepayClearAllColumnFilters() {
    acaRepayColumnFilters = {};
    renderCaRepayColumnFilterMenu();
    renderCaRepayActiveColumnFilters();
    updateCaRepayFilterBadge();
    acaRepayFilter();
}

function renderCaRepayActiveColumnFilters() {
    var row = document.getElementById('acaRepayActiveColumnFiltersRow');
    var keys = Object.keys(acaRepayColumnFilters);
    row.innerHTML = '';
    if (keys.length === 0) { row.style.display = 'none'; return; }
    row.style.display = 'flex';

    keys.forEach(function (key) {
        var f = acaRepayFieldConfig(key);
        if (!f) return;
        var chip = document.createElement('div');
        chip.className = 'column-filter-chip';
        var label = document.createElement('label');
        label.textContent = f.label;
        chip.appendChild(label);

        var input;
        if (f.type === 'daterange') {
            if (!acaRepayColumnFilters[key] || typeof acaRepayColumnFilters[key] !== 'object') acaRepayColumnFilters[key] = { from: '', to: '' };
            var range = acaRepayColumnFilters[key];
            input = document.createElement('span');
            input.style.cssText = 'display:flex;align-items:center;gap:6px;';
            var fromInput = document.createElement('input');
            fromInput.type = 'date';
            fromInput.value = range.from || '';
            fromInput.onchange = function () { range.from = this.value; acaRepayFilter(); };
            var toLabel = document.createElement('span');
            toLabel.textContent = 'to';
            toLabel.style.cssText = 'color:#8a9bad;font-size:12px;';
            var toInput = document.createElement('input');
            toInput.type = 'date';
            toInput.value = range.to || '';
            toInput.onchange = function () { range.to = this.value; acaRepayFilter(); };
            input.appendChild(fromInput);
            input.appendChild(toLabel);
            input.appendChild(toInput);
        } else if (f.type === 'numrange') {
            if (!acaRepayColumnFilters[key] || typeof acaRepayColumnFilters[key] !== 'object') acaRepayColumnFilters[key] = { from: '', to: '' };
            var numRange = acaRepayColumnFilters[key];
            input = document.createElement('span');
            input.style.cssText = 'display:flex;align-items:center;gap:6px;';
            var numFrom = document.createElement('input');
            numFrom.type = 'number'; numFrom.step = 'any'; numFrom.placeholder = 'Min'; numFrom.style.width = '90px';
            numFrom.value = numRange.from || '';
            numFrom.oninput = numFrom.onchange = function () { numRange.from = this.value; acaRepayFilter(); };
            var numToLabel = document.createElement('span');
            numToLabel.textContent = 'to';
            numToLabel.style.cssText = 'color:#8a9bad;font-size:12px;';
            var numTo = document.createElement('input');
            numTo.type = 'number'; numTo.step = 'any'; numTo.placeholder = 'Max'; numTo.style.width = '90px';
            numTo.value = numRange.to || '';
            numTo.oninput = numTo.onchange = function () { numRange.to = this.value; acaRepayFilter(); };
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
                if (acaRepayColumnFilters[key] === o) opt.selected = true;
                input.appendChild(opt);
            });
            input.onchange = function () { acaRepayColumnFilters[key] = this.value; acaRepayFilter(); };
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.placeholder = 'Search ' + f.label.toLowerCase() + '...';
            input.value = acaRepayColumnFilters[key];
            input.oninput = function () { acaRepayColumnFilters[key] = this.value; acaRepayFilter(); };
        }
        chip.appendChild(input);

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'cfm-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = function () { acaRepayRemoveColumnFilter(key); };
        chip.appendChild(removeBtn);

        row.appendChild(chip);
    });
}

function acaRepayMatchesColumnFilters(row) {
    for (var key in acaRepayColumnFilters) {
        var f = acaRepayFieldConfig(key);
        if (!f) continue;

        if (f.type === 'daterange') {
            var range = acaRepayColumnFilters[key];
            if (!range || (!range.from && !range.to)) continue;
            var rowDate = (row.getAttribute(f.dataAttr) || '').toString();
            if (!rowDate) return false;
            if (range.from && rowDate < range.from) return false;
            if (range.to && rowDate > range.to) return false;
            continue;
        }

        if (f.type === 'numrange') {
            var numRangeVal = acaRepayColumnFilters[key];
            if (!numRangeVal || (numRangeVal.from === '' && numRangeVal.to === '')) continue;
            var rawVal = (row.getAttribute(f.dataAttr) || '').toString().replace(/[^0-9.\-]/g, '');
            var rowNum = rawVal === '' ? NaN : parseFloat(rawVal);
            if (isNaN(rowNum)) return false;
            if (numRangeVal.from !== '' && rowNum < parseFloat(numRangeVal.from)) return false;
            if (numRangeVal.to !== '' && rowNum > parseFloat(numRangeVal.to)) return false;
            continue;
        }

        var filterVal = (acaRepayColumnFilters[key] || '').toString().trim().toLowerCase();
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

function acaRepayFilter() {
    var rows = document.querySelectorAll('#acaRepaymentsTable tbody tr[data-id]');
    var visibleCount = 0;

    rows.forEach(function (row) {
        var show = acaRepayMatchesColumnFilters(row);
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    var hasFilter = Object.keys(acaRepayColumnFilters).length > 0;
    var noMatchRow = document.getElementById('acaRepayNoMatchRow');
    if (noMatchRow) {
        noMatchRow.style.display = (hasFilter && rows.length > 0 && visibleCount === 0) ? '' : 'none';
    }

    var countEl = document.getElementById('acaRepaymentRecordsCount');
    if (countEl) {
        countEl.textContent = hasFilter
            ? (visibleCount + ' of ' + rows.length + ' shown')
            : ({{ $totalRepaymentRecords }} + ' record' + ({{ $totalRepaymentRecords }} == 1 ? '' : 's'));
    }

    // Re-sync the select-all / bulk bar since filtering can hide selected rows.
    acaRepayUpdateBulkBar();
}

document.addEventListener('click', function (e) {
    var dropdown = document.getElementById('acaRepayColumnFilterDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('acaRepayColumnFilterMenu').style.display = 'none';
    }
});

// ---- Agent typable/searchable dropdown ----
function acaToggleAgentDropdown() {
    var d = document.getElementById('acaAgentDropdown');
    if (!d) return;
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
}

function acaFilterAgentDropdown(value) {
    var d = document.getElementById('acaAgentDropdown');
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
    // stale agent_id that no longer matches the visible text.
    var idField = document.getElementById('aca_agent_id');
    if (idField) idField.value = '';
}

function acaSelectAgent(id, label) {
    var searchField = document.getElementById('aca_agent_search');
    var idField = document.getElementById('aca_agent_id');
    if (searchField) {
        searchField.value = label;
        searchField.classList.remove('aca-invalid');
    }
    if (idField) idField.value = id;
    var dropdown = document.getElementById('acaAgentDropdown');
    if (dropdown) dropdown.style.display = 'none';
    var err = document.getElementById('err_agent_search');
    if (err) err.textContent = '';
}

document.addEventListener('click', function(e) {
    var searchField = document.getElementById('aca_agent_search');
    var dropdown = document.getElementById('acaAgentDropdown');
    if (!searchField || !dropdown) return;
    if (!searchField.parentElement.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});

// ---- Select / Bulk Delete: Agent Cash Advance Records ----
function acaToggleSelectAll(source) {
    document.querySelectorAll('#acaTable tbody .aca-row-checkbox').forEach(function(cb) {
        var row = cb.closest('tr');
        if (row && row.style.display === 'none') return; // respect amount filter
        cb.checked = source.checked;
    });
    acaUpdateBulkBar();
}

function acaUpdateBulkBar() {
    var checked = document.querySelectorAll('#acaTable tbody .aca-row-checkbox:checked');
    var btn = document.getElementById('acaBulkDeleteBtn');
    if (btn) {
        btn.textContent = 'Delete Selected (' + checked.length + ')';
        btn.disabled = checked.length === 0;
    }
    var selectAll = document.getElementById('acaSelectAll');
    if (selectAll) {
        var visible = Array.from(document.querySelectorAll('#acaTable tbody tr[data-amount]'))
            .filter(function(r) { return r.style.display !== 'none'; })
            .map(function(r) { return r.querySelector('.aca-row-checkbox'); })
            .filter(Boolean);
        selectAll.checked = visible.length > 0 && visible.every(function(cb) { return cb.checked; });
        selectAll.indeterminate = !selectAll.checked && visible.some(function(cb) { return cb.checked; });
    }
}

function acaGetSelectedIds() {
    return Array.from(document.querySelectorAll('#acaTable tbody .aca-row-checkbox:checked')).map(function(cb) { return cb.value; });
}

function acaDeleteSelected() {
    var ids = acaGetSelectedIds();
    if (!ids.length) return;
    document.getElementById('acaBulkDeleteCount').textContent = ids.length;
    document.getElementById('acaBulkDeleteModal').style.display = 'flex';
}

function acaCancelBulkDelete() {
    document.getElementById('acaBulkDeleteModal').style.display = 'none';
}

function acaConfirmBulkDelete() {
    var ids = acaGetSelectedIds();
    document.getElementById('acaBulkDeleteModal').style.display = 'none';
    if (!ids.length) return;

    var btn = document.getElementById('acaBulkDeleteBtn');
    btn.disabled = true;
    btn.textContent = 'Deleting...';

    Promise.all(ids.map(function(id) {
        return fetch('/agent-cash-advance/' + id, {
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
function acaRepayToggleSelectAll(source) {
    document.querySelectorAll('#acaRepaymentsTable tbody .aca-repay-row-checkbox').forEach(function(cb) {
        var row = cb.closest('tr');
        if (row && row.style.display === 'none') return; // respect active filters
        cb.checked = source.checked;
    });
    acaRepayUpdateBulkBar();
}

function acaRepayUpdateBulkBar() {
    var checked = document.querySelectorAll('#acaRepaymentsTable tbody .aca-repay-row-checkbox:checked');
    var btn = document.getElementById('acaRepayBulkDeleteBtn');
    if (btn) {
        btn.textContent = 'Delete Selected (' + checked.length + ')';
        btn.disabled = checked.length === 0;
    }
    var selectAll = document.getElementById('acaRepaySelectAll');
    if (selectAll) {
        var visible = Array.from(document.querySelectorAll('#acaRepaymentsTable tbody tr[data-id]'))
            .filter(function(r) { return r.style.display !== 'none'; })
            .map(function(r) { return r.querySelector('.aca-repay-row-checkbox'); })
            .filter(Boolean);
        selectAll.checked = visible.length > 0 && visible.every(function(cb) { return cb.checked; });
        selectAll.indeterminate = !selectAll.checked && visible.some(function(cb) { return cb.checked; });
    }
}

function acaRepayGetSelectedIds() {
    return Array.from(document.querySelectorAll('#acaRepaymentsTable tbody .aca-repay-row-checkbox:checked')).map(function(cb) { return cb.value; });
}

function acaRepayDeleteSelected() {
    var ids = acaRepayGetSelectedIds();
    if (!ids.length) return;
    document.getElementById('acaRepayBulkDeleteCount').textContent = ids.length;
    document.getElementById('acaRepayBulkDeleteModal').style.display = 'flex';
}

function acaRepayCancelBulkDelete() {
    document.getElementById('acaRepayBulkDeleteModal').style.display = 'none';
}

function acaRepayConfirmBulkDelete() {
    var ids = acaRepayGetSelectedIds();
    document.getElementById('acaRepayBulkDeleteModal').style.display = 'none';
    if (!ids.length) return;

    var btn = document.getElementById('acaRepayBulkDeleteBtn');
    btn.disabled = true;
    btn.textContent = 'Deleting...';

    Promise.all(ids.map(function(id) {
        return fetch('/agent-cash-advance-repayments/' + id, {
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