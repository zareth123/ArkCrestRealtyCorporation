@extends('layouts.dashboard')

@section('content')
<div class="commission-monitoring-container">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-content">
            <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:6px;">Finance</div>
            <h1 class="welcome-title">Commission Monitoring</h1>
            <p class="welcome-subtitle">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Track and monitor all commission requests — {{ date('F Y') }}
            </p>
        </div>
        <div class="welcome-decoration">
            <div class="decoration-circle circle-1"></div>
            <div class="decoration-circle circle-2"></div>
            <div class="decoration-circle circle-3"></div>
        </div>
    </div>

    <!-- Statistics Cards -->
    @if(!in_array('commission-monitoring.cards', $hiddenSections))
    <div class="stats-grid">
        <div class="stat-card card-blue">
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Requests</div>
                <div class="stat-value" id="statTotal">{{ $commissionRequests->count() }}</div>
            </div>
        </div>

        <div class="stat-card card-yellow">
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Not Yet Released</div>
                <div class="stat-value" id="statNotReleased">{{ $commissionRequests->where('status', 'Not Yet Released')->count() }}</div>
            </div>
        </div>

        <div class="stat-card card-green">
            <div class="stat-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-label">Released</div>
                <div class="stat-value" id="statReleased">{{ $commissionRequests->where('status', 'Released')->count() }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Add Commission Request Form -->
    @if(!in_array('commission-monitoring.add-form', $hiddenSections))
    <div class="add-commission-section">
        <div class="section-header-commission">
            <h2>ADD NEW COMMISSION REQUEST</h2>
        </div>
        @if(session('error'))
        <div style="background:#fee2e2;color:#dc2626;padding:12px 16px;border-radius:8px;margin-bottom:12px;font-size:13px;">{{ session('error') }}</div>
        @endif
        @if(session('success'))
        <div style="background:#dcfce7;color:#166534;padding:12px 16px;border-radius:8px;margin-bottom:12px;font-size:13px;">✔ {{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div style="background:#fee2e2;color:#dc2626;padding:12px 16px;border-radius:8px;margin-bottom:12px;font-size:13px;">
            @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
        </div>
        @endif
        <form id="cmAddForm" class="commission-form" action="{{ route('commission-monitoring.store') }}" method="POST">
            @csrf
            <div class="form-section">
                <div class="section-title-bar">
                    <span class="section-icon">📋</span>
                    COMMISSION REQUEST INFORMATION
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>CLIENT'S NAME <span class="required">*</span></label>
                        <input type="text" name="client_name" placeholder="Enter client name" required>
                    </div>
                    <div class="form-group">
                        <label>RESERVATION DATE</label>
                        <input type="date" name="reservation_date">
                    </div>
                    <div class="form-group">
                        <label>PROJECT NAME <span class="required">*</span></label>
                        <input type="text" name="project_name" placeholder="Enter project name" required>
                    </div>
                    <div class="form-group">
                        <label>PROPERTY DETAILS (BLOCK & LOT NO.)</label>
                        <input type="text" name="property_details" placeholder="e.g., Block 3 Lot 12, Tower A">
                    </div>
                    <div class="form-group">
                        <label>PRICE / SQM</label>
                        <input type="number" id="cm_add_price_sqm" name="price_sqm" placeholder="0.00" step="0.01" min="0" oninput="computeAddTCP()">
                    </div>
                    <div class="form-group">
                        <label>LOT AREA</label>
                        <input type="number" id="cm_add_lot_area" name="lot_area" placeholder="0.0000" step="0.0001" min="0" oninput="computeAddTCP()">
                    </div>
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:8px;">
                            DISCOUNT
                            <span style="display:inline-flex;border:1px solid #d1d5db;border-radius:6px;overflow:hidden;font-size:11px;font-weight:700;">
                                <button type="button" id="cm_add_disc_pct_btn" onclick="setAddDiscountType('percent')" style="padding:2px 10px;background:#1e457c;color:#fff;border:none;cursor:pointer;">%</button>
                                <button type="button" id="cm_add_disc_val_btn" onclick="setAddDiscountType('value')" style="padding:2px 10px;background:#fff;color:#374151;border:none;cursor:pointer;">VALUE</button>
                            </span>
                        </label>
                        <input type="number" id="cm_add_discount" name="discount" placeholder="0.00" step="0.01" min="0" max="100" oninput="computeAddNetTCP()">
                        <input type="hidden" id="cm_add_discount_type" name="discount_type" value="percent">
                    </div>
                    <div class="form-group">
                        <label>NET TCP <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                        <input type="text" id="cm_add_net_tcp_display" placeholder="0.00" readonly style="background:#f3f4f6;cursor:not-allowed;color:#374151;">
                        <input type="hidden" id="cm_add_net_tcp" name="net_tcp">
                    </div>
                    @if($isAdmin)
                    <div class="form-group">
                        <label>% OF COMMISSION</label>
                        <input type="number" id="cm_add_commission_percent" name="commission_percent" placeholder="e.g. 5" step="0.0001" min="0" max="100" oninput="computeAddCommission()">
                    </div>
                    @endif
                    <div class="form-group">
                        <label>VALUE OF COMMISSION <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                        <input type="text" id="cm_add_commission_display" placeholder="0.00" oninput="computeAddCommissionFromValue()" style="color:#374151;">
                    </div>
                    <div class="form-group">
                        <label>COMMISSION TERMS</label>
                        <select id="cm_add_payment_type" name="payment_type" onchange="computeValueOfPaymentTerms()">
                            <option value="">— Select —</option>
                            <option value="Full Payment">Full Payment</option>
                            <option value="2 Months Commission">2 Months Commission</option>
                            <option value="3 Months Commission">3 Months Commission</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>VALUE OF COMMISSION TERMS <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                        <input type="text" id="cm_add_vopt_display" placeholder="0.00" readonly style="background:#f3f4f6;cursor:not-allowed;color:#374151;">
                        <input type="hidden" id="cm_add_value_of_payment_terms" name="value_of_payment_terms">
                    </div>
                    <div class="form-group">
                        <label>TERMS OF PAYMENT <span class="required">*</span></label>
                        <div class="combobox-wrapper">
                            <input type="text" id="cm_add_terms" name="terms_of_payment" class="combobox-input" required autocomplete="off" placeholder="Type or select payment terms" onclick="toggleCmTermsDropdown()" oninput="filterCmTerms(this.value)">
                            <button type="button" class="combobox-arrow" onclick="toggleCmTermsDropdown()">▼</button>
                            <div id="cmTermsDropdown" class="combobox-dropdown" style="display:none;">
                                <div class="dropdown-item" onclick="selectCmTerm('30% DP - 70% BAL 5 YRS')">30% DP - 70% BAL 5 YRS</div>
                                <div class="dropdown-item" onclick="selectCmTerm('50% DP - 50% BAL 5 YRS')">50% DP - 50% BAL 5 YRS</div>
                                <div class="dropdown-item" onclick="selectCmTerm('30% DP (6 MOS) - 70% BAL 54 MOS')">30% DP (6 MOS) - 70% BAL 54 MOS</div>
                                <div class="dropdown-item" onclick="selectCmTerm('30% DP (3 MOS) - 70% BAL 57 MOS')">30% DP (3 MOS) - 70% BAL 57 MOS</div>
                                <div class="dropdown-item" onclick="selectCmTerm('30% DP (9 MOS) - 70% BAL 36 MOS')">30% DP (9 MOS) - 70% BAL 36 MOS</div>
                                <div class="dropdown-item" onclick="selectCmTerm('30% DP (2 MOS) - 70% BAL 57 MOS')">30% DP (2 MOS) - 70% BAL 57 MOS</div>
                                <div class="dropdown-item" onclick="selectCmTerm('30% DP (2 MOS) - 70% BAL 5 YRS')">30% DP (2 MOS) - 70% BAL 5 YRS</div>
                                <div class="dropdown-item" onclick="selectCmTerm('STRAIGHT PAYMENT')">STRAIGHT PAYMENT</div>
                                <div class="dropdown-item" onclick="selectCmTerm('30% DP - 70% BAL 3 YRS')">30% DP - 70% BAL 3 YRS</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>MODE OF PAYMENT <span class="required">*</span></label>
                        <select name="mode_of_payment" required>
                            <option value="">Select mode of payment</option>
                            <option value="BANK DEPOSIT">BANK DEPOSIT</option>
                            <option value="BANK TRANSFER">BANK TRANSFER</option>
                            <option value="CASH PAYMENT">CASH PAYMENT</option>
                            <option value="MANAGER'S CHECK">MANAGER'S CHECK</option>
                            <option value="PERSONAL CHECK">PERSONAL CHECK</option>
                            <option value="POST-DATED CHECK">POST-DATED CHECK</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>AGENT'S NAME <span class="required">*</span></label>
                        <input type="text" name="agent_name" placeholder="Enter agent name" required>
                    </div>
                    <div class="form-group">
                        <label>DATE REQUESTED <span class="required">*</span></label>
                        <input type="date" name="date_requested" required>
                    </div>
                    <div class="form-group">
                        <label>NUMBER OF UNITS <span class="required">*</span></label>
                        <input type="number" name="number_of_units" placeholder="Enter number of units" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>STATUS <span class="required">*</span></label>
                        <select name="status" required>
                            <option value="Not Yet Released">Not Yet Released</option>
                            <option value="Released">Released</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>DATE RELEASED</label>
                        <input type="date" name="date_released">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>REMARKS</label>
                        <textarea name="remarks" placeholder="Enter any remarks or notes..." rows="3" style="width:100%;padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;resize:vertical;font-family:inherit;color:#374151;"></textarea>
                    </div>
                </div>
            </div>
            <input type="hidden" name="commission" id="cm_add_commission" value="">
            <div class="form-actions">
                <button type="button" class="btn-clear" onclick="document.getElementById('cmAddForm').reset()">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Clear
                </button>
                <button type="submit" class="btn-submit">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Submit Request
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Commission Table -->
    @if(!in_array('commission-monitoring.table', $hiddenSections))
    <div class="monitoring-table-container">
        <!-- Table Header with Title + Filters -->
        <div class="table-top-bar">
            <h3 class="table-section-title">ALL COMMISSION REQUESTS</h3>
            <div class="table-filters-row">
                <div class="filter-left">
                    <label>Status:</label>
                    <select id="statusFilter" class="filter-select-inline">
                        <option value="">All</option>
                        <option value="Not Yet Released">Not Yet Released</option>
                        <option value="Released">Released</option>
                    </select>
                    <label>Month:</label>
                    <select id="monthFilter" class="filter-select-inline">
                        <option value="">All</option>
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    <label>Year:</label>
                    <select id="yearFilter" class="filter-select-inline">
                        <option value="">All</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-right">
                    <div class="search-box-inline">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" id="monitoringSearch" placeholder="Search requests...">
                    </div>

                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="monitoring-table">
                <thead>
                    <tr>
                        <th>Client's Name</th>
                        <th>Reservation Date</th>
                        <th>Project Name</th>
                        <th>Property Details (Block & Lot No.)</th>
                        @if($isAdmin)
                        <th>Price/SQM</th>
                        <th>Lot Area</th>
                        <th>Discount</th>
                        @endif
                        <th>Net TCP</th>
                        <th>Terms of Payment</th>
                        <th>Mode of Payment</th>
                        <th>Remarks</th>
                        <th>Agent's Name</th>
                        <th>Date Requested</th>
                        <th>Units</th>
                        @if($isAdmin)
                        <th>Commission %</th>
                        <th>Commission</th>
                        @endif
                        <th>Date Released</th>
                        <th>Commission Terms</th>
                        <th>Value of Commission Terms</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="monitoringTableBody">
                    @forelse($commissionRequests as $request)
                    <tr data-status="{{ $request->status }}" data-date="{{ $request->date_requested ? $request->date_requested->format('Y-m') : '' }}">
                        <td>{{ $request->client_name ?? '-' }}</td>
                        <td>{{ $request->reservation_date ? $request->reservation_date->format('M d, Y') : '-' }}</td>
                        <td>{{ $request->project_name ?? '-' }}</td>
                        <td>{{ $request->property_details ?? '-' }}</td>
                        @if($isAdmin)
                        <td>{{ $request->price_sqm ? '₱'.number_format($request->price_sqm, 2) : '-' }}</td>
                        <td>{{ $request->lot_area ? number_format($request->lot_area, 2).' sqm' : '-' }}</td>
                        <td>{{ $request->discount ? '₱'.number_format($request->discount, 2) : '-' }}</td>
                        @endif
                        <td>{{ $request->net_tcp ? '₱'.number_format($request->net_tcp, 2) : '-' }}</td>
                        <td>{{ $request->terms_of_payment ?? '-' }}</td>
                        <td>{{ $request->mode_of_payment ?? '-' }}</td>
                        <td>{{ $request->remarks ? \Illuminate\Support\Str::limit($request->remarks, 30) : '-' }}</td>
                        <td>{{ $request->agent_name ?? '-' }}</td>
                        <td>{{ $request->date_requested ? $request->date_requested->format('M d, Y') : '-' }}</td>
                        <td>{{ $request->number_of_units ?? '-' }}</td>
                        @if($isAdmin)
                        <td>{{ $request->commission_percent ? $request->commission_percent.'%' : '-' }}</td>
                        <td>{{ $request->commission ? '₱'.number_format($request->commission, 2) : '-' }}</td>
                        @endif
                        <td>{{ $request->date_released ? $request->date_released->format('M d, Y') : '-' }}</td>
                        <td>{{ $request->payment_type ?? '-' }}</td>
                        <td>{{ $request->value_of_payment_terms ? '₱'.number_format($request->value_of_payment_terms, 2) : '-' }}</td>
                        <td>
                            <span class="status-badge 
                                @if($request->status == 'Released') status-released
                                @else status-pending
                                @endif">
                                {{ $request->status }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action-text btn-view" title="View" onclick="viewCommission({{ $request->id }})">
                                    VIEW
                                </button>
                                <button class="btn-action-text btn-edit" title="Edit" onclick="requireAdmin(() => editCommission({{ $request->id }}))">
                                    EDIT
                                </button>
                                <form action="{{ route('commission-monitoring.destroy', $request->id) }}" method="POST" style="display: inline-flex; align-items: center;" onsubmit="return requireAdminSync(event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action-text btn-delete" title="Delete">
                                        DELETE
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 19 : 18 }}" style="text-align: center; padding: 40px; color: #6b7280;">
                            No commission requests found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div id="cmNoResults" style="display:none;text-align:center;padding:60px 20px;color:#8a9bad;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:64px;height:64px;margin:0 auto 16px;display:block;opacity:0.35;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div style="font-size:18px;font-weight:600;color:#565651;margin-bottom:6px;">No Results Found</div>
                <div style="font-size:13px;">Try adjusting your search or filter criteria</div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .commission-monitoring-container {
        padding: 0;
    }

    /* Add Form Section */
    .add-commission-section {
        background: white;
        border-radius: 12px;
        padding: 36px 40px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 2px solid #1e4575;
    }
    .section-header-commission h2 {
        font-size: 18px;
        font-weight: 700;
        color: #1e4575;
        margin: 0 0 20px 0;
        letter-spacing: 0.4px;
    }
    .commission-form .form-section { margin-bottom: 0; }
    .section-title-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        font-weight: 700;
        color: #1e4575;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 0;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 20px;
    }
    .section-icon { font-size: 16px; }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label {
        font-size: 11px;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .required { color: #ef4444; }
    .form-group input,
    .form-group select {
        padding: 10px 14px;
        border: 2px solid #d0d5dd;
        border-radius: 8px;
        font-size: 14px;
        color: #374151;
        transition: border-color 0.2s;
        background: white;
    }
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #1e4575;
        box-shadow: 0 0 0 3px rgba(30,69,117,0.1);
    }
    .combobox-wrapper { position: relative; }
    .combobox-input { width: 100%; padding-right: 36px !important; }
    .combobox-arrow {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer; color: #6b7280; font-size: 12px;
    }
    .combobox-dropdown {
        position: absolute; top: 100%; left: 0; right: 0;
        background: white; border: 2px solid #d0d5dd; border-radius: 8px;
        z-index: 100; max-height: 200px; overflow-y: auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .dropdown-item {
        padding: 10px 14px; font-size: 13px; cursor: pointer; color: #374151;
        transition: background 0.15s;
    }
    .dropdown-item:hover { background: #f0f4f8; }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }
    .btn-clear {
        display: flex; align-items: center; gap: 8px;
        padding: 11px 20px; background: white; color: #374151;
        border: 2px solid #d0d5dd; border-radius: 8px;
        font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;
    }
    .btn-clear:hover { background: #f3f4f6; border-color: #9ca3af; }
    .btn-submit {
        display: flex; align-items: center; gap: 8px;
        padding: 11px 24px; background: #1e4575; color: white;
        border: none; border-radius: 8px;
        font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(30,69,117,0.3);
    }
    .btn-submit:hover { background: #152e4d; transform: translateY(-1px); }

    /* Welcome Banner */
    .welcome-banner {
        background: linear-gradient(135deg, #1e4575 0%, #2563eb 60%, #1e4575 100%);
        border-radius: 20px;
        padding: 36px 40px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(30,69,117,.25);
    }

    .welcome-content {
        position: relative;
        z-index: 2;
    }

    .welcome-title {
        font-size: 28px;
        font-weight: 700;
        color: white;
        margin: 0 0 8px 0;
    }

    .welcome-subtitle {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.75);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .icon-sm {
        width: 15px;
        height: 15px;
    }

    .welcome-decoration {
        position: absolute;
        top: 0;
        right: 0;
        width: 300px;
        height: 100%;
        pointer-events: none;
    }

    .decoration-circle {
        position: absolute;
        border-radius: 50%;
        background: rgba(163, 121, 41, 0.2);
    }

    .circle-1 {
        width: 200px;
        height: 200px;
        top: -50px;
        right: -50px;
        animation: float 6s ease-in-out infinite;
    }

    .circle-2 {
        width: 150px;
        height: 150px;
        top: 50px;
        right: 100px;
        animation: float 8s ease-in-out infinite 1s;
    }

    .circle-3 {
        width: 100px;
        height: 100px;
        bottom: -30px;
        right: 50px;
        animation: float 7s ease-in-out infinite 2s;
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s;
        animation: fadeInUp 0.6s ease-out both;
        position: relative;
        overflow: hidden;
        min-height: 110px;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        transition: width 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .stat-card:hover::before {
        width: 100%;
        opacity: 0.05;
    }

    .card-blue::before { background: #1e4575; }
    .card-yellow::before { background: #f59e0b; }
    .card-green::before { background: #10b981; }

    .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .card-blue .stat-icon { background: linear-gradient(135deg, #1e4575, #2563eb); }
    .card-yellow .stat-icon { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .card-green .stat-icon { background: linear-gradient(135deg, #10b981, #34d399); }

    .stat-icon svg {
        width: 26px;
        height: 26px;
        color: white;
    }

    .stat-content {
        flex: 1;
        min-width: 0;
    }

    .stat-label {
        font-size: 13px;
        color: #6b7280;
        font-weight: 600;
        margin-bottom: 4px;
        line-height: 1.3;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #111827;
        line-height: 1.1;
        margin-bottom: 4px;
    }

    .stat-subtitle {
        font-size: 12px;
        color: #9ca3af;
        line-height: 1.3;
    }

    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
    }

    /* Monitoring Table */
    .monitoring-table-container {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 2px solid #1e4575;
        overflow: hidden;
    }

    .table-top-bar {
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e5e7eb;
    }

    .table-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #1e4575;
        margin: 0 0 14px 0;
        letter-spacing: 0.4px;
    }

    .table-filters-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .filter-left {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-left label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
    }

    .filter-select-inline {
        padding: 8px 12px;
        border: 1.5px solid #d0d5dd;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        background: white;
        color: #374151;
        cursor: pointer;
        transition: border-color 0.2s;
        min-width: 130px;
    }

    .filter-select-inline:focus {
        outline: none;
        border-color: #1e4575;
        box-shadow: 0 0 0 3px rgba(30,69,117,0.1);
    }

    .filter-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .search-box-inline {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-box-inline svg {
        position: absolute;
        left: 10px;
        width: 16px;
        height: 16px;
        color: #9ca3af;
        pointer-events: none;
    }

    .search-box-inline input {
        padding: 8px 12px 8px 34px;
        border: 1.5px solid #d0d5dd;
        border-radius: 8px;
        font-size: 13px;
        width: 220px;
        transition: border-color 0.2s;
        color: #374151;
    }

    .search-box-inline input:focus {
        outline: none;
        border-color: #1e4575;
        box-shadow: 0 0 0 3px rgba(30,69,117,0.1);
    }

    .btn-reset-inline {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: #1e4575;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.2s;
        flex-shrink: 0;
    }

    .btn-reset-inline:hover {
        background: #152e4d;
    }

    .table-wrapper {
        overflow-x: auto;
        overflow-y: visible;
    }

    .monitoring-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        min-width: 2200px;
    }

    .monitoring-table thead {
        background: linear-gradient(135deg, #1e4575, #2563eb);
    }

    .monitoring-table th {
        padding: 12px 8px;
        text-align: left;
        font-weight: 600;
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-size: 10px;
        white-space: nowrap;
    }

    .monitoring-table tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: all 0.2s;
    }

    .monitoring-table tbody tr:hover {
        background: #f9fafb;
    }

    .monitoring-table td {
        padding: 12px 8px;
        color: #374151;
        white-space: nowrap;
        font-size: 12px;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
    }

    .status-pending {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-released {
        background: #dcfce7;
        color: #166534;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 6px;
        justify-content: center;
        align-items: center;
        flex-wrap: nowrap;
    }

    .action-buttons form {
        display: inline-flex;
        align-items: center;
        margin: 0;
        padding: 0;
    }

    .btn-action-text {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        width: 60px;
        height: 28px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        line-height: 1;
        box-sizing: border-box;
    }

    .btn-view {
        background: #1e4575;
        color: white;
    }

    .btn-view:hover {
        background: #152e4d;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(30, 69, 117, 0.3);
    }

    .btn-edit {
        background: #f59e0b;
        color: white;
    }

    .btn-edit:hover {
        background: #d97706;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .btn-delete:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
    }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-box {
        background: white;
        border-radius: 16px;
        width: 90%;
        max-width: 700px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: modalIn 0.25s ease-out;
    }

    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.95) translateY(-10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-header {
        background: #1e4575;
        color: white;
        padding: 18px 24px;
        border-radius: 16px 16px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 3px solid #A37929;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: 0.3px;
    }

    .modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .modal-close:hover { background: rgba(255,255,255,0.35); }

    .modal-body { padding: 24px; }

    .modal-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .modal-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .modal-field label {
        font-size: 11px;
        font-weight: 700;
        color: #1e4575;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .modal-field .field-value {
        font-size: 14px;
        color: #374151;
        font-weight: 500;
        padding: 10px 14px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .modal-field input,
    .modal-field select {
        padding: 10px 14px;
        border: 2px solid #d0d5dd;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        transition: border-color 0.2s;
    }

    .modal-field input:focus,
    .modal-field select:focus {
        outline: none;
        border-color: #1e4575;
        box-shadow: 0 0 0 3px rgba(30,69,117,0.1);
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .btn-modal-cancel {
        padding: 10px 20px;
        background: #f3f4f6;
        color: #374151;
        border: 2px solid #d0d5dd;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-modal-cancel:hover { background: #e5e7eb; }

    .btn-modal-save {
        padding: 10px 24px;
        background: #1e4575;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-modal-save:hover { background: #152e4d; }

    /* Responsive */
    @media (max-width: 768px) {
        .filters-section {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            min-width: 100%;
        }

        .filter-group {
            flex-direction: column;
            width: 100%;
        }

        .filter-select,
        .btn-reset {
            width: 100%;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('monitoringSearch');
    const statusFilter = document.getElementById('statusFilter');
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');
    const tableBody = document.getElementById('monitoringTableBody');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value;
        const selectedMonth = monthFilter.value;
        const selectedYear = yearFilter.value;

        // Only real data rows (has data-status attribute)
        const dataRows = Array.from(tableBody.querySelectorAll('tr[data-status]'));

        let visible = 0;

        for (let row of dataRows) {
            const text = row.textContent.toLowerCase();
            const rowStatus = row.getAttribute('data-status');
            const rowDate = row.getAttribute('data-date') || '';
            const [rowYear, rowMonth] = rowDate.split('-');

            const matchesSearch = !searchTerm || text.includes(searchTerm);
            const matchesStatus = !selectedStatus || rowStatus === selectedStatus;
            const matchesMonth = !selectedMonth || rowMonth === selectedMonth;
            const matchesYear = !selectedYear || rowYear === selectedYear;

            if (matchesSearch && matchesStatus && matchesMonth && matchesYear) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        }

        // Show/hide no results message
        const cmNoResults = document.getElementById('cmNoResults');
        if (cmNoResults) {
            cmNoResults.style.display = (visible === 0 && dataRows.length > 0) ? '' : 'none';
        }

        // Update stat cards
        document.getElementById('statTotal').textContent = visible;
        document.getElementById('statNotReleased').textContent =
            dataRows.filter(r => r.style.display !== 'none' && r.getAttribute('data-status') === 'Not Yet Released').length;
        document.getElementById('statReleased').textContent =
            dataRows.filter(r => r.style.display !== 'none' && r.getAttribute('data-status') === 'Released').length;
    }

    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    monthFilter.addEventListener('change', filterTable);
    yearFilter.addEventListener('change', filterTable);
});

function resetFilters() {
    document.getElementById('monitoringSearch').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('monthFilter').value = '';
    document.getElementById('yearFilter').value = '';

    const tableBody = document.getElementById('monitoringTableBody');
    const rows = Array.from(tableBody.getElementsByTagName('tr'))
        .filter(r => !r.id || r.id !== 'noResultsRow');

    let total = 0, notReleased = 0, released = 0;
    for (let row of rows) {
        row.style.display = '';
        if (row.cells.length === 1) continue;
        total++;
        const s = row.getAttribute('data-status');
        if (s === 'Not Yet Released') notReleased++;
        if (s === 'Released') released++;
    }

    const noResultsEl = document.getElementById('cmNoResults');
    if (noResultsEl) noResultsEl.style.display = 'none';

    document.getElementById('statTotal').textContent = total;
    document.getElementById('statNotReleased').textContent = notReleased;
    document.getElementById('statReleased').textContent = released;
}

function viewCommission(id) {
    fetch(`/api/commission-monitoring/${id}`)
        .then(r => r.json())
        .then(data => {
            const fmt = (v) => v ?? '-';
            const fmtMoney = (v) => v ? '₱' + parseFloat(v).toLocaleString('en-PH', {minimumFractionDigits:2}) : '-';
            const fmtDate = (v) => v ? new Date(v).toLocaleDateString('en-US', {month:'short', day:'2-digit', year:'numeric'}) : '-';
            document.getElementById('cm_view_client_name').textContent = fmt(data.client_name);
            document.getElementById('cm_view_reservation_date').textContent = fmtDate(data.reservation_date);
            document.getElementById('cm_view_project_name').textContent = fmt(data.project_name);
            document.getElementById('cm_view_property_details').textContent = fmt(data.property_details);
            document.getElementById('cm_view_price_sqm').textContent = fmtMoney(data.price_sqm);
            document.getElementById('cm_view_lot_area').textContent = data.lot_area ? data.lot_area + ' sqm' : '-';
            document.getElementById('cm_view_discount').textContent = fmtMoney(data.discount);
            document.getElementById('cm_view_net_tcp').textContent = fmtMoney(data.net_tcp);
            document.getElementById('cm_view_terms_of_payment').textContent = fmt(data.terms_of_payment);
            document.getElementById('cm_view_mode_of_payment').textContent = fmt(data.mode_of_payment);
            document.getElementById('cm_view_agent_name').textContent = fmt(data.agent_name);
            document.getElementById('cm_view_date_requested').textContent = fmtDate(data.date_requested);
            document.getElementById('cm_view_number_of_units').textContent = fmt(data.number_of_units);
            document.getElementById('cm_view_commission_percent').textContent = data.commission_percent ? data.commission_percent + '%' : '-';
            document.getElementById('cm_view_commission').textContent = fmtMoney(data.commission);
            document.getElementById('cm_view_date_released').textContent = fmtDate(data.date_released);
            document.getElementById('cm_view_status').textContent = fmt(data.status);
            document.getElementById('cm_view_remarks').textContent = fmt(data.remarks);
            document.getElementById('cmViewModal').classList.add('active');
        });
}

function editCommission(id) {
    fetch(`/api/commission-monitoring/${id}`)
        .then(r => r.json())
        .then(data => {
            const d = (v) => v ? v.split('T')[0] : '';
            document.getElementById('cm_edit_id').value = data.id;
            document.getElementById('cm_edit_client_name').value = data.client_name ?? '';
            document.getElementById('cm_edit_reservation_date').value = d(data.reservation_date);
            document.getElementById('cm_edit_project_name').value = data.project_name ?? '';
            document.getElementById('cm_edit_property_details').value = data.property_details ?? '';
            document.getElementById('cm_edit_price_sqm').value = data.price_sqm ?? '';
            document.getElementById('cm_edit_lot_area').value = data.lot_area ?? '';
            document.getElementById('cm_edit_discount').value = data.discount ?? '';
            document.getElementById('cm_edit_net_tcp').value = data.net_tcp ?? '';
            document.getElementById('cm_edit_net_tcp_display').value = data.net_tcp ? parseFloat(data.net_tcp).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}) : '';
            if (document.getElementById('cm_edit_payment_type')) document.getElementById('cm_edit_payment_type').value = data.payment_type ?? '';
            if (document.getElementById('cm_edit_value_of_payment_terms')) document.getElementById('cm_edit_value_of_payment_terms').value = data.value_of_payment_terms ?? '';
            if (document.getElementById('cm_edit_vopt_display')) document.getElementById('cm_edit_vopt_display').value = data.value_of_payment_terms ? parseFloat(data.value_of_payment_terms).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}) : '';
            document.getElementById('cm_edit_terms_of_payment').value = data.terms_of_payment ?? '';
            document.getElementById('cm_edit_mode_of_payment').value = data.mode_of_payment ?? '';
            document.getElementById('cm_edit_agent_name').value = data.agent_name ?? '';
            document.getElementById('cm_edit_date_requested').value = d(data.date_requested);
            document.getElementById('cm_edit_number_of_units').value = data.number_of_units ?? '';
            document.getElementById('cm_edit_commission_percent').value = data.commission_percent ?? '';
            document.getElementById('cm_edit_commission').value = data.commission ?? '';
            document.getElementById('cm_edit_date_released').value = d(data.date_released);
            document.getElementById('cm_edit_status').value = data.status ?? 'Not Yet Released';
            document.getElementById('cm_edit_remarks').value = data.remarks ?? '';
            document.getElementById('cmEditModal').classList.add('active');
        });
}

function computeCommission() {
    const netTcp = parseFloat(document.getElementById('cm_edit_net_tcp').value) || 0;
    const pct    = parseFloat(document.getElementById('cm_edit_commission_percent').value) || 0;
    const result = netTcp * (pct / 100);
    document.getElementById('cm_edit_commission').value = result > 0 ? Math.round(result) : '';
}

function closeCmModal(id) {
    document.getElementById(id).classList.remove('active');
}

function fmtComma(n) { return n.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}); }

function computeAddTCP() {
    const priceSqm = parseFloat(document.getElementById('cm_add_price_sqm').value) || 0;
    const lotArea  = parseFloat(document.getElementById('cm_add_lot_area').value) || 0;
    const tcp      = priceSqm * lotArea;
    const discPct  = parseFloat(document.getElementById('cm_add_discount').value) || 0;
    const netTcp   = tcp - (tcp * discPct / 100);
    document.getElementById('cm_add_net_tcp').value = netTcp > 0 ? netTcp.toFixed(2) : '';
    document.getElementById('cm_add_net_tcp_display').value = netTcp > 0 ? fmtComma(netTcp) : '';
    computeAddCommission();
    computeValueOfPaymentTerms();
}
function setAddDiscountType(type) {
    document.getElementById('cm_add_discount_type').value = type;
    const input = document.getElementById('cm_add_discount');
    if (type === 'percent') {
        input.max = 100;
        input.placeholder = '0.00';
        document.getElementById('cm_add_disc_pct_btn').style.background = '#1e457c';
        document.getElementById('cm_add_disc_pct_btn').style.color = '#fff';
        document.getElementById('cm_add_disc_val_btn').style.background = '#fff';
        document.getElementById('cm_add_disc_val_btn').style.color = '#374151';
    } else {
        input.removeAttribute('max');
        input.placeholder = '0.00';
        document.getElementById('cm_add_disc_val_btn').style.background = '#1e457c';
        document.getElementById('cm_add_disc_val_btn').style.color = '#fff';
        document.getElementById('cm_add_disc_pct_btn').style.background = '#fff';
        document.getElementById('cm_add_disc_pct_btn').style.color = '#374151';
    }
    computeAddNetTCP();
}

function computeAddNetTCP() {
    const priceSqm   = parseFloat(document.getElementById('cm_add_price_sqm').value) || 0;
    const lotArea    = parseFloat(document.getElementById('cm_add_lot_area').value) || 0;
    const tcp        = priceSqm * lotArea;
    const discType   = document.getElementById('cm_add_discount_type')?.value || 'percent';
    const input      = document.getElementById('cm_add_discount');
    let discVal      = parseFloat(input.value) || 0;
    if (discType === 'percent') {
        if (discVal > 100) { discVal = 100; input.value = 100; }
        if (discVal < 0)   { discVal = 0;   input.value = 0; }
    }
    const discAmount = discType === 'percent' ? (tcp * discVal / 100) : discVal;
    const netTcp     = tcp - discAmount;
    document.getElementById('cm_add_net_tcp').value = netTcp > 0 ? netTcp.toFixed(2) : '';
    document.getElementById('cm_add_net_tcp_display').value = netTcp > 0 ? fmtComma(netTcp) : '';
    computeAddCommission();
    computeValueOfPaymentTerms();
}
function computeValueOfPaymentTerms() {
    const netTcp = parseFloat(document.getElementById('cm_add_net_tcp').value) || 0;
    const type   = document.getElementById('cm_add_payment_type')?.value || '';
    const fullPayment = netTcp * 0.08;
    let result = 0;
    if (type === 'Full Payment')         result = fullPayment;
    if (type === '2 Months Commission')  result = fullPayment / 2;
    if (type === '3 Months Commission')  result = fullPayment / 3;
    document.getElementById('cm_add_value_of_payment_terms').value = result > 0 ? result.toFixed(2) : '';
    document.getElementById('cm_add_vopt_display').value = result > 0 ? fmtComma(result) : '';
}

function setEditDiscountType(type) {
    document.getElementById('cm_edit_discount_type').value = type;
    const input = document.getElementById('cm_edit_discount');
    if (type === 'percent') {
        input.max = 100;
        document.getElementById('cm_edit_disc_pct_btn').style.background = '#1e457c';
        document.getElementById('cm_edit_disc_pct_btn').style.color = '#fff';
        document.getElementById('cm_edit_disc_val_btn').style.background = '#fff';
        document.getElementById('cm_edit_disc_val_btn').style.color = '#374151';
    } else {
        input.removeAttribute('max');
        document.getElementById('cm_edit_disc_val_btn').style.background = '#1e457c';
        document.getElementById('cm_edit_disc_val_btn').style.color = '#fff';
        document.getElementById('cm_edit_disc_pct_btn').style.background = '#fff';
        document.getElementById('cm_edit_disc_pct_btn').style.color = '#374151';
    }
    computeEditNetTCP();
}

function computeEditNetTCP() {
    const priceSqm = parseFloat(document.getElementById('cm_edit_price_sqm').value) || 0;
    const lotArea  = parseFloat(document.getElementById('cm_edit_lot_area').value) || 0;
    const tcp      = priceSqm * lotArea;
    const discType = document.getElementById('cm_edit_discount_type')?.value || 'percent';
    const input    = document.getElementById('cm_edit_discount');
    let discVal    = parseFloat(input.value) || 0;
    if (discType === 'percent') {
        if (discVal > 100) { discVal = 100; input.value = 100; }
        if (discVal < 0)   { discVal = 0;   input.value = 0; }
    }
    const discAmount = discType === 'percent' ? (tcp * discVal / 100) : discVal;
    const netTcp     = tcp - discAmount;
    const display    = document.getElementById('cm_edit_net_tcp_display');
    const hidden     = document.getElementById('cm_edit_net_tcp');
    if (display) display.value = netTcp > 0 ? netTcp.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}) : '';
    if (hidden)  hidden.value  = netTcp > 0 ? netTcp.toFixed(2) : '';
    computeEditValueOfPaymentTerms();
}

function computeEditValueOfPaymentTerms() {
    const netTcp = parseFloat(document.getElementById('cm_edit_net_tcp')?.value) || 0;
    const type   = document.getElementById('cm_edit_payment_type')?.value || '';
    const fullPayment = netTcp * 0.08;
    let result = 0;
    if (type === 'Full Payment')         result = fullPayment;
    if (type === '2 Months Commission')  result = fullPayment / 2;
    if (type === '3 Months Commission')  result = fullPayment / 3;
    const vEl = document.getElementById('cm_edit_value_of_payment_terms');
    const dEl = document.getElementById('cm_edit_vopt_display');
    if (vEl) vEl.value = result > 0 ? result.toFixed(2) : '';
    if (dEl) dEl.value = result > 0 ? fmtComma(result) : '';
}
function computeAddCommission() {
    const netTcp = parseFloat(document.getElementById('cm_add_net_tcp').value) || 0;
    const pctEl  = document.getElementById('cm_add_commission_percent');
    const pct    = pctEl ? (parseFloat(pctEl.value) || 0) : 0;
    const result = netTcp * (pct / 100);
    document.getElementById('cm_add_commission').value = result > 0 ? result.toFixed(2) : '';
    const display = document.getElementById('cm_add_commission_display');
    if (display) display.value = result > 0 ? fmtComma(result) : '0.00';
    computeValueOfPaymentTerms();
}
function computeAddCommissionFromValue() {
    const netTcp = parseFloat(document.getElementById('cm_add_net_tcp').value) || 0;
    const rawVal = (document.getElementById('cm_add_commission_display').value || '').replace(/,/g, '');
    const val    = parseFloat(rawVal) || 0;
    const pct    = netTcp > 0 ? (val / netTcp) * 100 : 0;
    document.getElementById('cm_add_commission').value = val > 0 ? val.toFixed(2) : '';
    const pctEl = document.getElementById('cm_add_commission_percent');
    if (pctEl) pctEl.value = pct > 0 ? pct.toFixed(4).replace(/\.?0+$/, '') : '';
}
function computeAddCommissionFromValue() {
    const netTcp = parseFloat(document.getElementById('cm_add_net_tcp').value) || 0;
    const val    = parseFloat(document.getElementById('cm_add_commission_display').value) || 0;
    const pct    = netTcp > 0 ? (val / netTcp) * 100 : 0;
    document.getElementById('cm_add_commission').value = val > 0 ? val.toFixed(2) : '';
    const pctEl = document.getElementById('cm_add_commission_percent');
    if (pctEl) pctEl.value = pct > 0 ? pct.toFixed(4).replace(/\.?0+$/, '') : '';
}

// Ensure commission is computed before form submit
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cmAddForm');
    if (form) {
        form.addEventListener('submit', function() {
            computeAddCommission();
        });
    }
});

function toggleCmTermsDropdown() {
    const dd = document.getElementById('cmTermsDropdown');
    dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
}
function selectCmTerm(val) {
    document.getElementById('cm_add_terms').value = val;
    document.getElementById('cmTermsDropdown').style.display = 'none';
}
function filterCmTerms(val) {
    const items = document.querySelectorAll('#cmTermsDropdown .dropdown-item');
    let hasVisible = false;
    items.forEach(item => {
        const match = item.textContent.toLowerCase().includes(val.toLowerCase());
        item.style.display = match ? '' : 'none';
        if (match) hasVisible = true;
    });
    document.getElementById('cmTermsDropdown').style.display = hasVisible ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('cm_add_terms')?.closest('.combobox-wrapper');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('cmTermsDropdown').style.display = 'none';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });
    });

    document.getElementById('cmEditForm').addEventListener('submit', function() {
        const id = document.getElementById('cm_edit_id').value;
        this.action = `/commission-monitoring/${id}`;
        showToast('Saving changes...', 'info');
    });
});
</script>

<script>
const IS_ADMIN = {{ (auth()->check() && auth()->user()->isAdmin()) ? 'true' : 'false' }};

let _cmPermAction = 'edit', _cmPermRecordId = null;

function requireAdmin(callback, recordId, action) {
    if (IS_ADMIN) { callback(); return; }
    // Check if already approved
    fetch(`/api/permission-requests/check?action=${action || 'edit'}&record_id=${recordId}`)
        .then(r => r.json())
        .then(data => {
            if (data.approved) {
                if (callback) callback();
            } else {
                _cmPermAction = action || 'edit';
                _cmPermRecordId = recordId || null;
                document.getElementById('cmPermTitle').textContent = 'Request to ' + (_cmPermAction.charAt(0).toUpperCase() + _cmPermAction.slice(1)) + ' Record';
                document.getElementById('cmPermReason').value = '';
                document.getElementById('cmPermError').style.display = 'none';
                document.getElementById('permissionModal').classList.add('active');
                setTimeout(() => document.getElementById('cmPermReason').focus(), 100);
            }
        });
}

function requireAdminSync(event, recordId) {
    if (!IS_ADMIN) {
        event.preventDefault();
        requireAdmin(null, recordId, 'delete');
        return false;
    }
    return confirm('Are you sure you want to delete this commission request?');
}

function closeCmPermModal() {
    document.getElementById('permissionModal').classList.remove('active');
}

function submitCmPermRequest() {
    const reason = document.getElementById('cmPermReason').value.trim();
    if (reason.length < 5) { document.getElementById('cmPermError').style.display = 'block'; return; }
    document.getElementById('cmPermError').style.display = 'none';
    const btn = document.getElementById('cmPermBtn');
    btn.disabled = true; btn.textContent = 'Sending...';
    fetch('/api/permission-requests', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ action: _cmPermAction, module: 'Commission Monitoring', record_id: _cmPermRecordId, record_label: 'Record #' + _cmPermRecordId, reason })
    })
    .then(r => r.json())
    .then(() => {
        closeCmPermModal();
        btn.disabled = false; btn.textContent = 'Send Request';
        if (typeof showToast === 'function') showToast('Your request has been sent to admin for approval.', 'success', 'Request Sent');
        if (typeof pollNotifications === 'function') pollNotifications();
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Send Request'; });
}
</script>

<!-- Permission Modal -->
<div id="permissionModal" class="modal-overlay" onclick="if(event.target===this)closeCmPermModal()">
    <div class="modal-box" style="max-width:460px;padding:0;overflow:hidden;">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);padding:18px 22px;display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div style="flex:1;">
                <div style="color:rgba(255,255,255,.7);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;">Permission Required</div>
                <div id="cmPermTitle" style="color:white;font-size:15px;font-weight:700;margin-top:1px;">Request to Edit Record</div>
            </div>
            <button onclick="closeCmPermModal()" style="background:rgba(255,255,255,.15);border:none;color:white;width:28px;height:28px;border-radius:6px;cursor:pointer;font-size:18px;line-height:1;">&times;</button>
        </div>
        <div style="padding:20px 22px;">
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Reason for Request <span style="color:#dc2626;">*</span></label>
                <textarea id="cmPermReason" rows="4" placeholder="Please explain why you need to perform this action..." style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-family:inherit;resize:none;outline:none;box-sizing:border-box;" onfocus="this.style.borderColor='#1e4575'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
                <div id="cmPermError" style="color:#dc2626;font-size:11px;margin-top:4px;display:none;">Please provide a reason (at least 5 characters).</div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button onclick="closeCmPermModal()" style="padding:9px 18px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;">Cancel</button>
                <button onclick="submitCmPermRequest()" id="cmPermBtn" style="padding:9px 20px;background:#1e4575;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Send Request</button>
            </div>
        </div>
    </div>
</div>

<!-- VIEW Modal -->
<div id="cmViewModal" class="modal-overlay">
    <div class="modal-box" style="max-width:800px;">
        <div class="modal-header">
            <h3>Commission Request Details</h3>
            <button class="modal-close" onclick="closeCmModal('cmViewModal')">✖</button>
        </div>
        <div class="modal-body">
            <div class="modal-grid">
                <div class="modal-field"><label>Client's Name</label><div class="field-value" id="cm_view_client_name">-</div></div>
                <div class="modal-field"><label>Reservation Date</label><div class="field-value" id="cm_view_reservation_date">-</div></div>
                <div class="modal-field"><label>Project Name</label><div class="field-value" id="cm_view_project_name">-</div></div>
                <div class="modal-field"><label>Property Details (Block & Lot No.)</label><div class="field-value" id="cm_view_property_details">-</div></div>
                <div class="modal-field"><label>Price / SQM</label><div class="field-value" id="cm_view_price_sqm">-</div></div>
                <div class="modal-field"><label>Lot Area</label><div class="field-value" id="cm_view_lot_area">-</div></div>
                <div class="modal-field"><label>Discount</label><div class="field-value" id="cm_view_discount">-</div></div>
                <div class="modal-field"><label>Net TCP</label><div class="field-value" id="cm_view_net_tcp">-</div></div>
                <div class="modal-field"><label>Terms of Payment</label><div class="field-value" id="cm_view_terms_of_payment">-</div></div>
                <div class="modal-field"><label>Mode of Payment</label><div class="field-value" id="cm_view_mode_of_payment">-</div></div>
                <div class="modal-field"><label>Agent's Name</label><div class="field-value" id="cm_view_agent_name">-</div></div>
                <div class="modal-field"><label>Date Requested</label><div class="field-value" id="cm_view_date_requested">-</div></div>
                <div class="modal-field"><label>Number of Units</label><div class="field-value" id="cm_view_number_of_units">-</div></div>
                <div class="modal-field"><label>Commission %</label><div class="field-value" id="cm_view_commission_percent">-</div></div>
                <div class="modal-field"><label>Commission</label><div class="field-value" id="cm_view_commission">-</div></div>
                <div class="modal-field"><label>Date Released</label><div class="field-value" id="cm_view_date_released">-</div></div>
                <div class="modal-field"><label>Status</label><div class="field-value" id="cm_view_status">-</div></div>
                <div class="modal-field" style="grid-column:1/-1;"><label>Remarks</label><div class="field-value" id="cm_view_remarks">-</div></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-modal-cancel" onclick="closeCmModal('cmViewModal')">Close</button>
        </div>
    </div>
</div>

<!-- EDIT Modal -->
<div id="cmEditModal" class="modal-overlay">
    <div class="modal-box" style="max-width:800px;">
        <div class="modal-header">
            <h3>Edit Commission Request</h3>
            <button class="modal-close" onclick="closeCmModal('cmEditModal')">✖</button>
        </div>
        <form id="cmEditForm" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" id="cm_edit_id" name="id">
            <div class="modal-body">
                <div class="modal-grid">
                    <div class="modal-field">
                        <label>Client's Name <span style="color:#ef4444">*</span></label>
                        <input type="text" id="cm_edit_client_name" name="client_name" required>
                    </div>
                    <div class="modal-field">
                        <label>Reservation Date</label>
                        <input type="date" id="cm_edit_reservation_date" name="reservation_date">
                    </div>
                    <div class="modal-field">
                        <label>Project Name <span style="color:#ef4444">*</span></label>
                        <input type="text" id="cm_edit_project_name" name="project_name" required>
                    </div>
                    <div class="modal-field">
                        <label>Property Details (Block & Lot No.) <span style="color:#ef4444">*</span></label>
                        <input type="text" id="cm_edit_property_details" name="property_details" placeholder="e.g., Block 3 Lot 12, Tower A" required>
                    </div>
                    <div class="modal-field">
                        <label>Price / SQM</label>
                        <input type="number" id="cm_edit_price_sqm" name="price_sqm" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="modal-field">
                        <label>Lot Area</label>
                        <input type="number" id="cm_edit_lot_area" name="lot_area" step="0.0001" min="0" placeholder="0.0000">
                    </div>
                    <div class="modal-field">
                        <label style="display:flex;align-items:center;gap:8px;">
                            Discount
                            <span style="display:inline-flex;border:1px solid #d1d5db;border-radius:6px;overflow:hidden;font-size:11px;font-weight:700;">
                                <button type="button" id="cm_edit_disc_pct_btn" onclick="setEditDiscountType('percent')" style="padding:2px 10px;background:#1e457c;color:#fff;border:none;cursor:pointer;">%</button>
                                <button type="button" id="cm_edit_disc_val_btn" onclick="setEditDiscountType('value')" style="padding:2px 10px;background:#fff;color:#374151;border:none;cursor:pointer;">VALUE</button>
                            </span>
                        </label>
                        <input type="number" id="cm_edit_discount" name="discount" step="0.01" min="0" max="100" placeholder="0.00" oninput="computeEditNetTCP()">
                        <input type="hidden" id="cm_edit_discount_type" name="discount_type" value="percent">
                    </div>
                    <div class="modal-field">
                        <label>Net TCP <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                        <input type="text" id="cm_edit_net_tcp_display" placeholder="0.00" readonly style="background:#f3f4f6;cursor:not-allowed;color:#374151;">
                        <input type="hidden" id="cm_edit_net_tcp" name="net_tcp">
                    </div>
                    <div class="modal-field">
                        <label>% of Commission</label>
                        <input type="number" id="cm_edit_commission_percent" name="commission_percent" step="0.0001" min="0" max="100" placeholder="e.g. 5" oninput="computeCommission()">
                    </div>
                    <div class="modal-field">
                        <label>Commission (Auto-computed)</label>
                        <input type="number" id="cm_edit_commission" name="commission" step="1" placeholder="0.00" style="background:#fff;">
                    </div>
                    <div class="modal-field">
                        <label>Commission Terms</label>
                        <select id="cm_edit_payment_type" name="payment_type" onchange="computeEditValueOfPaymentTerms()">
                            <option value="">— Select —</option>
                            <option value="Full Payment">Full Payment</option>
                            <option value="2 Months Commission">2 Months Commission</option>
                            <option value="3 Months Commission">3 Months Commission</option>
                        </select>
                    </div>
                    <div class="modal-field">
                        <label>Value of Commission Terms <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                        <input type="text" id="cm_edit_vopt_display" placeholder="0.00" readonly style="background:#f3f4f6;cursor:not-allowed;color:#374151;">
                        <input type="hidden" id="cm_edit_value_of_payment_terms" name="value_of_payment_terms">
                    </div>
                    <div class="modal-field">
                        <label>Terms of Payment <span style="color:#ef4444">*</span></label>
                        <input type="text" id="cm_edit_terms_of_payment" name="terms_of_payment" required>
                    </div>
                    <div class="modal-field">
                        <label>Mode of Payment</label>
                        <select id="cm_edit_mode_of_payment" name="mode_of_payment">
                            <option value="">Select mode</option>
                            <option value="BANK DEPOSIT">BANK DEPOSIT</option>
                            <option value="BANK TRANSFER">BANK TRANSFER</option>
                            <option value="CASH PAYMENT">CASH PAYMENT</option>
                            <option value="MANAGER'S CHECK">MANAGER'S CHECK</option>
                            <option value="PERSONAL CHECK">PERSONAL CHECK</option>
                            <option value="POST-DATED CHECK">POST-DATED CHECK</option>
                        </select>
                    </div>
                    <div class="modal-field">
                        <label>Agent's Name <span style="color:#ef4444">*</span></label>
                        <input type="text" id="cm_edit_agent_name" name="agent_name" required>
                    </div>
                    <div class="modal-field">
                        <label>Date Requested <span style="color:#ef4444">*</span></label>
                        <input type="date" id="cm_edit_date_requested" name="date_requested" required>
                    </div>
                    <div class="modal-field">
                        <label>Number of Units <span style="color:#ef4444">*</span></label>
                        <input type="number" id="cm_edit_number_of_units" name="number_of_units" min="1" required>
                    </div>
                    <div class="modal-field">
                        <label>Date Released</label>
                        <input type="date" id="cm_edit_date_released" name="date_released">
                    </div>
                    <div class="modal-field">
                        <label>Status <span style="color:#ef4444">*</span></label>
                        <select id="cm_edit_status" name="status" required>
                            <option value="Not Yet Released">Not Yet Released</option>
                            <option value="Released">Released</option>
                        </select>
                    </div>
                    <div class="modal-field" style="grid-column:1/-1;">
                        <label>Remarks</label>
                        <textarea id="cm_edit_remarks" name="remarks" rows="3" style="width:100%;padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;resize:vertical;font-family:inherit;transition:border-color 0.2s;"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeCmModal('cmEditModal')">Cancel</button>
                <button type="submit" class="btn-modal-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@endsection

<script>
// ── Prefill form from trip_done / client_done notification ──
(function() {
    const params = new URLSearchParams(window.location.search);
    const client    = params.get('prefill_client');
    const project   = params.get('prefill_project');
    const agent     = params.get('prefill_agent');
    const date      = params.get('prefill_date');
    const netTcp    = params.get('prefill_net_tcp');
    const resDate   = params.get('prefill_reservation');
    const terms     = params.get('prefill_terms');
    const units     = params.get('prefill_units');
    const commPct   = params.get('prefill_commission_pct');
    const developer = params.get('prefill_developer');
    const blockLot  = params.get('prefill_block_lot');
    const priceSqm  = params.get('prefill_price_sqm');
    const lotArea   = params.get('prefill_lot_area');
    const discount  = params.get('prefill_discount');
    const mop       = params.get('prefill_mode_of_payment');

    if (!client && !project) return;

    document.addEventListener('DOMContentLoaded', function() {
        const set = (name, val) => {
            const el = document.querySelector('[name="' + name + '"]');
            if (el && val) el.value = val;
        };
        set('client_name',        client);
        set('project_name',       project);
        set('agent_name',         agent);
        set('date_requested',     date);
        set('net_tcp',            netTcp);
        set('reservation_date',   resDate);
        set('terms_of_payment',   terms);
        set('number_of_units',    units);
        set('commission_percent', commPct);
        set('property_details',   blockLot);
        set('price_sqm',          priceSqm);
        set('lot_area',           lotArea);
        set('discount',           discount);
        set('mode_of_payment',    mop);

        // Scroll to and highlight the form
        const form = document.getElementById('cmAddForm');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            form.style.transition = 'box-shadow .4s';
            form.style.boxShadow  = '0 0 0 3px #2563eb, 0 8px 32px rgba(37,99,235,.2)';
            setTimeout(() => { form.style.boxShadow = ''; }, 2500);
        }

        // Show a small toast
        const toast = document.createElement('div');
        toast.textContent = '✔ Form pre-filled from client database';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#1e4575;color:white;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.2);animation:fadeIn .3s ease';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);

        // Clean URL
        const clean = window.location.pathname;
        window.history.replaceState({}, '', clean);
    });
})();
</script>
