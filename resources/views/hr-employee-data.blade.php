@extends('layouts.dashboard')
@section('title', 'Employee Data')
@section('content')

<style>
.hr-banner { background:linear-gradient(135deg,#0f2444 0%,#1e4575 50%,#2563eb 100%);border-radius:20px;padding:36px 40px;margin-bottom:28px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25); }
.hr-banner-content { position:relative;z-index:2; }
.hr-banner-label { font-size:11px;font-weight:700;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px; }
.hr-banner h1 { font-size:30px;font-weight:800;color:white;margin:0 0 6px; }
.hr-banner p { font-size:14px;color:rgba(255,255,255,.7);margin:0; }
.hr-banner-deco { position:absolute;top:0;right:0;width:320px;height:100%;pointer-events:none; }
.hr-banner-deco span { position:absolute;border-radius:50%;background:rgba(255,255,255,.06); }

.hr-card { background:white;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);margin-bottom:20px;overflow:hidden; }
.hr-card-header { padding:12px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between; }
.hr-card-title { font-size:14px;font-weight:700;color:#0f172a; }
.hr-card-sub { font-size:12px;color:#94a3b8;margin-top:2px; }
.hr-card-body { padding:16px; }

.hr-form-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px; }
.hr-form-group label { display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px; }
.hr-input { width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;color:#0f172a;background:#fff;outline:none;transition:border-color .2s;box-sizing:border-box; }
.hr-input:focus { border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1); }

.hr-table { width:100%;border-collapse:collapse;font-size:13px; }
.hr-table thead tr { background:linear-gradient(135deg,#0f2444,#1e4575); }
.hr-table thead th { padding:8px 12px;text-align:left;font-size:11px;font-weight:700;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.6px; }
.hr-table tbody tr { border-bottom:1px solid #f1f5f9;transition:background .15s; }
.hr-table tbody tr:hover { background:#f8fafc; }
.hr-table tbody td { padding:9px 12px;color:#374151; }
.hr-table tbody tr.edit-row { background:#eff6ff; }

.hr-badge { display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700; }
.hr-badge-admin { background:#dbeafe;color:#1e4575; }
.hr-badge-staff { background:#f1f5f9;color:#64748b; }
.hr-badge-active { background:#dcfce7;color:#166534; }
.hr-badge-pending { background:#fef3c7;color:#92400e; }

.hr-btn { padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;border:none;transition:all .2s; }
.hr-btn-primary { background:linear-gradient(135deg,#1e4575,#2563eb);color:white; }
.hr-btn-primary:hover { opacity:.9; }
.hr-btn-danger { background:#fee2e2;color:#991b1b; }
.hr-btn-danger:hover { background:#fecaca; }
.hr-btn-ghost { background:#f1f5f9;color:#374151; }
.hr-btn-ghost:hover { background:#e2e8f0; }
.hr-btn-lg { padding:10px 24px;font-size:13px; }
</style>

<div class="hr-banner">
    <div class="hr-banner-content">
        <div class="hr-banner-label">Human Resource</div>
        <h1>Employee Data</h1>
        <p>Manage and update employment details for all active users</p>
    </div>
    <div class="hr-banner-deco">
        <span style="width:220px;height:220px;top:-60px;right:-40px;"></span>
        <span style="width:140px;height:140px;bottom:-30px;right:100px;"></span>
    </div>
</div>

@if(session('emp_success') || session('success'))
<div style="background:#d1fae5;color:#065f46;padding:12px 18px;border-radius:10px;margin-bottom:20px;font-weight:600;display:flex;align-items:center;gap:8px;">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('emp_success') ?? session('success') }}
</div>
@endif

@php $isAdmin = auth()->user()->isAdmin(); @endphp

{{-- Add New Employee --}}
<div class="hr-card">
    <div class="hr-card-header">
        <div>
            <div class="hr-card-title">Add New Employee</div>
            <div class="hr-card-sub">Pre-register an employee record</div>
        </div>
    </div>
    <div class="hr-card-body">
        <form method="POST" action="{{ route('settings.employee.add') }}">@csrf
            <div class="hr-form-grid">
                <div class="hr-form-group"><label>Name <span style="color:#ef4444;">*</span></label><input class="hr-input" type="text" name="name" required placeholder="Full name"></div>
                <div class="hr-form-group"><label>Position <span style="color:#ef4444;">*</span></label><input class="hr-input" type="text" name="position" required placeholder="Job title"></div>
                <div class="hr-form-group"><label>Employee ID <span style="color:#ef4444;">*</span></label><input class="hr-input" type="text" name="employee_id" required placeholder="e.g. 0050"></div>
                <div class="hr-form-group"><label>Date Hired <span style="color:#ef4444;">*</span></label><input class="hr-input" type="date" name="date_hired" required></div>
            </div>
            <div style="margin-top:18px;">
                <button type="submit" class="hr-btn hr-btn-primary hr-btn-lg">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Employee
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Employee Records --}}
<div class="hr-card">
    <div class="hr-card-header">
        <div>
            <div class="hr-card-title">Employee Records</div>
            <div class="hr-card-sub">{{ $activeUsers->count() }} employees on record</div>
        </div>
    </div>
    <div style="overflow-x:auto;">
        @if($activeUsers->isEmpty())
            <div style="padding:40px;text-align:center;color:#94a3b8;">No employees yet.</div>
        @else
        <table class="hr-table">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Email</th><th>Position</th><th>Employee ID</th><th>Date Hired</th><th>Role</th><th>Status</th>
                    @if($isAdmin)<th>Actions</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($activeUsers as $i => $u)
                <tr id="emp-view-{{ $u->id }}">
                    <td style="color:#cbd5e1;font-weight:600;">{{ $i + 1 }}</td>
                    <td style="font-weight:700;color:#0f172a;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#1e4575,#2563eb);display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700;flex-shrink:0;">{{ strtoupper(substr($u->name,0,1)) }}</div>
                            {{ $u->name }}
                        </div>
                    </td>
                    <td style="color:#64748b;font-size:12px;">{{ $u->email }}</td>
                    <td>{{ $u->position ?: '—' }}</td>
                    <td><span style="font-family:monospace;background:#f1f5f9;padding:2px 8px;border-radius:6px;font-size:12px;">{{ $u->employee_id ?: '—' }}</span></td>
                    <td>{{ $u->date_hired ? $u->date_hired->format('M d, Y') : '—' }}</td>
                    <td><span class="hr-badge {{ $u->role === 'admin' ? 'hr-badge-admin' : 'hr-badge-staff' }}">{{ ucfirst($u->role ?? 'staff') }}</span></td>
                    <td><span class="hr-badge {{ $u->status === 'active' ? 'hr-badge-active' : 'hr-badge-pending' }}">{{ ucfirst($u->status ?? '—') }}</span></td>
                    @if($isAdmin)
                    <td style="white-space:nowrap;">
                        <button type="button" class="hr-btn hr-btn-primary" onclick="toggleEmpEdit({{ $u->id }})">Edit</button>
                        @if($u->id !== auth()->id())
                        <form method="POST" action="{{ route('settings.users.remove', $u->id) }}" style="display:inline;" onsubmit="return confirm('Remove {{ addslashes($u->name) }}?')">@csrf @method('DELETE')
                            <button type="submit" class="hr-btn hr-btn-danger">Delete</button>
                        </form>
                        @endif
                    </td>
                    @endif
                </tr>
                <tr id="emp-edit-{{ $u->id }}" class="edit-row" style="display:none;">
                    <td colspan="{{ $isAdmin ? 9 : 8 }}" style="padding:16px 20px;">
                        <form method="POST" action="{{ route('settings.users.employee-info', $u->id) }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;background:#eff6ff;padding:16px;border-radius:10px;border:1.5px solid #bfdbfe;">@csrf
                            <div style="flex:1.5;min-width:130px;"><label style="font-size:11px;font-weight:700;color:#1e4575;display:block;margin-bottom:4px;">Position</label><input class="hr-input" type="text" name="position" value="{{ $u->position }}"></div>
                            <div style="flex:1;min-width:110px;"><label style="font-size:11px;font-weight:700;color:#1e4575;display:block;margin-bottom:4px;">Employee ID</label><input class="hr-input" type="text" name="employee_id" value="{{ $u->employee_id }}"></div>
                            <div style="flex:1;min-width:140px;"><label style="font-size:11px;font-weight:700;color:#1e4575;display:block;margin-bottom:4px;">Date Hired</label><input class="hr-input" type="date" name="date_hired" value="{{ $u->date_hired ? $u->date_hired->format('Y-m-d') : '' }}"></div>
                            <div style="display:flex;gap:8px;flex-shrink:0;">
                                <button type="submit" class="hr-btn hr-btn-primary">Save</button>
                                <button type="button" class="hr-btn hr-btn-ghost" onclick="toggleEmpEdit({{ $u->id }})">Cancel</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

<script>
function toggleEmpEdit(id) {
    var edit = document.getElementById('emp-edit-' + id);
    edit.style.display = edit.style.display === 'none' ? 'table-row' : 'none';
}
</script>
@endsection
