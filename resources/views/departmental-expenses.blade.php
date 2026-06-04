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

    <!-- Department Budget Overview -->
    @if(!in_array('departments.budget-cards', $hiddenSections))
    <div class="budget-overview-container">
        <h3 class="budget-overview-title">
            <svg class="title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Departments Allowable Budgets
        </h3>
        <div class="budget-cards-grid">
            @foreach($departments as $dept)
                @if($dept->slug !== 'capex')
                @php
                    $totalExpenses = \App\Models\CommissionRequest::where('department', $dept->name)->sum('requested_amount');
                    $remaining = $dept->allowable_budget - $totalExpenses;
                    $pct = $dept->allowable_budget > 0 ? min(100, ($totalExpenses / $dept->allowable_budget) * 100) : 0;
                    $barColor = $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#16a34a');
                @endphp
                <div class="budget-card-compact" onclick="selectDepartmentFromCard('{{ $dept->name }}')" style="cursor:pointer;" title="Click to select {{ $dept->name }}">
                    <div class="budget-card-header-compact" style="padding-bottom:8px;border-bottom:1px solid #e5e7eb;margin-bottom:10px;">
                        <h4 style="font-size:13px;font-weight:700;color:#fff;margin:0;white-space:normal;word-break:break-word;">{{ $dept->name }}</h4>
                        @if($dept->budget_from || $dept->budget_to)
                        <div style="font-size:12px;color:rgba(255,255,255,0.9);margin-top:5px;font-weight:500;">
                            {{ $dept->budget_from?->format('M d, Y') ?? '—' }} → {{ $dept->budget_to?->format('M d, Y') ?? '—' }}
                        </div>
                        @else
                        <div style="font-size:12px;color:rgba(255,255,255,0.5);margin-top:5px;font-style:italic;">No date set</div>
                        @endif
                    </div>
                    <div class="budget-card-body-compact">
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <div style="display:flex;justify-content:space-between;font-size:12px;">
                                <span style="color:#6b7280;">Budget</span>
                                <span style="font-weight:700;color:#1e4575;" id="budget_display_{{ $dept->id }}">₱{{ number_format($dept->allowable_budget, 2) }}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:12px;">
                                <span style="color:#6b7280;">Expenses</span>
                                <span style="font-weight:600;color:#dc2626;">₱{{ number_format($totalExpenses, 2) }}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:12px;">
                                <span style="color:#6b7280;">Remaining</span>
                                <span style="font-weight:700;color:{{ $remaining >= 0 ? '#16a34a' : '#dc2626' }};" id="remaining_display_{{ $dept->id }}">₱{{ number_format($remaining, 2) }}</span>
                            </div>
                        </div>
                        {{-- Progress bar --}}
                        <div style="margin-top:10px;background:#f3f4f6;border-radius:99px;height:6px;overflow:hidden;">
                            <div style="height:100%;width:{{ $pct }}%;background:{{ $barColor }};border-radius:99px;"></div>
                        </div>
                        <div style="font-size:10px;color:#9ca3af;text-align:right;margin-top:2px;">{{ number_format($pct, 1) }}% used</div>
                        @if(auth()->user()->isAdmin())
                        <div style="display:flex;gap:8px;margin-top:10px;">
                            <button onclick="event.stopPropagation();openBudgetModal({{ $dept->id }}, '{{ $dept->name }}', {{ $dept->allowable_budget }}, '{{ $dept->budget_from?->format('Y-m-d') ?? '' }}', '{{ $dept->budget_to?->format('Y-m-d') ?? '' }}')" class="btn-update-budget" style="flex:1;justify-content:center;">
                                <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </button>
                            <button onclick="event.stopPropagation();deleteDepartment({{ $dept->id }}, '{{ $dept->name }}')" style="padding:6px 10px;background:#fee2e2;color:#dc2626;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:4px;">
                                <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
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
                        <label>Date Requested</label>
                        <input type="date" id="date_requested" name="date_requested" class="form-control">
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
                            <option value="NOT YET LIQUIDATED">NOT YET LIQUIDATED</option>
                            <option value="LIQUIDATED">LIQUIDATED</option>
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
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <label for="monthFilter" style="font-weight: 600; color: #1e4575; font-size: 13px; white-space: nowrap;">Month:</label>
                        <select id="monthFilter" class="filter-select" style="min-width: 110px; font-size: 13px; padding: 6px 10px; border: 1.5px solid #d0d5dd; border-radius: 6px; background-color: white; color: #344054; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                            <option value="all">All</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <label for="yearFilter" style="font-weight: 600; color: #1e4575; font-size: 13px; white-space: nowrap;">Year:</label>
                        <select id="yearFilter" class="filter-select" style="min-width: 90px; font-size: 13px; padding: 6px 10px; border: 1.5px solid #d0d5dd; border-radius: 6px; background-color: white; color: #344054; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                            <option value="all">All</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; margin-left: auto;">
                    <div class="search-box">
                        <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" id="tableSearch" class="search-input-table" placeholder="Search requests...">
                    </div>
                </div>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
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
                    <tr id="expense-{{ $req->id }}" data-id="{{ $req->id }}" data-department="{{ $req->department }}" data-date-released="{{ $req->date_released ? $req->date_released->format('Y-m-d') : '' }}" data-control="{{ $req->control_number }}">
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
    </div>
    @endif
</div>
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
                    <label>Date Requested</label>
                    <input type="date" id="edit_date_requested" name="date_requested" class="form-control form-control-sm">
                </div>

                <div class="form-group">
                    <label>Requested Amount <span class="required">*</span></label>
                    <input type="text" id="edit_requested_amount" name="requested_amount" class="form-control form-control-sm" placeholder="0.00" inputmode="decimal" required>
                </div>

                <div class="form-group">
                    <label>Status <span class="required">*</span></label>
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
function mapDepartmentName(shortName) {
    const mapping = {
        'Admin': 'Administrative',
        'HR': 'Human Resources',
        'Sales & Marketing': 'Sales & Marketing',
        'Finance': 'Finance',
        'Executive': 'Executive'
    };
    return mapping[shortName] || shortName;
}

// Reverse mapping for saving to database
function reverseDepartmentName(fullName) {
    const reverseMapping = {
        'Administrative': 'Admin',
        'Human Resources': 'HR',
        'Sales & Marketing': 'Sales & Marketing',
        'Finance': 'Finance',
        'Executive': 'Executive'
    };
    return reverseMapping[fullName] || fullName;
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
 'edit_requested_amount','edit_total_expenses','edit_amount_returned'].forEach(addCommaFormatting);

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

// Add request
document.getElementById('addRequestForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
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

// Edit request
function editRequest(id) {
    @if(!auth()->user()->isAdmin())
    // Check if already approved
    fetch(`/api/permission-requests/check?action=edit&record_id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.approved) {
                _doEditRequest(id);
            } else {
                const row = document.querySelector(`tr[data-id="${id}"]`);
                const label = row ? (row.cells[0]?.textContent + ' - ' + row.getAttribute('data-department') + ' - ' + row.cells[3]?.textContent) : ('Record #' + id);
                requestPermission('edit', 'Departmental Expenses', id, label, null);
            }
        });
    return;
    @endif
    _doEditRequest(id);
}

function _doEditRequest(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    const cells = row.cells;
    
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_control_number').value = row.getAttribute('data-control') || cells[0].textContent.trim();
    document.getElementById('edit_requestor_name').value = cells[1].textContent;
    
    // Get original department value from data attribute
    const originalDepartment = row.getAttribute('data-department');
    const mappedDepartment = mapDepartmentName(originalDepartment);
    document.getElementById('edit_department').value = mappedDepartment;
    
    updateEditCategoryDropdown(mappedDepartment);
    
    setTimeout(() => {
        document.getElementById('edit_category').value = cells[3].textContent;
    }, 100);
    
    const dateRequested = cells[4].textContent.trim();
    if (dateRequested !== '-') {
        const [month, day, year] = dateRequested.split('/');
        document.getElementById('edit_date_requested').value = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    } else {
        document.getElementById('edit_date_requested').value = '';
    }
    
    document.getElementById('edit_requested_amount').value = cells[5].textContent.replace('₱ ', '').replace(/,/g, '');
    document.getElementById('edit_status').value = cells[6].querySelector('.status-badge').textContent.trim();
    
    if (cells[7].textContent !== '-') {
        const dateReleased = cells[7].textContent.trim();
        const [month, day, year] = dateReleased.split('/');
        document.getElementById('edit_date_released').value = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    } else {
        document.getElementById('edit_date_released').value = '';
    }
    
    if (cells[8].textContent !== '-') {
        document.getElementById('edit_total_expenses').value = cells[8].textContent.replace('₱ ', '').replace(/,/g, '');
    } else {
        document.getElementById('edit_total_expenses').value = '';
    }
    
    if (cells[9].textContent !== '-') {
        document.getElementById('edit_amount_returned').value = cells[9].textContent.replace('₱ ', '').replace(/,/g, '');
    } else {
        document.getElementById('edit_amount_returned').value = '';
    }
    
    if (cells[10].textContent !== '-') {
        const dateReturned = cells[10].textContent.trim();
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
    
    document.getElementById('view_control_number').textContent = cells[0].textContent;
    document.getElementById('view_requestor_name').textContent = cells[1].textContent;
    document.getElementById('view_department').textContent = cells[2].textContent;
    document.getElementById('view_category').textContent = cells[3].textContent;
    document.getElementById('view_date_requested').textContent = cells[4].textContent;
    document.getElementById('view_requested_amount').textContent = cells[5].textContent;
    
    const statusBadge = cells[6].querySelector('.status-badge').cloneNode(true);
    const statusContainer = document.getElementById('view_status');
    statusContainer.innerHTML = '';
    statusContainer.appendChild(statusBadge);
    
    document.getElementById('view_date_released').textContent = cells[7].textContent;
    document.getElementById('view_total_expenses').textContent = cells[8].textContent;
    document.getElementById('view_amount_returned').textContent = cells[9].textContent;
    document.getElementById('view_date_of_amount_returned').textContent = cells[10].textContent;
    
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

// Update request
document.getElementById('editRequestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
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
    @if(!auth()->user()->isAdmin())
    // Check if already approved
    fetch(`/api/permission-requests/check?action=delete&record_id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.approved) {
                _doDeleteRequest(id);
            } else {
                const row = document.querySelector(`tr[data-id="${id}"]`);
                const label = row ? (row.cells[0]?.textContent + ' - ' + row.getAttribute('data-department') + ' - ' + row.cells[3]?.textContent) : ('Record #' + id);
                requestPermission('delete', 'Departmental Expenses', id, label, null);
            }
        });
    return;
    @endif
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
}

// Simple Table Search - Multiple words support WITH FILTER RESPECT
const searchInput = document.getElementById('tableSearch');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const searchText = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#requestsTableBody tr');
        const monthFilter = document.getElementById('monthFilter');
        const yearFilter = document.getElementById('yearFilter');
        
        if (searchText.length > 0) {
            const searchWords = searchText.split(/\s+/).filter(word => word.length > 0);
            const selectedMonth = monthFilter ? monthFilter.value : 'all';
            const selectedYear = yearFilter ? yearFilter.value : 'all';
            
            let visibleCount = 0;
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const dateRequested = row.getAttribute('data-date-released') || '';
                
                // Check if text matches search
                const allWordsFound = searchWords.every(word => text.includes(word));
                
                // Check if row matches current month/year filter
                let matchesFilter = true;
                if (dateRequested) {
                    const rowDate = new Date(dateRequested);
                    const rowMonth = String(rowDate.getMonth() + 1).padStart(2, '0');
                    const rowYear = String(rowDate.getFullYear());
                    
                    if (selectedMonth !== 'all' && rowMonth !== selectedMonth) {
                        matchesFilter = false;
                    }
                    if (selectedYear !== 'all' && rowYear !== selectedYear) {
                        matchesFilter = false;
                    }
                }
                
                // Show only if matches both search AND filter
                if (allWordsFound && matchesFilter) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
        } else {
            // If no search text, just apply the month/year filters
            applyMonthYearFilters();
        }
        
        checkNoResults();
    });
}

// Month/Year Filter Function
function applyMonthYearFilters() {
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');
    
    if (!monthFilter || !yearFilter) {
        return;
    }
    
    const selectedMonth = monthFilter.value;
    const selectedYear = yearFilter.value;
    
    localStorage.setItem('expensesMonthFilter', selectedMonth);
    localStorage.setItem('expensesYearFilter', selectedYear);
    
    const rows = document.querySelectorAll('#requestsTableBody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 0) {
            row.style.display = 'none';
            return;
        }
        
        const dateCell = row.cells[7].textContent.trim();
        
        if (dateCell === '-' || !dateCell) {
            row.style.display = 'none';
            return;
        }
        
        const [month, day, year] = dateCell.split('/');
        
        if (!month || !year) {
            row.style.display = 'none';
            return;
        }
        
        const monthMatch = selectedMonth === 'all' || month === selectedMonth.padStart(2, '0');
        const yearMatch = selectedYear === 'all' || year === selectedYear;
        
        if (monthMatch && yearMatch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    checkNoResults();
}

// Populate month filter from table data
function populateMonthFilter() {
    const monthFilter = document.getElementById('monthFilter');
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'];
    monthNames.forEach((name, i) => {
        const option = document.createElement('option');
        option.value = String(i + 1).padStart(2, '0');
        option.textContent = name;
        monthFilter.appendChild(option);
    });
}

// Populate year filter from table data
function populateYearFilter() {
    const rows = document.querySelectorAll('#requestsTableBody tr');
    const years = new Set();
    
    rows.forEach(row => {
        if (row.cells.length > 4) {
            const dateCell = row.cells[7].textContent.trim();
            if (dateCell && dateCell !== '-' && dateCell.includes('/')) {
                const parts = dateCell.split('/');
                if (parts.length === 3) {
                    const year = parts[2];
                    if (year && year.length === 4) {
                        years.add(year);
                    }
                }
            }
        }
    });
    
    const yearFilter = document.getElementById('yearFilter');
    const sortedYears = Array.from(years).sort((a, b) => b - a);
    
    sortedYears.forEach(year => {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearFilter.appendChild(option);
    });
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
    // Map department names in table
    document.querySelectorAll('.department-cell').forEach(cell => {
        cell.textContent = mapDepartmentName(cell.textContent);
    });
    
    populateMonthFilter();
    populateYearFilter();
    
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');
    
    // Default to current month/year instead of localStorage
    const now = new Date();
    const currentMonth = String(now.getMonth() + 1).padStart(2, '0');
    const currentYear = String(now.getFullYear());

    const savedMonth = localStorage.getItem('expensesMonthFilter') || currentMonth;
    const savedYear = localStorage.getItem('expensesYearFilter') || currentYear;
    
    if (monthFilter) {
        monthFilter.value = savedMonth;
    }
    
    if (yearFilter) {
        yearFilter.value = savedYear;
    }
    
    applyMonthYearFilters();
    
    if (monthFilter) {
        monthFilter.addEventListener('change', function() {
            // Clear search box when filter changes
            const searchInput = document.getElementById('tableSearch');
            if (searchInput) {
                searchInput.value = '';
            }
            applyMonthYearFilters();
        });
    }
    
    if (yearFilter) {
        yearFilter.addEventListener('change', function() {
            // Clear search box when filter changes
            const searchInput = document.getElementById('tableSearch');
            if (searchInput) {
                searchInput.value = '';
            }
            applyMonthYearFilters();
        });
    }
});

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
