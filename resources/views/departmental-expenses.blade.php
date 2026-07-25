@extends('layouts.dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/departmental-expenses-enhanced.css') }}?v={{ time() }}">

<div class="commission-requests-page">
    <!-- Page Banner -->
    <div class="page-welcome-banner" style="background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:28px 32px;margin-bottom:24px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25);display:flex;align-items:center;justify-content:space-between;">
        <div style="position:relative;z-index:2;">
            <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:6px;">Finance</div>
            <h1 style="font-size:24px;font-weight:700;color:white;margin:0 0 6px;">Departmental Expenses</h1>
            <p style="font-size:13px;color:rgba(255,255,255,.75);margin:0;">Budget & expense tracking per department</p>
        </div>
        @if(auth()->user()->isAdmin())
        <button onclick="document.getElementById('addDeptModal').style.display='flex'" style="display:flex;align-items:center;gap:6px;padding:10px 18px;background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;backdrop-filter:blur(4px);position:relative;z-index:2;white-space:nowrap;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Department
        </button>
        @endif
        <div style="position:absolute;top:0;right:0;width:300px;height:100%;pointer-events:none;">
            <div style="position:absolute;width:220px;height:220px;top:-60px;right:-40px;border-radius:50%;background:rgba(255,255,255,.06);"></div>
            <div style="position:absolute;width:140px;height:140px;top:40px;right:120px;border-radius:50%;background:rgba(255,255,255,.04);"></div>
        </div>
    </div>

    {{-- Add Department Modal --}}
    <div id="addDeptModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;padding:28px;width:100%;max-width:480px;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h3 style="font-size:16px;font-weight:700;color:#1e4575;margin:0;">Add Department</h3>
                <button onclick="document.getElementById('addDeptModal').style.display='none'" style="background:none;border:none;font-size:20px;cursor:pointer;color:#6b7280;">&times;</button>
            </div>
            <form id="addDeptForm">
                @csrf
                <div style="margin-bottom:14px;">
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Department Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="new_dept_name" required placeholder="e.g. Operations" style="width:100%;padding:9px 12px;border:1.5px solid #d0d5dd;border-radius:8px;font-size:13px;box-sizing:border-box;">
                </div>
                <div style="margin-bottom:14px;">
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">Categories</label>
                    <div id="new_dept_categories" style="display:flex;flex-direction:column;gap:6px;margin-bottom:8px;"></div>
                    <div style="display:flex;gap:8px;">
                        <input type="text" id="new_cat_input" placeholder="Add category..." style="flex:1;padding:8px 12px;border:1.5px solid #d0d5dd;border-radius:8px;font-size:13px;">
                        <button type="button" onclick="addNewDeptCategory()" style="padding:8px 14px;background:#1e4575;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer;">Add</button>
                    </div>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                    <button type="button" onclick="document.getElementById('addDeptModal').style.display='none'" style="padding:8px 16px;background:#f3f4f6;color:#374151;border:none;border-radius:8px;font-size:13px;cursor:pointer;">Cancel</button>
                    <button type="submit" style="padding:8px 16px;background:#1e4575;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Add Department</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.getElementById('addDeptForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('new_dept_name').value.trim();
        if (!name) return;
        const cats = Array.from(document.querySelectorAll('#new_dept_categories .dept-cat-tag')).map(t => t.dataset.cat);
        fetch('/api/departments/add', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({name: name, categories: cats})
        }).then(r => r.json()).then(d => {
            if (d.success) { location.reload(); }
            else { alert(d.message || 'Error adding department'); }
        });
    });

    function addNewDeptCategory() {
        const input = document.getElementById('new_cat_input');
        const val = input.value.trim();
        if (!val) return;
        const container = document.getElementById('new_dept_categories');
        const tag = document.createElement('div');
        tag.className = 'dept-cat-tag';
        tag.dataset.cat = val;
        tag.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:6px 10px;background:#f0f4ff;border-radius:6px;font-size:12px;';
        tag.innerHTML = '<span>' + val + '</span><button type="button" onclick="this.closest(\'.dept-cat-tag\').remove()" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:14px;">&times;</button>';
        container.appendChild(tag);
        input.value = '';
        input.focus();
    }

    document.getElementById('new_cat_input').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); addNewDeptCategory(); }
    });

    function deleteDepartment(id, name) {
        showConfirm('Delete "' + name + '" department? This cannot be undone.', function() {
            fetch('/api/departments/' + id + '/delete', {
                method: 'DELETE',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            }).then(r => r.json()).then(d => {
                if (d.success) { location.reload(); }
                else { showToast('error', 'Error', d.message || 'Error deleting department'); }
            });
        }, 'Delete Department');
    }
    </script>

    <!-- Department Expenses Overview (observation only — budget tracking removed) -->
    @if(!in_array('departments.budget-cards', $hiddenSections))
    <div class="budget-overview-container">
        <h3 class="budget-overview-title">
            <svg class="title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Departments Expenses
        </h3>
        <div class="budget-cards-grid">
            @foreach($departments as $dept)
                @if($dept->slug !== 'capex')
                @php
                    $totalExpenses = $commitments[$dept->name]['liquidated'] ?? 0;
                @endphp
                <div class="budget-card-compact" onclick="selectDepartmentFromCard('{{ $dept->name }}')" style="cursor:pointer;" title="Click to select {{ $dept->name }}">
                    <div class="budget-card-header-compact" style="padding-bottom:8px;border-bottom:1px solid #e5e7eb;margin-bottom:10px;">
                        <h4 style="font-size:13px;font-weight:700;color:#fff;margin:0;white-space:normal;word-break:break-word;">{{ $dept->name }}</h4>
                    </div>
                    <div class="budget-card-body-compact">
                        <div style="display:flex;justify-content:space-between;font-size:12px;">
                            <span style="color:#6b7280;">Expenses</span>
                            <span style="font-weight:600;color:#dc2626;">₱{{ number_format($totalExpenses, 2) }}</span>
                        </div>

                        @php $recent = $recentExpenses[$dept->name] ?? collect(); @endphp
                        @if($recent->isNotEmpty())
                        <div style="margin-top:8px;padding-top:8px;border-top:1px dashed #e5e7eb;">
                            <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
                                Recent Expenses
                            </div>
                            @foreach($recent as $exp)
                            <div style="display:flex;justify-content:space-between;font-size:11px;color:#374151;margin-bottom:2px;">
                                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:60%;" title="{{ $exp->category }}">
                                    {{ $exp->category }}
                                </span>
                                <span style="color:#059669;font-weight:600;">
                                    ₱{{ number_format($exp->total_expenses, 2) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <!-- Add Request Form -->
    @if(!in_array('departments.add-form', $hiddenSections))
    <div class="request-form-container">
        <h3 class="form-title">Add New Expenses</h3>
        <form id="addRequestForm" class="request-form">
            <!-- Request Information Section -->
            <div class="form-section">
                <h4 class="section-label">Request Information</h4>
                
                <!-- Row 1: 3 fields -->
                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Requestor Name <span class="required">*</span></label>
                        <div class="combobox-wrapper">
                            <input type="text" id="requestor_name" name="requestor_name" class="form-control combobox-input" autocomplete="off" placeholder="Type or select requestor" onclick="toggleRequestorDropdown()" oninput="filterRequestors(this.value)">
                            <button type="button" class="combobox-arrow" onclick="toggleRequestorDropdown()">▼</button>
                            <div id="requestorDropdown" class="combobox-dropdown" style="display: none;">
                                @foreach($requestorNames as $name)
                                    <div class="dropdown-item" onclick="selectRequestor('{{ $name }}')">{{ $name }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Department<span class="required">*</span></label>
                        <div class="combobox-wrapper">
                            <input type="text" id="department" name="department" class="form-control combobox-input" required autocomplete="off" placeholder="Type or select department" onclick="toggleDepartmentDropdown()" oninput="filterDepartments(this.value)">
                            <button type="button" class="combobox-arrow" onclick="toggleDepartmentDropdown()">▼</button>
                            <div id="departmentDropdown" class="combobox-dropdown" style="display: none;">
                                @foreach($departments->where('slug', '!=', 'capex') as $dept)
                                <div class="dropdown-item" onclick="selectDepartment('{{ $dept->name }}')">{{ $dept->name }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Category <span class="required">*</span></label>
                        <div class="combobox-wrapper">
                            <input type="text" id="category" name="category" class="form-control combobox-input" required autocomplete="off" placeholder="Type or select category" onclick="toggleCategoryDropdown()" oninput="filterCategories(this.value)">
                            <button type="button" class="combobox-arrow" onclick="toggleCategoryDropdown()">▼</button>
                            <div id="categoryDropdown" class="combobox-dropdown" style="display: none;">
                                <div class="dropdown-item">Select Department First</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: 2 fields -->
                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Date Requested <span class="required">*</span></label>
                        <input type="date" id="date_requested" name="date_requested" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Requested Amount <span class="required">*</span></label>
                        <input type="text" id="requested_amount" name="requested_amount" class="form-control" placeholder="0.00" required inputmode="decimal">
                    </div>
                </div>
            </div>

            <!-- Release & Liquidation Section -->
            <div class="form-section">
                <h4 class="section-label">Release & Liquidation Details</h4>
                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Release Status <span class="required">*</span></label>
                        <select id="release_status" name="release_status" class="form-control" required>
                            <option value="NOT YET RELEASED">NOT YET RELEASED</option>
                            <option value="RELEASED">RELEASED</option>
                            <option value="REJECTED">REJECTED</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Liquidation Status <span class="required">*</span></label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="NOT YET LIQUIDATED">NOT YET LIQUIDATED</option>
                            <option value="LIQUIDATED">LIQUIDATED</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Date Released</label>
                        <input type="date" id="date_released" name="date_released" class="form-control" disabled>
                    </div>

                    <div class="form-group">
                        <label>Total Expenses</label>
                        <input type="text" id="total_expenses" name="total_expenses" class="form-control" placeholder="Complete liquidation form" inputmode="decimal" disabled>
                    </div>

                    <div class="form-group">
                        <label>Amount Returned</label>
                        <input type="text" id="amount_returned" name="amount_returned" class="form-control" placeholder="0.00" inputmode="decimal" readonly disabled style="background-color: #f4f6f8;">
                    </div>

                    <div class="form-group">
                        <label>Date of Amount Returned</label>
                        <input type="date" id="date_of_amount_returned" name="date_of_amount_returned" class="form-control" disabled>
                    </div>
                </div>
                <small style="display:block;color:#6b7280;margin-top:8px;">Release the request first. Selecting LIQUIDATED opens the required liquidation form.</small>
            </div>

            <div class="form-actions-right">
                <button type="submit" class="btn-submit">Add Expenses</button>
            </div>
        </form>
    </div>
    @endif

    <!-- Requests Table -->
    @if(!in_array('departments.table', $hiddenSections))
    <div class="requests-table-container">
        <div class="table-header-section" style="flex-direction: column; align-items: stretch;">
            <!-- Title on top -->
            <div style="margin-bottom: 15px;">
                <h3 class="table-title" style="margin: 0; font-size: 24px;">All Expenses</h3>
            </div>
            
            <!-- Filters and Search below title -->
            <div class="expenses-filters-bar" style="display: flex; flex-direction: column; gap: 14px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                <div class="expenses-filters-row" style="display: flex; justify-content: flex-start; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <button type="button" id="printSelectedBtn" onclick="printSelectedRecords()" style="display:flex;align-items:center;gap:6px;padding:9px 14px;background:#1e4575;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;height:40px;box-sizing:border-box;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print Selected
                    </button>
                    <div class="expenses-search-wrapper" style="display: flex; align-items: center; gap: 10px; width: 100%; max-width: 560px;">
                        <div class="column-filter-dropdown" id="columnFilterDropdown" style="position: relative;">
                            <button type="button" id="columnFilterBtn" class="column-filter-btn" onclick="toggleColumnFilterMenu(event)">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                                <span>Filter</span>
                                <span id="filterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div id="columnFilterMenu" class="column-filter-menu" style="display:none;"></div>
                        </div>
                        <div class="search-box" style="width: 100%;">
                            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" id="tableSearch" class="search-input-table" placeholder="Search requests..." style="width: 100%; max-width: 100%; min-width: 0; box-sizing: border-box;">
                        </div>
                    </div>
                </div>
                <div id="activeColumnFiltersRow" class="active-column-filters-row" style="display:none;"></div>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="requests-table js-sort-table js-sort-dropdown">
                <thead>
                    <tr>
                        <th style="width: 40px; min-width: 40px;">
                            <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th style="min-width: 150px;">Control Number</th>
                        <th style="min-width: 180px;">Requestor Name</th>
                        <th style="min-width: 180px;">Department</th>
                        <th style="min-width: 180px;">Release Status</th>
                        <th style="min-width: 190px;">Liquidation Status</th>
                        <th style="min-width: 200px;">Category</th>
                        <th style="min-width: 150px;">Date Requested</th>
                        <th style="min-width: 150px;">Requested Amount</th>
                        <th style="min-width: 150px;">Date Released</th>
                        <th style="min-width: 150px;">Total Expenses</th>
                        <th style="min-width: 150px;">Amount Returned</th>
                        <th style="min-width: 180px;">Date of Amount Returned</th>
                        <th style="min-width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="requestsTableBody">
                    @foreach($requests as $req)
                    <tr id="expense-{{ $req->id }}" data-id="{{ $req->id }}" data-finalized="0" data-department="{{ $req->department }}" data-date-requested="{{ $req->date_requested ? $req->date_requested->format('Y-m-d') : '' }}" data-date-released="{{ $req->date_released ? $req->date_released->format('Y-m-d') : '' }}" data-control="{{ $req->control_number }}" data-requestor="{{ $req->requestor_name }}" data-category="{{ $req->category }}" data-release-status="{{ $req->release_status }}" data-liquidation-status="{{ $req->status }}" data-status="{{ $req->status }}" data-requested-amount="{{ $req->requested_amount }}" data-total-expenses="{{ $req->total_expenses }}" data-amount-returned="{{ $req->amount_returned }}" data-date-returned="{{ $req->date_of_amount_returned ? $req->date_of_amount_returned->format('Y-m-d') : '' }}" data-date-added="{{ $req->created_at?->timestamp }}" data-date-modified="{{ $req->updated_at?->timestamp }}">
                        <td><input type="checkbox" class="row-select-checkbox" value="{{ $req->id }}"></td>
                        <td>{{ $req->control_number }}</td>
                        <td>{{ $req->requestor_name }}</td>
                        <td class="department-cell">{{ $req->department }}</td>
                        <td class="inline-status-cell">
                            <select
                                id="releaseStatusSelect_{{ $req->id }}"
                                class="inline-status-select inline-release-status"
                                data-current="{{ $req->release_status }}"
                                data-status="{{ $req->release_status }}"
                                aria-label="Release status for {{ $req->control_number }}"
                                title="Click to change release status"
                                onchange="handleInlineReleaseStatusChange(this, {{ $req->id }})">
                                <option value="NOT YET RELEASED" {{ $req->release_status === 'NOT YET RELEASED' ? 'selected' : '' }}>NOT YET RELEASED</option>
                                <option value="RELEASED" {{ $req->release_status === 'RELEASED' ? 'selected' : '' }}>RELEASED</option>
                                <option value="REJECTED" {{ $req->release_status === 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                            </select>
                        </td>
                        <td class="inline-status-cell">
                            <select
                                id="liquidationStatusSelect_{{ $req->id }}"
                                class="inline-status-select inline-liquidation-status"
                                data-current="{{ $req->status }}"
                                data-status="{{ $req->status }}"
                                aria-label="Liquidation status for {{ $req->control_number }}"
                                title="Click to change liquidation status"
                                onchange="handleInlineLiquidationStatusChange(this, {{ $req->id }})">
                                <option value="NOT YET LIQUIDATED" {{ $req->status === 'NOT YET LIQUIDATED' ? 'selected' : '' }}>NOT YET LIQUIDATED</option>
                                <option value="LIQUIDATED" {{ $req->status === 'LIQUIDATED' ? 'selected' : '' }}>LIQUIDATED</option>
                            </select>
                        </td>
                        <td>{{ $req->category }}</td>
                        <td>{{ $req->date_requested ? $req->date_requested->format('m/d/Y') : '-' }}</td>
                        <td>₱ {{ number_format($req->requested_amount, 2) }}</td>
                        <td>{{ $req->date_released ? $req->date_released->format('m/d/Y') : '-' }}</td>
                        <td>{{ $req->total_expenses ? '₱ ' . number_format($req->total_expenses, 2) : '-' }}</td>
                        <td>{{ $req->amount_returned ? '₱ ' . number_format($req->amount_returned, 2) : '-' }}</td>
                        <td>{{ $req->date_of_amount_returned ? $req->date_of_amount_returned->format('m/d/Y') : '-' }}</td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="viewRequest({{ $req->id }})" class="btn-action btn-view">View</button>
                                <a href="{{ route('departmental-expenses.view-form', $req->id) }}" target="_blank" class="btn-action btn-view" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;" title="View & print the original Budget Request Form">Form</a>
                                <button type="button" onclick="editRequest({{ $req->id }})" class="btn-action btn-edit" data-edit-expense>Edit</button>
                                <button onclick="deleteRequest({{ $req->id }})" class="btn-action btn-delete">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- No Results Message -->
            <div id="noResultsMessage" style="display: none; text-align: center; padding: 60px 20px; color: #8a9bad;">
                <svg style="width: 80px; height: 80px; margin: 0 auto 20px; opacity: 0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 style="font-size: 20px; color: #565651; margin-bottom: 8px;">No Results Found</h3>
                <p style="font-size: 14px;">Try adjusting your search or filter criteria</p>
            </div>
        </div>
        <div id="printArea" class="print-only"></div>
    </div>
    @endif
</div>

<style>
/* Mobile responsiveness fix for the "All Expenses" date-range filters and
   search bar. These were plain inline-styled flex rows with no breakpoint,
   so on narrow screens the two date inputs, the "to" labels, the Clear
   Dates button, and the search bar all tried to stay on one line and
   overflowed the viewport instead of wrapping/stacking. */
@media (max-width: 768px) {
    .expenses-filters-row {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .expenses-date-filters {
        flex-direction: column !important;
        align-items: stretch !important;
        width: 100%;
        gap: 14px !important;
    }
    .date-range-group {
        width: 100%;
    }
    .date-range-inputs {
        flex-wrap: wrap !important;
        width: 100%;
    }
    .date-range-inputs input[type="date"] {
        flex: 1 1 120px !important;
        min-width: 0 !important;
        width: auto !important;
    }
    .clear-dates-btn {
        width: 100% !important;
        text-align: center;
    }
    .expenses-search-wrapper {
        max-width: 100% !important;
        width: 100% !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px !important;
    }
    .column-filter-dropdown {
        width: 100% !important;
    }
    .column-filter-btn {
        width: 100% !important;
        justify-content: center !important;
    }
    .column-filter-menu {
        left: 0 !important;
        right: 0 !important;
        min-width: 0 !important;
        width: 100% !important;
        box-sizing: border-box;
    }
    .active-column-filters-row {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .column-filter-chip {
        width: 100% !important;
        flex-wrap: wrap !important;
        box-sizing: border-box;
    }
    .column-filter-chip label {
        flex: 1 1 100%;
    }
    .column-filter-chip input,
    .column-filter-chip select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        width: 100%;
    }
    .clear-column-filters-btn {
        width: 100% !important;
        text-align: center;
    }
    #printSelectedBtn {
        width: 100% !important;
        justify-content: center !important;
    }

}

/* Separate Release Status badge colors */
.status-not-yet-released {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.1));
    color: #92400e;
    border: 2px solid #f59e0b;
}
.status-released {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.18), rgba(37, 99, 235, 0.08));
    color: #1d4ed8;
    border: 2px solid #3b82f6;
}
.release-status-badge.status-rejected {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.18), rgba(239, 68, 68, 0.08));
    color: #b91c1c;
    border: 2px solid #ef4444;
}

/* One-click status controls inside the expenses table */
.inline-status-cell {
    text-align: center;
    overflow: visible;
}
.inline-status-select {
    width: 100%;
    min-width: 150px;
    max-width: 185px;
    padding: 7px 28px 7px 12px;
    border-radius: 999px;
    border: 2px solid transparent;
    font-family: inherit;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
    cursor: pointer;
    outline: none;
    transition: box-shadow .15s ease, opacity .15s ease, transform .1s ease;
}
.inline-status-select:hover {
    box-shadow: 0 3px 10px rgba(15, 23, 42, .14);
}
.inline-status-select:focus {
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .18);
}
.inline-status-select.is-saving {
    cursor: wait;
    opacity: .65;
}
.inline-status-select:disabled {
    opacity: .8;
    cursor: not-allowed;
    background-image: none;
}
.btn-finalized-lock,
.btn-finalized-lock:disabled {
    opacity: .72;
    cursor: not-allowed;
    pointer-events: none;
}
.inline-release-status[data-status="NOT YET RELEASED"] {
    background: #fef3c7;
    color: #92400e;
    border-color: #f59e0b;
}
.inline-release-status[data-status="RELEASED"] {
    background: #dbeafe;
    color: #1d4ed8;
    border-color: #3b82f6;
}
.inline-release-status[data-status="REJECTED"] {
    background: #fee2e2;
    color: #b91c1c;
    border-color: #ef4444;
}
.inline-liquidation-status[data-status="NOT YET LIQUIDATED"] {
    background: #fee2e2;
    color: #b91c1c;
    border-color: #ef4444;
}
.inline-liquidation-status[data-status="LIQUIDATED"] {
    background: #dcfce7;
    color: #166534;
    border-color: #22c55e;
}

/* Column Filter (per-field filter dropdown) */
.column-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    font-size: 13px;
    font-weight: 600;
    color: #1e4575;
    background: white;
    border: 2px solid #1e4575;
    border-radius: 8px;
    padding: 9px 14px;
    cursor: pointer;
    height: 40px;
    box-sizing: border-box;
    transition: all .2s ease;
}
.column-filter-btn:hover {
    background: #eef2f7;
}
.filter-count-badge {
    background: #A37929;
    color: white;
    font-size: 11px;
    font-weight: 700;
    border-radius: 999px;
    min-width: 18px;
    height: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
}
.column-filter-menu {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    min-width: 240px;
    background: white;
    border: 1.5px solid #d0d5dd;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    z-index: 500;
    padding: 6px;
}
.column-filter-menu-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 10px;
    font-size: 13px;
    font-weight: 500;
    color: #344054;
    border-radius: 6px;
    cursor: pointer;
}
.column-filter-menu-item:hover {
    background: #eef2f7;
}
.column-filter-menu-item .cfm-check {
    width: 14px;
    color: #A37929;
    font-weight: 700;
    visibility: hidden;
}
.column-filter-menu-item.is-active .cfm-check {
    visibility: visible;
}
.column-filter-menu-item.is-active {
    color: #1e4575;
    font-weight: 700;
}
.active-column-filters-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-top: 12px;
}
.column-filter-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #f5f7fa;
    border: 1.5px solid #d0d5dd;
    border-radius: 8px;
    padding: 6px 8px 6px 12px;
}
.column-filter-chip label {
    font-size: 11px;
    font-weight: 700;
    color: #1e4575;
    text-transform: uppercase;
    letter-spacing: .3px;
    white-space: nowrap;
}
.column-filter-chip input,
.column-filter-chip select {
    font-size: 13px;
    padding: 6px 8px;
    border: 1.5px solid #d0d5dd;
    border-radius: 6px;
    color: #344054;
    min-width: 130px;
}
.column-filter-chip .cfm-remove {
    background: none;
    border: none;
    color: #8a9bad;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    padding: 2px 4px;
}
.column-filter-chip .cfm-remove:hover {
    color: #dc2626;
}
.clear-column-filters-btn {
    font-size: 12px;
    font-weight: 600;
    color: #1e4575;
    background: #eef2f7;
    border: 1px solid #d0d5dd;
    border-radius: 6px;
    padding: 8px 14px;
    cursor: pointer;
    white-space: nowrap;
}

/* Sticky checkbox + Control Number + Requestor Name columns in All Expenses table.
   Column order is: (1) checkbox, (2) Control Number, (3) Requestor Name */
.table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.requests-table th:nth-child(1),
.requests-table td:nth-child(1) {
    width: 40px;
    min-width: 40px;
    text-align: center;
}
.requests-table th:nth-child(1),
.requests-table td:nth-child(1),
.requests-table th:nth-child(2),
.requests-table td:nth-child(2),
.requests-table th:nth-child(3),
.requests-table td:nth-child(3) {
    position: sticky;
    z-index: 2;
}
.requests-table td:nth-child(1),
.requests-table td:nth-child(2),
.requests-table td:nth-child(3) {
    background: #fff;
}
.requests-table th:nth-child(1),
.requests-table th:nth-child(2),
.requests-table th:nth-child(3) {
    z-index: 3;
}
.requests-table th:nth-child(1),
.requests-table td:nth-child(1) {
    left: 0;
}
.requests-table th:nth-child(2),
.requests-table td:nth-child(2) {
    left: 40px;
}
.requests-table th:nth-child(3),
.requests-table td:nth-child(3) {
    left: 190px;
}
.requests-table td:nth-child(3),
.requests-table th:nth-child(3) {
    box-shadow: 2px 0 4px -2px rgba(0,0,0,0.12);
}

@media (max-width: 768px) {
    .requests-table th:nth-child(1),
    .requests-table td:nth-child(1),
    .requests-table th:nth-child(2),
    .requests-table td:nth-child(2),
    .requests-table th:nth-child(3),
    .requests-table td:nth-child(3) {
        position: static;
        box-shadow: none;
        left: auto;
    }
    .requests-table th:nth-child(2),
    .requests-table td:nth-child(2) {
        min-width: 110px !important;
        max-width: 110px !important;
        white-space: normal;
        word-break: break-word;
        font-size: 12px;
    }
    .requests-table th:nth-child(3),
    .requests-table td:nth-child(3) {
        min-width: 140px !important;
    }
}

/* Print view - hidden on screen, shown only when printing */
.print-only { display: none; }

@media print {
    /* #printArea is reparented to be a direct child of <body> by
       printSelectedRecords() right before printing. Hiding every OTHER
       direct child of body (display:none, not visibility:hidden) removes
       it from layout entirely, instead of just hiding it visually while
       it still reserves its full height — that reserved height was what
       produced several blank pages when only one row was selected. */
    body > *:not(.print-only) {
        display: none !important;
    }
    html, body {
        overflow: visible !important;
        height: auto !important;
        max-height: none !important;
    }
    .print-only {
        display: block !important;
        position: static !important;
        width: 100%;
    }
    .print-header { margin-bottom: 20px; }
    .print-header h2 { margin: 0 0 4px; font-size: 18px; color: #1e4575; }
    .print-header p { margin: 0; font-size: 12px; color: #555; }
    .print-table { width: 100%; border-collapse: collapse; font-size: 11px; }
    .print-table th, .print-table td {
        border: 1px solid #999;
        padding: 6px 8px;
        text-align: left;
    }
    .print-table th {
        background: #eef2f7 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .print-table tr { page-break-inside: avoid; }
    .print-table thead { display: table-header-group; }
    @page { size: landscape; margin: 12mm; }
}
</style>

<div id="budgetModal" class="modal">
    <div class="modal-content modal-compact" style="max-width: 480px;max-height:85vh;overflow-y:auto;">
        <div class="modal-header">
            <h3>Edit Department</h3>
            <span class="close" onclick="closeBudgetModal()">&times;</span>
        </div>
        <form id="budgetUpdateForm" class="modal-form">
            <input type="hidden" id="budget_dept_id">
            <div class="form-group">
                <label>Department</label>
                <input type="text" id="budget_dept_name" class="form-control form-control-sm" readonly style="background-color: #f4f6f8;">
            </div>
            <div class="form-group">
                <label>Total Expenses</label>
                <input type="text" id="budget_total_expenses" class="form-control form-control-sm" readonly style="background-color: #f4f6f8;">
            </div>
            <div class="form-group">
                <label>Allowable Budget <span class="required">*</span></label>
                <input type="number" id="budget_amount" class="form-control form-control-sm" step="0.01" required oninput="calculateRemainingBudget()">
            </div>
            <div class="form-group">
                <label>Remaining Budget</label>
                <input type="text" id="budget_remaining" class="form-control form-control-sm" readonly style="background-color: #f4f6f8; color: #27ae60; font-weight: 600;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label>Date From</label>
                    <input type="date" id="budget_from" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>Date To</label>
                    <input type="date" id="budget_to" class="form-control form-control-sm">
                </div>
            </div>
            {{-- Categories section --}}
            <div class="form-group" style="margin-top:8px;">
                <label style="font-weight:600;font-size:12px;">Categories</label>
                <div id="budget_categories_list" style="display:flex;flex-wrap:wrap;gap:6px;margin:8px 0;min-height:32px;"></div>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="budget_new_cat" placeholder="Add category..." class="form-control form-control-sm" style="flex:1;">
                    <button type="button" onclick="addBudgetCategory()" style="padding:5px 12px;background:#1e4575;color:#fff;border:none;border-radius:6px;font-size:12px;cursor:pointer;">Add</button>
                </div>
            </div>
            <div class="form-actions-right" style="margin-top: 20px;">
                <button type="submit" class="btn-submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content modal-compact">
        <div class="modal-header">
            <h3>Edit Request</h3>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <form id="editRequestForm" class="request-form modal-form">
            <input type="hidden" id="edit_id">
            
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Control Number <span class="required">*</span></label>
                    <input type="text" id="edit_control_number" name="control_number" class="form-control form-control-sm" required>
                    <small style="color: #8a9bad; font-size: 11px;">Must be unique (e.g., ARCS-02-001-26)</small>
                </div>

                <div class="form-group">
                    <label>Requestor Name <span class="required">*</span></label>
                    <div class="combobox-wrapper">
                        <input type="text" id="edit_requestor_name" name="requestor_name" class="form-control form-control-sm combobox-input" required autocomplete="off" placeholder="Type or select requestor" onclick="toggleEditRequestorDropdown()" oninput="filterEditRequestors(this.value)">
                        <button type="button" class="combobox-arrow" onclick="toggleEditRequestorDropdown()">▼</button>
                        <div id="editRequestorDropdown" class="combobox-dropdown" style="display: none;">
                            @foreach($requestorNames as $name)
                                <div class="dropdown-item" onclick="selectEditRequestor('{{ $name }}')">{{ $name }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Department <span class="required">*</span></label>
                    <div class="combobox-wrapper">
                        <input type="text" id="edit_department" name="department" class="form-control form-control-sm combobox-input" required autocomplete="off" placeholder="Type or select department" onclick="toggleEditDepartmentDropdown()" oninput="filterEditDepartments(this.value)">
                        <button type="button" class="combobox-arrow" onclick="toggleEditDepartmentDropdown()">▼</button>
                        <div id="editDepartmentDropdown" class="combobox-dropdown" style="display: none;">
                            @foreach($departments->where('slug', '!=', 'capex') as $dept)
                            <div class="dropdown-item" onclick="selectEditDepartment('{{ $dept->name }}')">{{ $dept->name }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <div class="combobox-wrapper">
                        <input type="text" id="edit_category" name="category" class="form-control form-control-sm combobox-input" required autocomplete="off" placeholder="Type or select category" onclick="toggleEditCategoryDropdown()" oninput="filterEditCategories(this.value)">
                        <button type="button" class="combobox-arrow" onclick="toggleEditCategoryDropdown()">▼</button>
                        <div id="editCategoryDropdown" class="combobox-dropdown" style="display: none;">
                            <div class="dropdown-item">Select Department First</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Date Requested <span class="required" style="color:#ef4444;">*</span></label>
                    <input type="date" id="edit_date_requested" name="date_requested" class="form-control form-control-sm" required>
                </div>

                <div class="form-group">
                    <label>Requested Amount <span class="required">*</span></label>
                    <input type="text" id="edit_requested_amount" name="requested_amount" class="form-control form-control-sm" placeholder="0.00" inputmode="decimal" required>
                </div>

                <div class="form-group">
                    <label>Release Status <span class="required">*</span></label>
                    <select id="edit_release_status" name="release_status" class="form-control form-control-sm" required>
                        <option value="NOT YET RELEASED">NOT YET RELEASED</option>
                        <option value="RELEASED">RELEASED</option>
                        <option value="REJECTED">REJECTED</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Liquidation Status <span class="required">*</span></label>
                    <select id="edit_status" name="status" class="form-control form-control-sm" required>
                        <option value="NOT YET LIQUIDATED">NOT YET LIQUIDATED</option>
                        <option value="LIQUIDATED">LIQUIDATED</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date Released</label>
                    <input type="date" id="edit_date_released" name="date_released" class="form-control form-control-sm">
                </div>

                <div class="form-group">
                    <label>Total Expenses</label>
                    <input type="text" id="edit_total_expenses" name="total_expenses" class="form-control form-control-sm" placeholder="0.00" inputmode="decimal">
                </div>

                <div class="form-group">
                    <label>Amount Returned</label>
                    <input type="text" id="edit_amount_returned" name="amount_returned" class="form-control form-control-sm" placeholder="0.00" inputmode="decimal" readonly style="background-color: #f4f6f8;">
                </div>

                <div class="form-group">
                    <label>Date of Amount Returned</label>
                    <input type="date" id="edit_date_of_amount_returned" name="date_of_amount_returned" class="form-control form-control-sm">
                </div>
            </div>

            <div class="form-actions-right" style="margin-top: 20px;">
                <button type="submit" class="btn-submit">Update Request</button>
            </div>
        </form>
    </div>
</div>

<!-- Liquidation Update Modal (appears when status is changed to LIQUIDATED) -->
<div id="liquidationUpdateModal" class="modal">
    <div class="modal-content modal-compact">
        <div class="modal-header">
            <h3>Complete Liquidation Form</h3>
            <span class="close" onclick="cancelLiquidationModal()">&times;</span>
        </div>
        <form id="liquidationUpdateForm" class="request-form modal-form">
            <input type="hidden" id="liq_source">
            <input type="hidden" id="liq_id">
            <input type="hidden" id="liq_control_number">
            <input type="hidden" id="liq_date_released">

            <!-- Request Information Section (auto-filled from the budget request form, read-only) -->
            <div class="form-section">
                <h4 class="section-label">Request Information</h4>
                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Requestor Name</label>
                        <input type="text" id="liq_requestor_name" class="form-control" readonly style="background-color: #f4f6f8;">
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" id="liq_department" class="form-control" readonly style="background-color: #f4f6f8;">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" id="liq_category" class="form-control" readonly style="background-color: #f4f6f8;">
                    </div>
                </div>
                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Date Requested</label>
                        <input type="date" id="liq_date_requested" class="form-control" readonly style="background-color: #f4f6f8;">
                    </div>
                    <div class="form-group">
                        <label>Requested Amount</label>
                        <input type="text" id="liq_requested_amount" class="form-control" readonly style="background-color: #f4f6f8;">
                    </div>
                </div>
            </div>

            <!-- Required liquidation fields -->
            <div class="form-section">
                <h4 class="section-label">Liquidation Details</h4>
                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Total Expenses <span class="required" style="color:#ef4444;">*</span></label>
                        <input type="text" id="liq_total_expenses" class="form-control" placeholder="0.00" inputmode="decimal" required>
                    </div>
                    <div class="form-group">
                        <label>Amount Returned</label>
                        <input type="text" id="liq_amount_returned" class="form-control" placeholder="0.00" inputmode="decimal" readonly style="background-color: #f4f6f8;">
                    </div>
                    <div class="form-group">
                        <label>Date of Amount Returned <span id="liq_date_returned_required" class="required" style="color:#ef4444;display:none;">*</span></label>
                        <input type="date" id="liq_date_of_amount_returned" class="form-control">
                        <small id="liq_date_returned_help" style="display:block;color:#6b7280;margin-top:5px;">Required only when there is an amount to return.</small>
                    </div>
                </div>
            </div>

            <div class="form-actions-right" style="margin-top: 20px; gap: 10px; display: flex; justify-content: flex-end;">
                <button type="button" onclick="cancelLiquidationModal()" style="padding: 10px 20px; background: #f4f6f8; color: #565651; border: 1px solid #dfe3e8; border-radius: 6px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn-submit">Save Liquidation</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Notification -->
<div id="toastNotification" class="custom-toast">
    <div class="toast-icon" id="toastIcon"></div>
    <div class="toast-content">
        <div class="toast-title" id="toastTitle"></div>
        <div class="toast-message" id="toastMessage"></div>
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content modal-compact" style="max-width: 900px;">
        <div class="modal-header">
            <h3>Request Details</h3>
            <span class="close" onclick="closeViewModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="de-modal-grid">
                <div class="de-modal-field full-width">
                    <label>Control Number</label>
                    <div class="de-field-value" id="view_control_number">-</div>
                </div>

                <div class="de-modal-field full-width">
                    <label>Requestor Name</label>
                    <div class="de-field-value" id="view_requestor_name">-</div>
                </div>

                <div class="de-modal-field">
                    <label>Department</label>
                    <div class="de-field-value" id="view_department">-</div>
                </div>

                <div class="de-modal-field">
                    <label>Category</label>
                    <div class="de-field-value" id="view_category">-</div>
                </div>

                <div class="de-modal-field">
                    <label>Date Requested</label>
                    <div class="de-field-value" id="view_date_requested">-</div>
                </div>

                <div class="de-modal-field">
                    <label>Requested Amount</label>
                    <div class="de-field-value" id="view_requested_amount">-</div>
                </div>

                <div class="de-modal-field">
                    <label>Release Status</label>
                    <div class="de-field-value" id="view_release_status">-</div>
                </div>

                <div class="de-modal-field">
                    <label>Liquidation Status</label>
                    <div class="de-field-value" id="view_status">-</div>
                </div>

                <div class="de-modal-field">
                    <label>Date Released</label>
                    <div class="de-field-value" id="view_date_released">-</div>
                </div>

                <div class="de-modal-field">
                    <label>Total Expenses</label>
                    <div class="de-field-value" id="view_total_expenses">-</div>
                </div>

                <div class="de-modal-field">
                    <label>Amount Returned</label>
                    <div class="de-field-value" id="view_amount_returned">-</div>
                </div>

                <div class="de-modal-field full-width">
                    <label>Date of Amount Returned</label>
                    <div class="de-field-value" id="view_date_of_amount_returned">-</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
try {
const categories = @json($categories);

// Department name mapping function
// NOTE: previously this translated short codes ("Admin", "HR") to/from
// the full department names ("Administrative", "Human Resource") used
// by the Departments table. That translation caused a real bug: every
// save (including liquidation via the "UPDATE RECORD" popup) wrote the
// short code back into departmental_expenses.department, which never
// matched Department::name, so remainingBudget() saw allowable_budget=0
// for that department and reported wildly negative "remaining" values.
// All dropdowns already emit the real department name directly, so
// these are now no-ops kept only so existing call sites don't need to
// change.
function mapDepartmentName(name) {
    return name;
}

// Reverse mapping for saving to database
function reverseDepartmentName(name) {
    return name;
}

// Save and restore scroll position of page-content
window.addEventListener('beforeunload', function() {
    const pageContent = document.querySelector('.page-content');
    if (pageContent) {
        sessionStorage.setItem('scrollPos', pageContent.scrollTop);
    }
});

window.addEventListener('load', function() {
    const scrollPos = sessionStorage.getItem('scrollPos');
    if (scrollPos) {
        const pageContent = document.querySelector('.page-content');
        if (pageContent) {
            pageContent.scrollTop = parseInt(scrollPos);
        }
        sessionStorage.removeItem('scrollPos');
    }
});

// Toast Notification Function
let deleteConfirmId = null;

function showToast(type, title, message, callback) {
    const toast = document.getElementById('toastNotification');
    const icon = document.getElementById('toastIcon');
    const titleEl = document.getElementById('toastTitle');
    const messageEl = document.getElementById('toastMessage');
    
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ',
        confirm: '?'
    };
    
    icon.textContent = icons[type] || icons.info;
    titleEl.textContent = title;
    messageEl.textContent = message;
    
    toast.classList.remove('success', 'error', 'warning', 'info', 'confirm', 'hiding');
    toast.classList.add(type);
    toast.classList.add('show');
    
    if (type !== 'confirm') {
        setTimeout(() => {
            toast.classList.add('hiding');
            setTimeout(() => {
                toast.classList.remove('show', 'hiding');
                if (callback) callback();
            }, 300);
        }, 5000);
    }
}



// Helper function to close all dropdowns
function closeAllDropdowns() {
    document.getElementById('requestorDropdown').style.display = 'none';
    document.getElementById('departmentDropdown').style.display = 'none';
    document.getElementById('categoryDropdown').style.display = 'none';
    
    const editRequestorDropdown = document.getElementById('editRequestorDropdown');
    const editDepartmentDropdown = document.getElementById('editDepartmentDropdown');
    const editCategoryDropdown = document.getElementById('editCategoryDropdown');
    
    if (editRequestorDropdown) editRequestorDropdown.style.display = 'none';
    if (editDepartmentDropdown) editDepartmentDropdown.style.display = 'none';
    if (editCategoryDropdown) editCategoryDropdown.style.display = 'none';
}

// Requestor Name Combobox
function toggleRequestorDropdown() {
    const dropdown = document.getElementById('requestorDropdown');
    const isVisible = dropdown.style.display === 'block';
    closeAllDropdowns();
    if (!isVisible) {
        const items = dropdown.getElementsByClassName('dropdown-item');
        Array.from(items).forEach(item => item.style.display = 'block');
        dropdown.style.display = 'block';
    }
}

function selectRequestor(value) {
    document.getElementById('requestor_name').value = value;
    closeAllDropdowns();
}

function filterRequestors(searchText) {
    closeAllDropdowns();
    const dropdown = document.getElementById('requestorDropdown');
    const items = dropdown.getElementsByClassName('dropdown-item');
    
    if (searchText === '') {
        Array.from(items).forEach(item => item.style.display = 'block');
    } else {
        Array.from(items).forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchText.toLowerCase()) ? 'block' : 'none';
        });
    }
    dropdown.style.display = 'block';
}

// Department Combobox
function toggleDepartmentDropdown() {
    const dropdown = document.getElementById('departmentDropdown');
    const isVisible = dropdown.style.display === 'block';
    closeAllDropdowns();
    if (!isVisible) {
        const items = dropdown.getElementsByClassName('dropdown-item');
        Array.from(items).forEach(item => item.style.display = 'block');
        dropdown.style.display = 'block';
    }
}

function selectDepartmentFromCard(deptName) {
    // Set the department field in the form
    selectDepartment(deptName);

    // Highlight the selected card
    document.querySelectorAll('.budget-card-compact').forEach(c => c.classList.remove('card-selected'));
    event.currentTarget.classList.add('card-selected');

    // Scroll to the form
    const form = document.querySelector('.request-form-container');
    if (form) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function selectDepartment(value) {
    document.getElementById('department').value = value;
    updateCategoryDropdown(value);
    closeAllDropdowns();
}

function filterDepartments(searchText) {
    closeAllDropdowns();
    const dropdown = document.getElementById('departmentDropdown');
    const items = dropdown.getElementsByClassName('dropdown-item');
    
    if (searchText === '') {
        Array.from(items).forEach(item => item.style.display = 'block');
    } else {
        Array.from(items).forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchText.toLowerCase()) ? 'block' : 'none';
        });
    }
    dropdown.style.display = 'block';
}

// Category Combobox
function toggleCategoryDropdown() {
    const dropdown = document.getElementById('categoryDropdown');
    const isVisible = dropdown.style.display === 'block';
    closeAllDropdowns();
    if (!isVisible) {
        const items = dropdown.getElementsByClassName('dropdown-item');
        Array.from(items).forEach(item => item.style.display = 'block');
        dropdown.style.display = 'block';
    }
}

function selectCategory(value) {
    document.getElementById('category').value = value;
    closeAllDropdowns();
}

function filterCategories(searchText) {
    closeAllDropdowns();
    const dropdown = document.getElementById('categoryDropdown');
    const department = document.getElementById('department').value;
    const defaultCats = department && categories[department] ? categories[department] : [];
    dropdown.innerHTML = '';
    
    const filtered = defaultCats.filter(cat => 
        cat.toLowerCase().includes(searchText.toLowerCase())
    );
    
    if (filtered.length === 0) {
        dropdown.innerHTML = '<div class="dropdown-item" style="color: #999;">No matches found</div>';
    } else {
        filtered.forEach(cat => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';
            item.textContent = cat;
            item.onclick = function() { selectCategory(cat); };
            dropdown.appendChild(item);
        });
    }
    dropdown.style.display = 'block';
}

function updateCategoryDropdown(dept) {
    const dropdown = document.getElementById('categoryDropdown');
    dropdown.innerHTML = '';
    
    if (dept && categories[dept]) {
        categories[dept].forEach(cat => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';
            item.textContent = cat;
            item.onclick = function() { selectCategory(cat); };
            dropdown.appendChild(item);
        });
    } else {
        dropdown.innerHTML = '<div class="dropdown-item">Select Department First</div>';
    }
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.combobox-wrapper')) {
        closeAllDropdowns();
    }
});

// Edit Modal Combobox Functions
function toggleEditRequestorDropdown() {
    const dropdown = document.getElementById('editRequestorDropdown');
    const isVisible = dropdown.style.display === 'block';
    closeAllDropdowns();
    if (!isVisible) {
        const items = dropdown.getElementsByClassName('dropdown-item');
        Array.from(items).forEach(item => item.style.display = 'block');
        dropdown.style.display = 'block';
    }
}

function selectEditRequestor(value) {
    document.getElementById('edit_requestor_name').value = value;
    closeAllDropdowns();
}

function filterEditRequestors(searchText) {
    closeAllDropdowns();
    const dropdown = document.getElementById('editRequestorDropdown');
    const items = dropdown.getElementsByClassName('dropdown-item');
    
    if (searchText === '') {
        Array.from(items).forEach(item => item.style.display = 'block');
    } else {
        Array.from(items).forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchText.toLowerCase()) ? 'block' : 'none';
        });
    }
    dropdown.style.display = 'block';
}

function toggleEditDepartmentDropdown() {
    const dropdown = document.getElementById('editDepartmentDropdown');
    const isVisible = dropdown.style.display === 'block';
    closeAllDropdowns();
    if (!isVisible) {
        const items = dropdown.getElementsByClassName('dropdown-item');
        Array.from(items).forEach(item => item.style.display = 'block');
        dropdown.style.display = 'block';
    }
}

function selectEditDepartment(value) {
    document.getElementById('edit_department').value = value;
    updateEditCategoryDropdown(value);
    closeAllDropdowns();
}

function filterEditDepartments(searchText) {
    closeAllDropdowns();
    const dropdown = document.getElementById('editDepartmentDropdown');
    const items = dropdown.getElementsByClassName('dropdown-item');
    
    if (searchText === '') {
        Array.from(items).forEach(item => item.style.display = 'block');
    } else {
        Array.from(items).forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchText.toLowerCase()) ? 'block' : 'none';
        });
    }
    dropdown.style.display = 'block';
}

function toggleEditCategoryDropdown() {
    const dropdown = document.getElementById('editCategoryDropdown');
    const isVisible = dropdown.style.display === 'block';
    closeAllDropdowns();
    if (!isVisible) {
        const items = dropdown.getElementsByClassName('dropdown-item');
        Array.from(items).forEach(item => item.style.display = 'block');
        dropdown.style.display = 'block';
    }
}

function selectEditCategory(value) {
    document.getElementById('edit_category').value = value;
    closeAllDropdowns();
}

function filterEditCategories(searchText) {
    closeAllDropdowns();
    const dropdown = document.getElementById('editCategoryDropdown');
    const items = dropdown.getElementsByClassName('dropdown-item');
    
    if (searchText === '') {
        Array.from(items).forEach(item => item.style.display = 'block');
    } else {
        Array.from(items).forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchText.toLowerCase()) ? 'block' : 'none';
        });
    }
    dropdown.style.display = 'block';
}

function updateEditCategoryDropdown(dept) {
    const dropdown = document.getElementById('editCategoryDropdown');
    dropdown.innerHTML = '';
    
    if (dept && categories[dept]) {
        categories[dept].forEach(cat => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';
            item.textContent = cat;
            item.onclick = function() { selectEditCategory(cat); };
            dropdown.appendChild(item);
        });
    } else {
        dropdown.innerHTML = '<div class="dropdown-item">Select Department First</div>';
    }
}

// Comma formatting for number inputs
function addCommaFormatting(inputId) {
    const el = document.getElementById(inputId);
    if (!el) return;
    el.addEventListener('blur', function() {
        const raw = parseFloat(this.value.replace(/,/g, ''));
        if (!isNaN(raw) && raw > 0) {
            this.value = raw.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        }
    });
    el.addEventListener('focus', function() {
        // Strip commas when focused so user can type normally
        this.value = this.value.replace(/,/g, '');
    });
}
['requested_amount','total_expenses','amount_returned',
 'edit_requested_amount','edit_total_expenses','edit_amount_returned',
 'liq_requested_amount','liq_total_expenses','liq_amount_returned'].forEach(addCommaFormatting);

// ---- Release and liquidation workflow ----
// Release Status is handled independently from Liquidation Status. A request
// must be RELEASED before LIQUIDATED can be selected. Selecting LIQUIDATED
// opens the required liquidation form.
function syncWorkflowFieldsState(prefix) {
    const releaseEl = document.getElementById(prefix + 'release_status');
    const liquidationEl = document.getElementById(prefix + 'status');
    if (!releaseEl || !liquidationEl) return;

    const isReleased = releaseEl.value === 'RELEASED';
    const isRejected = releaseEl.value === 'REJECTED';
    const dateReleasedEl = document.getElementById(prefix + 'date_released');

    liquidationEl.disabled = !isReleased;
    if (!isReleased) {
        liquidationEl.value = 'NOT YET LIQUIDATED';
    }

    if (dateReleasedEl) {
        dateReleasedEl.disabled = !isReleased;
        if (!isReleased) dateReleasedEl.value = '';
    }

    const isLiquidated = isReleased && liquidationEl.value === 'LIQUIDATED';
    ['total_expenses', 'amount_returned', 'date_of_amount_returned'].forEach(function(field) {
        const el = document.getElementById(prefix + field);
        if (!el) return;
        el.disabled = !isLiquidated;
        if (!isLiquidated) el.value = '';
    });

    if (isRejected) {
        liquidationEl.title = 'Rejected requests cannot be liquidated.';
    } else if (!isReleased) {
        liquidationEl.title = 'Set Release Status to RELEASED first.';
    } else {
        liquidationEl.title = '';
    }
}

let addStatusPrevValue = 'NOT YET LIQUIDATED';
let editStatusPrevValue = 'NOT YET LIQUIDATED';

function normalizeDateInput(value) {
    if (!value) return '';
    const text = String(value);
    return text.length >= 10 ? text.substring(0, 10) : text;
}

function formatTableDate(value) {
    const normalized = normalizeDateInput(value);
    if (!normalized) return '-';
    const parts = normalized.split('-');
    if (parts.length !== 3) return normalized;
    return `${parts[1]}/${parts[2]}/${parts[0]}`;
}

function formatTableMoney(value) {
    if (value === null || value === undefined || value === '') return '-';
    const number = Number(value);
    if (!Number.isFinite(number)) return '-';
    return '₱ ' + number.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function isExpenseRowFinalized(row) {
    // Released and liquidated records remain editable. Destructive reversals
    // are protected by strict confirmation dialogs instead of a hard lock.
    return false;
}

function syncFinalizedExpenseRow(row) {
    if (!row) return;

    row.dataset.finalized = '0';

    const releaseSelect = row.querySelector('.inline-release-status');
    const liquidationSelect = row.querySelector('.inline-liquidation-status');
    if (releaseSelect) {
        releaseSelect.disabled = false;
        releaseSelect.title = 'Click to change release status';
    }
    if (liquidationSelect) {
        liquidationSelect.disabled = false;
        liquidationSelect.title = 'Click to change liquidation status';
    }

    const editButton = row.querySelector('[data-edit-expense]');
    if (editButton) {
        editButton.disabled = false;
        editButton.textContent = 'Edit';
        editButton.title = 'Edit this expense request';
        editButton.classList.remove('btn-finalized-lock');
        if (!editButton.getAttribute('onclick')) {
            editButton.setAttribute('onclick', 'editRequest(' + row.dataset.id + ')');
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#requestsTableBody tr[data-id]').forEach(function(row) {
        syncFinalizedExpenseRow(row);
    });
});

function setInlineStatusSelectStyle(select, status) {
    if (!select) return;
    select.value = status;
    select.dataset.current = status;
    select.dataset.status = status;
}

function createStatusBadge(status, kind) {
    const badge = document.createElement('span');
    badge.className = 'status-badge ' + (kind === 'release' ? 'release-status-badge ' : '')
        + 'status-' + String(status || '').toLowerCase().replace(/\s+/g, '-');
    badge.textContent = status || '-';
    return badge;
}

function applyExpenseRowData(row, data) {
    if (!row || !data) return;

    const releaseStatus = data.release_status || row.dataset.releaseStatus || 'NOT YET RELEASED';
    const liquidationStatus = data.status || row.dataset.liquidationStatus || 'NOT YET LIQUIDATED';
    const dateReleased = normalizeDateInput(data.date_released);
    const dateReturned = normalizeDateInput(data.date_of_amount_returned);

    row.dataset.releaseStatus = releaseStatus;
    row.dataset.liquidationStatus = liquidationStatus;
    row.dataset.status = liquidationStatus;
    row.dataset.dateReleased = dateReleased;
    row.dataset.totalExpenses = data.total_expenses ?? '';
    row.dataset.amountReturned = data.amount_returned ?? '';
    row.dataset.dateReturned = dateReturned;
    if (data.updated_at) {
        row.dataset.dateModified = Math.floor(new Date(data.updated_at).getTime() / 1000) || row.dataset.dateModified;
    }

    setInlineStatusSelectStyle(row.querySelector('.inline-release-status'), releaseStatus);
    setInlineStatusSelectStyle(row.querySelector('.inline-liquidation-status'), liquidationStatus);
    syncFinalizedExpenseRow(row);

    const cells = row.cells;
    if (cells[9]) cells[9].textContent = formatTableDate(dateReleased);
    if (cells[10]) cells[10].textContent = formatTableMoney(data.total_expenses);
    if (cells[11]) cells[11].textContent = formatTableMoney(data.amount_returned);
    if (cells[12]) cells[12].textContent = formatTableDate(dateReturned);

    if (typeof applyFilters === 'function') applyFilters();
}

function requestInlineStatusUpdate(select, url, payload, successTitle) {
    const previous = select.dataset.current;
    select.dataset.status = select.value;
    select.disabled = true;
    select.classList.add('is-saving');

    return fetch(url, {
        method: 'PATCH',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(async response => {
        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to update the status.');
        }
        return result;
    })
    .then(result => {
        const row = select.closest('tr');
        applyExpenseRowData(row, result.data);
        showToast('success', successTitle, result.message || 'Status updated successfully.');
        return result;
    })
    .catch(error => {
        setInlineStatusSelectStyle(select, previous);
        showToast('error', 'Update Failed', error.message || 'Unable to update the status.');
        throw error;
    })
    .finally(() => {
        select.disabled = false;
        select.classList.remove('is-saving');
    });
}

function handleInlineReleaseStatusChange(select, id) {
    const row = select.closest('tr');
    const previous = select.dataset.current || row.dataset.releaseStatus || 'NOT YET RELEASED';
    const selected = select.value;

    if (selected === previous) return;

    // Keep the saved value visible until the user confirms the change.
    setInlineStatusSelectStyle(select, previous);

    const erasesLiquidation = row
        && row.dataset.liquidationStatus === 'LIQUIDATED'
        && selected !== 'RELEASED';

    const message = erasesLiquidation
        ? 'WARNING: Changing Release Status from RELEASED to ' + selected
            + ' will also change Liquidation Status to NOT YET LIQUIDATED and permanently erase the saved Total Expenses, Amount Returned, and Date of Amount Returned. Continue?'
        : 'Change Release Status from ' + previous + ' to ' + selected + '?';

    const title = erasesLiquidation ? 'Erase Liquidation Data?' : 'Confirm Release Status';

    showConfirm(message, function() {
        select.value = selected;
        requestInlineStatusUpdate(
            select,
            `/api/departmental-expenses/${id}/release-status`,
            { release_status: selected },
            'Release Status Updated'
        ).catch(() => {});
    }, title);
}

function handleInlineLiquidationStatusChange(select, id) {
    const selected = select.value;
    const previous = select.dataset.current || 'NOT YET LIQUIDATED';
    const row = select.closest('tr');

    if (selected === previous) return;

    // Keep the saved value visible until the action is confirmed/completed.
    setInlineStatusSelectStyle(select, previous);

    if (selected === 'LIQUIDATED') {
        if (!row || row.dataset.releaseStatus !== 'RELEASED' || !row.dataset.dateReleased) {
            showToast('warning', 'Release Required', 'Set Release Status to RELEASED before completing liquidation.');
            return;
        }

        // The status is only changed after the required form is completed and
        // its final confirmation popup is accepted.
        openLiquidationModal({
            source: 'inline',
            id: id,
            control_number: row.dataset.control || '',
            requestor_name: row.dataset.requestor || '',
            department: row.dataset.department || '',
            category: row.dataset.category || '',
            date_requested: row.dataset.dateRequested || '',
            requested_amount: row.dataset.requestedAmount || '',
            date_released: row.dataset.dateReleased || '',
            total_expenses: row.dataset.totalExpenses || '',
            amount_returned: row.dataset.amountReturned || '',
            date_of_amount_returned: row.dataset.dateReturned || ''
        });
        return;
    }

    const erasesLiquidation = previous === 'LIQUIDATED';
    const message = erasesLiquidation
        ? 'WARNING: Changing Liquidation Status to NOT YET LIQUIDATED will permanently erase the saved Total Expenses, Amount Returned, and Date of Amount Returned. Continue?'
        : 'Change Liquidation Status from ' + previous + ' to NOT YET LIQUIDATED?';

    showConfirm(message, function() {
        select.value = 'NOT YET LIQUIDATED';
        requestInlineStatusUpdate(
            select,
            `/api/departmental-expenses/${id}/liquidation-status`,
            { status: 'NOT YET LIQUIDATED' },
            'Liquidation Status Updated'
        ).catch(() => {});
    }, erasesLiquidation ? 'Erase Liquidation Data?' : 'Confirm Liquidation Status');
}

function openLiquidationModal(data) {
    document.getElementById('liq_source').value = data.source;
    document.getElementById('liq_id').value = data.id || '';
    document.getElementById('liq_control_number').value = data.control_number || '';
    document.getElementById('liq_requestor_name').value = data.requestor_name || '';
    document.getElementById('liq_department').value = data.department || '';
    document.getElementById('liq_category').value = data.category || '';
    document.getElementById('liq_date_requested').value = normalizeDateInput(data.date_requested);
    document.getElementById('liq_requested_amount').value = data.requested_amount || '';
    document.getElementById('liq_date_released').value = normalizeDateInput(data.date_released);
    document.getElementById('liq_total_expenses').value = data.total_expenses || '';
    document.getElementById('liq_amount_returned').value = data.amount_returned || '';
    document.getElementById('liq_date_of_amount_returned').value = normalizeDateInput(data.date_of_amount_returned);
    calculateLiquidationAmountReturned();

    if (data.source === 'edit') {
        document.getElementById('editModal').style.display = 'none';
    }

    document.getElementById('liquidationUpdateModal').style.display = 'block';
}

function cancelLiquidationModal() {
    const source = document.getElementById('liq_source').value;
    const id = document.getElementById('liq_id').value;
    document.getElementById('liquidationUpdateModal').style.display = 'none';

    if (source === 'edit') {
        const editStatus = document.getElementById('edit_status');
        editStatus.value = editStatusPrevValue;
        editStatus.dataset.current = editStatusPrevValue;
        syncWorkflowFieldsState('edit_');
        document.getElementById('editModal').style.display = 'block';
    } else if (source === 'add') {
        document.getElementById('status').value = addStatusPrevValue;
        syncWorkflowFieldsState('');
    } else if (source === 'inline' && id) {
        const select = document.getElementById('liquidationStatusSelect_' + id);
        if (select) setInlineStatusSelectStyle(select, select.dataset.current || 'NOT YET LIQUIDATED');
    }
}

function handleLiquidationStatusChange(source) {
    const prefix = source === 'edit' ? 'edit_' : '';
    const releaseEl = document.getElementById(prefix + 'release_status');
    const statusEl = document.getElementById(prefix + 'status');

    if (statusEl.value !== 'LIQUIDATED') {
        if (source === 'edit') editStatusPrevValue = statusEl.value;
        else addStatusPrevValue = statusEl.value;
        syncWorkflowFieldsState(prefix);
        return;
    }

    if (!releaseEl || releaseEl.value !== 'RELEASED') {
        statusEl.value = source === 'edit' ? editStatusPrevValue : addStatusPrevValue;
        syncWorkflowFieldsState(prefix);
        showToast('warning', 'Release Required', 'Set Release Status to RELEASED before starting liquidation.');
        return;
    }

    openLiquidationModal({
        source: source,
        id: source === 'edit' ? document.getElementById('edit_id').value : null,
        control_number: source === 'edit' ? document.getElementById('edit_control_number').value : '',
        requestor_name: document.getElementById(prefix + 'requestor_name').value.trim(),
        department: document.getElementById(prefix + 'department').value.trim(),
        category: document.getElementById(prefix + 'category').value.trim(),
        date_requested: document.getElementById(prefix + 'date_requested').value,
        requested_amount: document.getElementById(prefix + 'requested_amount').value,
        date_released: document.getElementById(prefix + 'date_released').value,
        total_expenses: '',
        amount_returned: '',
        date_of_amount_returned: ''
    });
}

const addReleaseStatusSelect = document.getElementById('release_status');
const addStatusSelect = document.getElementById('status');
if (addReleaseStatusSelect && addStatusSelect) {
    syncWorkflowFieldsState('');
    addReleaseStatusSelect.addEventListener('change', function() {
        syncWorkflowFieldsState('');
    });
    addStatusSelect.addEventListener('change', function() {
        handleLiquidationStatusChange('add');
    });
}

const editReleaseStatusSelect = document.getElementById('edit_release_status');
const editLiquidationStatusSelect = document.getElementById('edit_status');
if (editReleaseStatusSelect) {
    editReleaseStatusSelect.addEventListener('change', function() {
        const previous = this.dataset.current || 'NOT YET RELEASED';
        const selected = this.value;
        if (selected === previous) return;

        const row = document.querySelector(`tr[data-id="${document.getElementById('edit_id').value}"]`);
        const erasesLiquidation = row
            && row.dataset.liquidationStatus === 'LIQUIDATED'
            && selected !== 'RELEASED';

        this.value = previous;
        const message = erasesLiquidation
            ? 'WARNING: Changing Release Status to ' + selected
                + ' will reset Liquidation Status and erase Total Expenses, Amount Returned, and Date of Amount Returned when the record is saved. Continue?'
            : 'Change Release Status from ' + previous + ' to ' + selected + '?';

        showConfirm(message, () => {
            this.value = selected;
            this.dataset.current = selected;
            syncWorkflowFieldsState('edit_');
            if (editLiquidationStatusSelect) {
                editLiquidationStatusSelect.dataset.current = editLiquidationStatusSelect.value;
                editStatusPrevValue = editLiquidationStatusSelect.value;
            }
        }, erasesLiquidation ? 'Erase Liquidation Data?' : 'Confirm Release Status');
    });
}
if (editLiquidationStatusSelect) {
    editLiquidationStatusSelect.addEventListener('change', function() {
        const previous = this.dataset.current || editStatusPrevValue || 'NOT YET LIQUIDATED';
        const selected = this.value;
        if (selected === previous) return;

        this.value = previous;

        if (selected === 'NOT YET LIQUIDATED' && previous === 'LIQUIDATED') {
            showConfirm(
                'WARNING: Changing Liquidation Status to NOT YET LIQUIDATED will erase Total Expenses, Amount Returned, and Date of Amount Returned when the record is saved. Continue?',
                () => {
                    this.value = selected;
                    this.dataset.current = selected;
                    editStatusPrevValue = selected;
                    syncWorkflowFieldsState('edit_');
                },
                'Erase Liquidation Data?'
            );
            return;
        }

        this.value = selected;
        this.dataset.current = selected;
        handleLiquidationStatusChange('edit');
    });
}

function calculateLiquidationAmountReturned() {
    const requestedRaw = (document.getElementById('liq_requested_amount').value || '').replace(/,/g, '');
    const expensesRaw = (document.getElementById('liq_total_expenses').value || '').replace(/,/g, '').trim();
    const amountReturnedEl = document.getElementById('liq_amount_returned');
    const dateReturnedEl = document.getElementById('liq_date_of_amount_returned');
    const requiredMarker = document.getElementById('liq_date_returned_required');

    if (expensesRaw === '') {
        amountReturnedEl.value = '';
        dateReturnedEl.required = false;
        dateReturnedEl.disabled = true;
        dateReturnedEl.value = '';
        if (requiredMarker) requiredMarker.style.display = 'none';
        return;
    }

    const requested = parseFloat(requestedRaw) || 0;
    const expenses = parseFloat(expensesRaw) || 0;
    const returned = Math.max(0, requested - expenses);
    amountReturnedEl.value = returned.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    const requiresDate = returned > 0;
    dateReturnedEl.required = requiresDate;
    dateReturnedEl.disabled = !requiresDate;
    if (!requiresDate) dateReturnedEl.value = '';
    if (requiredMarker) requiredMarker.style.display = requiresDate ? 'inline' : 'none';
}

document.getElementById('liq_total_expenses').addEventListener('input', calculateLiquidationAmountReturned);

document.getElementById('liquidationUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!document.getElementById('liq_date_released').value) {
        showToast('error', 'Release Required', 'This request must have a Date Released before it can be liquidated.');
        return;
    }
    if (!validateAmountField('liq_total_expenses', 'Total Expenses', true)) return;

    const amountReturned = parseFloat((document.getElementById('liq_amount_returned').value || '0').replace(/,/g, '')) || 0;
    if (amountReturned > 0 && !document.getElementById('liq_date_of_amount_returned').value) {
        showToast('error', 'Date Required', 'Please enter the Date of Amount Returned.');
        document.getElementById('liq_date_of_amount_returned').focus();
        return;
    }

    showConfirm('Save the liquidation form and mark this record as LIQUIDATED?', function() {
        _submitLiquidationUpdate();
    });
});

function _submitLiquidationUpdate() {
    const source = document.getElementById('liq_source').value;
    const id = document.getElementById('liq_id').value;
    const liquidationFields = {
        status: 'LIQUIDATED',
        total_expenses: document.getElementById('liq_total_expenses').value
            ? parseFloat(document.getElementById('liq_total_expenses').value.replace(/,/g, ''))
            : null,
        amount_returned: document.getElementById('liq_amount_returned').value
            ? parseFloat(document.getElementById('liq_amount_returned').value.replace(/,/g, ''))
            : null,
        date_of_amount_returned: document.getElementById('liq_date_of_amount_returned').value || null
    };

    let url;
    let method;
    let payload;

    if (source === 'inline' && id) {
        url = `/api/departmental-expenses/${id}/liquidation-status`;
        method = 'PATCH';
        payload = liquidationFields;
    } else {
        payload = {
            requestor_name: document.getElementById('liq_requestor_name').value.trim(),
            department: reverseDepartmentName(document.getElementById('liq_department').value.trim()),
            release_status: 'RELEASED',
            status: 'LIQUIDATED',
            category: document.getElementById('liq_category').value.trim(),
            date_requested: document.getElementById('liq_date_requested').value || null,
            requested_amount: parseFloat(document.getElementById('liq_requested_amount').value.replace(/,/g, '')) || 0,
            date_released: document.getElementById('liq_date_released').value || null,
            total_expenses: liquidationFields.total_expenses,
            amount_returned: liquidationFields.amount_returned,
            date_of_amount_returned: liquidationFields.date_of_amount_returned
        };

        const isEdit = source === 'edit' && id;
        if (isEdit) payload.control_number = document.getElementById('liq_control_number').value.trim();
        url = isEdit ? `/api/departmental-expenses/${id}` : '/api/departmental-expenses';
        method = isEdit ? 'PUT' : 'POST';
    }

    fetch(url, {
        method: method,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(async response => {
        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Error saving liquidation.');
        }
        return result;
    })
    .then(result => {
        document.getElementById('liquidationUpdateModal').style.display = 'none';

        if (source === 'inline' && id) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            applyExpenseRowData(row, result.data);
            showToast('success', 'Liquidation Saved', result.message || 'Liquidation form saved successfully.');
        } else {
            showToast('success', 'Success', 'Liquidation form completed and record marked as liquidated.');
            setTimeout(() => location.reload(), 800);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error', error.message || 'Error saving liquidation');
        document.getElementById('liquidationUpdateModal').style.display = 'block';
    });
}

// Highlight row from permission notification URL params
(function() {
    const params = new URLSearchParams(window.location.search);
    const highlightId = params.get('highlight');
    const hlStatus = params.get('status');
    const hlAction = params.get('action');
    if (!highlightId) return;
    setTimeout(function() {
        const row = document.querySelector('tr[data-id="' + highlightId + '"]');
        if (!row) return;

        // Force show the row even if filtered
        row.style.display = '';

        row.scrollIntoView({ behavior: 'smooth', block: 'center' });

        const isApproved = hlStatus === 'approved';
        const isPending  = hlStatus === 'pending';
        const bgColor    = isApproved ? 'rgba(22,163,74,.15)' : (isPending ? 'rgba(234,179,8,.15)' : 'rgba(220,38,38,.12)');
        const borderColor= isApproved ? '#16a34a' : (isPending ? '#d97706' : '#dc2626');
        const badgeColor = isApproved ? '#16a34a' : (isPending ? '#d97706' : '#dc2626');
        const badgeText  = isApproved ? '✓ Approved — Can ' + (hlAction||'edit')
                         : (isPending  ? '👁 ' + (hlAction||'edit') + ' requested'
                         : '✕ Rejected');

        row.style.background   = bgColor;
        row.style.outline      = '2px solid ' + borderColor;
        row.style.outlineOffset= '-1px';
        row.style.transition   = 'all .3s';

        const firstTd = row.querySelector('td');
        if (firstTd && !firstTd.querySelector('.hl-badge')) {
            const badge = document.createElement('span');
            badge.className = 'hl-badge';
            badge.style.cssText = 'display:inline-block;margin-left:6px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:' + badgeColor + ';color:white;vertical-align:middle;';
            badge.textContent = badgeText;
            firstTd.appendChild(badge);
        }

        setTimeout(function() {
            row.style.background   = '';
            row.style.outline      = '';
            const badge = row.querySelector('.hl-badge');
            if (badge) badge.remove();
        }, 10000);
    }, 800);
})();

// Auto-calculate Amount Returned
function calculateAmountReturned() {
    const requestedAmount = parseFloat(document.getElementById('requested_amount').value.replace(/,/g,'')) || 0;
    const totalExpenses = parseFloat(document.getElementById('total_expenses').value.replace(/,/g,'')) || 0;
    const amountReturned = requestedAmount - totalExpenses;
    document.getElementById('amount_returned').value = amountReturned >= 0 ? amountReturned.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}) : '0.00';
}

document.getElementById('requested_amount').addEventListener('input', calculateAmountReturned);
document.getElementById('total_expenses').addEventListener('input', calculateAmountReturned);

function calculateEditAmountReturned() {
    const requestedAmount = parseFloat(document.getElementById('edit_requested_amount').value.replace(/,/g,'')) || 0;
    const totalExpenses = parseFloat(document.getElementById('edit_total_expenses').value.replace(/,/g,'')) || 0;
    const amountReturned = requestedAmount - totalExpenses;
    document.getElementById('edit_amount_returned').value = amountReturned >= 0 ? amountReturned.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}) : '0.00';
}

document.getElementById('edit_requested_amount').addEventListener('input', calculateEditAmountReturned);
document.getElementById('edit_total_expenses').addEventListener('input', calculateEditAmountReturned);

// Validate that an amount field contains a valid, non-negative number.
// Returns true if valid. Shows a toast and focuses the field if invalid.
// Empty string is allowed only when `required` is false.
function validateAmountField(inputId, label, required) {
    const el = document.getElementById(inputId);
    if (!el) return true;
    const raw = el.value.replace(/,/g, '').trim();

    if (raw === '') {
        if (required) {
            showToast('error', 'Invalid Amount', label + ' is required and must be a number.');
            el.focus();
            return false;
        }
        return true;
    }

    if (isNaN(raw) || isNaN(parseFloat(raw))) {
        showToast('error', 'Invalid Amount', label + ' must be a number, not letters or symbols.');
        el.focus();
        return false;
    }

    if (parseFloat(raw) < 0) {
        showToast('error', 'Invalid Amount', label + ' cannot be negative.');
        el.focus();
        return false;
    }

    return true;
}
// Validate that a name field contains only letters (spaces, periods, hyphens, apostrophes allowed).
// Blocks numbers and other symbols. Returns true if valid.
function validateNameField(inputId, label) {
    const el = document.getElementById(inputId);
    if (!el) return true;
    const value = el.value.trim();

    if (value === '') {
        showToast('error', 'Invalid Name', label + ' is required.');
        el.focus();
        return false;
    }

    const nameRegex = /^[A-Za-z.\-'\s]+$/;
    if (!nameRegex.test(value)) {
        showToast('error', 'Invalid Name', label + ' must contain letters only, no numbers or symbols.');
        el.focus();
        return false;
    }

    return true;
}

// Add request
document.getElementById('addRequestForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    if (!validateNameField('requestor_name', 'Requestor Name')) return;
    if (!validateAmountField('requested_amount', 'Requested Amount', true)) return;

    const releaseStatus = document.getElementById('release_status').value;
    const liquidationStatus = document.getElementById('status').value;

    if (releaseStatus === 'RELEASED' && !document.getElementById('date_released').value) {
        showToast('error', 'Date Released Required', 'Please select a Date Released when Release Status is RELEASED.');
        document.getElementById('date_released').focus();
        return;
    }

    if (liquidationStatus === 'LIQUIDATED') {
        showToast('warning', 'Liquidation Form Required', 'Select LIQUIDATED again and complete the liquidation form before saving.');
        return;
    }

    showConfirm('Add this expense request?', function() {
        const formData = {
            requestor_name: document.getElementById('requestor_name').value.trim(),
            department: document.getElementById('department').value.trim(),
            release_status: releaseStatus,
            status: liquidationStatus,
            category: document.getElementById('category').value.trim(),
            date_requested: document.getElementById('date_requested').value || null,
            requested_amount: parseFloat(document.getElementById('requested_amount').value.replace(/,/g,'')) || 0,
            date_released: document.getElementById('date_released').value || null,
            total_expenses: null,
            amount_returned: null,
            date_of_amount_returned: null
        };

        fetch('/api/departmental-expenses', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Error adding request'); });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('success', 'Success', 'Request added successfully!');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error', error.message || 'Error adding request');
        });
    });
});

// Edit request
function editRequest(id) {
    _doEditRequest(id);
}

function _doEditRequest(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) return;

    document.getElementById('edit_id').value = id;
    document.getElementById('edit_control_number').value = row.getAttribute('data-control') || '';
    document.getElementById('edit_requestor_name').value = row.getAttribute('data-requestor') || '';

    const originalDepartment = row.getAttribute('data-department') || '';
    document.getElementById('edit_department').value = originalDepartment;
    updateEditCategoryDropdown(originalDepartment);
    setTimeout(() => {
        document.getElementById('edit_category').value = row.getAttribute('data-category') || '';
    }, 100);

    document.getElementById('edit_date_requested').value = row.getAttribute('data-date-requested') || '';
    document.getElementById('edit_requested_amount').value = row.getAttribute('data-requested-amount') || '';
    document.getElementById('edit_release_status').value = row.getAttribute('data-release-status') || 'NOT YET RELEASED';
    document.getElementById('edit_status').value = row.getAttribute('data-liquidation-status') || 'NOT YET LIQUIDATED';
    document.getElementById('edit_release_status').dataset.current = document.getElementById('edit_release_status').value;
    document.getElementById('edit_status').dataset.current = document.getElementById('edit_status').value;
    editStatusPrevValue = document.getElementById('edit_status').value;

    document.getElementById('edit_date_released').value = row.getAttribute('data-date-released') || '';
    document.getElementById('edit_total_expenses').value = row.getAttribute('data-total-expenses') || '';
    document.getElementById('edit_amount_returned').value = row.getAttribute('data-amount-returned') || '';
    document.getElementById('edit_date_of_amount_returned').value = row.getAttribute('data-date-returned') || '';

    syncWorkflowFieldsState('edit_');
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// View request
function viewRequest(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) return;
    const cells = row.cells;

    document.getElementById('view_control_number').textContent = cells[1].textContent;
    document.getElementById('view_requestor_name').textContent = cells[2].textContent;
    document.getElementById('view_department').textContent = cells[3].textContent;

    const releaseContainer = document.getElementById('view_release_status');
    releaseContainer.innerHTML = '';
    releaseContainer.appendChild(createStatusBadge(row.dataset.releaseStatus, 'release'));

    const liquidationContainer = document.getElementById('view_status');
    liquidationContainer.innerHTML = '';
    liquidationContainer.appendChild(createStatusBadge(row.dataset.liquidationStatus, 'liquidation'));

    document.getElementById('view_category').textContent = cells[6].textContent;
    document.getElementById('view_date_requested').textContent = cells[7].textContent;
    document.getElementById('view_requested_amount').textContent = cells[8].textContent;
    document.getElementById('view_date_released').textContent = cells[9].textContent;
    document.getElementById('view_total_expenses').textContent = cells[10].textContent;
    document.getElementById('view_amount_returned').textContent = cells[11].textContent;
    document.getElementById('view_date_of_amount_returned').textContent = cells[12].textContent;

    document.getElementById('viewModal').style.display = 'block';
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

// Budget Modal Functions
function openBudgetModal(deptId, deptName, currentBudget, budgetFrom, budgetTo) {
    document.getElementById('budget_dept_id').value = deptId;
    document.getElementById('budget_dept_name').value = deptName;
    document.getElementById('budget_amount').value = currentBudget;
    document.getElementById('budget_from').value = budgetFrom || '';
    document.getElementById('budget_to').value = budgetTo || '';

    const remaining = document.getElementById('remaining_display_' + deptId);
    const budget = document.getElementById('budget_display_' + deptId);
    const budgetVal = parseFloat((budget ? budget.textContent : '0').replace(/[₱,]/g, '')) || 0;
    const remainingVal = parseFloat((remaining ? remaining.textContent : '0').replace(/[₱,]/g, '')) || 0;
    const totalExp = budgetVal - remainingVal;

    document.getElementById('budget_total_expenses').value = '₱ ' + totalExp.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('budget_remaining').value = '₱ ' + remainingVal.toLocaleString('en-US', {minimumFractionDigits: 2});

    // Load existing categories
    const catList = document.getElementById('budget_categories_list');
    catList.innerHTML = '';
    const deptCats = categories[deptName] || [];
    deptCats.forEach(cat => {
        const tag = document.createElement('div');
        tag.style.cssText = 'display:flex;align-items:center;gap:6px;padding:4px 10px;background:#f0f4ff;border-radius:6px;font-size:12px;';
        tag.innerHTML = '<span>' + cat + '</span><button type="button" onclick="removeBudgetCategory(this, \'' + cat + '\')" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:14px;">&times;</button>';
        catList.appendChild(tag);
    });

    document.getElementById('budgetModal').style.display = 'block';
}

function addBudgetCategory() {
    const input = document.getElementById('budget_new_cat');
    const val = input.value.trim();
    if (!val) return;
    const catList = document.getElementById('budget_categories_list');
    const tag = document.createElement('div');
    tag.style.cssText = 'display:flex;align-items:center;gap:6px;padding:4px 10px;background:#f0f4ff;border-radius:6px;font-size:12px;';
    tag.innerHTML = '<span>' + val + '</span><button type="button" onclick="removeBudgetCategory(this, \'' + val + '\')" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:14px;">&times;</button>';
    catList.appendChild(tag);
    input.value = '';
}

function removeBudgetCategory(btn, catName) {
    showConfirm('Remove "' + catName + '" category?', function() {
        btn.closest('div').remove();
    }, 'Remove Category');
}

document.addEventListener('DOMContentLoaded', function() {
    const budgetCatInput = document.getElementById('budget_new_cat');
    if (budgetCatInput) {
        budgetCatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); addBudgetCategory(); }
        });
    }
});

function closeBudgetModal() {
    document.getElementById('budgetModal').style.display = 'none';
}

function calculateRemainingBudget() {
    const allowableBudget = parseFloat(document.getElementById('budget_amount').value) || 0;
    const totalExpensesText = document.getElementById('budget_total_expenses').value.replace('₱ ', '').replace(/,/g, '');
    const totalExpenses = parseFloat(totalExpensesText) || 0;
    const remaining = allowableBudget - totalExpenses;
    
    document.getElementById('budget_remaining').value = '₱ ' + parseFloat(remaining).toLocaleString('en-US', {minimumFractionDigits: 2});
}

// Update Budget
document.getElementById('budgetUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const deptId = document.getElementById('budget_dept_id').value;
    const newBudget = document.getElementById('budget_amount').value;
    
    fetch(`/api/departments/${deptId}/budget`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            allowable_budget: newBudget,
            budget_from: document.getElementById('budget_from').value || null,
            budget_to: document.getElementById('budget_to').value || null,
            categories: Array.from(document.querySelectorAll('#budget_categories_list div span')).map(s => s.textContent),
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Success', 'Budget updated successfully!');
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error', 'Error updating budget');
    });
});

    document.getElementById('editRequestForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!validateNameField('edit_requestor_name', 'Requestor Name')) return;
    if (!validateAmountField('edit_requested_amount', 'Requested Amount', true)) return;

    if (!document.getElementById('edit_date_requested').value) {
        showToast('error', 'Date Requested Required', 'Please select a Date Requested.');
        document.getElementById('edit_date_requested').focus();
        return;
    }

    const releaseStatus = document.getElementById('edit_release_status').value;
    const liquidationStatus = document.getElementById('edit_status').value;

    if (releaseStatus === 'RELEASED' && !document.getElementById('edit_date_released').value) {
        showToast('error', 'Date Released Required', 'Please select a Date Released when Release Status is RELEASED.');
        document.getElementById('edit_date_released').focus();
        return;
    }

    if (liquidationStatus === 'LIQUIDATED' && !validateAmountField('edit_total_expenses', 'Total Expenses', true)) return;

    const id = document.getElementById('edit_id').value;
    const formData = {
        control_number: document.getElementById('edit_control_number').value.trim(),
        requestor_name: document.getElementById('edit_requestor_name').value.trim(),
        department: reverseDepartmentName(document.getElementById('edit_department').value.trim()),
        release_status: releaseStatus,
        status: liquidationStatus,
        category: document.getElementById('edit_category').value.trim(),
        date_requested: document.getElementById('edit_date_requested').value || null,
        requested_amount: parseFloat(document.getElementById('edit_requested_amount').value.replace(/,/g,'')) || 0,
        date_released: document.getElementById('edit_date_released').value || null,
        total_expenses: liquidationStatus === 'LIQUIDATED' && document.getElementById('edit_total_expenses').value
            ? parseFloat(document.getElementById('edit_total_expenses').value.replace(/,/g,''))
            : null,
        amount_returned: liquidationStatus === 'LIQUIDATED' && document.getElementById('edit_amount_returned').value
            ? parseFloat(document.getElementById('edit_amount_returned').value.replace(/,/g,''))
            : null,
        date_of_amount_returned: liquidationStatus === 'LIQUIDATED'
            ? (document.getElementById('edit_date_of_amount_returned').value || null)
            : null
    };

    const originalRow = document.querySelector(`tr[data-id="${id}"]`);
    const erasesSavedLiquidation = originalRow
        && originalRow.dataset.liquidationStatus === 'LIQUIDATED'
        && (releaseStatus !== 'RELEASED' || liquidationStatus !== 'LIQUIDATED');
    const updateConfirmation = erasesSavedLiquidation
        ? 'FINAL WARNING: Saving these changes will permanently erase the saved Total Expenses, Amount Returned, and Date of Amount Returned. Continue?'
        : 'Update this expense request and its statuses?';

    showConfirm(updateConfirmation, function() {
        fetch(`/api/departmental-expenses/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Error updating request'); });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('success', 'Success', 'Request updated successfully!');
                setTimeout(() => location.reload(), 800);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error', error.message || 'Error updating request');
        });
    });
});

// Delete request
function deleteRequest(id) {
    _doDeleteRequest(id);
}

function _doDeleteRequest(id) {
    showConfirm('Are you sure you want to delete this record?', function() {
        fetch(`/api/departmental-expenses/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Deleted', 'Request deleted successfully!');
                setTimeout(() => location.reload(), 800);
            }
        })
        .catch(error => {
            showToast('error', 'Error', 'Error deleting request');
        });
    });
}

window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeEditModal();
    }
    const liqModal = document.getElementById('liquidationUpdateModal');
    if (event.target == liqModal) {
        cancelLiquidationModal();
    }
}


// Table Search + Date Range filtering (combined) - Multiple words support
const searchInput = document.getElementById('tableSearch');

// ---- Per-column Filter dropdown ----
const FILTERABLE_FIELDS = [
    { key: 'control_number',           label: 'Control Number',           dataAttr: 'data-control',             type: 'text'  },
    { key: 'requestor_name',           label: 'Requestor Name',           dataAttr: 'data-requestor',           type: 'text'  },
    { key: 'department',               label: 'Department',               dataAttr: 'data-department',          type: 'text'  },
    { key: 'release_status',           label: 'Release Status',           dataAttr: 'data-release-status',      type: 'select', options: ['NOT YET RELEASED', 'RELEASED', 'REJECTED'] },
    { key: 'liquidation_status',       label: 'Liquidation Status',       dataAttr: 'data-liquidation-status',  type: 'select', options: ['NOT YET LIQUIDATED', 'LIQUIDATED'] },
    { key: 'category',                 label: 'Category',                 dataAttr: 'data-category',            type: 'text'  },
    { key: 'date_requested',           label: 'Date Requested',           dataAttr: 'data-date-requested',      type: 'daterange' },
    { key: 'date_released',            label: 'Date Released',            dataAttr: 'data-date-released',       type: 'daterange' },
    { key: 'date_of_amount_returned',  label: 'Date of Amount Returned',  dataAttr: 'data-date-returned',       type: 'daterange' },
    { key: 'requested_amount',         label: 'Requested Amount',         dataAttr: 'data-requested-amount',    type: 'numrange'  },
    { key: 'total_expenses',           label: 'Total Expenses',           dataAttr: 'data-total-expenses',      type: 'numrange'  },
    { key: 'amount_returned',          label: 'Amount Returned',          dataAttr: 'data-amount-returned',     type: 'numrange'  },
];

// Active per-column filters: { fieldKey: currentValue }
const columnFilters = {};

function fieldConfig(key) {
    return FILTERABLE_FIELDS.find(f => f.key === key);
}

function toggleColumnFilterMenu(evt) {
    if (evt) evt.stopPropagation();
    const menu = document.getElementById('columnFilterMenu');
    if (!menu) return;
    const isOpen = menu.style.display === 'block';
    menu.style.display = isOpen ? 'none' : 'block';
    if (!isOpen) renderColumnFilterMenu();
}

function closeColumnFilterMenu() {
    const menu = document.getElementById('columnFilterMenu');
    if (menu) menu.style.display = 'none';
}

// Close the dropdown when clicking anywhere outside of it
document.addEventListener('click', function(evt) {
    const wrapper = document.getElementById('columnFilterDropdown');
    if (wrapper && !wrapper.contains(evt.target)) {
        closeColumnFilterMenu();
    }
});

function renderColumnFilterMenu() {
    const menu = document.getElementById('columnFilterMenu');
    if (!menu) return;
    menu.innerHTML = FILTERABLE_FIELDS.map(f => {
        const active = columnFilters.hasOwnProperty(f.key);
        return `<div class="column-filter-menu-item${active ? ' is-active' : ''}" onclick="toggleColumnFilter('${f.key}')">
                    <span class="cfm-check">&#10003;</span><span>${f.label}</span>
                </div>`;
    }).join('');
}

function toggleColumnFilter(key) {
    if (columnFilters.hasOwnProperty(key)) {
        removeColumnFilter(key);
    } else {
        const f = fieldConfig(key);
        columnFilters[key] = (f && (f.type === 'daterange' || f.type === 'numrange')) ? { from: '', to: '' } : '';
        renderColumnFilterMenu();
        renderActiveColumnFilters();
        closeColumnFilterMenu();
        setTimeout(() => {
            const el = document.getElementById('colFilterInput_' + key) || document.getElementById('colFilterInput_' + key + '_from');
            if (el) el.focus();
        }, 0);
    }
}
function removeColumnFilter(key) {
    delete columnFilters[key];
    renderColumnFilterMenu();
    renderActiveColumnFilters();
    applyFilters();
}

function clearAllColumnFilters() {
    Object.keys(columnFilters).forEach(k => delete columnFilters[k]);
    renderColumnFilterMenu();
    renderActiveColumnFilters();
    applyFilters();
}

function updateColumnFilterValue(key, value) {
    columnFilters[key] = value;
    applyFilters();
}

function updateDateRangeFilterValue(key, part, value) {
    if (!columnFilters[key] || typeof columnFilters[key] !== 'object') {
        columnFilters[key] = { from: '', to: '' };
    }
    columnFilters[key][part] = value;
    applyFilters();
}

function renderActiveColumnFilters() {
    const row = document.getElementById('activeColumnFiltersRow');
    const badge = document.getElementById('filterCountBadge');
    if (!row) return;
    const keys = Object.keys(columnFilters);

    if (badge) {
        badge.style.display = keys.length ? 'inline-flex' : 'none';
        badge.textContent = keys.length;
    }

    if (keys.length === 0) {
        row.style.display = 'none';
        row.innerHTML = '';
        return;
    }

    row.style.display = 'flex';
    row.innerHTML = keys.map(key => {
        const f = fieldConfig(key);
        let inputHtml = '';
        if (f.type === 'select') {
            const val = columnFilters[key] || '';
            inputHtml = `<select id="colFilterInput_${key}" onchange="updateColumnFilterValue('${key}', this.value)">
                            <option value="">All</option>
                            ${f.options.map(o => `<option value="${o}" ${val === o ? 'selected' : ''}>${o}</option>`).join('')}
                         </select>`;
        } else if (f.type === 'daterange') {
            const range = (columnFilters[key] && typeof columnFilters[key] === 'object') ? columnFilters[key] : { from: '', to: '' };
            inputHtml = `<input type="date" id="colFilterInput_${key}_from" value="${range.from || ''}" onchange="updateDateRangeFilterValue('${key}', 'from', this.value)">
                         <span style="color:#8a9bad;font-size:12px;">to</span>
                         <input type="date" id="colFilterInput_${key}_to" value="${range.to || ''}" onchange="updateDateRangeFilterValue('${key}', 'to', this.value)">`;
        } else if (f.type === 'numrange') {
            const range = (columnFilters[key] && typeof columnFilters[key] === 'object') ? columnFilters[key] : { from: '', to: '' };
            inputHtml = `<input type="number" step="any" id="colFilterInput_${key}_from" placeholder="Min" value="${range.from || ''}" style="width:100px;" onchange="updateDateRangeFilterValue('${key}', 'from', this.value)">
                         <span style="color:#8a9bad;font-size:12px;">to</span>
                         <input type="number" step="any" id="colFilterInput_${key}_to" placeholder="Max" value="${range.to || ''}" style="width:100px;" onchange="updateDateRangeFilterValue('${key}', 'to', this.value)">`;
        } else if (f.type === 'date') {
            const val = columnFilters[key] || '';
            inputHtml = `<input type="date" id="colFilterInput_${key}" value="${val}" oninput="updateColumnFilterValue('${key}', this.value)">`;
        } else {
            const val = columnFilters[key] || '';
            inputHtml = `<input type="text" id="colFilterInput_${key}" placeholder="Search ${f.label.toLowerCase()}..." value="${val}" oninput="updateColumnFilterValue('${key}', this.value)">`;
        }
        return `<div class="column-filter-chip">
                    <label>${f.label}</label>
                    ${inputHtml}
                    <button type="button" class="cfm-remove" title="Remove filter" onclick="removeColumnFilter('${key}')">&times;</button>
                </div>`;
    }).join('') + `<button type="button" class="clear-column-filters-btn" onclick="clearAllColumnFilters()">Clear Filters</button>`;
}

function matchesColumnFilters(row) {
    for (const key in columnFilters) {
        const f = fieldConfig(key);
        if (!f) continue;

        if (f.type === 'daterange') {
            const range = columnFilters[key];
            if (!range || (!range.from && !range.to)) continue;
            const rowVal = (row.getAttribute(f.dataAttr) || '').toString();
            if (!rowVal) return false;
            if (range.from && rowVal < range.from) return false;
            if (range.to && rowVal > range.to) return false;
            continue;
        }

        if (f.type === 'numrange') {
            const range = columnFilters[key];
            if (!range || (range.from === '' && range.to === '')) continue;
            const rawVal = (row.getAttribute(f.dataAttr) || '').toString().replace(/[^0-9.\-]/g, '');
            const rowNum = rawVal === '' ? NaN : parseFloat(rawVal);
            if (isNaN(rowNum)) return false;
            if (range.from !== '' && rowNum < parseFloat(range.from)) return false;
            if (range.to !== '' && rowNum > parseFloat(range.to)) return false;
            continue;
        }

        const filterVal = (columnFilters[key] || '').toString().trim().toLowerCase();
        if (!filterVal) continue;
        const rowVal = (row.getAttribute(f.dataAttr) || '').toString().toLowerCase();

        if (f.type === 'date') {
            if (rowVal !== filterVal) return false;
        } else if (f.type === 'select') {
            if (rowVal !== filterVal) return false;
        } else {
            if (!rowVal.includes(filterVal)) return false;
        }
    }
    return true;
}

function applyFilters() {
    const searchText = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const searchWords = searchText.split(/\s+/).filter(word => word.length > 0);
    const rows = document.querySelectorAll('#requestsTableBody tr');

    rows.forEach(row => {
        if (row.cells.length === 0) {
            row.style.display = 'none';
            return;
        }

        const searchableCellText = Array.from(row.cells)
            .filter((cell, index) => index !== 4 && index !== 5 && index !== row.cells.length - 1)
            .map(cell => cell.textContent)
            .join(' ');
        const text = `${searchableCellText} ${row.dataset.releaseStatus || ''} ${row.dataset.liquidationStatus || ''}`.toLowerCase();
        const allWordsFound = searchWords.length === 0 || searchWords.every(word => text.includes(word));
        const columnMatch = matchesColumnFilters(row);

        row.style.display = (allWordsFound && columnMatch) ? '' : 'none';
    });

    checkNoResults();
}

if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
}

function checkNoResults() {
    const tableRows = document.querySelectorAll('#requestsTableBody tr');
    const noResultsMsg = document.getElementById('noResultsMessage');
    const table = document.querySelector('.requests-table');
    
    let visibleCount = 0;
    tableRows.forEach(row => {
        if (row.style.display !== 'none') {
            visibleCount++;
        }
    });
    
    if (visibleCount === 0) {
        table.style.display = 'none';
        noResultsMsg.style.display = 'block';
    } else {
        table.style.display = 'table';
        noResultsMsg.style.display = 'none';
    }
}

// Initialize filters on page load
document.addEventListener('DOMContentLoaded', function() {
    // Department names are now stored consistently as the real Department
    // name (e.g. "Administrative"), so no more mapping needed here.
    applyFilters();
});

// ---- Row selection (checkboxes) + Print Selected ----
function toggleSelectAll(cb) {
    document.querySelectorAll('.row-select-checkbox').forEach(c => {
        const row = c.closest('tr');
        if (row.style.display !== 'none') c.checked = cb.checked;
    });
}

function getSelectedPrintRows() {
    return Array.from(document.querySelectorAll('.row-select-checkbox:checked'))
        .map(cb => cb.closest('tr'))
        .filter(row => row.style.display !== 'none');
}

function printSelectedRecords() {
    const rows = getSelectedPrintRows();
    if (rows.length === 0) {
        showToast('warning', 'No Selection', 'Please select at least one record to print.');
        return;
    }

    const headers = ['Control Number','Requestor Name','Department','Release Status','Liquidation Status','Category','Date Requested','Requested Amount','Date Released','Total Expenses','Amount Returned','Date of Amount Returned'];

    let tableHtml = '<table class="print-table"><thead><tr>';
    headers.forEach(h => tableHtml += `<th>${h}</th>`);
    tableHtml += '</tr></thead><tbody>';

    rows.forEach(row => {
        const cells = row.cells;
        tableHtml += '<tr>';
        // cells[0] = checkbox column, cells[1..12] = data columns, skip Actions (last)
        for (let i = 1; i <= 12; i++) {
            let value = cells[i].textContent.trim();
            if (i === 4) value = row.dataset.releaseStatus || '-';
            if (i === 5) value = row.dataset.liquidationStatus || '-';
            tableHtml += `<td>${value}</td>`;
        }
        tableHtml += '</tr>';
    });
    tableHtml += '</tbody></table>';

    const now = new Date();
    const dateStr = now.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

    const printArea = document.getElementById('printArea');
    printArea.innerHTML = `
        <div class="print-header">
            <h2>Departmental Expenses Report</h2>
            <p>Generated on ${dateStr} — ${rows.length} record(s)</p>
        </div>
        ${tableHtml}
    `;

    // #printArea normally sits inside .main-content, which (along with
    // .content-wrapper / .dashboard-container in layouts.dashboard) uses
    // overflow:hidden. That clips printed content to one viewport-sized
    // box instead of letting it paginate. layouts.dashboard already works
    // around this for position:fixed modals by moving them to <body> — we
    // do the same here for #printArea, then move it back afterward so the
    // page's DOM/layout is unaffected outside of printing.
    const printAreaAnchor = document.createComment('printArea-anchor');
    printArea.parentNode.insertBefore(printAreaAnchor, printArea);
    document.body.appendChild(printArea);

    function restorePrintArea() {
        printAreaAnchor.parentNode.insertBefore(printArea, printAreaAnchor);
        printAreaAnchor.remove();
        window.removeEventListener('afterprint', restorePrintArea);
    }
    window.addEventListener('afterprint', restorePrintArea);

    window.print();
}

// Set active state for Departments nav item - run separately to avoid conflicts
setTimeout(function() {
    const navItem = document.querySelector('.nav-item[data-page="departments"]');
    if (navItem) {
        // Remove active from all
        document.querySelectorAll('.nav-item[data-page]').forEach(item => {
            item.classList.remove('active');
        });
        // Add active to departments
        navItem.classList.add('active');
        console.log('✅ Departments nav item activated');
    } else {
        console.warn('⚠️ Departments nav item not found');
    }
}, 200);

} catch(error) {
    console.error('Error in departmental-expenses JavaScript:', error);
}

// Auto-open edit after admin approval redirect
(function() {
    const params = new URLSearchParams(window.location.search);
    const hlId     = params.get('highlight');
    const hlStatus = params.get('status');
    const hlAction = params.get('action');
    if (!hlId || hlStatus !== 'approved') return;

    function doHighlight() {
        const row = document.querySelector('tr[data-id="' + hlId + '"]');
        if (!row) return;
        row.style.background   = 'rgba(22,163,74,.12)';
        row.style.outline      = '2px solid #16a34a';
        row.style.outlineOffset= '-1px';
        row.style.transition   = 'all .3s';
        const scroller = document.querySelector('.page-content');
        if (scroller) {
            const rr = row.getBoundingClientRect(), sr = scroller.getBoundingClientRect();
            scroller.scrollTo({ top: scroller.scrollTop + rr.top - sr.top - 100, behavior: 'smooth' });
        } else { row.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        // Show toast
        if (typeof showToast === 'function') showToast('Your edit request was approved. You can now edit this record.', 'success', 'Request Approved');
        // Auto-open edit modal
        if (hlAction === 'edit') {
            setTimeout(() => editRequest(parseInt(hlId)), 700);
        }
        // Auto-trigger delete
        if (hlAction === 'delete') {
            setTimeout(() => {
                if (confirm('Your delete request was approved. Delete this record now?')) {
                    deleteRequest(parseInt(hlId));
                }
            }, 700);
        }
        // Remove highlight on click
        row.addEventListener('click', function() {
            row.style.background = ''; row.style.outline = '';
        }, { once: true });
        setTimeout(() => { row.style.background = ''; row.style.outline = ''; }, 10000);
    }
    setTimeout(doHighlight, 800);
    setTimeout(doHighlight, 1500);
    window.history.replaceState({}, '', window.location.pathname);
})();
</script>
@endsection