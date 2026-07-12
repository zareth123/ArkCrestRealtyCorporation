@extends('layouts.dashboard')

@section('content')
<link rel="stylesheet" href="{{ asset('css/department-enhancements.css') }}?v={{ time() }}">

<!-- Department Navigation Tabs -->
<div class="department-tabs">
    <a href="{{ route('departments.admin') }}" class="dept-tab {{ request()->routeIs('departments.admin') ? 'active' : '' }}">Admin</a>
    <a href="{{ route('departments.sales') }}" class="dept-tab {{ request()->routeIs('departments.sales') ? 'active' : '' }}">Sales & Marketing</a>
    <a href="{{ route('departments.hr') }}" class="dept-tab {{ request()->routeIs('departments.hr') ? 'active' : '' }}">HR</a>
    <a href="{{ route('departments.finance') }}" class="dept-tab {{ request()->routeIs('departments.finance') ? 'active' : '' }}">Finance</a>
    <a href="{{ route('departments.executive') }}" class="dept-tab {{ request()->routeIs('departments.executive') ? 'active' : '' }}">Executive</a>
</div>

<!-- Department Header -->
<div class="dept-header">
    <h2 class="dept-title">{{ $department->name }} Department</h2>
</div>

<!-- Date Selector & Allowable Budget -->
<div class="dept-controls">
    <div class="date-section">
        <label for="expenseDate">Select Date:</label>
        <input type="date" id="expenseDate" class="date-input" value="{{ date('Y-m-d') }}">
    </div>
    
    <div class="budget-section">
        <label for="allowableBudget">Allowable Budget:</label>
        <input type="number" id="allowableBudget" class="budget-input" placeholder="Enter budget" step="0.01" value="{{ $department->allowable_budget }}">
        <button onclick="updateBudget()" class="btn-update-budget">Update</button>
    </div>
    
    <div class="budget-display">
        <span class="budget-label">Current Budget:</span>
        <span class="budget-amount" id="budgetDisplay">₱ {{ number_format($department->allowable_budget, 2) }}</span>
    </div>

    <div class="budget-display">
        <span class="budget-label">Requested (Pending):</span>
        <span class="budget-amount" id="pendingDisplay">₱ {{ number_format($commitments['pending'], 2) }}</span>
    </div>

    <div class="budget-display">
        <span class="budget-label">Liquidated:</span>
        <span class="budget-amount" id="liquidatedDisplay">₱ {{ number_format($commitments['liquidated'], 2) }}</span>
    </div>

    <div class="budget-display remaining-budget">
        <span class="budget-label">Remaining:</span>
        <span class="budget-amount remaining" id="remainingDisplay">₱ {{ number_format($department->allowable_budget - $expenses->sum('total_amount') - $commitments['pending'] - $commitments['liquidated'], 2) }}</span>
    </div>
</div>
<p class="budget-note" style="font-size:12px;color:#6b7280;margin:-8px 0 16px;">
    Remaining budget accounts for manually-logged expenses below, plus amounts requested (PENDING) and already liquidated from submitted Budget Request Forms.
</p>

<!-- Add Category Section -->
<div class="add-category-section">
    <h3 class="section-subtitle">Add Expense Category</h3>
    <form id="addCategoryForm" onsubmit="addCategory(event)">
        <div class="category-inputs">
            <div class="input-group">
                <label>Category</label>
                <input type="text" id="categoryName" placeholder="e.g., Meals, Ads, Supplies" class="form-input" required>
            </div>
            <div class="input-group">
                <label>Amount</label>
                <input type="number" id="categoryAmount" placeholder="0.00" class="form-input" step="0.01" required>
            </div>
            <button type="submit" class="btn-add-category">Add Category</button>
        </div>
    </form>
</div>

<!-- Manage Categories Section (Collapsible) -->
<div class="manage-categories-wrapper">
    <button onclick="toggleManageCategories()" class="btn-manage-categories">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
        </svg>
        Manage Categories
    </button>
    
    <div id="manageCategoriesPanel" class="manage-categories-panel" style="display: none;">
        <h4 class="panel-title">Categories for {{ $department->name }}</h4>
        <div class="categories-list">
            @if($categories->count() > 0)
                @foreach($categories as $category)
                <div class="category-item">
                    <span class="category-name">{{ $category->name }}</span>
                    <button onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')" class="btn-delete-cat">Delete</button>
                </div>
                @endforeach
            @else
                <p class="no-categories">No categories yet. Add one above!</p>
            @endif
        </div>
    </div>
</div>

<!-- Filter Controls -->
<div class="filter-controls">
    <div class="filter-group">
        <label for="monthFilter">Month:</label>
        <select id="monthFilter" class="filter-select">
            <option value="all">All</option>
            <option value="1">January</option>
            <option value="2">February</option>
            <option value="3">March</option>
            <option value="4">April</option>
            <option value="5">May</option>
            <option value="6">June</option>
            <option value="7">July</option>
            <option value="8">August</option>
            <option value="9">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
        </select>
    </div>
    
    <div class="filter-group">
        <label for="yearFilter">Year:</label>
        <select id="yearFilter" class="filter-select">
            <option value="all">All</option>
        </select>
    </div>
</div>

<!-- Expenses Table -->
<div class="dept-table-container">
    <table class="dept-table" id="expensesTable">
        <thead>
            <tr id="tableHeader">
                <th>Date</th>
                @foreach($categories as $category)
                    <th>{{ $category->name }}</th>
                @endforeach
                <th>Total Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            @foreach($expenses as $expense)
            <tr data-id="{{ $expense->id }}">
                <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                @foreach($categories as $category)
                    <td>₱ {{ number_format($expense->categories_data[$category->name] ?? 0, 2) }}</td>
                @endforeach
                <td class="total-col">₱ {{ number_format($expense->total_amount, 2) }}</td>
                <td>
                    <button onclick="editRow({{ $expense->id }})" class="btn-edit">Edit</button>
                    <button onclick="deleteRow({{ $expense->id }})" class="btn-delete">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td><strong>Grand Total</strong></td>
                @foreach($categories as $category)
                    <td></td>
                @endforeach
                <td class="grand-total" id="grandTotal">₱ {{ number_format($expenses->sum('total_amount'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<button onclick="addNewRow()" class="btn-add-row">+ Add New Row</button>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content modal-horizontal">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h3>Edit Expense Row - <span id="editDate"></span></h3>
        <form id="editForm" onsubmit="saveEdit(event)">
            <input type="hidden" id="editExpenseId">
            <div id="editFormFields" class="edit-row-horizontal">
                <!-- Fields will be generated dynamically -->
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn-save">Save Changes</button>
                <button type="button" onclick="closeModal()" class="btn-cancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/department-filters.js') }}"></script>
<script>
const departmentId = {{ $department->id }};
const categories = @json($categories->pluck('name'));
const csrfToken = '{{ csrf_token() }}';
// Amounts already committed against this department's budget from
// submitted Budget Request Forms (PENDING = earmarked/awaiting release,
// LIQUIDATED = actually spent). Factored into the client-side
// over-budget warnings below so they reflect the real remaining amount,
// not just the manually-logged expenses in the table.
const pendingCommitted = {{ (float) $commitments['pending'] }};
const liquidatedCommitted = {{ (float) $commitments['liquidated'] }};

async function updateBudget() {
    const budget = document.getElementById('allowableBudget').value;
    
    try {
        const response = await fetch(`/api/departments/${departmentId}/budget`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ budget })
        });
        
        const data = await response.json();
        const newBudget = parseFloat(data.budget);
        
        // Update budget display
        document.getElementById('budgetDisplay').textContent = '₱ ' + newBudget.toLocaleString('en-US', {minimumFractionDigits: 2});
        
        // Recalculate with current filters
        applyFilters();
        
        alert('Budget updated successfully!');
    } catch (error) {
        console.error('Error:', error);
        alert('Error updating budget');
    }
}

async function addCategory(event) {
    event.preventDefault();
    const name = document.getElementById('categoryName').value.trim();
    const amount = parseFloat(document.getElementById('categoryAmount').value) || 0;
    const date = document.getElementById('expenseDate').value;
    
    if (!name) {
        alert('Please enter a category name!');
        return;
    }
    
    if (!date) {
        alert('Please select a date first!');
        return;
    }
    
    // Check budget before adding
    const currentBudget = parseFloat(document.getElementById('allowableBudget').value) || 0;
    const totalExpenses = parseFloat(document.getElementById('grandTotal').textContent.replace('₱ ', '').replace(/,/g, '')) || 0;
    const remaining = currentBudget - totalExpenses - pendingCommitted - liquidatedCommitted;
    
    if (amount > remaining) {
        const shortage = amount - remaining;
        const confirmAdd = confirm(
            `WARNING: Budget exceeded!\n\n` +
            `Remaining Budget: ₱${remaining.toLocaleString('en-US', {minimumFractionDigits: 2})}\n` +
            `Expense Amount: ₱${amount.toLocaleString('en-US', {minimumFractionDigits: 2})}\n` +
            `Shortage: ₱${shortage.toLocaleString('en-US', {minimumFractionDigits: 2})}\n\n` +
            `Do you want to proceed anyway?`
        );
        
        if (!confirmAdd) {
            return;
        }
    }
    
    try {
        // Add category and amount for the selected date
        const response = await fetch(`/api/departments/${departmentId}/add-category-with-amount`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ 
                name: name,
                amount: amount,
                date: date
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            alert(data.message || 'Error adding category');
            return;
        }
        
        alert('Category and amount added successfully!');
        
        // Clear form
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryAmount').value = '';
        
        location.reload();
    } catch (error) {
        console.error('Error:', error);
        alert('Error adding category');
    }
}

async function addNewRow() {
    const date = document.getElementById('expenseDate').value;
    
    if (!date) {
        alert('Please select a date first!');
        return;
    }
    
    // Check if date already exists
    const rows = document.querySelectorAll('#tableBody tr');
    for (let row of rows) {
        if (row.cells[0].textContent === date) {
            alert('A row for this date already exists! Please edit the existing row or select a different date.');
            return;
        }
    }
    
    const categoriesData = {};
    categories.forEach(cat => categoriesData[cat] = 0);
    
    try {
        const response = await fetch(`/api/departments/${departmentId}/expenses`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                date: date,
                categories: categoriesData,
                total: 0
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            alert('Error adding row');
            return;
        }
        
        location.reload();
    } catch (error) {
        console.error('Error:', error);
        alert('Error adding row');
    }
}

async function deleteCategory(categoryId, categoryName) {
    if (!confirm(`Are you sure you want to delete the category "${categoryName}"? This will remove it from all expense records.`)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/categories/${categoryId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        if (!response.ok) {
            alert('Error deleting category');
            return;
        }
        
        alert('Category deleted successfully!');
        location.reload();
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting category');
    }
}

function editRow(expenseId) {
    const row = document.querySelector(`tr[data-id="${expenseId}"]`);
    const date = row.cells[0].textContent;
    
    document.getElementById('editDate').textContent = date;
    document.getElementById('editExpenseId').value = expenseId;
    
    // Generate form fields horizontally
    const formFields = document.getElementById('editFormFields');
    formFields.innerHTML = '';
    
    categories.forEach((cat, index) => {
        const value = row.cells[index + 1].textContent.replace('₱ ', '').replace(/,/g, '');
        const formGroup = document.createElement('div');
        formGroup.className = 'form-group-inline';
        formGroup.innerHTML = `
            <label>${cat}</label>
            <input type="number" name="${cat}" value="${value}" step="0.01" class="form-input">
        `;
        formFields.appendChild(formGroup);
    });
    
    document.getElementById('editModal').style.display = 'flex';
}

async function saveEdit(event) {
    event.preventDefault();
    const expenseId = document.getElementById('editExpenseId').value;
    const formData = new FormData(event.target);
    
    const categoriesData = {};
    let total = 0;
    
    categories.forEach(cat => {
        const value = parseFloat(formData.get(cat)) || 0;
        categoriesData[cat] = value;
        total += value;
    });
    
    // Check budget before saving
    const currentBudget = parseFloat(document.getElementById('allowableBudget').value) || 0;
    const currentTotal = parseFloat(document.getElementById('grandTotal').textContent.replace('₱ ', '').replace(/,/g, '')) || 0;
    
    // Get the old total for this expense
    const row = document.querySelector(`tr[data-id="${expenseId}"]`);
    const oldTotal = parseFloat(row.querySelector('.total-col').textContent.replace('₱ ', '').replace(/,/g, '')) || 0;
    
    // Calculate new grand total
    const newGrandTotal = currentTotal - oldTotal + total;
    const remaining = currentBudget - newGrandTotal - pendingCommitted - liquidatedCommitted;
    
    if (remaining < 0) {
        const shortage = Math.abs(remaining);
        const confirmSave = confirm(
            `WARNING: Budget will be exceeded!\n\n` +
            `Current Budget: ₱${currentBudget.toLocaleString('en-US', {minimumFractionDigits: 2})}\n` +
            `New Total Expenses: ₱${newGrandTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}\n` +
            `Shortage: ₱${shortage.toLocaleString('en-US', {minimumFractionDigits: 2})}\n\n` +
            `Do you want to proceed anyway?`
        );
        
        if (!confirmSave) {
            return;
        }
    }
    
    try {
        const response = await fetch(`/api/expenses/${expenseId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                categories: categoriesData,
                total: total
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            alert('Error updating expense');
            return;
        }
        
        location.reload();
    } catch (error) {
        console.error('Error:', error);
        alert('Error updating expense');
    }
}

async function deleteRow(expenseId) {
    if (!confirm('Are you sure you want to delete this expense record?')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/expenses/${expenseId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        if (!response.ok) {
            alert('Error deleting expense');
            return;
        }
        
        location.reload();
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting expense');
    }
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

function toggleManageCategories() {
    const panel = document.getElementById('manageCategoriesPanel');
    if (panel.style.display === 'none') {
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
}
</script>
@endsection
