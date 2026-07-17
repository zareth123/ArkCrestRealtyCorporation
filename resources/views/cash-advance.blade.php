@extends('layouts.dashboard')

@section('content')
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

    <div class="ca-grid">
        <!-- New Request Form -->
        <div class="ca-card ca-form-card">
            <h3 class="ca-card-title">New Request</h3>
            <p class="ca-card-sub">Fill out the form to submit a cash advance.</p>

            <form id="caForm" novalidate>
                @csrf
                <div class="ca-field">
                    <label for="ca_employee_id">Employee Name</label>
                    <select id="ca_employee_id" name="employee_id" required>
                        <option value="" disabled selected>Select employee...</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}@if($emp->position) — {{ $emp->position }}@endif</option>
                        @endforeach
                    </select>
                    <span class="ca-error" id="err_employee_id"></span>
                </div>

                <div class="ca-field">
                    <label for="ca_amount">Amount (₱)</label>
                    <input type="number" id="ca_amount" name="amount" min="1" step="0.01" placeholder="0.00" required>
                    <span class="ca-error" id="err_amount"></span>
                </div>

                <div class="ca-field">
                    <label for="ca_reason">Reason</label>
                    <textarea id="ca_reason" name="reason" rows="3" placeholder="e.g. Medical emergency" required></textarea>
                    <span class="ca-error" id="err_reason"></span>
                </div>

                <div class="ca-field">
                    <label for="ca_repayment_date">Repayment Date</label>
                    <input type="date" id="ca_repayment_date" name="repayment_date" required>
                    <span class="ca-error" id="err_repayment_date"></span>
                </div>

                <button type="submit" class="ca-btn-submit" id="caSubmitBtn">Submit Request</button>
            </form>
        </div>

        <!-- Records -->
        <div class="ca-card ca-records-card">
            <div class="ca-records-header">
                <h3 class="ca-card-title">Records</h3>
                <span class="ca-records-count">{{ $totalRecords }} total</span>
            </div>

            <div class="ca-table-wrap">
                <table class="ca-table" id="caTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Amount</th>
                            <th>Repay By</th>
                            <th>Status</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $r)
                        <tr id="ca-row-{{ $r->id }}">
                            <td class="ca-id">{{ $r->control_number }}</td>
                            <td>
                                <div class="ca-employee-name">{{ $r->employee_name }}</div>
                                <div class="ca-employee-reason">{{ $r->reason }}</div>
                            </td>
                            <td>₱{{ number_format($r->amount, 2) }}</td>
                            <td>{{ optional($r->repayment_date)->format('Y-m-d') }}</td>
                            <td>
                                <span class="ca-badge ca-badge-{{ strtolower($r->status) }}">{{ ucfirst(strtolower($r->status)) }}</span>
                            </td>
                            <td>
                                <div class="ca-actions">
                                    @if($r->status === 'PENDING')
                                    <button type="button" class="ca-btn-approve" onclick="caApprove({{ $r->id }}, '{{ $r->control_number }}')">Approve</button>
                                    <button type="button" class="ca-btn-reject" onclick="caReject({{ $r->id }}, '{{ $r->control_number }}')">Reject</button>
                                    @endif
                                    <button type="button" class="ca-btn-delete" title="Delete record" onclick="caDelete({{ $r->id }}, '{{ $r->control_number }}')">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="caEmptyRow">
                            <td colspan="6" class="ca-empty">No cash advance records yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
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

.ca-grid { display: grid; grid-template-columns: 340px minmax(0, 1fr); gap: 20px; align-items: start; }
@media (max-width: 900px) { .ca-grid { grid-template-columns: minmax(0, 1fr); } }

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

.ca-card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,.06); border: 1px solid #eef1f5; min-width: 0; }
.ca-card-title { font-size: 16px; font-weight: 700; color: #1e2a3a; margin: 0 0 4px; }
.ca-card-sub { font-size: 12.5px; color: #8A9BAD; margin: 0 0 18px; }

.ca-field { margin-bottom: 16px; }
.ca-field label { display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.ca-field select, .ca-field input, .ca-field textarea {
    width: 100%; padding: 10px 12px; border: 1.5px solid #d0d5dd; border-radius: 9px;
    font-size: 13px; font-family: inherit; box-sizing: border-box; color: #1e2a3a; background: #fff;
    transition: border-color .15s;
}
.ca-field select:focus, .ca-field input:focus, .ca-field textarea:focus { outline: none; border-color: #1e4575; }
.ca-field textarea { resize: vertical; min-height: 70px; }
.ca-field input.ca-invalid, .ca-field select.ca-invalid, .ca-field textarea.ca-invalid { border-color: #dc2626; background: #fef2f2; }
.ca-error { display: block; font-size: 11.5px; color: #dc2626; margin-top: 4px; min-height: 14px; }

.ca-btn-submit {
    width: 100%; padding: 12px; background: #1e2a3a; color: #fff; border: none; border-radius: 10px;
    font-size: 14px; font-weight: 600; cursor: pointer; transition: all .15s;
}
.ca-btn-submit:hover { background: #12202f; transform: translateY(-1px); }
.ca-btn-submit:disabled { opacity: .6; cursor: not-allowed; transform: none; }

.ca-records-header { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 14px; }
.ca-records-count { font-size: 12px; color: #8A9BAD; font-weight: 600; }

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

.ca-actions { display: flex; gap: 6px; align-items: center; flex-wrap: nowrap; }
.ca-btn-approve, .ca-btn-reject {
    padding: 6px 12px; border: 1.5px solid; border-radius: 7px; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .3px; cursor: pointer; background: #fff; white-space: nowrap;
    transition: all .15s;
}
.ca-btn-approve { color: #166534; border-color: #bbf7d0; }
.ca-btn-approve:hover { background: #f0fdf4; }
.ca-btn-reject { color: #991b1b; border-color: #fecaca; }
.ca-btn-reject:hover { background: #fef2f2; }
.ca-btn-delete {
    display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px;
    border: none; background: transparent; color: #9ca3af; cursor: pointer; border-radius: 7px; transition: all .15s;
}
.ca-btn-delete svg { width: 15px; height: 15px; }
.ca-btn-delete:hover { background: #fef2f2; color: #dc2626; }
</style>

<script>
(function() {
    const todayStr = new Date().toISOString().split('T')[0];
    const dateInput = document.getElementById('ca_repayment_date');
    dateInput.setAttribute('min', todayStr);

    const form = document.getElementById('caForm');
    const submitBtn = document.getElementById('caSubmitBtn');

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

        const amount = parseFloat(data.amount);
        if (!data.amount || isNaN(amount) || amount <= 0) {
            setError('ca_amount', 'Amount must be a positive number greater than 0.');
            valid = false;
        }

        if (!data.reason || !data.reason.trim()) {
            setError('ca_reason', 'Please enter a reason.');
            valid = false;
        }

        if (!data.repayment_date) {
            setError('ca_repayment_date', 'Please select a repayment date.');
            valid = false;
        } else if (data.repayment_date < todayStr) {
            setError('ca_repayment_date', 'Repayment date cannot be earlier than today.');
            valid = false;
        }

        return valid;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const data = {
            employee_id: document.getElementById('ca_employee_id').value,
            amount: document.getElementById('ca_amount').value,
            reason: document.getElementById('ca_reason').value,
            repayment_date: document.getElementById('ca_repayment_date').value,
        };

        if (!validateForm(data)) {
            showToast('Please fix the highlighted fields.', 'error', 'Validation Failed');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        fetch('{{ route('cash-advance.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(r => r.json().then(json => ({ status: r.status, json })))
        .then(({ status, json }) => {
            if (status === 200 && json.success) {
                showToast(json.message, 'success', 'Request Submitted');
                setTimeout(() => location.reload(), 900);
            } else {
                showToast(json.message || 'Something went wrong. Please try again.', 'error', 'Submission Failed');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Request';
            }
        })
        .catch(() => {
            showToast('Network error. Please try again.', 'error', 'Submission Failed');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Request';
        });
    });
})();

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
</script>
@endsection