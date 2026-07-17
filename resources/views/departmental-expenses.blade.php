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
                        <label>Status <span class="required">*</span></label>
                            <select id="status" name="status" class="form-control" required>
                            <option value="PENDING">PENDING</option>
                            <option value="NOT YET LIQUIDATED">NOT YET LIQUIDATED</option>
                            <option value="LIQUIDATED">LIQUIDATED</option>
                            <option value="REJECTED">REJECTED</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Date Released</label>
                        <input type="date" id="date_released" name="date_released" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Total Expenses</label>
                        <input type="text" id="total_expenses" name="total_expenses" class="form-control" placeholder="0.00" inputmode="decimal">
                    </div>

                    <div class="form-group">
                        <label>Amount Returned</label>
                        <input type="text" id="amount_returned" name="amount_returned" class="form-control" placeholder="0.00" inputmode="decimal" readonly style="background-color: #f4f6f8;">
                    </div>

                    <div class="form-group">
                        <label>Date of Amount Returned</label>
                        <input type="date" id="date_of_amount_returned" name="date_of_amount_returned" class="form-control">
                    </div>
                </div>
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
            <table class="requests-table js-sort-table">
                <thead>
                    <tr>
                        <th style="width: 40px; min-width: 40px;">
                            <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th style="min-width: 150px;">Control Number</th>
                        <th style="min-width: 180px;">Requestor Name</th>
                        <th style="min-width: 180px;">Department</th>
                        <th style="min-width: 200px;">Category</th>
                        <th style="min-width: 150px;">Date Requested</th>
                        <th style="min-width: 150px;">Requested Amount</th>
                        <th style="min-width: 180px;">Status</th>
                        <th style="min-width: 150px;">Date Released</th>
                        <th style="min-width: 150px;">Total Expenses</th>
                        <th style="min-width: 150px;">Amount Returned</th>
                        <th style="min-width: 180px;">Date of Amount Returned</th>
                        <th style="min-width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="requestsTableBody">
                    @foreach($requests as $req)
                    <tr id="expense-{{ $req->id }}" data-id="{{ $req->id }}" data-department="{{ $req->department }}" data-date-requested="{{ $req->date_requested ? $req->date_requested->format('Y-m-d') : '' }}" data-date-released="{{ $req->date_released ? $req->date_released->format('Y-m-d') : '' }}" data-control="{{ $req->control_number }}" data-requestor="{{ $req->requestor_name }}" data-category="{{ $req->category }}" data-status="{{ $req->status }}" data-requested-amount="{{ $req->requested_amount }}" data-total-expenses="{{ $req->total_expenses }}" data-amount-returned="{{ $req->amount_returned }}" data-date-returned="{{ $req->date_of_amount_returned ? $req->date_of_amount_returned->format('Y-m-d') : '' }}">
                        <td><input type="checkbox" class="row-select-checkbox" value="{{ $req->id }}"></td>
                        <td>{{ $req->control_number }}</td>
                        <td>{{ $req->requestor_name }}</td>
                        <td class="department-cell">{{ $req->department }}</td>
                        <td>{{ $req->category }}</td>
                        <td>{{ $req->date_requested ? $req->date_requested->format('m/d/Y') : '-' }}</td>
                        <td>₱ {{ number_format($req->requested_amount, 2) }}</td>
                        <td><span class="status-badge status-{{ strtolower(str_replace(' ', '-', $req->status)) }}">{{ $req->status }}</span></td>
                        <td>{{ $req->date_released ? $req->date_released->format('m/d/Y') : '-' }}</td>
                        <td>{{ $req->total_expenses ? '₱ ' . number_format($req->total_expenses, 2) : '-' }}</td>
                        <td>{{ $req->amount_returned ? '₱ ' . number_format($req->amount_returned, 2) : '-' }}</td>
                        <td>{{ $req->date_of_amount_returned ? $req->date_of_amount_returned->format('m/d/Y') : '-' }}</td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="viewRequest({{ $req->id }})" class="btn-action btn-view">View</button>
                                <a href="{{ route('departmental-expenses.view-form', $req->id) }}" target="_blank" class="btn-action btn-view" style="text-decoration:none;display:inline-flex;align-items:center;" title="View & print the original Budget Request Form">Form</a>
                                <button onclick="editRequest({{ $req->id }})" class="btn-action btn-edit">Edit</button>
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
                    <label>Status <span class="required">*</span></label>
                    <select id="edit_status" name="status" class="form-control form-control-sm" required>
                        <option value="PENDING">PENDING</option>
                        <option value="NOT YET LIQUIDATED">NOT YET LIQUIDATED</option>
                        <option value="LIQUIDATED">LIQUIDATED</option>
                        <option value="REJECTED">REJECTED</option>
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
            <h3>UPDATE RECORD</h3>
            <span class="close" onclick="cancelLiquidationModal()">&times;</span>
        </div>
        <form id="liquidationUpdateForm" class="request-form modal-form">
            <input type="hidden" id="liq_source">
            <input type="hidden" id="liq_id">
            <input type="hidden" id="liq_control_number">

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

            <!-- Release & Liquidation Section (left blank for the user to fill in now) -->
            <div class="form-section">
                <h4 class="section-label">Release & Liquidation Details</h4>
                <div class="form-row-inline">
                    <div class="form-group">
                        <label>Status</label>
                        <input type="text" id="liq_status_display" class="form-control" value="LIQUIDATED" readonly style="background-color: #f4f6f8;">
                    </div>
                    <div class="form-group">
                        <label>Date Released <span class="required" style="color:#ef4444;">*</span></label>
                        <input type="date" id="liq_date_released" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Total Expenses <span class="required" style="color:#ef4444;">*</span></label>
                        <input type="text" id="liq_total_expenses" class="form-control" placeholder="0.00" inputmode="decimal" required>
                    </div>
                    <div class="form-group">
                        <label>Amount Returned</label>
                        <input type="text" id="liq_amount_returned" class="form-control" placeholder="0.00" inputmode="decimal" readonly style="background-color: #f4f6f8;">
                    </div>
                    <div class="form-group">
                        <label>Date of Amount Returned</label>
                        <input type="date" id="liq_date_of_amount_returned" class="form-control">
                    </div>
                </div>
            </div>

            <div class="form-actions-right" style="margin-top: 20px; gap: 10px; display: flex; justify-content: flex-end;">
                <button type="button" onclick="cancelLiquidationModal()" style="padding: 10px 20px; background: #f4f6f8; color: #565651; border: 1px solid #dfe3e8; border-radius: 6px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn-submit">Update</button>
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
            <div class="view-details">
                <!-- Header Section with Control Number -->
                <div class="view-header-section">
                    <div class="view-control-label">Control Number</div>
                    <div class="view-control-number" id="view_control_number"></div>
                </div>

                <!-- Requestor Information -->
                <div class="section-title-view">Requestor Information</div>
                
                <div class="detail-row full-width">
                    <span class="detail-label">Requestor Name</span>
                    <span class="detail-value" id="view_requestor_name"></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Department</span>
                    <span class="detail-value" id="view_department"></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Category</span>
                    <span class="detail-value" id="view_category"></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Date Requested</span>
                    <span class="detail-value" id="view_date_requested"></span>
                </div>

                <!-- Financial Information -->
                <div class="section-title-view">Financial Information</div>
                
                <div class="detail-row highlight-row">
                    <span class="detail-label">Requested Amount</span>
                    <span class="detail-value amount" id="view_requested_amount"></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value" id="view_status"></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Date Released</span>
                    <span class="detail-value" id="view_date_released"></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Total Expenses</span>
                    <span class="detail-value" id="view_total_expenses"></span>
                </div>
                
                <div class="detail-row highlight-row">
                    <span class="detail-label">Amount Returned</span>
                    <span class="detail-value amount" id="view_amount_returned"></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Date of Amount Returned</span>
                    <span class="detail-value" id="view_date_of_amount_returned"></span>
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

// ---- Liquidation "UPDATE RECORD" popup ----
// Triggered whenever the Status field (on the Add New Expenses form, or the
// Edit Request modal) is switched to LIQUIDATED. Shows a popup mirroring the
// Add New Expenses "Request Information" fields (auto-filled and locked,
// since they come from the original budget request form) plus the
// Release & Liquidation fields, which are left blank for the user to fill
// in now.
// ---- Lock the Release & Liquidation fields (Date Released, Total Expenses,
// Amount Returned, Date of Amount Returned) unless Status is LIQUIDATED.
// These are only ever meant to be filled in through the "UPDATE RECORD"
// popup, so outside of an already-liquidated record they stay blank and
// non-editable here. ----
function syncLiquidationFieldsState(prefix) {
    const statusEl = document.getElementById(prefix + 'status');
    const isLiquidated = !!statusEl && statusEl.value === 'LIQUIDATED';
    ['date_released', 'total_expenses', 'amount_returned', 'date_of_amount_returned'].forEach(function(field) {
        const el = document.getElementById(prefix + field);
        if (!el) return;
        if (isLiquidated) {
            el.disabled = false;
        } else {
            el.value = '';
            el.disabled = true;
        }
    });
}

let addStatusPrevValue = document.getElementById('status') ? document.getElementById('status').value : 'PENDING';
let editStatusPrevValue = 'PENDING';

function openLiquidationModal(data) {
    document.getElementById('liq_source').value = data.source;
    document.getElementById('liq_id').value = data.id || '';
    document.getElementById('liq_control_number').value = data.control_number || '';
    document.getElementById('liq_requestor_name').value = data.requestor_name || '';
    document.getElementById('liq_department').value = data.department || '';
    document.getElementById('liq_category').value = data.category || '';
    document.getElementById('liq_date_requested').value = data.date_requested || '';
    document.getElementById('liq_requested_amount').value = data.requested_amount || '';

    // These are intentionally left blank so the user fills them in now.
    document.getElementById('liq_date_released').value = '';
    document.getElementById('liq_total_expenses').value = '';
    document.getElementById('liq_amount_returned').value = '';
    document.getElementById('liq_date_of_amount_returned').value = '';

    if (data.source === 'edit') {
        document.getElementById('editModal').style.display = 'none';
    }

    document.getElementById('liquidationUpdateModal').style.display = 'block';
}

function cancelLiquidationModal() {
    const source = document.getElementById('liq_source').value;
    document.getElementById('liquidationUpdateModal').style.display = 'none';

    if (source === 'edit') {
        document.getElementById('edit_status').value = editStatusPrevValue;
        syncLiquidationFieldsState('edit_');
        document.getElementById('editModal').style.display = 'block';
    } else if (source === 'add') {
        const statusEl = document.getElementById('status');
        if (statusEl) statusEl.value = addStatusPrevValue;
        syncLiquidationFieldsState('');
    }
}

const addStatusSelect = document.getElementById('status');
if (addStatusSelect) {
    syncLiquidationFieldsState('');
    addStatusSelect.addEventListener('change', function() {
        if (this.value === 'LIQUIDATED') {
            openLiquidationModal({
                source: 'add',
                id: null,
                control_number: '',
                requestor_name: document.getElementById('requestor_name').value.trim(),
                department: document.getElementById('department').value.trim(),
                category: document.getElementById('category').value.trim(),
                date_requested: document.getElementById('date_requested').value,
                requested_amount: document.getElementById('requested_amount').value
            });
        } else {
            addStatusPrevValue = this.value;
        }
        syncLiquidationFieldsState('');
    });
}

document.getElementById('edit_status').addEventListener('change', function() {
    if (this.value === 'LIQUIDATED') {
        openLiquidationModal({
            source: 'edit',
            id: document.getElementById('edit_id').value,
            control_number: document.getElementById('edit_control_number').value,
            requestor_name: document.getElementById('edit_requestor_name').value,
            department: document.getElementById('edit_department').value,
            category: document.getElementById('edit_category').value,
            date_requested: document.getElementById('edit_date_requested').value,
            requested_amount: document.getElementById('edit_requested_amount').value
        });
    } else {
        editStatusPrevValue = this.value;
    }
    syncLiquidationFieldsState('edit_');
});

document.getElementById('liquidationUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!document.getElementById('liq_date_released').value) {
        showToast('error', 'Date Released Required', 'Please select a Date Released before marking this record as liquidated.');
        document.getElementById('liq_date_released').focus();
        return;
    }
    if (!validateAmountField('liq_total_expenses', 'Total Expenses', true)) return;

    showConfirm('Are you sure you want to update this record?', function() {
        _submitLiquidationUpdate();
    });
});

function _submitLiquidationUpdate() {
    const source = document.getElementById('liq_source').value;
    const id = document.getElementById('liq_id').value;

    const payload = {
        requestor_name: document.getElementById('liq_requestor_name').value.trim(),
        department: reverseDepartmentName(document.getElementById('liq_department').value.trim()),
        category: document.getElementById('liq_category').value.trim(),
        date_requested: document.getElementById('liq_date_requested').value || null,
        requested_amount: parseFloat(document.getElementById('liq_requested_amount').value.replace(/,/g,'')) || 0,
        status: 'LIQUIDATED',
        date_released: document.getElementById('liq_date_released').value || null,
        total_expenses: document.getElementById('liq_total_expenses').value ? parseFloat(document.getElementById('liq_total_expenses').value.replace(/,/g,'')) : null,
        amount_returned: document.getElementById('liq_amount_returned').value ? parseFloat(document.getElementById('liq_amount_returned').value.replace(/,/g,'')) : null,
        date_of_amount_returned: document.getElementById('liq_date_of_amount_returned').value || null
    };

    if (source === 'edit' && id) {
        payload.control_number = document.getElementById('liq_control_number').value.trim();

        fetch(`/api/departmental-expenses/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Error updating request'); });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('success', 'Success', 'Record updated and marked as liquidated!');
                setTimeout(() => location.reload(), 800);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error', error.message || 'Error updating request');
            // Re-open the liquidation modal so the user can correct the amount
            // instead of silently losing the entered data.
            document.getElementById('liquidationUpdateModal').style.display = 'block';
        });
    } else {
        fetch('/api/departmental-expenses', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Error adding request'); });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('success', 'Success', 'Request added and marked as liquidated!');
                setTimeout(() => location.reload(), 800);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error', error.message || 'Error adding request');
            document.getElementById('liquidationUpdateModal').style.display = 'block';
        });
    }
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
    if (!validateAmountField('total_expenses', 'Total Expenses', false)) return;

    showConfirm('Add this expense request?', function() {
        const formData = {
            requestor_name: document.getElementById('requestor_name').value.trim(),
            department: document.getElementById('department').value.trim(),
            category: document.getElementById('category').value.trim(),
            date_requested: document.getElementById('date_requested').value || null,
            requested_amount: parseFloat(document.getElementById('requested_amount').value.replace(/,/g,'')) || 0,
            status: document.getElementById('status').value,
            date_released: document.getElementById('date_released').value || null,
            total_expenses: document.getElementById('total_expenses').value ? parseFloat(document.getElementById('total_expenses').value.replace(/,/g,'')) : null,
            amount_returned: document.getElementById('amount_returned').value ? parseFloat(document.getElementById('amount_returned').value.replace(/,/g,'')) : null,
            date_of_amount_returned: document.getElementById('date_of_amount_returned').value || null
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
                return response.json().then(err => {
                    throw new Error(err.message || 'Error adding request');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('success', 'Success', 'Request added successfully!');
                setTimeout(() => location.reload(), 1500);
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
    const cells = row.cells;
    
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_control_number').value = row.getAttribute('data-control') || cells[1].textContent.trim();
    document.getElementById('edit_requestor_name').value = cells[2].textContent;
    
    // Department is now stored consistently as the real Department name,
    // so it's used directly (no more short-code mapping).
    const originalDepartment = row.getAttribute('data-department');
    document.getElementById('edit_department').value = originalDepartment;
    
    updateEditCategoryDropdown(originalDepartment);
    
    setTimeout(() => {
        document.getElementById('edit_category').value = cells[4].textContent;
    }, 100);
    
    const dateRequested = cells[5].textContent.trim();
    if (dateRequested !== '-') {
        const [month, day, year] = dateRequested.split('/');
        document.getElementById('edit_date_requested').value = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    } else {
        document.getElementById('edit_date_requested').value = '';
    }
    
    document.getElementById('edit_requested_amount').value = cells[6].textContent.replace('₱ ', '').replace(/,/g, '');
    document.getElementById('edit_status').value = cells[7].querySelector('.status-badge').textContent.trim();
    editStatusPrevValue = document.getElementById('edit_status').value;
    syncLiquidationFieldsState('edit_');
    
    if (cells[8].textContent !== '-') {
        const dateReleased = cells[8].textContent.trim();
        const [month, day, year] = dateReleased.split('/');
        document.getElementById('edit_date_released').value = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    } else {
        document.getElementById('edit_date_released').value = '';
    }
    
    if (cells[9].textContent !== '-') {
        document.getElementById('edit_total_expenses').value = cells[9].textContent.replace('₱ ', '').replace(/,/g, '');
    } else {
        document.getElementById('edit_total_expenses').value = '';
    }
    
    if (cells[10].textContent !== '-') {
        document.getElementById('edit_amount_returned').value = cells[10].textContent.replace('₱ ', '').replace(/,/g, '');
    } else {
        document.getElementById('edit_amount_returned').value = '';
    }
    
    if (cells[11].textContent !== '-') {
        const dateReturned = cells[11].textContent.trim();
        const [month, day, year] = dateReturned.split('/');
        document.getElementById('edit_date_of_amount_returned').value = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    } else {
        document.getElementById('edit_date_of_amount_returned').value = '';
    }
    
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// View request
function viewRequest(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    const cells = row.cells;
    
    document.getElementById('view_control_number').textContent = cells[1].textContent;
    document.getElementById('view_requestor_name').textContent = cells[2].textContent;
    document.getElementById('view_department').textContent = cells[3].textContent;
    document.getElementById('view_category').textContent = cells[4].textContent;
    document.getElementById('view_date_requested').textContent = cells[5].textContent;
    document.getElementById('view_requested_amount').textContent = cells[6].textContent;
    
    const statusBadge = cells[7].querySelector('.status-badge').cloneNode(true);
    const statusContainer = document.getElementById('view_status');
    statusContainer.innerHTML = '';
    statusContainer.appendChild(statusBadge);
    
    document.getElementById('view_date_released').textContent = cells[8].textContent;
    document.getElementById('view_total_expenses').textContent = cells[9].textContent;
    document.getElementById('view_amount_returned').textContent = cells[10].textContent;
    document.getElementById('view_date_of_amount_returned').textContent = cells[11].textContent;
    
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

    const isLiquidated = document.getElementById('edit_status').value === 'LIQUIDATED';

    if (isLiquidated) {
        if (!document.getElementById('edit_date_released').value) {
            showToast('error', 'Date Released Required', 'Please select a Date Released before saving a liquidated record.');
            document.getElementById('edit_date_released').focus();
            return;
        }
        if (!validateAmountField('edit_total_expenses', 'Total Expenses', true)) return;
    } else {
        if (!validateAmountField('edit_total_expenses', 'Total Expenses', false)) return;
    }

    const id = document.getElementById('edit_id').value;
    const formData = {
        control_number: document.getElementById('edit_control_number').value.trim(),
        requestor_name: document.getElementById('edit_requestor_name').value.trim(),
        department: reverseDepartmentName(document.getElementById('edit_department').value.trim()),
        category: document.getElementById('edit_category').value.trim(),
        date_requested: document.getElementById('edit_date_requested').value || null,
        requested_amount: parseFloat(document.getElementById('edit_requested_amount').value.replace(/,/g,'')) || 0,
        status: document.getElementById('edit_status').value,
        date_released: document.getElementById('edit_date_released').value || null,
        total_expenses: document.getElementById('edit_total_expenses').value ? parseFloat(document.getElementById('edit_total_expenses').value.replace(/,/g,'')) : null,
        amount_returned: document.getElementById('edit_amount_returned').value ? parseFloat(document.getElementById('edit_amount_returned').value.replace(/,/g,'')) : null,
        date_of_amount_returned: document.getElementById('edit_date_of_amount_returned').value || null
    };
    
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
            return response.json().then(err => {
                throw new Error(err.message || 'Error updating request');
            });
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

// ---- Per-column "Filter" dropdown (Control Number, Requestor Name, Department,
// Category, Requested Amount, Status, Total Expenses, Amount Returned,
// Date of Amount Returned) ----
const FILTERABLE_FIELDS = [
    { key: 'control_number',           label: 'Control Number',           dataAttr: 'data-control',          type: 'text'  },
    { key: 'requestor_name',           label: 'Requestor Name',           dataAttr: 'data-requestor',        type: 'text'  },
    { key: 'department',               label: 'Department',               dataAttr: 'data-department',       type: 'text'  },
    { key: 'category',                 label: 'Category',                 dataAttr: 'data-category',         type: 'text'  },
    { key: 'date_requested',           label: 'Date Requested',           dataAttr: 'data-date-requested',   type: 'daterange' },
    { key: 'date_released',            label: 'Date Released',            dataAttr: 'data-date-released',    type: 'daterange' },
    { key: 'date_of_amount_returned',  label: 'Date of Amount Returned',  dataAttr: 'data-date-returned',    type: 'daterange' },
    { key: 'requested_amount',         label: 'Requested Amount',         dataAttr: 'data-requested-amount', type: 'text'  },
    { key: 'status',                   label: 'Status',                   dataAttr: 'data-status',           type: 'select', options: ['PENDING', 'NOT LIQUIDATED', 'LIQUIDATED', 'REJECTED'] },
    { key: 'total_expenses',           label: 'Total Expenses',           dataAttr: 'data-total-expenses',   type: 'text'  },
    { key: 'amount_returned',          label: 'Amount Returned',          dataAttr: 'data-amount-returned',  type: 'text'  },
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
        columnFilters[key] = (f && f.type === 'daterange') ? { from: '', to: '' } : '';
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

        const text = row.textContent.toLowerCase();
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

    const headers = ['Control Number','Requestor Name','Department','Category','Date Requested','Requested Amount','Status','Date Released','Total Expenses','Amount Returned','Date of Amount Returned'];

    let tableHtml = '<table class="print-table"><thead><tr>';
    headers.forEach(h => tableHtml += `<th>${h}</th>`);
    tableHtml += '</tr></thead><tbody>';

    rows.forEach(row => {
        const cells = row.cells;
        tableHtml += '<tr>';
        // cells[0] = checkbox column, cells[1..11] = data columns, skip Actions (last)
        for (let i = 1; i <= 11; i++) {
            tableHtml += `<td>${cells[i].textContent.trim()}</td>`;
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