@extends('layouts.dashboard')
@section('title', 'Client Database')
@section('content')
<style>
.cd-wrap{padding:0}
.cd-header{background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:28px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25)}
.cd-header-eyebrow{font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px}
.cd-header h1{font-size:28px;font-weight:700;color:white;margin:0 0 8px;position:relative;z-index:2}
.cd-header p{font-size:14px;color:rgba(255,255,255,0.75);margin:0;position:relative;z-index:2}
.add-commission-section{background:white;border-radius:12px;padding:32px;margin-bottom:30px;box-shadow:0 2px 8px rgba(0,0,0,0.08);border:2px solid #1e4575}
.section-header-commission{padding:0 0 12px;border-bottom:1px solid #d0d5dd;margin-bottom:24px}
.section-header-commission h2{color:#1e4575;font-size:18px;font-weight:700;margin:0;text-transform:uppercase}
.form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:12px}
.form-group{display:flex;flex-direction:column;gap:4px}
.form-group label{font-size:12px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:0.4px}
.required{color:#ef4444}
.form-group input,.form-group select{padding:12px 16px;border:2px solid #1e4575;border-radius:8px;font-size:14px;transition:all 0.3s;background:white;color:#344054;font-weight:500}
.form-group input:focus,.form-group select:focus{outline:none;border-color:#A37929;box-shadow:0 0 0 3px rgba(163,121,41,0.1)}
.section-title-bar{font-size:16px;font-weight:700;color:#A37929;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.6px;display:flex;align-items:center;gap:8px}
.section-title-bar::before{content:'';width:3px;height:20px;background:linear-gradient(180deg,#1e4575,#A37929);border-radius:2px}
.form-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:28px}
.btn-clear,.btn-submit{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:6px;font-weight:600;font-size:14px;cursor:pointer;transition:all 0.3s;border:none;min-height:44px}
.btn-clear{background:#f3f4f6;color:#374151;border:2px solid #d0d5dd}
.btn-clear:hover{background:#e5e7eb;transform:translateY(-2px)}
.btn-submit{background:#1e4575;color:white;box-shadow:0 2px 8px rgba(30,69,117,0.3)}
.btn-submit:hover{background:#152e4d;transform:translateY(-2px)}
</style>

<div class="cd-wrap">
    @if(session('error'))
    <div style="background:#fee2e2;color:#dc2626;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;">⚠ {{ session('error') }}</div>
    @endif
    @if(session('success'))
    <div style="background:#dcfce7;color:#166534;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;">✓ {{ session('success') }}</div>
    @endif
    <div class="cd-header">
        <div style="position:relative;z-index:2;">
            <div class="cd-header-eyebrow">Sales & Marketing</div>
            <h1>Client Database</h1>
            <p>Manage client records and commission requests</p>
        </div>
        <div style="position:absolute;top:0;right:0;width:300px;height:100%;pointer-events:none;">
            <div style="position:absolute;width:220px;height:220px;top:-60px;right:-40px;border-radius:50%;background:rgba(255,255,255,.06);"></div>
            <div style="position:absolute;width:140px;height:140px;top:40px;right:120px;border-radius:50%;background:rgba(255,255,255,.04);"></div>
        </div>
    </div>

    <div class="add-commission-section">
        <div class="section-header-commission">
            <h2>ADD NEW CLIENT RECORD</h2>
        </div>
        <form id="commissionForm" action="{{ route('client-database.store') }}" method="POST">
            @csrf
            <div class="section-title-bar"><span>📋</span> COMMISSION REQUEST INFORMATION</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>DEVELOPER'S NAME</label>
                    <div style="position:relative;">
                        <input type="text" name="developer_name" id="dev_name_input" placeholder="Type or select developer" autocomplete="off"
                            onclick="toggleDevDropdown()" oninput="filterDev(this.value)"
                            style="width:100%;padding:12px 40px 12px 16px;border:2px solid #1e4575;border-radius:8px;font-size:14px;font-weight:500;background:white;color:#344054;box-sizing:border-box;">
                        <button type="button" onclick="toggleDevDropdown()" style="position:absolute;right:2px;top:50%;transform:translateY(-50%);width:36px;height:calc(100% - 4px);background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:0 6px 6px 0;cursor:pointer;font-size:12px;">▼</button>
                        <div id="devDropdown" style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;background:white;border:2px solid #1e4575;border-radius:8px;max-height:200px;overflow-y:auto;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                            <div onclick="selectDev('758 Real Estate Management')" style="padding:12px 16px;cursor:pointer;font-size:14px;color:#374151;font-weight:500;border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#e3f2fd'" onmouseout="this.style.background=''">758 Real Estate Management</div>
                            <div onclick="selectDev('Xceed Realty and Development Inc.')" style="padding:12px 16px;cursor:pointer;font-size:14px;color:#374151;font-weight:500;" onmouseover="this.style.background='#e3f2fd'" onmouseout="this.style.background=''">Xceed Realty and Development Inc.</div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>PROJECT NAME <span class="required">*</span></label>
                    <input type="text" name="project_name" placeholder="Enter project name" required>
                </div>
                <div class="form-group">
                    <label>BLOCK & LOT NUMBER</label>
                    <input type="text" name="block_lot_number" placeholder="e.g., Block 3 Lot 12">
                </div>
                <div class="form-group">
                    <label>CLIENT'S NAME <span class="required">*</span></label>
                    <input type="text" name="client_name" placeholder="Enter client name" required>
                </div>
                <div class="form-group">
                    <label>LOT AREA</label>
                    <input type="number" name="lot_area" id="f_lot_area" placeholder="0.0000" step="0.0001" min="0" oninput="computeTCP()">
                </div>
                <div class="form-group">
                    <label>PRICE PER SQM</label>
                    <input type="text" id="f_price_sqm_display" placeholder="0.00" oninput="onPriceSqmInput(this)" style="color:#374151;">
                    <input type="hidden" name="price_sqm" id="f_price_sqm">
                </div>
                <div class="form-group">
                    <label>TCP <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                    <input type="text" id="f_tcp_display" placeholder="0.00" readonly style="background:#f3f4f6;cursor:not-allowed;color:#374151;">
                    <input type="hidden" name="tcp" id="f_tcp">
                </div>
                <div class="form-group">
                    <label>DISCOUNT (%)</label>
                    <input type="number" name="discount" id="f_discount_pct" placeholder="0.00" step="0.0000000001" min="0" max="100" oninput="computeDiscount()">
                </div>
                <div class="form-group">
                    <label>DISCOUNT VALUE <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                    <input type="number" id="f_discount_val" placeholder="0.00" step="0.01" min="0" oninput="computeDiscountFromValue()" style="color:#374151;">
                </div>
                <div class="form-group">
                    <label>NET TCP <span style="font-size:11px;color:#9ca3af;font-weight:400">(auto)</span></label>
                    <input type="text" id="f_net_tcp_display" placeholder="0.00" readonly style="background:#f3f4f6;cursor:not-allowed;color:#374151;">
                    <input type="hidden" name="net_tcp" id="f_net_tcp">
                </div>
                <div class="form-group">
                    <label>TERMS OF PAYMENT <span class="required">*</span></label>
                    <div style="position:relative">
                        <input type="text" id="terms_of_payment" name="terms_of_payment" required autocomplete="off" placeholder="Type or select payment terms" onclick="toggleTermsDropdown()" oninput="filterTerms(this.value)" style="width:100%;padding:12px 40px 12px 16px;border:2px solid #1e4575;border-radius:8px;font-size:14px;font-weight:500;background:white;color:#344054;box-sizing:border-box">
                        <button type="button" onclick="toggleTermsDropdown()" style="position:absolute;right:2px;top:50%;transform:translateY(-50%);width:36px;height:calc(100% - 4px);background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:0 6px 6px 0;cursor:pointer;font-size:12px">▼</button>
                        <div id="termsDropdown" style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;background:white;border:2px solid #1e4575;border-radius:8px;max-height:250px;overflow-y:auto;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.15)">
                            @foreach(['30% DP - 70% BAL 5 YRS','50% DP - 50% BAL 5 YRS','30% DP (6 MOS) - 70% BAL 54 MOS','30% DP (3 MOS) - 70% BAL 57 MOS','30% DP (9 MOS) - 70% BAL 36 MOS','30% DP (2 MOS) - 70% BAL 57 MOS','30% DP (2 MOS) - 70% BAL 5 YRS','STRAIGHT PAYMENT','30% DP - 70% BAL 3 YRS'] as $term)
                            <div onclick="selectTerm('{{ $term }}')" style="padding:12px 16px;cursor:pointer;font-size:14px;color:#374151;font-weight:500;border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#e3f2fd'" onmouseout="this.style.background=''">{{ $term }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>RESERVATION DATE</label>
                    <input type="date" name="reservation_date">
                </div>
                <div class="form-group">
                    <label>NUMBER OF UNITS</label>
                    <input type="number" name="number_of_units" min="1" value="1" placeholder="1">
                </div>
                <div class="form-group">
                    <label>DATE OF DOWNPAYMENT</label>
                    <input type="date" name="date_of_downpayment">
                </div>
                <div class="form-group">
                    <label>AGENT'S NAME <span class="required">*</span></label>
                    <input type="text" name="agent_name" placeholder="Enter agent name" required>
                </div>
                <div class="form-group">
                    <label>CLIENT STATUS</label>
                    <select name="status" style="width:100%;padding:12px 16px;border:2px solid #1e4575;border-radius:8px;font-size:14px;font-weight:500;background:white;color:#344054;">
                        <option value="">No Status</option>
                        <option value="Done">Done</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="date_requested" value="{{ date('Y-m-d') }}">
            
            <input type="hidden" name="property_details" value="">
            <input type="hidden" name="commission" value="">
            <input type="hidden" name="remarks" value="">
            <input type="hidden" name="commission_percent" value="">
            <div class="form-actions">
                <button type="button" class="btn-clear" onclick="document.getElementById('commissionForm').reset()">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear
                </button>
                <button type="submit" class="btn-submit">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Submit Request
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div style="background:white;border-radius:12px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.08);border:2px solid #1e4575;margin-top:30px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding-bottom:16px;border-bottom:2px solid #e5e7eb;flex-wrap:wrap;gap:12px;">
            <h3 style="font-size:20px;font-weight:700;color:#1e4575;margin:0;text-transform:uppercase">Client Database Records</h3>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <div style="position:relative;">
                    <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#6b7280" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="cdSearch" placeholder="Search by name, agent, project... (space = AND)" style="width:320px;padding:9px 12px 9px 36px;border:2px solid #d0d5dd;border-radius:8px;font-size:13px;box-sizing:border-box;outline:none;" oninput="cdFilter()">
                </div>
                <select id="cdStatusFilter" onchange="cdFilter()" style="padding:9px 12px;border:2px solid #d0d5dd;border-radius:8px;font-size:13px;color:#374151;background:white;cursor:pointer;outline:none;">
                    <option value="">All Status</option>
                    <option value="done">Done</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="none">No Status</option>
                </select>
                <button onclick="document.getElementById('cdSearch').value='';document.getElementById('cdStatusFilter').value='';cdFilter();" style="padding:9px 14px;background:#f1f5f9;border:2px solid #d0d5dd;border-radius:8px;font-size:13px;color:#64748b;cursor:pointer;">Clear</button>
                <span id="cdCount" style="font-size:12px;color:#94a3b8;white-space:nowrap;"></span>
            </div>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:13px">
                <thead style="background:linear-gradient(135deg,#1e4575,#2563eb)">
                    <tr>
                        @foreach(['Developer','Project','Block & Lot','Client','Lot Area','Price/SQM','TCP','Discount (%)','Discount Value','Net TCP','Terms','Reservation Date','Units','Downpayment Date','Agent','Status','Downpayment Status','Actions'] as $h)
                        <th style="padding:14px 12px;text-align:left;font-weight:600;color:white;text-transform:uppercase;font-size:11px;white-space:nowrap">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="cdTableBody">
                    @forelse($commissionRequests ?? [] as $req)
                    <tr data-id="{{ $req->id }}" style="border-bottom:1px solid #e5e7eb">
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->developer_name ?? '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->project_name ?? '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->block_lot_number ?? '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->client_name ?? '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->lot_area ? number_format($req->lot_area,2).' sqm' : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->price_sqm ? '₱'.number_format($req->price_sqm,2) : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->tcp ? '₱'.number_format($req->tcp,2) : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->discount !== null ? number_format($req->discount, 2).'%' : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">
                            @php $discVal = $req->tcp && $req->discount ? $req->tcp * ($req->discount / 100) : null; @endphp
                            {{ $discVal ? '₱'.number_format($discVal, 2) : '-' }}
                        </td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->net_tcp ? '₱'.number_format($req->net_tcp,2) : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->terms_of_payment ?? '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->reservation_date ? $req->reservation_date->format('M d, Y') : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap;text-align:center;">{{ $req->number_of_units ?? '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->date_of_downpayment ? $req->date_of_downpayment->format('M d, Y') : '-' }}</td>
                        <td style="padding:14px 12px;color:#374151;white-space:nowrap">{{ $req->agent_name ?? '-' }}</td>
                        <td style="padding:10px 12px;white-space:nowrap">
                            <form method="POST" action="{{ route('client-database.status', $req->id) }}">
                                @csrf @method('PATCH')
                                <select name="client_status" onchange="this.form.submit()"
                                    data-client-status="{{ strtolower($req->client_status ?? '') }}"
                                    style="padding:5px 10px;border-radius:20px;font-size:12px;font-weight:600;border:none;cursor:pointer;outline:none;
                                    background:{{ $req->client_status === 'Done' ? '#dcfce7' : ($req->client_status === 'Cancelled' ? '#fee2e2' : '#f1f5f9') }};
                                    color:{{ $req->client_status === 'Done' ? '#166534' : ($req->client_status === 'Cancelled' ? '#991b1b' : '#64748b') }};">
                                    <option value="" {{ !$req->client_status ? 'selected' : '' }}>— Set Status —</option>
                                    <option value="Done" {{ $req->client_status === 'Done' ? 'selected' : '' }} style="background:#dcfce7;color:#166534;">Done</option>
                                    <option value="Cancelled" {{ $req->client_status === 'Cancelled' ? 'selected' : '' }} style="background:#fee2e2;color:#991b1b;">Cancelled</option>
                                </select>
                            </form>
                        </td>
                        <td style="padding:10px 12px;white-space:nowrap">
                            <button onclick="openDPModal({{ $req->id }}, {{ $req->downpayment_amount ?? 0 }}, {{ $req->downpayment_terms ?? 1 }}, {{ $req->downpayment_per_term ?? 0 }}, '{{ $req->downpayment_status ?? '' }}')"
                                style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;border:none;cursor:pointer;
                                background:{{ $req->downpayment_status === 'Paid' || $req->downpayment_status === 'Spot Paid' ? '#dcfce7' : ($req->downpayment_status && $req->downpayment_status !== '— Set —' ? '#fef3c7' : '#f1f5f9') }};
                                color:{{ $req->downpayment_status === 'Paid' || $req->downpayment_status === 'Spot Paid' ? '#166534' : ($req->downpayment_status && $req->downpayment_status !== '— Set —' ? '#92400e' : '#64748b') }};">
                                {{ $req->downpayment_status ?: '— Set —' }}
                            </button>
                        </td>
                        <td style="padding:14px 12px;white-space:nowrap">
                            <div style="display:flex;gap:6px">
                                <button onclick="viewRow({{ $req->id }})" style="width:60px;height:28px;background:#1e4575;color:white;border:none;border-radius:5px;font-size:11px;font-weight:700;cursor:pointer">VIEW</button>
                                <button onclick="requireAdmin(() => editRow({{ $req->id }}), {{ $req->id }}, '{{ addslashes($req->client_name ?? '') }} - {{ addslashes($req->project_name ?? '') }}', 'edit')" style="width:60px;height:28px;background:#f59e0b;color:white;border:none;border-radius:5px;font-size:11px;font-weight:700;cursor:pointer">EDIT</button>
                                <form action="{{ route('client-database.destroy', $req->id) }}" method="POST" style="display:inline" onsubmit="return requireAdminSync(event, {{ $req->id }}, '{{ addslashes($req->client_name ?? '') }} - {{ addslashes($req->project_name ?? '') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="width:60px;height:28px;background:#ef4444;color:white;border:none;border-radius:5px;font-size:11px;font-weight:700;cursor:pointer">DELETE</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="15" style="text-align:center;padding:40px;color:#6b7280">No client records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Permission Modal -->
<div id="permissionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this)closeLocalPermModal()">
    <div style="background:white;border-radius:16px;max-width:460px;width:90%;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.2);">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);padding:18px 22px;display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div style="flex:1;">
                <div style="color:rgba(255,255,255,.7);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;">Permission Required</div>
                <div id="localPermTitle" style="color:white;font-size:15px;font-weight:700;margin-top:1px;">Request to Edit Record</div>
            </div>
            <button onclick="closeLocalPermModal()" style="background:rgba(255,255,255,.15);border:none;color:white;width:28px;height:28px;border-radius:6px;cursor:pointer;font-size:18px;line-height:1;">&times;</button>
        </div>
        <div style="padding:20px 22px;">
            <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;margin-bottom:16px;border:1px solid #e2e8f0;">
                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Record</div>
                <div id="localPermRecord" style="font-size:13px;font-weight:600;color:#1e293b;">—</div>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Reason for Request <span style="color:#dc2626;">*</span></label>
                <textarea id="localPermReason" rows="4" placeholder="Please explain why you need to perform this action..." style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-family:inherit;resize:none;outline:none;box-sizing:border-box;" onfocus="this.style.borderColor='#1e4575'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
                <div id="localPermError" style="color:#dc2626;font-size:11px;margin-top:4px;display:none;">Please provide a reason (at least 5 characters).</div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button onclick="closeLocalPermModal()" style="padding:9px 18px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;">Cancel</button>
                <button onclick="submitLocalPermRequest()" id="localPermBtn" style="padding:9px 20px;background:#1e4575;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Send Request</button>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:white;border-radius:16px;width:95%;max-width:960px;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);color:white;padding:20px 24px;border-radius:16px 16px 0 0;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;font-size:18px;font-weight:700">Commission Request Details</h3>
            <button onclick="document.getElementById('viewModal').style.display='none'" style="background:rgba(255,255,255,0.2);border:none;color:white;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:18px">✕</button>
        </div>
        <div style="padding:24px">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px" id="viewContent"></div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end">
            <button onclick="document.getElementById('viewModal').style.display='none'" style="padding:10px 20px;background:#f3f4f6;color:#374151;border:2px solid #d0d5dd;border-radius:8px;font-weight:600;cursor:pointer">Close</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:white;border-radius:16px;width:95%;max-width:960px;box-shadow:0 20px 60px rgba(0,0,0,0.3)">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);color:white;padding:20px 24px;border-radius:16px 16px 0 0;display:flex;justify-content:space-between;align-items:center">
            <h3 style="margin:0;font-size:18px;font-weight:700">Edit Commission Request</h3>
            <button onclick="document.getElementById('editModal').style.display='none'" style="background:rgba(255,255,255,0.2);border:none;color:white;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:18px">✕</button>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <input type="hidden" id="edit_id" name="id">
            <div style="padding:24px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Developer's Name</label><input type="text" id="edit_developer_name" name="developer_name" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Project Name *</label><input type="text" id="edit_project_name" name="project_name" required style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Block & Lot Number</label><input type="text" id="edit_block_lot_number" name="block_lot_number" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Client's Name *</label><input type="text" id="edit_client_name" name="client_name" required style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Lot Area</label><input type="number" id="edit_lot_area" name="lot_area" step="0.0001" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Price Per SQM</label><input type="number" id="edit_price_sqm" name="price_sqm" step="0.01" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">TCP</label><input type="number" id="edit_tcp" name="tcp" step="0.01" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Discount (%)</label><input type="number" id="edit_discount" name="discount" step="0.01" min="0" max="100" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Net TCP</label><input type="number" id="edit_net_tcp" name="net_tcp" step="0.01" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Terms of Payment *</label><input type="text" id="edit_terms_of_payment" name="terms_of_payment" required style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Reservation Date</label><input type="date" id="edit_reservation_date" name="reservation_date" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Number of Units</label><input type="number" id="edit_number_of_units" name="number_of_units" min="1" value="1" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Date of Downpayment</label><input type="date" id="edit_date_of_downpayment" name="date_of_downpayment" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Agent's Name *</label><input type="text" id="edit_agent_name" name="agent_name" required style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px"></div>
                <div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">Client Status</label>
                    <select id="edit_client_status" name="status" style="padding:10px 14px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px">
                        <option value="">No Status</option>
                        <option value="Done">Done</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div style="padding:16px 24px;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:12px">
                <button type="button" onclick="document.getElementById('editModal').style.display='none'" style="padding:10px 20px;background:#f3f4f6;color:#374151;border:2px solid #d0d5dd;border-radius:8px;font-weight:600;cursor:pointer">Cancel</button>
                <button type="submit" style="padding:10px 24px;background:#1e4575;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
const IS_ADMIN = {{ (auth()->check() && auth()->user()->isAdmin()) ? 'true' : 'false' }};

let _localPermAction = '', _localPermModule = 'Client Database', _localPermRecordId = null, _localPermRecordLabel = '';

function requireAdmin(cb, recordId, recordLabel, action) {
    if (IS_ADMIN) { cb(); return; }
    // Check if already approved for this specific record+action
    fetch(`/api/permission-requests/check?action=${action || 'edit'}&record_id=${recordId}`)
        .then(r => r.json())
        .then(data => {
            if (data.approved) {
                if (cb) cb();
            } else {
                _localPermAction = action || 'edit';
                _localPermRecordId = recordId || null;
                _localPermRecordLabel = recordLabel || '';
                document.getElementById('localPermTitle').textContent = 'Request to ' + (_localPermAction.charAt(0).toUpperCase() + _localPermAction.slice(1)) + ' Record';
                document.getElementById('localPermRecord').textContent = recordLabel || 'Record #' + recordId;
                document.getElementById('localPermReason').value = '';
                document.getElementById('localPermError').style.display = 'none';
                document.getElementById('permissionModal').style.display = 'flex';
                setTimeout(() => document.getElementById('localPermReason').focus(), 100);
            }
        });
}

function requireAdminSync(e, recordId, recordLabel) {
    if (!IS_ADMIN) {
        e.preventDefault();
        requireAdmin(null, recordId, recordLabel, 'delete');
        return false;
    }
    return confirm('Delete this record?');
}

function closeLocalPermModal() {
    document.getElementById('permissionModal').style.display = 'none';
}

function submitLocalPermRequest() {
    const reason = document.getElementById('localPermReason').value.trim();
    if (reason.length < 5) { document.getElementById('localPermError').style.display = 'block'; return; }
    document.getElementById('localPermError').style.display = 'none';
    const btn = document.getElementById('localPermBtn');
    btn.disabled = true; btn.textContent = 'Sending...';
    fetch('/api/permission-requests', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ action: _localPermAction, module: _localPermModule, record_id: _localPermRecordId, record_label: _localPermRecordLabel, reason })
    })
    .then(r => r.json())
    .then(() => {
        closeLocalPermModal();
        btn.disabled = false; btn.textContent = 'Send Request';
        if (typeof showToast === 'function') showToast('Your request has been sent to admin for approval.', 'success', 'Request Sent');
        if (typeof pollNotifications === 'function') pollNotifications();
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Send Request'; });
}

function toggleTermsDropdown(){ var d=document.getElementById('termsDropdown'); d.style.display=d.style.display==='none'?'block':'none'; }

function toggleDevDropdown(){ var d=document.getElementById('devDropdown'); d.style.display=d.style.display==='none'?'block':'none'; }
function selectDev(v){ document.getElementById('dev_name_input').value=v; document.getElementById('devDropdown').style.display='none'; }
function filterDev(val){ var d=document.getElementById('devDropdown'),items=d.children,f=val.toUpperCase(),has=false; for(var i of items){ var show=i.textContent.toUpperCase().includes(f); i.style.display=show?'':'none'; if(show)has=true; } d.style.display=has?'block':'none'; }
document.addEventListener('click',function(e){ if(!e.target.closest('#dev_name_input') && !e.target.closest('#devDropdown') && !e.target.closest('[onclick="toggleDevDropdown()"]')) document.getElementById('devDropdown').style.display='none'; });

function fmtComma(n) {
    if (!n && n !== 0) return '';
    return parseFloat(n).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:10});
}

function onPriceSqmInput(el) {
    // Strip commas, keep only digits and decimal
    var raw = el.value.replace(/,/g, '');
    var num = parseFloat(raw);
    // Store raw numeric in hidden field
    document.getElementById('f_price_sqm').value = isNaN(num) ? '' : num;
    // Format display with commas (only if user has finished typing a valid number)
    // Use a small delay so cursor doesn't jump while typing
    clearTimeout(el._fmt);
    el._fmt = setTimeout(function() {
        if (!isNaN(num) && raw !== '') {
            el.value = fmtComma(num);
        }
    }, 800);
    computeTCP();
}

function computeTCP(){
    var area = parseFloat(document.getElementById('f_lot_area').value) || 0;
    var psqm = parseFloat(document.getElementById('f_price_sqm').value) || 0;
    var tcp  = area * psqm;
    document.getElementById('f_tcp').value = tcp ? tcp.toFixed(2) : '';
    document.getElementById('f_tcp_display').value = tcp ? fmtComma(tcp) : '';
    computeDiscount();
}
function computeDiscount(){
    var tcp  = parseFloat(document.getElementById('f_tcp').value) || 0;
    var pct  = parseFloat(document.getElementById('f_discount_pct').value) || 0;
    var val  = tcp * (pct / 100);
    var net  = tcp - val;
    document.getElementById('f_discount_val').value = val ? val.toFixed(2) : '';
    document.getElementById('f_net_tcp').value = net ? net.toFixed(2) : '';
    document.getElementById('f_net_tcp_display').value = net ? fmtComma(net) : '';
}
function computeDiscountFromValue(){
    var tcp = parseFloat(document.getElementById('f_tcp').value) || 0;
    var val = parseFloat(document.getElementById('f_discount_val').value) || 0;
    var pct = tcp > 0 ? (val / tcp) * 100 : 0;
    var net = tcp - val;
    document.getElementById('f_discount_pct').value = pct ? pct.toFixed(10).replace(/\.?0+$/, '') : '';
    document.getElementById('f_net_tcp').value = net ? net.toFixed(2) : '';
    document.getElementById('f_net_tcp_display').value = net ? fmtComma(net) : '';
}
function selectTerm(t){ document.getElementById('terms_of_payment').value=t; document.getElementById('termsDropdown').style.display='none'; }
function filterTerms(v){ var d=document.getElementById('termsDropdown'),items=d.children,f=v.toUpperCase(),has=false; for(var i of items){ var show=i.textContent.toUpperCase().includes(f); i.style.display=show?'':'none'; if(show)has=true; } d.style.display=has?'block':'none'; }
document.addEventListener('click',function(e){ var w=document.getElementById('terms_of_payment')?.closest('div'); if(w&&!w.contains(e.target)) document.getElementById('termsDropdown').style.display='none'; });

function viewRow(id){
    fetch(`/sales-marketing/${id}`).then(r=>r.json()).then(d=>{
        var fmt=v=>(v??'-'), fmtD=v=>v?new Date(v).toLocaleDateString('en-US',{month:'short',day:'2-digit',year:'numeric'}):'-';
        var fmtP=v=>v?'₱'+parseFloat(v).toLocaleString('en-US',{minimumFractionDigits:2}):'-';
        var fields=[
            ["Developer's Name",fmt(d.developer_name)],
            ['Project Name',fmt(d.project_name)],
            ['Block & Lot Number',fmt(d.block_lot_number)],
            ["Client's Name",fmt(d.client_name)],
            ['Lot Area',d.lot_area?parseFloat(d.lot_area).toFixed(2)+' sqm':'-'],
            ['Price Per SQM',fmtP(d.price_sqm)],
            ['TCP',fmtP(d.tcp)],
            ['Discount',d.discount?parseFloat(d.discount).toFixed(2)+'%':'-'],
            ['Net TCP',fmtP(d.net_tcp)],
            ['Terms of Payment',fmt(d.terms_of_payment)],
            ['Reservation Date',fmtD(d.reservation_date)],
            ['Date of Downpayment',fmtD(d.date_of_downpayment)],
            ["Agent's Name",fmt(d.agent_name)],
            ['Client Status',fmt(d.status)||'No Status'],
        ];
        document.getElementById('viewContent').innerHTML=fields.map(([l,v])=>`<div style="display:flex;flex-direction:column;gap:4px"><label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase">${l}</label><div style="font-size:14px;color:#374151;font-weight:500;padding:10px 14px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb">${v}</div></div>`).join('');
        document.getElementById('viewModal').style.display='flex';
    });
}
function editRow(id){
    fetch(`/sales-marketing/${id}`).then(r=>r.json()).then(d=>{
        document.getElementById('edit_id').value=d.id;
        document.getElementById('edit_developer_name').value=d.developer_name??'';
        document.getElementById('edit_project_name').value=d.project_name??'';
        document.getElementById('edit_block_lot_number').value=d.block_lot_number??'';
        document.getElementById('edit_client_name').value=d.client_name??'';
        document.getElementById('edit_lot_area').value=d.lot_area??'';
        document.getElementById('edit_price_sqm').value=d.price_sqm??'';
        document.getElementById('edit_tcp').value=d.tcp??'';
        document.getElementById('edit_discount').value=d.discount??'';
        document.getElementById('edit_net_tcp').value=d.net_tcp??'';
        document.getElementById('edit_terms_of_payment').value=d.terms_of_payment??'';
        document.getElementById('edit_reservation_date').value=d.reservation_date?d.reservation_date.split('T')[0]:'';
        document.getElementById('edit_number_of_units').value=d.number_of_units??1;
        document.getElementById('edit_date_of_downpayment').value=d.date_of_downpayment?d.date_of_downpayment.split('T')[0]:'';
        document.getElementById('edit_agent_name').value=d.agent_name??'';
        document.getElementById('edit_client_status').value=d.status??'';
        document.getElementById('editForm').action=`/client-database/${d.id}`;
        document.getElementById('editModal').style.display='flex';
    });
}

document.addEventListener('DOMContentLoaded',function(){
    cdFilter();

    // Highlight row from permission notification
    const params = new URLSearchParams(window.location.search);
    const highlightId = params.get('highlight');
    const hlStatus = params.get('status');
    const hlAction = params.get('action');
    if (highlightId) {
        // Wait for full page render then scroll
        function doHighlight() {
            const row = document.querySelector('tr[data-id="' + highlightId + '"]');
            if (!row) return;

            // Force show the row even if filtered
            row.style.display = '';

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

            // Find the scrollable container and scroll to row
            const scroller = document.querySelector('.page-content');
            if (scroller) {
                const rowRect = row.getBoundingClientRect();
                const scrollerRect = scroller.getBoundingClientRect();
                const scrollTo = scroller.scrollTop + rowRect.top - scrollerRect.top - 100;
                scroller.scrollTo({ top: scrollTo, behavior: 'smooth' });
            } else {
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            setTimeout(function() {
                row.style.background   = '';
                row.style.outline      = '';
                const badge = row.querySelector('.hl-badge');
                if (badge) badge.remove();
            }, 10000);
        }

        // Try multiple times to ensure table is rendered
        setTimeout(doHighlight, 800);
        setTimeout(doHighlight, 1500);
    }
});

function cdFilter() {
    var raw = (document.getElementById('cdSearch')?.value || '').toLowerCase().trim();
    var statusFilter = (document.getElementById('cdStatusFilter')?.value || '').toLowerCase();
    // Split by spaces — each word must match somewhere in the row (AND logic)
    var keywords = raw ? raw.split(/\s+/).filter(k => k.length > 0) : [];

    var rows = document.querySelectorAll('#cdTableBody tr');
    var visible = 0;
    rows.forEach(function(r) {
        var text = r.textContent.toLowerCase();
        // Check all keywords match
        var keyMatch = keywords.every(k => text.includes(k));
        // Check status filter
        var statusCell = r.querySelector('[data-client-status]');
        var rowStatus = statusCell ? statusCell.getAttribute('data-client-status').toLowerCase() : '';
        var statusMatch = true;
        if (statusFilter === 'done') statusMatch = rowStatus === 'done';
        else if (statusFilter === 'cancelled') statusMatch = rowStatus === 'cancelled';
        else if (statusFilter === 'none') statusMatch = rowStatus === '';

        var show = keyMatch && statusMatch;
        r.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    var countEl = document.getElementById('cdCount');
    if (countEl) countEl.textContent = visible + ' record(s) shown';
}

// ── Prefill from site visit Reserve button ──
(function() {
    const p = new URLSearchParams(window.location.search);
    const client    = p.get('prefill_client');
    const project   = p.get('prefill_project');
    const agent     = p.get('prefill_agent');
    const date      = p.get('prefill_date');
    const developer = p.get('prefill_developer');
    // Handle ?view= param from sidebar sub-links
    if (!client && !project) return;

    document.addEventListener('DOMContentLoaded', function() {
        const set = (name, val) => {
            const el = document.querySelector('form [name="' + name + '"]');
            if (el && val) el.value = val;
        };
        set('client_name',    client);
        set('project_name',   project);
        set('agent_name',     agent);
        set('reservation_date', date);
        if (developer) {
            // developer_name uses a custom dropdown input
            const devInput = document.getElementById('dev_name_input');
            if (devInput) devInput.value = developer;
        }

        // Scroll to form and highlight it
        const form = document.querySelector('form[action*="client-database"]');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            form.style.transition = 'box-shadow .4s';
            form.style.boxShadow  = '0 0 0 3px #7c3aed, 0 8px 32px rgba(124,58,237,.15)';
            setTimeout(() => { form.style.boxShadow = ''; }, 2500);
        }

        // Toast
        const toast = document.createElement('div');
        toast.textContent = '✓ Form pre-filled from site visit data';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#7c3aed;color:white;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.2)';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);

        window.history.replaceState({}, '', window.location.pathname);
    });
})();
</script>
<!-- Downpayment Installment Modal -->
<div id="dpModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:white;border-radius:16px;width:90%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,0.3);overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);color:white;padding:20px 24px;display:flex;justify-content:space-between;align-items:center;flex-shrink:0">
            <h3 style="margin:0;font-size:18px;font-weight:700">Downpayment</h3>
            <button onclick="document.getElementById('dpModal').style.display='none'" style="background:rgba(255,255,255,0.2);border:none;color:white;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:18px">✕</button>
        </div>

        {{-- Step 1: Choose type --}}
        <div id="dp_step_type" style="padding:24px;display:flex;flex-direction:column;gap:12px;flex-shrink:0">
            <p style="font-size:13px;color:#64748b;margin:0;">Select downpayment type:</p>
            <div style="display:flex;gap:12px">
                <button onclick="selectDPType('spot')" style="flex:1;padding:14px;border:2px solid #e2e8f0;border-radius:10px;background:white;cursor:pointer;font-size:14px;font-weight:600;color:#374151;transition:all .2s" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#1e4575'" onmouseout="this.style.background='white';this.style.borderColor='#e2e8f0'">
                    💰 Spot Downpayment
                </button>
                <button onclick="selectDPType('installment')" style="flex:1;padding:14px;border:2px solid #e2e8f0;border-radius:10px;background:white;cursor:pointer;font-size:14px;font-weight:600;color:#374151;transition:all .2s" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#1e4575'" onmouseout="this.style.background='white';this.style.borderColor='#e2e8f0'">
                    📅 Installment Downpayment
                </button>
            </div>
            <div style="display:flex;gap:12px">
                <button onclick="selectDPType('others')" style="flex:1;padding:14px;border:2px solid #e2e8f0;border-radius:10px;background:white;cursor:pointer;font-size:14px;font-weight:600;color:#374151;transition:all .2s" onmouseover="this.style.background='#eff6ff';this.style.borderColor='#1e4575'" onmouseout="this.style.background='white';this.style.borderColor='#e2e8f0'">
                    📝 Others
                </button>
            </div>
        </div>

        {{-- Spot DP --}}
        <div id="dp_spot_section" style="display:none;padding:0 24px 24px;flex-direction:column;gap:12px">
            <div>
                <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px">Amount</label>
                <div style="display:flex;align-items:center;border:2px solid #d0d5dd;border-radius:8px;overflow:hidden;background:white;">
                    <input type="number" id="dp_spot_amount" step="0.01" min="0" placeholder="Enter amount here"
                        style="flex:1;padding:10px 12px;border:none;outline:none;font-size:14px;background:transparent;">
                    <button onclick="saveSpotDP()" style="padding:10px 16px;background:linear-gradient(135deg,#A37929,#d4a03a);color:white;border:none;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">Paid</button>
                </div>
            </div>
        </div>

        {{-- Installment DP --}}
        <div id="dp_installment_section" style="display:none;flex-direction:column;flex:1;min-height:0">
            <div style="padding:16px 24px;border-bottom:1px solid #e5e7eb;display:flex;gap:12px;align-items:flex-end;flex-shrink:0">
                <div style="flex:1">
                    <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px">Total Amount</label>
                    <input type="number" id="dp_total_amount" step="0.01" min="0" placeholder="0.00"
                        style="width:100%;padding:9px 12px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;box-sizing:border-box">
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px">Terms</label>
                    <select id="dp_terms_select" style="padding:9px 12px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px">
                        @for($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <button onclick="setupInstallments()" style="padding:9px 16px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap">Set Terms</button>
            </div>
            <div id="dp_installments_list" style="padding:16px 24px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:10px;min-height:60px">
                <div style="text-align:center;color:#94a3b8;padding:20px;font-size:13px;">Set amount and terms, then click "Set Terms".</div>
            </div>
        </div>

        {{-- Others DP --}}
        <div id="dp_others_section" style="display:none;padding:0 24px 24px;flex-direction:column;gap:12px">
            <div>
                <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px">Amount</label>
                <input type="number" id="dp_others_amount" step="0.01" min="0" placeholder="Enter amount"
                    style="width:100%;padding:10px 12px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;box-sizing:border-box">
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#1e4575;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px">Notes <span style="font-weight:400;color:#94a3b8">(e.g. 3 payments over 18 months)</span></label>
                <textarea id="dp_others_notes" rows="3" placeholder="Describe the payment arrangement..."
                    style="width:100%;padding:10px 12px;border:2px solid #d0d5dd;border-radius:8px;font-size:13px;box-sizing:border-box;resize:vertical;font-family:inherit"></textarea>
            </div>
        </div>

        <div style="padding:16px 24px;border-top:1px solid #e5e7eb;flex-shrink:0">
            <div id="dp_footer_type" style="display:flex">
            </div>
            <div id="dp_footer_spot" style="display:none;gap:10px">
                <button onclick="selectDPType('spot'); document.getElementById('dp_step_type').style.display='flex';" style="flex:1;padding:10px;background:#f1f5f9;color:#374151;border:1.5px solid #e2e8f0;border-radius:8px;font-weight:600;cursor:pointer">Back</button>
                <button onclick="saveSpotDP()" style="flex:1;padding:10px;background:linear-gradient(135deg,#A37929,#d4a03a);color:white;border:none;border-radius:8px;font-weight:700;cursor:pointer">Save</button>
            </div>
            <div id="dp_footer_installment" style="display:none;gap:10px">
                <button onclick="document.getElementById('dp_step_type').style.display='flex';document.getElementById('dp_installment_section').style.display='none';" style="flex:1;padding:10px;background:#f1f5f9;color:#374151;border:1.5px solid #e2e8f0;border-radius:8px;font-weight:600;cursor:pointer">Back</button>
                <button onclick="document.getElementById('dpModal').style.display='none'" style="flex:1;padding:10px;background:linear-gradient(135deg,#A37929,#d4a03a);color:white;border:none;border-radius:8px;font-weight:700;cursor:pointer">Done</button>
            </div>
            <div id="dp_footer_others" style="display:none;gap:10px">
                <button onclick="document.getElementById('dp_step_type').style.display='flex';document.getElementById('dp_others_section').style.display='none';document.getElementById('dp_footer_others').style.display='none';document.getElementById('dp_footer_type').style.display='flex';" style="flex:1;padding:10px;background:#f1f5f9;color:#374151;border:1.5px solid #e2e8f0;border-radius:8px;font-weight:600;cursor:pointer">Back</button>
                <button onclick="saveOthersDP()" style="flex:1;padding:10px;background:linear-gradient(135deg,#A37929,#d4a03a);color:white;border:none;border-radius:8px;font-weight:700;cursor:pointer">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
let _dpRecordId = null;
const _dpCsrf = document.querySelector('meta[name=csrf-token]')?.content || '';
const _isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};

function openDPModal(id, amount, terms, perTerm, status) {
    _dpRecordId = id;
    document.getElementById('dp_total_amount').value = amount || '';
    document.getElementById('dp_terms_select').value = terms || 1;
    document.getElementById('dp_spot_amount').value = amount || '';

    // Show type selection first, unless already set
    document.getElementById('dp_step_type').style.display = 'flex';
    document.getElementById('dp_spot_section').style.display = 'none';
    document.getElementById('dp_installment_section').style.display = 'none';
    document.getElementById('dp_footer_type').style.display = 'flex';
    document.getElementById('dp_footer_spot').style.display = 'none';
    document.getElementById('dp_footer_installment').style.display = 'none';

    // If already has installments, go straight to installment view
    if (terms > 1 || (status && status.includes('month'))) {
        selectDPType('installment');
        loadInstallments();
    } else if (status === 'Spot Paid') {
        selectDPType('spot');
    }

    document.getElementById('dpModal').style.display = 'flex';
}

function selectDPType(type) {
    document.getElementById('dp_step_type').style.display = 'none';
    document.getElementById('dp_footer_type').style.display = 'none';
    document.getElementById('dp_others_section').style.display = 'none';
    document.getElementById('dp_footer_others').style.display = 'none';
    if (type === 'spot') {
        document.getElementById('dp_spot_section').style.display = 'flex';
        document.getElementById('dp_installment_section').style.display = 'none';
        document.getElementById('dp_footer_spot').style.display = 'flex';
        document.getElementById('dp_footer_installment').style.display = 'none';
    } else if (type === 'others') {
        document.getElementById('dp_spot_section').style.display = 'none';
        document.getElementById('dp_installment_section').style.display = 'none';
        document.getElementById('dp_others_section').style.display = 'flex';
        document.getElementById('dp_footer_spot').style.display = 'none';
        document.getElementById('dp_footer_installment').style.display = 'none';
        document.getElementById('dp_footer_others').style.display = 'flex';
    } else {
        document.getElementById('dp_spot_section').style.display = 'none';
        document.getElementById('dp_installment_section').style.display = 'flex';
        document.getElementById('dp_footer_spot').style.display = 'none';
        document.getElementById('dp_footer_installment').style.display = 'flex';
        loadInstallments();
    }
}

function saveSpotDP() {
    const amount = document.getElementById('dp_spot_amount').value;
    fetch(`/client-database/${_dpRecordId}/downpayment-status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _dpCsrf },
        body: JSON.stringify({ _method: 'PATCH', downpayment_status: 'Spot Paid', downpayment_amount: amount })
    }).then(() => {
        const form = document.createElement('form');
        form.method = 'POST'; form.action = `/client-database/${_dpRecordId}/downpayment-status`;
        form.innerHTML = `<input name="_token" value="${_dpCsrf}"><input name="_method" value="PATCH"><input name="downpayment_status" value="Spot Paid"><input name="downpayment_amount" value="${amount}">`;
        document.body.appendChild(form); form.submit();
    });
}

function saveOthersDP() {
    const amount = document.getElementById('dp_others_amount').value;
    const notes  = document.getElementById('dp_others_notes').value;
    const status = 'Others' + (notes ? ': ' + notes.substring(0, 40) : '');
    const form = document.createElement('form');
    form.method = 'POST'; form.action = `/client-database/${_dpRecordId}/downpayment-status`;
    form.innerHTML = `<input name="_token" value="${_dpCsrf}"><input name="_method" value="PATCH"><input name="downpayment_status" value="${status}"><input name="downpayment_amount" value="${amount}">`;
    document.body.appendChild(form); form.submit();
}

function loadInstallments() {
    fetch(`/api/client-database/${_dpRecordId}/installments`)
        .then(r => r.json()).then(data => renderInstallments(data));
}

function setupInstallments() {
    const terms  = document.getElementById('dp_terms_select').value;
    const amount = parseFloat(document.getElementById('dp_total_amount').value) || 0;
    fetch(`/api/client-database/${_dpRecordId}/installments/setup`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _dpCsrf },
        body: JSON.stringify({ terms, total_amount: amount })
    }).then(r => r.json()).then(data => renderInstallments(data));
}

function renderInstallments(list) {
    const container = document.getElementById('dp_installments_list');
    if (!list.length) {
        container.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:20px;font-size:13px;">Set amount and terms, then click "Set Terms".</div>';
        return;
    }
    container.innerHTML = list.map(inst => {
        const border = inst.is_paid ? '#bbf7d0' : '#e2e8f0';
        const bg     = inst.is_paid ? '#f0fdf4' : '#f8fafc';

        if (_isAdmin) {
            // Admin: amount input (always editable) + Paid/Undo button
            const actionBtn = inst.is_paid
                ? `<button onclick="unmarkPaid(${inst.id})" style="padding:10px 14px;background:#dcfce7;color:#166534;border:none;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;border-left:1.5px solid #bbf7d0;" title="Click to undo">✓ Paid ↩</button>`
                : `<button onclick="markPaid(${inst.id})" style="padding:10px 16px;background:linear-gradient(135deg,#A37929,#d4a03a);color:white;border:none;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">Paid</button>`;
            return `
                <div style="display:flex;align-items:center;gap:0;border:1.5px solid ${border};border-radius:10px;overflow:hidden;background:${bg};">
                    <span style="font-size:13px;font-weight:700;color:#1e4575;padding:10px 14px;white-space:nowrap;border-right:1.5px solid ${border};">Term ${inst.term_number}</span>
                    <input type="number" id="inst_amount_${inst.id}" value="${inst.amount || ''}" placeholder="Enter amount here" step="0.01" min="0"
                        onblur="saveInstallmentAmount(${inst.id})"
                        style="flex:1;padding:10px 12px;border:none;outline:none;font-size:13px;background:transparent;${inst.is_paid ? 'color:#166534;' : ''}">
                    ${actionBtn}
                </div>`;
        } else {
            // Staff: no amount input, just term label + Paid button (locked if already paid)
            const actionBtn = inst.is_paid
                ? `<span style="padding:10px 14px;background:#dcfce7;color:#166534;font-size:12px;font-weight:700;white-space:nowrap;border-left:1.5px solid #bbf7d0;">✓ Paid</span>`
                : `<button onclick="markPaid(${inst.id})" style="padding:10px 16px;background:linear-gradient(135deg,#A37929,#d4a03a);color:white;border:none;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">Paid</button>`;
            return `
                <div style="display:flex;align-items:center;gap:0;border:1.5px solid ${border};border-radius:10px;overflow:hidden;background:${bg};">
                    <span style="font-size:13px;font-weight:700;color:#1e4575;padding:10px 14px;white-space:nowrap;border-right:1.5px solid ${border};flex:1;">Term ${inst.term_number}${inst.amount ? ' — ₱' + Number(inst.amount).toLocaleString() : ''}</span>
                    ${actionBtn}
                </div>`;
        }
    }).join('');
}

function saveInstallmentAmount(instId) {
    const amount = document.getElementById(`inst_amount_${instId}`).value;
    fetch(`/api/installments/${instId}/amount`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _dpCsrf },
        body: JSON.stringify({ amount })
    });
}

function markPaid(instId) {
    if (!confirm('Mark this term as paid?')) return;
    fetch(`/api/installments/${instId}/paid`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _dpCsrf },
        body: JSON.stringify({})
    }).then(r => r.json()).then(() => loadInstallments());
}

function unmarkPaid(instId) {
    if (!confirm('Undo this payment? This will mark the term as unpaid.')) return;
    fetch(`/api/installments/${instId}/unpaid`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _dpCsrf },
        body: JSON.stringify({})
    }).then(r => r.json()).then(() => loadInstallments());
}
</script>

@endsection
