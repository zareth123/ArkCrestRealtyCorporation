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
        <div class="stat-card card-blue" onclick="filterByStat('')" style="cursor:pointer;" title="Click to view all requests">
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

        <div class="stat-card card-yellow" onclick="filterByStat('Not Yet Released')" style="cursor:pointer;" title="Click to view Not Yet Released requests">
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

        <div class="stat-card card-green" onclick="filterByStat('Released')" style="cursor:pointer;" title="Click to view Released requests">
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
        <form id="cmAddForm" class="commission-form" action="{{ route('commission-monitoring.store') }}" method="POST" onsubmit="return previewCommissionSubmit(event)">
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
                        <label>RESERVATION DATE <span class="required">*</span></label>
                        <input type="date" name="reservation_date" required>
                    </div>
                    <div class="form-group">
                        <label>PROJECT NAME <span class="required">*</span></label>
                        <input type="text" name="project_name" placeholder="Enter project name" required>
                    </div>
                    <div class="form-group">
                        <label>PROPERTY DETAILS (BLOCK & LOT NO.) <span class="required">*</span></label>
                        <input type="text" name="property_details" placeholder="e.g., Block 3 Lot 12, Tower A" required>
                    </div>
                    <div class="form-group">
                        <label>PRICE / SQM <span class="required">*</span></label>
                        <input type="number" id="cm_add_price_sqm" name="price_sqm" placeholder="0.00" step="0.01" min="0" oninput="computeAddTCP()" required>
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
                        <label>% OF COMMISSION <span class="required">*</span></label>
                        <input type="number" id="cm_add_commission_percent" name="commission_percent" placeholder="e.g. 5" step="0.0001" min="0" max="100" oninput="computeAddCommission()" required>
                    </div>
                    @endif
                    <div class="form-group">
                        <label>VALUE OF COMMISSION <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                        <input type="text" id="cm_add_commission_display" placeholder="0.00" oninput="computeAddCommissionFromValue()" style="color:#374151;">
                    </div>
                    <div class="form-group">
                        <label>COMMISSION TERMS <span class="required">*</span></label>
                        <div class="select-wrapper">
                            <select id="cm_add_payment_type" name="payment_type" onchange="computeValueOfPaymentTerms()" required>
                                <option value="">— Select —</option>
                                <option value="Full Payment">Full Payment</option>
                                <option value="2 Months Commission">2 Months Commission</option>
                                <option value="3 Months Commission">3 Months Commission</option>
                            </select>
                            <span class="select-arrow">▼</span>
                        </div>
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
                        <select name="mode_of_payment" id="cm_add_mode_of_payment" onchange="calcCmDateReleased('cm_add')" required>
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
                        <input type="date" name="date_requested" id="cm_add_date_requested" onchange="calcCmDateReleased('cm_add')" required>
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
                        <input type="date" name="date_released" id="cm_add_date_released" readonly style="background:#f3f4f6;cursor:not-allowed;color:#374151;">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>REMARKS</label>
                        <textarea name="remarks" placeholder="Enter any remarks or notes..." rows="3" style="width:100%;padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;resize:vertical;font-family:inherit;color:#374151;"></textarea>
                    </div>
                </div>
            </div>
            <input type="hidden" name="commission" id="cm_add_commission" value="">
            <div class="form-actions">
                <button type="button" class="btn-clear" onclick="clearCmAddForm()">
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
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                <h3 class="table-section-title" style="margin:0;">ALL COMMISSION REQUESTS</h3>
                @if($isAdmin)
                <div style="display:flex;gap:8px;">
                    <button type="button" id="cmSelectModeBtn" class="clear-dates-btn" onclick="cmToggleSelectMode()">Select</button>
                    <button type="button" id="cmDeleteSelectedBtn" class="clear-dates-btn" style="background:#fee2e2;color:#dc2626;border-color:#fecaca;display:none;" onclick="cmDeleteSelected()">
                        Delete Selected (<span id="cmSelectedCount">0</span>)
                    </button>
                </div>
                @endif
            </div>

            <div class="expenses-filters-bar">
                <div class="expenses-filters-row">
                    <div class="expenses-search-wrapper">
            
                        <div class="column-filter-dropdown" id="columnFilterDropdown">
                            <button type="button" id="columnFilterBtn" class="column-filter-btn" onclick="toggleColumnFilterMenu(event)">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                                <span>Filter</span>
                                <span id="filterCountBadge" class="filter-count-badge" style="display:none;">0</span>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div id="columnFilterMenu" class="column-filter-menu" style="display:none;"></div>
                        </div>
                        <div class="search-box-inline" style="width:100%;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" id="monitoringSearch" placeholder="Search requests..." style="width:100%;">
                        </div>
                    </div>
                </div>
                <div id="activeColumnFiltersRow" class="active-column-filters-row" style="display:none;"></div>
            </div>
        </div>

        <div class="table-scroll-hint">⟵ Swipe left/right to see more columns ⟶</div>
        <div class="table-wrapper">
            <table class="monitoring-table{{ $isAdmin ? '' : ' no-checkbox' }}">
                <thead>
                    <tr>
                        @if($isAdmin)
                        <th class="col-sticky col-sticky-check"><input type="checkbox" id="cmSelectAll" onchange="cmToggleSelectAll(this)"></th>
                        @endif
                        <th class="col-sticky col-sticky-index">#</th>
                        <th class="col-sticky col-sticky-name">Client's Name</th>
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
                    @php
                        $isOverdue = $request->status !== 'Released' && $request->date_requested && $request->date_requested->diffInDays(now()) >= 7;
                        $isRecent = $request->updated_at && $request->created_at && !$request->updated_at->eq($request->created_at) && $request->updated_at->diffInHours(now()) <= 48;
                        $isHighValue = ($request->net_tcp ?? 0) >= 500000;
                        $rowHlClasses = trim(($isOverdue ? 'cm-row-overdue ' : '') . ($isHighValue ? 'cm-row-highvalue ' : ''));
                    @endphp
                    <tr id="cm-{{ $request->id }}" class="{{ $rowHlClasses }}" data-id="{{ $request->id }}"
                        data-status="{{ $request->status }}"
                        data-date-requested="{{ $request->date_requested ? $request->date_requested->format('Y-m-d') : '' }}"
                        data-date-released="{{ $request->date_released ? $request->date_released->format('Y-m-d') : '' }}"
                        data-client="{{ $request->client_name }}"
                        data-reservation-date="{{ $request->reservation_date ? $request->reservation_date->format('Y-m-d') : '' }}"
                        data-project="{{ $request->project_name }}"
                        data-property="{{ $request->property_details }}"
                        @if($isAdmin)
                        data-price-sqm="{{ $request->price_sqm }}"
                        data-lot-area="{{ $request->lot_area }}"
                        data-discount="{{ $request->discount }}"
                        data-commission-percent="{{ $request->commission_percent }}"
                        data-commission="{{ $request->commission }}"
                        @endif
                        data-net-tcp="{{ $request->net_tcp }}"
                        data-terms="{{ $request->terms_of_payment }}"
                        data-mode="{{ $request->mode_of_payment }}"
                        data-remarks="{{ $request->remarks }}"
                        data-units="{{ $request->number_of_units }}"
                        data-commission-terms="{{ $request->payment_type }}"
                        data-value-commission-terms="{{ $request->value_of_payment_terms }}"
                        data-agent="{{ $request->agent_name }}">
                        @if($isAdmin)
                        <td class="col-sticky col-sticky-check"><input type="checkbox" class="cm-row-check" value="{{ $request->id }}" onchange="cmUpdateSelectedCount()"></td>
                        @endif
                        <td class="col-sticky col-sticky-index">{{ $loop->iteration }}</td>
                        <td class="col-sticky col-sticky-name">{{ $request->client_name ?? '-' }}</td>
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
                            @if($isOverdue || $isRecent || $isHighValue)
                            <div class="cm-highlight-badges">
                                @if($isOverdue)<span class="cm-hl-badge cm-hl-overdue">⚠ Overdue</span>@endif
                                @if($isRecent)<span class="cm-hl-badge cm-hl-recent">● Updated</span>@endif
                                @if($isHighValue)<span class="cm-hl-badge cm-hl-highvalue">★ High Value</span>@endif
                            </div>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action-text btn-view" title="View" onclick="viewCommission({{ $request->id }})">
                                    VIEW
                                </button>
                                <button class="btn-action-text btn-edit" title="Edit" onclick="requireAdmin(() => editCommission({{ $request->id }}))">
                                    EDIT
                                </button>
                                @if($isAdmin)
                                <form action="{{ route('commission-monitoring.destroy', $request->id) }}" method="POST" style="display: inline-flex; align-items: center;" onsubmit="return confirm('Are you sure you want to delete this commission request? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action-text btn-delete" title="Delete">
                                        DELETE
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('commission-monitoring.destroy', $request->id) }}" method="POST" style="display: inline-flex; align-items: center;" onsubmit="return staffDeleteCommission(event, {{ $request->id }})">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action-text btn-delete" title="Delete">
                                        DELETE
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 21 : 19 }}" style="text-align: center; padding: 40px; color: #6b7280;">
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
    .col-sticky {
        position: sticky;
        background: white;
        z-index: 2;
        box-sizing: border-box;
        /* left is set dynamically by JS (cmUpdateStickyOffsets) */
    }
    .monitoring-table thead .col-sticky { background: #1e4575; z-index: 5; }

    /* Sticky header row (vertical scroll) — mirrors the sticky index/checkbox
    columns above (horizontal scroll), so both axes stay anchored together. */
    .monitoring-table thead th { position: sticky; top: 0; background: #1e4575; z-index: 4; box-shadow: 0 2px 4px -2px rgba(0,0,0,.25); }
    .col-sticky-check {
        width: 40px; min-width: 40px; max-width: 40px;
        text-align: center; padding: 12px 4px !important;
    }
    .col-sticky-index {
        width: 50px; min-width: 50px; max-width: 50px;
        text-align: center; font-weight: 600; color: #6b7280;
        padding: 12px 4px !important;
    }
    .col-sticky-name {
        min-width: 160px;
        box-shadow: 4px 0 6px -2px rgba(0,0,0,0.08);
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

    .select-wrapper { position: relative; }
    .select-wrapper select {
        width: 100%;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        padding-right: 36px !important;
    }
    .select-wrapper .select-arrow {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: #6b7280;
        font-size: 11px;
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

    /* ---- Date Requested / Date Released range filters + column Filter dropdown
       (matches the "All Expenses" filter pattern) ---- */
    .expenses-filters-bar {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .expenses-filters-row {
        display: flex;
        justify-content: flex-start;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 12px;
    }
    .expenses-date-filters {
        display: flex;
        gap: 20px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    .date-range-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .date-range-group label {
        font-weight: 600;
        color: #1e4575;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .3px;
    }
    .date-range-inputs {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .date-range-inputs input[type="date"] {
        font-size: 13px;
        padding: 7px 10px;
        border: 1.5px solid #d0d5dd;
        border-radius: 6px;
        background-color: white;
        color: #344054;
    }
    .date-range-to {
        color: #8a9bad;
        font-size: 12px;
    }
    .clear-dates-btn {
        font-size: 12px;
        font-weight: 600;
        color: #1e4575;
        background: #eef2f7;
        border: 1px solid #d0d5dd;
        border-radius: 6px;
        padding: 8px 14px;
        cursor: pointer;
        white-space: nowrap;
        height: 34px;
    }
    .expenses-search-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        max-width: 560px;
    }
    .column-filter-dropdown {
        position: relative;
    }
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
        max-height: 320px;
        overflow-y: auto;
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
        white-space: nowrap;
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

    @media (max-width: 768px) {
        .expenses-filters-row {
            flex-direction: column;
            align-items: stretch;
        }
        .expenses-date-filters {
            flex-direction: column;
            align-items: stretch;
            width: 100%;
            gap: 14px;
        }
        .date-range-group {
            width: 100%;
        }
        .date-range-inputs {
            flex-wrap: wrap;
            width: 100%;
        }
        .date-range-inputs input[type="date"] {
            flex: 1 1 120px;
            min-width: 0;
            width: auto;
        }
        .clear-dates-btn {
            width: 100%;
            text-align: center;
        }
        .expenses-search-wrapper {
            max-width: 100%;
            width: 100%;
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        .column-filter-dropdown {
            width: 100%;
        }
        .column-filter-btn {
            width: 100%;
            justify-content: center;
        }
        .column-filter-menu {
            left: 0;
            right: 0;
            min-width: 0;
            width: 100%;
            box-sizing: border-box;
        }
        .active-column-filters-row {
            flex-direction: column;
            align-items: stretch;
        }
        .column-filter-chip {
            width: 100%;
            flex-wrap: wrap;
            box-sizing: border-box;
        }
        .column-filter-chip label {
            flex: 1 1 100%;
        }
        .column-filter-chip input,
        .column-filter-chip select {
            flex: 1 1 auto;
            min-width: 0;
            width: 100%;
        }
        .clear-column-filters-btn {
            width: 100%;
            text-align: center;
        }
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
        -webkit-overflow-scrolling: touch;
        touch-action: pan-x pan-y;
        scrollbar-width: thin !important;
    }
    .table-wrapper::-webkit-scrollbar {
        display: block !important;
        height: 8px;
    }
    .table-wrapper::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 4px;
    }
    @media (max-width: 768px) {
        .table-scroll-hint {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #1e4575;
            background: #eef2f7;
            border-radius: 8px;
            padding: 8px;
            margin-bottom: 10px;
        }
    }
    .table-scroll-hint { display: none; }
    @media (max-width: 768px) {
        .col-sticky-check {
            width: 32px; min-width: 32px; max-width: 32px;
            padding: 12px 2px !important;
        }
        .col-sticky-index {
            width: 28px; min-width: 28px; max-width: 28px;
            font-size: 11px; padding: 12px 2px !important;
        }
        .col-sticky-name {
            min-width: 90px; max-width: 90px;
            font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
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
    /* ---- Record Highlights ---- */
    .cm-row-overdue { background: rgba(239,68,68,.05); }
    .cm-row-highvalue { background: rgba(163,121,41,.06); }
    .cm-row-overdue td:first-child,
    .cm-row-highvalue td:first-child { box-shadow: inset 4px 0 0 0 currentColor; }
    .cm-row-overdue td:first-child { color: #ef4444; }
    .cm-row-highvalue td:first-child { color: #A37929; }
    .cm-highlight-badges { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 5px; }
    .cm-hl-badge { display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .3px; white-space: nowrap; }
    .cm-hl-overdue { background: #fee2e2; color: #991b1b; }
    .cm-hl-recent { background: #dbeafe; color: #1e40af; }
    .cm-hl-highvalue { background: #fef3c7; color: #92400e; }
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
    .cm-field-error {
        color: #dc2626;
        font-size: 11px;
        font-weight: 600;
        margin-top: 4px;
    }
    .cm-field-invalid {
        border-color: #dc2626 !important;
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

        /* Make Add/Edit/View commission modals fit small screens */
        .modal-box {
            width: 95vw !important;
            max-width: 95vw !important;
            max-height: 92vh !important;
        }

        .modal-grid {
            grid-template-columns: 1fr !important;
        }

        .modal-header,
        .modal-body,
        .modal-footer {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .add-commission-section {
            padding: 20px !important;
        }

        .form-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
// ---- Column Filter fields (matches the "All Expenses" filter dropdown pattern) ----
// Date Requested / Date Released are handled separately as range pickers above.
const FILTERABLE_FIELDS = [
    { key: 'client_name',       label: "Client's Name",             dataAttr: 'data-client',                type: 'text'  },
    { key: 'project_name',      label: 'Project Name',              dataAttr: 'data-project',                type: 'text'  },
    { key: 'property_details',  label: 'Property Details',          dataAttr: 'data-property',               type: 'text'  },
    { key: 'agent_name',        label: "Agent's Name",              dataAttr: 'data-agent',                  type: 'text'  },
    { key: 'reservation_date',  label: 'Reservation Date',          dataAttr: 'data-reservation-date',      type: 'daterange' },
    { key: 'date_requested',    label: 'Date Requested',            dataAttr: 'data-date-requested',        type: 'daterange' },
    @if($isAdmin)
    { key: 'price_sqm',         label: 'Price/SQM',                 dataAttr: 'data-price-sqm',              type: 'text'  },
    { key: 'lot_area',          label: 'Lot Area',                  dataAttr: 'data-lot-area',                type: 'text'  },
    { key: 'discount',          label: 'Discount',                  dataAttr: 'data-discount',                type: 'text'  },
    @endif
    { key: 'net_tcp',           label: 'Net TCP',                   dataAttr: 'data-net-tcp',                 type: 'text'  },
    { key: 'terms_of_payment',  label: 'Terms of Payment',          dataAttr: 'data-terms',                   type: 'text'  },
    { key: 'mode_of_payment',   label: 'Mode of Payment',           dataAttr: 'data-mode',                    type: 'text'  },
    { key: 'remarks',           label: 'Remarks',                   dataAttr: 'data-remarks',                 type: 'text'  },
    { key: 'units',             label: 'Units',                     dataAttr: 'data-units',                   type: 'text'  },
    { key: 'date_released',     label: 'Date Released',             dataAttr: 'data-date-released',         type: 'daterange' },
    @if($isAdmin)
    { key: 'commission_percent',label: 'Commission %',              dataAttr: 'data-commission-percent',      type: 'text'  },
    { key: 'commission',        label: 'Commission',                dataAttr: 'data-commission',              type: 'text'  },
    @endif
    { key: 'commission_terms',  label: 'Commission Terms',          dataAttr: 'data-commission-terms',        type: 'text'  },
    { key: 'value_commission_terms', label: 'Value of Commission Terms', dataAttr: 'data-value-commission-terms', type: 'text' },
    { key: 'status',            label: 'Status',                    dataAttr: 'data-status',                  type: 'select', options: ['Not Yet Released', 'Released'] },
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
    const searchInput = document.getElementById('monitoringSearch');
    const tableBody = document.getElementById('monitoringTableBody');
    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const searchWords = searchTerm.split(/\s+/).filter(w => w.length > 0);

    // Only real data rows (has data-status attribute)
    const dataRows = Array.from(tableBody.querySelectorAll('tr[data-status]'));

    let visible = 0;

    for (let row of dataRows) {
        const text = row.textContent.toLowerCase();
        const matchesSearch = searchWords.length === 0 || searchWords.every(w => text.includes(w));
        const columnMatch = matchesColumnFilters(row);

        if (matchesSearch && columnMatch) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    }

    const cmNoResults = document.getElementById('cmNoResults');
    if (cmNoResults) {
        cmNoResults.style.display = (visible === 0 && dataRows.length > 0) ? '' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('monitoringSearch');
    if (searchInput) searchInput.addEventListener('input', applyFilters);
});

function filterByStat(status) {
    if (status) {
        columnFilters['status'] = status;
    } else {
        delete columnFilters['status'];
    }
    renderColumnFilterMenu();
    renderActiveColumnFilters();
    applyFilters();

    document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('stat-card-selected'));
    const cardMap = { '': 'card-blue', 'Not Yet Released': 'card-yellow', 'Released': 'card-green' };
    const activeCard = document.querySelector('.stat-card.' + cardMap[status]);
    if (activeCard) activeCard.classList.add('stat-card-selected');

    const tableContainer = document.querySelector('.monitoring-table-container');
    if (tableContainer) {
        tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function resetFilters() {
    const searchInput = document.getElementById('monitoringSearch');
    if (searchInput) searchInput.value = '';

    ['dateRequestedFrom','dateRequestedTo','dateReleasedFrom','dateReleasedTo'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });

    Object.keys(columnFilters).forEach(k => delete columnFilters[k]);
    renderColumnFilterMenu();
    renderActiveColumnFilters();

    document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('stat-card-selected'));

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
function cmUpdateStickyOffsets() {
    const table = document.querySelector('.monitoring-table');
    if (!table) return;

    // Use the header row as the source of truth for column widths —
    // every column in a table shares the same width as its header cell.
    const headerCells = Array.from(table.querySelectorAll('thead th.col-sticky'))
        .filter(el => el.offsetParent !== null); // skip hidden (display:none) cells

    let offset = 0;
    const offsets = [];
    headerCells.forEach(cell => {
        offsets.push(offset);
        offset += cell.getBoundingClientRect().width;
    });

    // Apply the same offsets to header AND every body row, matched by
    // column order (only counting visible sticky cells per row).
    table.querySelectorAll('tr').forEach(row => {
        const cells = Array.from(row.querySelectorAll('.col-sticky'))
            .filter(el => el.offsetParent !== null);
        cells.forEach((cell, i) => {
            if (offsets[i] !== undefined) cell.style.left = offsets[i] + 'px';
        });
    });
}
function clearCmAddForm() {
    window.showConfirmModal('Clear all entered fields? This cannot be undone.').then(function(confirmed) {
        if (confirmed) {
            document.getElementById('cmAddForm').reset();
        }
    });
}

function previewCommissionSubmit(event) {
    event.preventDefault();

    const val = (name) => {
        const el = document.querySelector('#cmAddForm [name="' + name + '"]');
        return el ? el.value : '';
    };
    const fmtMoney = (v) => v ? '₱' + parseFloat(v).toLocaleString('en-PH', {minimumFractionDigits:2}) : '-';
    const fmtDate  = (v) => v ? new Date(v + 'T00:00:00').toLocaleDateString('en-US', {month:'short', day:'2-digit', year:'numeric'}) : '-';
    const set = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text; };

    set('cmp_client_name',       val('client_name') || '-');
    set('cmp_reservation_date',  fmtDate(val('reservation_date')));
    set('cmp_project_name',      val('project_name') || '-');
    set('cmp_property_details',  val('property_details') || '-');
    set('cmp_price_sqm',         fmtMoney(val('price_sqm')));
    set('cmp_lot_area',          val('lot_area') ? val('lot_area') + ' sqm' : '-');
    set('cmp_discount',          val('discount') ? val('discount') + (val('discount_type') === 'percent' ? '%' : '') : '-');
    set('cmp_net_tcp',           fmtMoney(val('net_tcp')));
    set('cmp_commission_percent', val('commission_percent') ? val('commission_percent') + '%' : '-');
    set('cmp_commission',        fmtMoney(val('commission')));
    set('cmp_payment_type',      val('payment_type') || '-');
    set('cmp_value_of_payment_terms', fmtMoney(val('value_of_payment_terms')));
    set('cmp_terms_of_payment',  val('terms_of_payment') || '-');
    set('cmp_mode_of_payment',   val('mode_of_payment') || '-');
    set('cmp_agent_name',        val('agent_name') || '-');
    set('cmp_date_requested',    fmtDate(val('date_requested')));
    set('cmp_number_of_units',   val('number_of_units') || '-');
    set('cmp_status',            val('status') || '-');
    set('cmp_date_released',     fmtDate(val('date_released')));
    set('cmp_remarks',           val('remarks') || '-');

    document.getElementById('cmPreviewModal').classList.add('active');
    return false;
}

function confirmSubmitCmForm() {
    document.getElementById('cmPreviewModal').classList.remove('active');
    document.getElementById('cmAddForm').submit();
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

/* Auto-calculates Date Released from Date Requested + Mode of Payment.
   7 days for Bank Transfer / Cash Payment / Manager's Check / Bank Deposit.
   10 days for Personal Check / Post-Dated Check.
   Stays empty if either required field is missing. Used by both the
   Add form ('cm_add') and the Edit modal ('cm_edit'). */
function calcCmDateReleased(prefix) {
    const modeEl = document.getElementById(prefix + '_mode_of_payment');
    const reqEl  = document.getElementById(prefix + '_date_requested');
    const relEl  = document.getElementById(prefix + '_date_released');
    if (!modeEl || !reqEl || !relEl) return;

    const mode   = modeEl.value;
    const reqVal = reqEl.value;

    const sevenDayModes = ['BANK TRANSFER', 'CASH PAYMENT', "MANAGER'S CHECK", 'BANK DEPOSIT'];
    const tenDayModes    = ['PERSONAL CHECK', 'POST-DATED CHECK'];

    let days = null;
    if (sevenDayModes.includes(mode)) days = 7;
    else if (tenDayModes.includes(mode)) days = 10;

    if (!mode || !reqVal || days === null) {
        relEl.value = '';
        return;
    }

    const reqDate = new Date(reqVal + 'T00:00:00');
    if (isNaN(reqDate.getTime())) { relEl.value = ''; return; }
    reqDate.setDate(reqDate.getDate() + days);

    const yyyy = reqDate.getFullYear();
    const mm   = String(reqDate.getMonth() + 1).padStart(2, '0');
    const dd   = String(reqDate.getDate()).padStart(2, '0');
    relEl.value = yyyy + '-' + mm + '-' + dd;
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
    if (netTcp > 0) {
        document.getElementById('cm_add_commission').value = result > 0 ? result.toFixed(2) : '';
        const display = document.getElementById('cm_add_commission_display');
        if (display) display.value = result > 0 ? fmtComma(result) : '';
    }
    computeValueOfPaymentTerms();
}
function computeAddCommissionFromValue() {
    const netTcp = parseFloat(document.getElementById('cm_add_net_tcp').value) || 0;
    const rawVal = (document.getElementById('cm_add_commission_display').value || '').replace(/,/g, '');
    const val    = parseFloat(rawVal) || 0;
    const pct    = netTcp > 0 ? (val / netTcp) * 100 : 0;
    document.getElementById('cm_add_commission').value = val > 0 ? val.toFixed(2) : '';
    const pctEl = document.getElementById('cm_add_commission_percent');
    if (pctEl && pct > 0) pctEl.value = pct.toFixed(4).replace(/\.?0+$/, '');
}


// Ensure commission hidden field is synced from display before submit
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cmAddForm');
    if (form) {
        form.addEventListener('submit', function() {
            const displayVal = (document.getElementById('cm_add_commission_display')?.value || '').replace(/,/g, '');
            const hiddenEl   = document.getElementById('cm_add_commission');
            const parsed     = parseFloat(displayVal);
            if (parsed > 0) {
                hiddenEl.value = parsed.toFixed(2);
            } else {
                computeAddCommission();
            }
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
    const editWrap = document.getElementById('cm_edit_terms_of_payment')?.closest('.combobox-wrapper');
    if (editWrap && !editWrap.contains(e.target)) {
        document.getElementById('cmEditTermsDropdown').style.display = 'none';
    }
});

function toggleCmEditTermsDropdown() {
    const dd = document.getElementById('cmEditTermsDropdown');
    dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
}
function selectCmEditTerm(val) {
    document.getElementById('cm_edit_terms_of_payment').value = val;
    document.getElementById('cmEditTermsDropdown').style.display = 'none';
}
function filterCmEditTerms(val) {
    const items = document.querySelectorAll('#cmEditTermsDropdown .dropdown-item');
    let hasVisible = false;
    items.forEach(item => {
        const match = item.textContent.toLowerCase().includes(val.toLowerCase());
        item.style.display = match ? '' : 'none';
        if (match) hasVisible = true;
    });
    document.getElementById('cmEditTermsDropdown').style.display = hasVisible ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });
    });

    document.getElementById('cmEditForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const id = document.getElementById('cm_edit_id').value;

        // Clear any previous inline errors before retrying
        document.querySelectorAll('.cm-field-error').forEach(el => el.remove());
        document.querySelectorAll('.cm-field-invalid').forEach(el => el.classList.remove('cm-field-invalid'));

        showToast('Saving changes...', 'info');

        fetch(`/commission-monitoring/${id}`, {
            method: 'POST', // Laravel reads @method('PUT') from the hidden field
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: new FormData(form),
        })
        .then(async (r) => {
            const data = await r.json().catch(() => null);
            if (r.ok && data && data.success) {
                showToast('Record updated successfully.', 'success');
                closeCmModal('cmEditModal');
                setTimeout(() => window.location.reload(), 600);
                return;
            }

            // Validation error (422) — show inline messages near each affected field
            if (r.status === 422 && data && data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('cm-field-invalid');
                        const msg = document.createElement('div');
                        msg.className = 'cm-field-error';
                        msg.textContent = data.errors[field][0];
                        input.closest('.modal-field')?.appendChild(msg);
                    }
                });
                showToast(data.message || 'Please check the highlighted fields.', 'error', 'Validation Error');
                return;
            }

            // Any other failure — friendly toast, never raw JSON
            showToast((data && data.message) || 'Something went wrong. Please try again.', 'error');
        })
        .catch(() => {
            showToast('Network error. Please check your connection and try again.', 'error');
        });
    });

    // Auto-open edit/delete after admin approval redirect
    const _hlParams = new URLSearchParams(window.location.search);
    const _hlId     = _hlParams.get('highlight');
    const _hlStatus = _hlParams.get('status');
    const _hlAction = _hlParams.get('action');
    if (_hlStatus === 'approved') {
        // Show toast so staff knows they can now edit
        setTimeout(() => {
            if (typeof showToast === 'function') showToast('Your request was approved. You can now ' + (_hlAction || 'edit') + ' the record.', 'success', 'Request Approved');
        }, 500);
        function doCmHighlight() {
            if (!_hlId) return;
            const row = document.getElementById('cm-' + _hlId) || document.querySelector('[data-id="' + _hlId + '"]');
            if (!row) return;
            row.style.background = 'rgba(22,163,74,.12)';
            row.style.outline = '2px solid #16a34a';
            row.style.outlineOffset = '-1px';
            const scroller = document.querySelector('.page-content');
            if (scroller) {
                const rr = row.getBoundingClientRect(), sr = scroller.getBoundingClientRect();
                scroller.scrollTo({ top: scroller.scrollTop + rr.top - sr.top - 100, behavior: 'smooth' });
            } else { row.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
            if (_hlAction === 'edit') {
                setTimeout(() => editCommission(parseInt(_hlId)), 700);
            }
            if (_hlAction === 'delete') {
                setTimeout(() => {
                    window.showConfirmModal('Your delete request was approved. Delete this record now?').then(function(confirmed) {
                        if (confirmed) {
                            const delForm = row.querySelector('form');
                            if (delForm) delForm.submit();
                        }
                    });
                }, 700);
            }
            setTimeout(() => { row.style.background = ''; row.style.outline = ''; }, 10000);
            row.addEventListener('click', function() { row.style.background = ''; row.style.outline = ''; }, { once: true });
        }
        setTimeout(doCmHighlight, 800);
        setTimeout(doCmHighlight, 1500);
        window.history.replaceState({}, '', window.location.pathname);
    }
});
</script>

<script>
const IS_ADMIN = {{ (auth()->check() && auth()->user()->isAdmin()) ? 'true' : 'false' }};

let _cmPermAction = 'edit', _cmPermRecordId = null;

function requireAdmin(callback, recordId, action) {
    if (callback) callback();
}

function requireAdminSync(event, recordId) {
    return confirm('Are you sure you want to delete this commission request?');
}

// Staff delete — plain confirmation, no permission gate
function staffDeleteCommission(e, id) {
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
function cmIsMobile() {
    return window.matchMedia('(max-width: 768px)').matches;
}

function cmSetCheckboxColumnVisible(visible) {
    document.querySelectorAll('.col-sticky-check').forEach(el => {
        el.style.display = visible ? '' : 'none';
    });
}

function cmToggleSelectMode() {
    const table = document.querySelector('.monitoring-table');
    const btn = document.getElementById('cmSelectModeBtn');
    const isOn = table.classList.toggle('cm-select-mode');
    btn.textContent = isOn ? 'Cancel' : 'Select';
    btn.style.background = isOn ? '#1e4575' : '';
    btn.style.color = isOn ? '#fff' : '';

    cmSetCheckboxColumnVisible(isOn);
    cmUpdateStickyOffsets();

    if (!isOn) {
        document.querySelectorAll('.cm-row-check').forEach(cb => cb.checked = false);
        cmUpdateSelectedCount();
    }
}

// On mobile, start with the checkbox column hidden until "Select" is tapped.
// On desktop, always keep it visible.
document.addEventListener('DOMContentLoaded', function() {
    if (cmIsMobile()) {
        cmSetCheckboxColumnVisible(false);
    }
    cmUpdateStickyOffsets();
});
window.addEventListener('resize', cmUpdateStickyOffsets);

function cmToggleSelectAll(checkbox) {
    document.querySelectorAll('.cm-row-check').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    cmUpdateSelectedCount();
}

function cmUpdateSelectedCount() {
    const checked = document.querySelectorAll('.cm-row-check:checked');
    const btn = document.getElementById('cmDeleteSelectedBtn');
    const countEl = document.getElementById('cmSelectedCount');
    if (countEl) countEl.textContent = checked.length;
    if (btn) btn.style.display = checked.length > 0 ? 'inline-flex' : 'none';

    const selectAll = document.getElementById('cmSelectAll');
    const allBoxes = document.querySelectorAll('.cm-row-check');
    if (selectAll) selectAll.checked = allBoxes.length > 0 && checked.length === allBoxes.length;
}

function cmDeleteSelected() {
    const ids = Array.from(document.querySelectorAll('.cm-row-check:checked')).map(cb => cb.value);
    if (ids.length === 0) return;

    window.showConfirmModal('Delete ' + ids.length + ' selected commission request(s)? This cannot be undone.').then(function(confirmed) {
        if (!confirmed) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("commission-monitoring.bulk-delete") }}';
        form.innerHTML = `@csrf`;
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    });
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
                        <input type="number" id="cm_edit_lot_area" name="lot_area" step="0.0001" min="0" placeholder="0.0000" oninput="computeEditNetTCP()">
                    </div>
                    <div class="modal-field">
                        <label style="display:flex;align-items:center;gap:8px;">
                            DISCOUNT <span class="required">*</span>
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
                        <div class="select-wrapper">
                            <select id="cm_edit_payment_type" name="payment_type" onchange="computeEditValueOfPaymentTerms()" required>
                                <option value="">— Select —</option>
                                <option value="Full Payment">Full Payment</option>
                                <option value="2 Months Commission">2 Months Commission</option>
                                <option value="3 Months Commission">3 Months Commission</option>
                            </select>
                            <span class="select-arrow">▼</span>
                        </div>
                    </div>
                    <div class="modal-field">
                        <label>Value of Commission Terms <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                        <input type="text" id="cm_edit_vopt_display" placeholder="0.00" readonly style="background:#f3f4f6;cursor:not-allowed;color:#374151;">
                        <input type="hidden" id="cm_edit_value_of_payment_terms" name="value_of_payment_terms">
                    </div>
                    <div class="modal-field">
                        <label>Terms of Payment <span style="color:#ef4444">*</span></label>
                        <div class="combobox-wrapper">
                            <input type="text" id="cm_edit_terms_of_payment" name="terms_of_payment" class="combobox-input" required autocomplete="off" placeholder="Type or select payment terms" onclick="toggleCmEditTermsDropdown()" oninput="filterCmEditTerms(this.value)">
                            <button type="button" class="combobox-arrow" onclick="toggleCmEditTermsDropdown()">▼</button>
                            <div id="cmEditTermsDropdown" class="combobox-dropdown" style="display:none;">
                                <div class="dropdown-item" onclick="selectCmEditTerm('30% DP - 70% BAL 5 YRS')">30% DP - 70% BAL 5 YRS</div>
                                <div class="dropdown-item" onclick="selectCmEditTerm('50% DP - 50% BAL 5 YRS')">50% DP - 50% BAL 5 YRS</div>
                                <div class="dropdown-item" onclick="selectCmEditTerm('30% DP (6 MOS) - 70% BAL 54 MOS')">30% DP (6 MOS) - 70% BAL 54 MOS</div>
                                <div class="dropdown-item" onclick="selectCmEditTerm('30% DP (3 MOS) - 70% BAL 57 MOS')">30% DP (3 MOS) - 70% BAL 57 MOS</div>
                                <div class="dropdown-item" onclick="selectCmEditTerm('30% DP (9 MOS) - 70% BAL 36 MOS')">30% DP (9 MOS) - 70% BAL 36 MOS</div>
                                <div class="dropdown-item" onclick="selectCmEditTerm('30% DP (2 MOS) - 70% BAL 57 MOS')">30% DP (2 MOS) - 70% BAL 57 MOS</div>
                                <div class="dropdown-item" onclick="selectCmEditTerm('30% DP (2 MOS) - 70% BAL 5 YRS')">30% DP (2 MOS) - 70% BAL 5 YRS</div>
                                <div class="dropdown-item" onclick="selectCmEditTerm('STRAIGHT PAYMENT')">STRAIGHT PAYMENT</div>
                                <div class="dropdown-item" onclick="selectCmEditTerm('30% DP - 70% BAL 3 YRS')">30% DP - 70% BAL 3 YRS</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-field">
                        <label>Mode of Payment</label>
                        <select id="cm_edit_mode_of_payment" name="mode_of_payment" onchange="calcCmDateReleased('cm_edit')">
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
                        <input type="date" id="cm_edit_date_requested" name="date_requested" onchange="calcCmDateReleased('cm_edit')" required>
                    </div>
                    <div class="modal-field">
                        <label>Number of Units <span style="color:#ef4444">*</span></label>
                        <input type="number" id="cm_edit_number_of_units" name="number_of_units" min="1" required>
                    </div>
                    <div class="modal-field">
                        <label>Date Released</label>
                        <input type="date" id="cm_edit_date_released" name="date_released" readonly style="background:#f3f4f6;cursor:not-allowed;color:#374151;">
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

<!-- SUBMIT PREVIEW Modal -->
<div id="cmPreviewModal" class="modal-overlay">
    <div class="modal-box" style="max-width:800px;">
        <div class="modal-header">
            <h3>Review Commission Request</h3>
            <button class="modal-close" onclick="closeCmModal('cmPreviewModal')">✖</button>
        </div>
        <div class="modal-body">
            <div style="background:#fef3c7;color:#92400e;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:13px;font-weight:600;">
                ⚠ Please review the details below carefully. Are you sure you want to submit this request?
            </div>
            <div class="modal-grid">
                <div class="modal-field"><label>Client's Name</label><div class="field-value" id="cmp_client_name">-</div></div>
                <div class="modal-field"><label>Reservation Date</label><div class="field-value" id="cmp_reservation_date">-</div></div>
                <div class="modal-field"><label>Project Name</label><div class="field-value" id="cmp_project_name">-</div></div>
                <div class="modal-field"><label>Property Details</label><div class="field-value" id="cmp_property_details">-</div></div>
                @if($isAdmin)
                <div class="modal-field"><label>Price / SQM</label><div class="field-value" id="cmp_price_sqm">-</div></div>
                <div class="modal-field"><label>Lot Area</label><div class="field-value" id="cmp_lot_area">-</div></div>
                <div class="modal-field"><label>Discount</label><div class="field-value" id="cmp_discount">-</div></div>
                @endif
                <div class="modal-field"><label>Net TCP</label><div class="field-value" id="cmp_net_tcp">-</div></div>
                @if($isAdmin)
                <div class="modal-field"><label>Commission %</label><div class="field-value" id="cmp_commission_percent">-</div></div>
                @endif
                <div class="modal-field"><label>Value of Commission</label><div class="field-value" id="cmp_commission">-</div></div>
                <div class="modal-field"><label>Commission Terms</label><div class="field-value" id="cmp_payment_type">-</div></div>
                <div class="modal-field"><label>Value of Commission Terms</label><div class="field-value" id="cmp_value_of_payment_terms">-</div></div>
                <div class="modal-field"><label>Terms of Payment</label><div class="field-value" id="cmp_terms_of_payment">-</div></div>
                <div class="modal-field"><label>Mode of Payment</label><div class="field-value" id="cmp_mode_of_payment">-</div></div>
                <div class="modal-field"><label>Agent's Name</label><div class="field-value" id="cmp_agent_name">-</div></div>
                <div class="modal-field"><label>Date Requested</label><div class="field-value" id="cmp_date_requested">-</div></div>
                <div class="modal-field"><label>Number of Units</label><div class="field-value" id="cmp_number_of_units">-</div></div>
                <div class="modal-field"><label>Status</label><div class="field-value" id="cmp_status">-</div></div>
                <div class="modal-field"><label>Date Released</label><div class="field-value" id="cmp_date_released">-</div></div>
                <div class="modal-field" style="grid-column:1/-1;"><label>Remarks</label><div class="field-value" id="cmp_remarks">-</div></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-modal-cancel" onclick="closeCmModal('cmPreviewModal')">Go Back &amp; Edit</button>
            <button class="btn-modal-save" onclick="confirmSubmitCmForm()">Yes, Submit Request</button>
        </div>
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