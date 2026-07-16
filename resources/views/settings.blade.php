@extends('layouts.dashboard')

@section('content')

<style>

/* Remove page-content padding for settings full-bleed layout (desktop only —
   on mobile the sidebar stacks above the content and the page itself needs
   to scroll, so we must not clip it with overflow:hidden there) */

@media (min-width: 769px) {   
    .page-content { padding: 0 !important; overflow: hidden !important; height: 100% !important; }
    .main-content { overflow: hidden !important; }
}

@media (max-width: 768px) {
    /* Restore the site's normal scroll pattern: .page-content is the one
       bounded, scrollable box (there's no scrollable ancestor above it —
       .content-wrapper/.main-content are overflow:hidden by design). */
    .page-content { padding: 0 !important; overflow-y: auto !important; height: 100% !important; flex: 1; }
    .main-content { overflow: hidden !important; }
}

/* ===== SETTINGS PAGE ===== */

.st-page-wrap {

    display: flex;

    height: 100%;

    width: 100%;

    overflow: hidden;

    margin: 0;

    background: #f0f2f5;

    position: relative;

    z-index: 0;

    min-width: 0;

}

/* Settings Sidebar */

.st-sidebar {

    width: 230px;

    flex-shrink: 0;

    background: linear-gradient(180deg, #0f2444 0%, #1a3a6b 100%);

    display: flex;

    flex-direction: column;

    box-shadow: 4px 0 20px rgba(0,0,0,.2);

    overflow: hidden;

    position: relative;

    z-index: 1;

}

.st-nav-scroll { flex: 1; overflow-y: auto; min-height: 0; }

.st-nav-scroll::-webkit-scrollbar { width: 3px; }

.st-nav-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 2px; }

.st-sidebar-hdr {

    padding: 0 18px;

    height: 64px;

    display: flex;

    align-items: center;

    gap: 10px;

    border-bottom: 2px solid #A37929;

    background: rgba(0,0,0,.15);

    flex-shrink: 0;

}
.st-sidebar-hdr h2 { font-size: 15px; font-weight: 700; color: white; margin: 0; letter-spacing: .3px; }

.st-sidebar-hdr svg { color: #d4a03a; flex-shrink: 0; }

.st-nav-label {

    font-size: 9px; font-weight: 700; color: rgba(255,255,255,.5);

    letter-spacing: 1.5px; padding: 12px 18px 4px; text-transform: uppercase;

}

.st-nav-btn {

    display: flex; align-items: center; gap: 10px;

    width: calc(100% - 16px); margin: 1px 8px; padding: 8px 12px;

    background: none; border: none; font-size: 13px; color: rgba(255,255,255,.75);

    cursor: pointer; text-align: left; transition: all .2s ease;

    font-family: inherit; border-radius: 8px; position: relative; overflow: hidden;

}

.st-nav-btn::after {

    content: ''; position: absolute; left: 0; top: 20%; bottom: 20%;

    width: 3px; background: #d4a03a; border-radius: 0 3px 3px 0;

    transform: scaleY(0); transition: transform .2s ease;

}

.st-nav-btn:hover { background: rgba(255,255,255,.07); color: rgba(255,255,255,.9); transform: translateX(2px); }

.st-nav-btn:hover::after { transform: scaleY(1); }

.st-nav-btn.active { background: rgba(163,121,41,.2); color: #d4a03a; font-weight: 600; }

.st-nav-btn.active::after { transform: scaleY(1); }

.st-nav-btn svg { width: 15px; height: 15px; flex-shrink: 0; color: rgba(255,255,255,.6); transition: color .2s; }

.st-nav-btn.active svg { color: #d4a03a; }

.st-nav-btn:hover svg { color: rgba(255,255,255,.8); }

/* Content area */

.st-content {

    flex: 1;

    overflow-y: auto;

    overflow-x: hidden;

    padding: 0 24px 28px 24px;

    background: #f0f2f5;

    min-height: 0;

    min-width: 0;

    position: relative;

    z-index: 1;

}

.st-content::-webkit-scrollbar { width: 5px; }

.st-content::-webkit-scrollbar-thumb { background: #d0d5dd; border-radius: 3px; }

/* Panel animation */

.st-panel { display: none !important; }

.st-panel.active { display: block !important; animation: panelIn .3s ease forwards; }

@keyframes panelIn { from { opacity: 0; } to { opacity: 1; } }
/* Page header */

.st-page-header { margin-bottom: 20px; padding: 32px 0 0 0; }

.st-page-title { font-size: 22px; font-weight: 700; color: #1e4575; margin: 0 0 4px; }

.st-page-sub { font-size: 12px; color: #94a3b8; margin: 0; }

/* Cards */

.st-card {

    background: white; border-radius: 12px; border: 1px solid #e8ecf0;

    margin-bottom: 14px; box-shadow: 0 1px 4px rgba(0,0,0,.05); transition: box-shadow .2s;

}

.st-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }

.st-card-hdr {

    padding: 14px 18px; border-bottom: 1px solid #f1f5f9;

    display: flex; align-items: center; justify-content: space-between;

}

.st-card-hdr-text h3 { font-size: 13px; font-weight: 700; color: #0f172a; margin: 0 0 2px; }

.st-card-hdr-text p { font-size: 11px; color: #94a3b8; margin: 0; }

.st-card-body { padding: 16px 18px; }

/* Form elements */

.st-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.st-form-group { display: flex; flex-direction: column; gap: 5px; }

.st-form-group.full { grid-column: 1/-1; }

.st-label { font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: .4px; }

.st-input {

    padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px;

    font-size: 13px; color: #374151; transition: border-color .2s; background: white; font-family: inherit;

}

.st-input:focus { outline: none; border-color: #1e4575; box-shadow: 0 0 0 3px rgba(30,69,117,.08); }

.st-select { padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; color: #374151; background: white; font-family: inherit; }

/* Buttons */

.st-btn {

    padding: 9px 20px; border-radius: 8px; font-size: 13px; font-weight: 600;

    cursor: pointer; transition: all .2s; border: none; font-family: inherit;

}

.st-btn-primary { background: linear-gradient(135deg,#1e4575,#2563eb); color: white; box-shadow: 0 2px 8px rgba(30,69,117,.25); }

.st-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(30,69,117,.35); }

.st-btn-danger { background: white; color: #dc2626; border: 1.5px solid #fecaca; }

.st-btn-danger:hover { background: #fee2e2; }

.st-btn-sm { padding: 6px 14px; font-size: 12px; }

/* Alert */

.st-alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 13px; font-weight: 500; border-left: 3px solid #10b981; background: #f0fdf4; color: #166534; display: flex; align-items: center; gap: 8px; }

/* Avatar */

.avatar-wrap { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; padding: 16px; background: linear-gradient(135deg,#f0f4ff,#e8edf5); border-radius: 10px; }

.avatar-img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,.1); }

.avatar-initials { width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg,#1e4575,#2563eb); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; color: white; flex-shrink: 0; }

.avatar-info h3 { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 3px; }

.avatar-info p { font-size: 12px; color: #64748b; margin: 0; }

/* Stat grid */

.st-stat-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; }

.st-stat-box { background: white; border-radius: 10px; padding: 14px 16px; border: 1px solid #e8ecf0; }

.st-stat-box-val { font-size: 20px; font-weight: 700; color: #1e4575; }

.st-stat-box-lbl { font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-top: 2px; }

/* User table */

.st-user-table { width: 100%; border-collapse: collapse; }

.st-user-table th { padding: 10px 14px; text-align: left; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; border-bottom: 2px solid #f1f5f9; }

.st-user-table td { padding: 12px 14px; font-size: 13px; color: #374151; border-bottom: 1px solid #f8fafc; vertical-align: middle; }

.st-user-table tr:last-child td { border-bottom: none; }

.st-user-table tr:hover td { background: #f8fafc; }

/* Visibility grid */

.vis-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 10px; }

.vis-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; }

.vis-item input[type=checkbox] { width: 16px; height: 16px; accent-color: #1e4575; cursor: pointer; }

.vis-item label { font-size: 13px; color: #374151; font-weight: 500; cursor: pointer; }

/* Email row */

.email-row { display: flex; gap: 8px; margin-bottom: 8px; }

/* Misc */

.st-empty { text-align: center; padding: 32px; color: #94a3b8; font-size: 13px; }

.st-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }

.st-badge-admin { background: #dbeafe; color: #1e40af; }

.st-badge-staff { background: #f1f5f9; color: #64748b; }

.st-badge-you { background: #dbeafe; color: #1e40af; }

.perm-item { padding: 14px 16px; border-radius: 10px; border: 1px solid #e8ecf0; margin-bottom: 10px; background: white; }

.perm-item.pending-perm { border-left: 3px solid #f59e0b; background: #fffbeb; }

.perm-item.approved-perm { border-left: 3px solid #22c55e; }

.perm-item.rejected-perm { border-left: 3px solid #ef4444; }

.perm-actions { display: flex; gap: 8px; margin-top: 10px; }

.log-item { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f8fafc; align-items: flex-start; }

.log-item:last-child { border-bottom: none; }

.log-dot { width: 8px; height: 8px; border-radius: 50%; background: #1e4575; flex-shrink: 0; margin-top: 5px; }

.log-dot.create { background: #22c55e; }

.log-dot.update { background: #3b82f6; }

.log-dot.delete { background: #ef4444; }

.log-text { font-size: 12px; color: #374151; flex: 1; }

.log-time { font-size: 11px; color: #94a3b8; flex-shrink: 0; }

.team-card { background: white; border-radius: 10px; border: 1px solid #e8ecf0; padding: 14px 16px; margin-bottom: 10px; }

.team-card-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }

.team-name { font-size: 14px; font-weight: 700; color: #1e4575; }

.agent-chip { display: inline-block; background: #f1f5f9; border-radius: 20px; padding: 3px 10px; font-size: 11px; color: #374151; margin: 2px; }

.del-rec-item { padding: 12px 14px; border-radius: 8px; background: #f8fafc; border: 1px solid #f1f5f9; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }

.period-lock-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; margin-bottom: 8px; }

</style>

<div class="st-page-wrap">
  @php
    $sHidden = $hiddenSections ?? []; $isAdmin = auth()->user()->isAdmin(); $canSeeS = fn($k) => $isAdmin || !in_array($k, $sHidden);
    $activePanel = request('panel') ?: session('open_section', 'profile');
    $panelClass = fn($key) => 'st-panel' . ($activePanel === $key ? ' active' : '');
  @endphp
  {{-- Main Content --}}

  <div class="st-content" style="width:100%;">

    @if(session('success'))<div class="st-alert">&#10003; {{ session('success') }}</div>@endif
    @if(session('error'))<div class="st-alert" style="border-color:#ef4444;background:#fef2f2;color:#dc2626;">&#9888; {{ session('error') }}</div>@endif

    {{-- PROFILE PANEL --}}

    <div class="{{ $panelClass('profile') }}" id="panel-profile">

      <div class="st-page-header"><div class="st-page-title">My Profile</div><div class="st-page-sub">Update your personal information and password</div></div>

      <div class="st-card"><div class="st-card-body">

        <div class="avatar-wrap">

          @if(auth()->user()->avatar)

            <img src="{{ str_starts_with(auth()->user()->avatar, 'avatars/') ? \Storage::disk('public')->url(auth()->user()->avatar) : asset(auth()->user()->avatar) }}" class="avatar-img" alt="Avatar">

          @else

            <div class="avatar-initials">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>

          @endif

          <div class="avatar-info">

            <h3>{{ auth()->user()->name }}</h3>

            <p>{{ auth()->user()->email }}</p>

            <p>{{ ucfirst(auth()->user()->role ?? 'Staff') }}</p>

          </div>

        </div>

        <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data">

          @csrf

          <div class="st-form-grid">

            <div class="st-form-group">

              <label class="st-label">Full Name</label>

              <input class="st-input" type="text" name="name" value="{{ auth()->user()->name }}" required>

            </div>

            <div class="st-form-group">

              <label class="st-label">Email Address</label>

              <input class="st-input" type="email" value="{{ auth()->user()->email }}" disabled style="background:#f8fafc;color:#94a3b8;">

              <span style="font-size:11px;color:#94a3b8;">Email cannot be changed</span>

            </div>

            <div class="st-form-group">

              <label class="st-label">Preferred Address (e.g. Sir, Ma'am)</label>

              <input class="st-input" type="text" name="preferred_address" value="{{ auth()->user()->preferred_address }}">

            </div>

            <div class="st-form-group">

              <label class="st-label">Profile Photo</label>

              <input class="st-input" type="file" name="avatar" accept="image/*" style="padding:6px 12px;">

              <span style="font-size:11px;color:#94a3b8;">JPG, PNG or GIF. Max 2MB.</span>

            </div>

          </div>

          <div style="margin-top:16px;"><button type="submit" class="st-btn st-btn-primary">Save Profile</button></div>

        </form>

      </div></div>

<div class="st-card"><div class="st-card-hdr"><div class="st-card-hdr-text"><h3>Change Password</h3><p>Update your login password</p></div></div>

      <div class="st-card-body">

        <form method="POST" action="{{ route('settings.password') }}">

          @csrf

          <div class="st-form-grid">

            <div class="st-form-group">

              <label class="st-label">Current Password</label>

              <div style="position:relative;">
                <input class="st-input" type="password" name="current_password" id="settings_current_password" style="width:100%;box-sizing:border-box;padding-right:38px;">
                <button type="button" onclick="toggleSettingsPwdField('settings_current_password', this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:2px;display:flex;color:#94a3b8;" aria-label="Show password">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              @error('current_password')
                <div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>
              @enderror

            </div>

            <div class="st-form-group">

              <label class="st-label">New Password</label>

              <div style="position:relative;">
                <input class="st-input" type="password" name="password" id="settings_new_password" placeholder="Min. 8 chars, upper, lower, number, symbol" oninput="checkSettingsPwd(this.value)" style="width:100%;box-sizing:border-box;padding-right:38px;">
                <button type="button" onclick="toggleSettingsPwdField('settings_new_password', this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:2px;display:flex;color:#94a3b8;" aria-label="Show password">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <div id="settings-pwd-bar" style="height:3px;border-radius:2px;margin-top:5px;background:#e2e8f0;overflow:hidden;"><div id="settings-pwd-fill" style="height:100%;width:0;transition:width .3s,background .3s;"></div></div>
              <div id="settings-pwd-text" style="font-size:10px;color:#94a3b8;margin-top:2px;"></div>
              @if ($errors->has('password'))
                <ul style="color:#dc2626;font-size:12px;margin-top:4px;padding-left:18px;">
                  @foreach ($errors->get('password') as $msg)
                    <li>{{ $msg }}</li>
                  @endforeach
                </ul>
              @endif

            </div>

            <div class="st-form-group">

              <label class="st-label">Confirm New Password</label>

              <div style="position:relative;">
                <input class="st-input" type="password" name="password_confirmation" id="settings_confirm_password" style="width:100%;box-sizing:border-box;padding-right:38px;">
                <button type="button" onclick="toggleSettingsPwdField('settings_confirm_password', this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:2px;display:flex;color:#94a3b8;" aria-label="Show password">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>

            </div>

          </div>

          <div style="margin-top:16px;"><button type="submit" class="st-btn st-btn-primary">Update Password</button></div>

        </form>

      </div></div>

      <div class="st-card"><div class="st-card-hdr"><div class="st-card-hdr-text"><h3>Security Question</h3><p>Used for password recovery</p></div></div>

      <div class="st-card-body">

        <form method="POST" action="{{ route('settings.security-question') }}">

          @csrf

          <div class="st-form-grid">

            <div class="st-form-group">

              <label class="st-label">Security Question</label>

              <select class="st-input st-select" name="security_question">

                @foreach(["What is your mother's maiden name?","What was the name of your first pet?","What city were you born in?","What is your favorite book?","What was the name of your elementary school?"] as $q)

                  <option value="{{ $q }}" {{ auth()->user()->security_question === $q ? 'selected' : '' }}>{{ $q }}</option>

                @endforeach

              </select>

            </div>

            <div class="st-form-group">

              <label class="st-label">Answer</label>

              <input class="st-input" type="text" name="security_answer" placeholder="Leave blank to keep current answer">

            </div>

          </div>

          <div style="margin-top:16px;"><button type="submit" class="st-btn st-btn-primary">Save Security Question</button></div>

        </form>

      </div></div>


    </div>

    {{-- ABOUT ME PANEL --}}

    <div class="{{ $panelClass('employee-info') }}" id="panel-employee-info">

      <div class="st-page-header"><div class="st-page-title">About Me</div><div class="st-page-sub">Your employment details on record</div></div>

      <div class="st-card"><div class="st-card-body">

        <form method="POST" action="{{ route('settings.employee-info') }}">

          @csrf

          <div class="st-form-grid">

            <div class="st-form-group">

              <label class="st-label">Position / Job Title</label>

              <input class="st-input" type="text" name="position" value="{{ auth()->user()->position }}">

            </div>

            <div class="st-form-group">

              <label class="st-label">Employee ID</label>

              <input class="st-input" type="text" name="employee_id" value="{{ auth()->user()->employee_id }}">

            </div>

            <div class="st-form-group">

              <label class="st-label">Date Hired</label>

              <input class="st-input" type="date" name="date_hired" value="{{ auth()->user()->date_hired ? auth()->user()->date_hired->format('Y-m-d') : '' }}">

            </div>

          </div>

          <div style="margin-top:16px;"><button type="submit" class="st-btn st-btn-primary">Save</button></div>

        </form>

      </div></div>

    </div>

    {{-- SYSTEM INFO PANEL --}}

    <div class="{{ $panelClass('system') }}" id="panel-system">

      <div class="st-page-header"><div class="st-page-title">System Info</div><div class="st-page-sub">Application and environment details</div></div>

      <div class="st-stat-grid" style="margin-bottom:14px;">

        <div class="st-stat-box"><div class="st-stat-box-val">{{ config('app.name') }}</div><div class="st-stat-box-lbl">App Name</div></div>

        <div class="st-stat-box"><div class="st-stat-box-val">{{ app()->version() }}</div><div class="st-stat-box-lbl">Laravel Version</div></div>

        <div class="st-stat-box"><div class="st-stat-box-val">{{ PHP_VERSION }}</div><div class="st-stat-box-lbl">PHP Version</div></div>

        <div class="st-stat-box"><div class="st-stat-box-val">{{ config('app.env') }}</div><div class="st-stat-box-lbl">Environment</div></div>

        <div class="st-stat-box"><div class="st-stat-box-val">{{ config('database.default') }}</div><div class="st-stat-box-lbl">Database</div></div>

        <div class="st-stat-box"><div class="st-stat-box-val">{{ now()->format('M d, Y') }}</div><div class="st-stat-box-lbl">Server Date</div></div>

      </div>

    </div>

    {{-- PRIVACY PANEL --}}

    <div class="{{ $panelClass('privacy') }}" id="panel-privacy">

      <div class="st-page-header"><div class="st-page-title">Privacy &amp; Policy</div><div class="st-page-sub">Edit the privacy policy shown on the login page</div></div>

      <div class="st-card"><div class="st-card-body">

        <form method="POST" action="{{ route('settings.privacy') }}">

          @csrf

          <div class="st-form-group">

            <label class="st-label">Privacy Policy Content</label>

            <textarea class="st-input" name="privacy_content" rows="14" style="resize:vertical;font-family:inherit;">{{ $privacyContent }}</textarea>

          </div>

          <div style="margin-top:16px;"><button type="submit" class="st-btn st-btn-primary">Save Policy</button></div>

        </form>

      </div></div>

    </div>

    {{-- NOTES PANEL --}}

    <div class="{{ $panelClass('notes') }}" id="panel-notes">

      <div class="st-page-header"><div class="st-page-title">My Notes</div><div class="st-page-sub">Personal notes and reminders</div></div>

      @php $myNotes = \App\Models\Note::where('user_id', auth()->id())->orderBy('note_date')->get(); @endphp

      <div class="st-card"><div class="st-card-body">

        @if($myNotes->isEmpty())

          <div class="st-empty">No notes yet.</div>

        @else

          @foreach($myNotes as $note)

          <div style="display:flex;align-items:flex-start;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f8fafc;">

            <div>

              <div style="font-size:13px;font-weight:600;color:#0f172a;">{{ $note->title }}</div>

              @if($note->body)<div style="font-size:12px;color:#64748b;margin-top:2px;">{{ $note->body }}</div>@endif

              @if($note->note_date)<div style="font-size:11px;color:#94a3b8;margin-top:3px;">{{ \Carbon\Carbon::parse($note->note_date)->format('M d, Y') }}{{ $note->reminder_time ? ' at '.\Carbon\Carbon::parse($note->reminder_time)->format('g:i A') : '' }}</div>@endif

            </div>

            <form method="POST" action="{{ route('notes.destroy', $note->id) }}">@csrf @method('DELETE')

              <button type="submit" class="st-btn st-btn-danger st-btn-sm">Remove</button>

            </form>

          </div>

          @endforeach

        @endif

      </div></div>

    </div>

    {{-- NOTIFICATIONS PANEL --}}

    @if($isAdmin || $canSeeS('settings.users'))

    {{-- USERS PANEL --}}

    <div class="{{ $panelClass('users') }}" id="panel-users">

      <div class="st-page-header"><div class="st-page-title">User Management</div><div class="st-page-sub">Approve registrations, assign roles, manage users</div></div>

      <div class="st-card"><div class="st-card-hdr"><div class="st-card-hdr-text"><h3>Pending Approval</h3><p>New registrations waiting for review</p></div></div>

      <div class="st-card-body">

        @if($pendingUsers->isEmpty())

          <div class="st-empty">No pending registrations.</div>

        @else

          @foreach($pendingUsers as $u)

          <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f8fafc;gap:12px;">

            <div>

              <div style="font-size:13px;font-weight:600;color:#0f172a;">{{ $u->name }}</div>

              <div style="font-size:12px;color:#64748b;">{{ $u->email }}</div>

            </div>

            <div style="display:flex;gap:8px;">

              <form method="POST" action="{{ route('settings.users.approve', $u->id) }}">@csrf<button type="submit" class="st-btn st-btn-primary st-btn-sm">Approve</button></form>

              <form method="POST" action="{{ route('settings.users.reject', $u->id) }}">@csrf<button type="submit" class="st-btn st-btn-danger st-btn-sm">Reject</button></form>

            </div>

          </div>

          @endforeach

        @endif

      </div></div>

      <div class="st-card"><div class="st-card-hdr"><div class="st-card-hdr-text"><h3>Active Users</h3><p>Manage roles and access</p></div></div>

      <div class="st-card-body" style="padding:0;overflow-x:auto;">

        <table class="st-user-table" style="min-width:560px;">

          <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last Login</th><th></th></tr></thead>

          <tbody>

          @foreach($activeUsers as $u)

          <tr>

            <td style="font-weight:600;">
              <span style="display:inline-flex;align-items:center;gap:6px;">
                {{ $u->name }}
                @if(in_array($u->id, $onlineUserIds))
                  <span style="width:8px;height:8px;background:#22c55e;border-radius:50%;display:inline-block;" title="Online"></span>
                @endif
              </span>
            </td>

            <td style="color:#64748b;">{{ $u->email }}</td>

            <td>

              @if($u->id === auth()->id())

                <span class="st-badge st-badge-you">Admin (You)</span>

              @else

                <form method="POST" action="{{ route('settings.users.role', $u->id) }}" style="display:flex;gap:6px;align-items:center;">@csrf

                  <select name="role" class="st-select" style="padding:5px 8px;font-size:12px;">

                    <option value="staff" {{ $u->role==='staff'?'selected':'' }}>Staff</option>

                    <option value="admin" {{ $u->role==='admin'?'selected':'' }}>Admin</option>

                  </select>

                  <button type="submit" class="st-btn st-btn-primary st-btn-sm">Save</button>

                </form>

              @endif

            </td>

            <td style="color:#94a3b8;font-size:12px;">{{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}</td>

            <td>

              @if($u->id !== auth()->id())

              <form method="POST" action="{{ route('settings.users.remove', $u->id) }}" onsubmit="return confirm('Remove this user?')">@csrf @method('DELETE')

                <button type="submit" class="st-btn st-btn-danger st-btn-sm">Remove</button>

              </form>

              @endif

            </td>

          </tr>

          @endforeach

          </tbody>

        </table>

      </div></div>

    </div>

    @endif

    @if($isAdmin || $canSeeS('settings.visibility'))

    {{-- VISIBILITY PANEL --}}

    <div class="{{ $panelClass('visibility') }}" id="panel-visibility">

      <div class="st-page-header">
        <div class="st-page-title">Page Visibility</div>
        <div class="st-page-sub">Checked items are visible to the selected user. Uncheck to hide. Admin always sees everything.</div>
      </div>

      <div class="st-card"><div class="st-card-body">

        @php
          $visGroups = [
            'Finance' => [
              'dashboard'                        => 'Finance Dashboard',
              'departments'                      => 'Departments',
              'summary-report'                   => 'Summary Report',
              'commission-monitoring'            => 'Commission Monitoring',
              'commission-monitoring.dashboard'  => '↳ Commission Dashboard',
              'cash-advance'                     => 'Cash Advance',
              'calendar'                         => 'Calendar',
            ],
            'Sales & Marketing' => [
              'sales-marketing'                  => 'Sales & Marketing Dashboard',
              'client-database'                  => 'Client Database',
              'client-database.list'             => '↳ List of Clients',
              'client-database.property'         => '↳ List of Properties',
              'site-visit-database'              => 'Site Visit Database',
              'sales-calendar'                   => 'Calendar',
            ],
            'Forms' => [
              'forms'                            => 'Forms',
            ],
            'Human Resource' => [
              'human-resource'                   => 'HR Dashboard',
              'human-resource.employee-data'     => '↳ Employee Data',
              'human-resource.contact-list'      => '↳ ARC Contact List',
            ],
            'Settings' => [
              'settings.users'                   => 'User Management',
              'settings.visibility'              => 'Page Visibility',
              'settings.activity'                => 'Activity Log',
              'settings.deleted'                 => 'Deleted Records',
              'settings.permissions'             => 'Permission Requests',
              'settings.teams'                   => 'Team Management',
              'settings.period-lock'             => 'Period Lock',
              'settings.backup'                  => 'Backup & Restore',
              'settings.export'                  => 'Export Records',
            ],
          ];
          $staffUsers = $activeUsers->whereNotIn('status', ['pre_registered'])->where('role', '!=', 'admin');
          $selectedUserId = request('vis_user') ?? ($staffUsers->first()->id ?? null);
          $selectedUser = $staffUsers->firstWhere('id', $selectedUserId);
          $userHidden = $selectedUser ? ($selectedUser->hidden_pages ?? []) : [];
        @endphp

        {{-- User selector --}}
        <div style="margin-bottom:20px;">
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:8px;">
            <label style="font-weight:700;font-size:13px;color:#1e4575;margin:0;">Page Visibility — Select User</label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
              <select id="visDeptFilter" class="st-select" onchange="filterVisUserTabs()" style="min-width:170px;">
                <option value="">All Departments</option>
                <option value="finance">Finance</option>
                <option value="sales & marketing">Sales & Marketing</option>
                <option value="human resource">Human Resource</option>
              </select>
              <div style="position:relative;">
                <input type="text" id="visUserSearch" class="st-input" placeholder="Search staff by name..." oninput="filterVisUserTabs()" style="width:220px;padding-left:32px;">
                <svg width="14" height="14" fill="none" stroke="#94a3b8" viewBox="0 0 24 24" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);"><circle cx="11" cy="11" r="8" stroke-width="2"/><path d="m21 21-4.3-4.3" stroke-width="2" stroke-linecap="round"/></svg>
              </div>
            </div>
          </div>
          @if($staffUsers->isEmpty())
            <div style="color:#6b7280;font-size:13px;padding:10px 0;">No users yet.</div>
          @else
          @php
            // Department filter is derived from each user's Position (the
            // field editable on the Employee Data page), not a separate
            // department column — so choosing "Finance" in the dropdown
            // surfaces anyone whose position reads as a finance role
            // (Finance Officer, Finance Manager, Accountant, etc.), not
            // just users explicitly tagged with an exact department value.
            // Add/adjust keywords below to match your actual position titles.
            $deptPositionKeywords = [
                'finance'           => ['finance', 'accounting', 'accountant', 'treasury', 'audit', 'bookkeep'],
                'sales & marketing' => ['sales', 'marketing'],
                'human resource'    => ['human resource', 'hr'],
            ];
            $resolveUserDept = function ($position) use ($deptPositionKeywords) {
                $position = strtolower(trim($position ?? ''));
                if ($position === '') return '';
                foreach ($deptPositionKeywords as $dept => $keywords) {
                    foreach ($keywords as $kw) {
                        // word-boundary match so short keywords like "hr"
                        // don't false-positive inside unrelated words
                        if (preg_match('/\b' . preg_quote($kw, '/') . '\b/', $position)) {
                            return $dept;
                        }
                    }
                }
                return '';
            };
          @endphp
          <div style="display:flex;gap:12px;overflow-x:auto;padding-bottom:8px;" id="vis-user-tabs">
            @foreach($staffUsers as $u)
              <button type="button" data-name="{{ strtolower($u->name) }}" data-department="{{ $resolveUserDept($u->position) }}" onclick="selectVisUser({{ $u->id }}, this, '{{ addslashes($u->name) }}')"
                style="flex-shrink:0;display:flex;flex-direction:column;align-items:center;gap:8px;padding:14px 18px;border-radius:12px;cursor:pointer;border:2px solid {{ $selectedUserId == $u->id ? '#1e4575' : '#e5e7eb' }};background:{{ $selectedUserId == $u->id ? '#1e4575' : '#fff' }};color:{{ $selectedUserId == $u->id ? '#fff' : '#374151' }};width:110px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                <div style="position:relative;">
                @if($u->avatar)
                  <img src="{{ str_starts_with($u->avatar, 'avatars/') ? \Storage::disk('public')->url($u->avatar) : asset($u->avatar) }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid {{ $selectedUserId == $u->id ? 'rgba(255,255,255,0.4)' : '#e5e7eb' }};">
                @else
                  <div style="width:40px;height:40px;border-radius:50%;background:{{ $selectedUserId == $u->id ? 'rgba(255,255,255,0.25)' : '#e8edf5' }};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;color:{{ $selectedUserId == $u->id ? '#fff' : '#1e4575' }};">
                    {{ strtoupper(substr($u->name,0,1)) }}
                  </div>
                @endif
                @if(in_array($u->id, $onlineUserIds))
                  <span style="position:absolute;bottom:1px;right:1px;width:11px;height:11px;background:#22c55e;border-radius:50%;border:2px solid {{ $selectedUserId == $u->id ? '#1e4575' : '#fff' }};"></span>
                @endif
                </div>
                <div style="font-size:12px;font-weight:600;text-align:center;line-height:1.3;width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $u->name }}</div>
                <div style="font-size:10px;opacity:0.75;text-align:center;">{{ ucfirst($u->status) }}</div>
              </button>
            @endforeach
          </div>
          <div id="vis-user-no-results" style="display:none;color:#94a3b8;font-size:13px;padding:10px 0;">No users match your search.</div>
          @endif
        </div>

        <form method="POST" action="{{ route('settings.visibility') }}" id="vis-form">@csrf
          <input type="hidden" name="visibility_submitted" value="1">
          <input type="hidden" name="visibility_user_id" id="vis_user_id" value="{{ $selectedUserId }}">

          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
            @foreach($visGroups as $groupName => $pages)
            @php $groupId = 'visgroup_'.preg_replace('/[^a-z0-9]/i','_',strtolower($groupName)); @endphp
            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #e5e7eb;">
                <span style="font-weight:700;font-size:13px;color:#1e4575;">{{ $groupName }}</span>
                <div style="display:flex;gap:6px;">
                  <button type="button" onclick="selectAllGroup('{{ $groupId }}', true)" style="font-size:11px;font-weight:600;color:#1e4575;background:#eff6ff;border:1px solid #bfdbfe;border-radius:5px;padding:2px 8px;cursor:pointer;">All</button>
                  <button type="button" onclick="selectAllGroup('{{ $groupId }}', false)" style="font-size:11px;font-weight:600;color:#6b7280;background:#f9fafb;border:1px solid #e5e7eb;border-radius:5px;padding:2px 8px;cursor:pointer;">None</button>
                </div>
              </div>
              <div id="{{ $groupId }}">
              @foreach($pages as $key => $label)
              <div style="display:flex;align-items:center;gap:8px;padding:4px 0;{{ str_starts_with($label,'↳') ? 'padding-left:12px;' : '' }}">
                <input type="checkbox" id="vis_{{ str_replace(['.', '-'], '_', $key) }}" name="visible_pages[]" value="{{ $key }}"
                  {{ !in_array($key, $userHidden) ? 'checked' : '' }}
                  style="width:15px;height:15px;cursor:pointer;">
                <label for="vis_{{ str_replace(['.', '-'], '_', $key) }}" style="font-size:13px;color:#374151;cursor:pointer;">{{ $label }}</label>
              </div>
              @endforeach
              </div>
            </div>
            @endforeach
          </div>

          <div style="margin-top:16px;display:flex;align-items:center;gap:12px;">
            <button type="submit" class="st-btn st-btn-primary" {{ !$selectedUser ? 'disabled' : '' }}>Save Visibility</button>
            <span style="font-size:13px;color:#6b7280;" id="vis-for-label">
              {{ $selectedUser ? 'for '.$selectedUser->name : 'Select a user above to save' }}
            </span>
          </div>
        </form>

      </div></div>

    </div>

    @endif

    @if($isAdmin || $canSeeS('settings.activity'))

    {{-- ACTIVITY LOG PANEL --}}

    <div class="{{ $panelClass('activity') }}" id="panel-activity">

      <div class="st-page-header"><div class="st-page-title">Activity Log</div><div class="st-page-sub">Recent system activity and audit trail</div></div>

      <div class="st-card"><div class="st-card-body" style="padding:8px 18px;max-height:500px;overflow-y:auto;">

        @forelse($activityLogs as $log)

        <div class="log-item">

          <div class="log-dot {{ $log->action }}"></div>

          <div class="log-text">

            <span style="font-weight:600;">{{ $log->user->name ?? 'System' }}</span>

            {{ $log->action }} in <span style="color:#1e4575;font-weight:600;">{{ $log->module }}</span>

            @if($log->description) — {{ $log->description }}@endif

          </div>

          <div class="log-time">{{ $log->created_at->diffForHumans() }}</div>

        </div>

        @empty

        <div class="st-empty">No activity yet.</div>

        @endforelse

      </div></div>

    </div>

    @endif

    @if($isAdmin || $canSeeS('settings.deleted'))

    {{-- DELETED RECORDS PANEL --}}


<div class="{{ $panelClass('deleted') }}" id="panel-deleted">

  <div class="st-page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <div>
      <div class="st-page-title">Deleted Records</div>
      <div class="st-page-sub">Restore or permanently delete records, grouped by source</div>
    </div>
    <select id="delFilterModule" class="st-select" onchange="filterDeletedGroups()" style="font-size:12px;">
      <option value="">All Sources</option>
      @if($deletedExpenses->isNotEmpty())<option value="Departmental Expenses">Departmental Expenses</option>@endif
      @foreach($deletedLogsGrouped as $module => $logs)
        <option value="{{ $module }}">{{ $module }}</option>
      @endforeach
    </select>
  </div>

  <div id="del-bulk-bar" style="display:none;align-items:center;justify-content:space-between;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;margin-bottom:14px;">
    <span id="del-selected-count" style="font-size:13px;font-weight:600;color:#1e4575;">0 selected</span>
    <div style="display:flex;gap:8px;">
      <button type="button" class="st-btn st-btn-primary st-btn-sm" onclick="bulkAction('restore')">Restore Selected</button>
      <button type="button" class="st-btn st-btn-danger st-btn-sm" onclick="bulkAction('delete')">Delete Selected</button>
      <button type="button" class="st-btn st-btn-sm" style="background:#f1f5f9;color:#374151;" onclick="clearDelSelection()">Clear</button>
    </div>
  </div>

  @if($deletedExpenses->isEmpty() && $deletedLogsGrouped->isEmpty())
    <div class="st-card"><div class="st-card-body"><div class="st-empty">No deleted records.</div></div></div>
  @endif

  {{-- Departmental Expenses group (own columns, doesn't mix with other modules) --}}
  @if($deletedExpenses->isNotEmpty())
  <div class="st-card del-group" data-module="Departmental Expenses" style="margin-bottom:16px;">
    <div class="st-card-hdr">
      <div class="st-card-hdr-text"><h3>Departmental Expenses</h3><p>{{ $deletedExpenses->count() }} deleted record(s)</p></div>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;cursor:pointer;">
        <input type="checkbox" onchange="toggleGroupSelect(this,'expense')"> Select All
      </label>
    </div>
    <div class="st-card-body" style="padding:0;overflow-x:auto;">
      <table class="st-user-table" style="min-width:700px;">
        <thead><tr>
          <th style="width:30px;"></th>
          <th>Control #</th><th>Requestor</th><th>Department</th><th>Amount</th><th>Deleted By</th><th>Deleted On</th><th>Actions</th>
        </tr></thead>
        <tbody>
        @foreach($deletedExpenses as $exp)
        @php
          $expDetail = [
            'title'      => $exp->control_number,
            'module'     => 'Departmental Expenses',
            'deleted_by' => $exp->deleted_by_name,
            'deleted_at' => optional($exp->deleted_at)->format('M d, Y g:i A'),
            'fields'     => [
              'Requestor'      => $exp->requestor_name,
              'Department'     => $exp->department,
              'Amount'         => '₱'.number_format($exp->requested_amount, 2),
              'Date Requested' => optional($exp->date_requested)->format('M d, Y'),
              'Status'         => $exp->status,
            ],
          ];
        @endphp
        <tr>
          <td><input type="checkbox" class="del-select" data-type="expense" data-id="{{ $exp->id }}"></td>
          <td style="cursor:pointer;color:#1e4575;font-weight:600;" onclick='openDelDetail(@json($expDetail))'>{{ $exp->control_number }}</td>
          <td>{{ $exp->requestor_name }}</td>
          <td>{{ $exp->department }}</td>
          <td>₱{{ number_format($exp->requested_amount, 2) }}</td>
          <td style="font-size:12px;color:#374151;">{{ $exp->deleted_by_name }}</td>
          <td style="font-size:11px;color:#94a3b8;">{{ $exp->deleted_at ? $exp->deleted_at->format('M d, Y g:i A') : '—' }}</td>
          <td>
            <div style="display:flex;gap:6px;">
              <button type="button" class="st-btn st-btn-primary st-btn-sm" onclick="delSingleAction('expense', {{ $exp->id }}, 'restore')">Restore</button>
              <button type="button" class="st-btn st-btn-danger st-btn-sm" onclick="delSingleAction('expense', {{ $exp->id }}, 'delete')">Delete</button>
            </div>
          </td>
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

  {{-- Every other module gets its own group, so field sets never collide --}}
  @foreach($deletedLogsGrouped as $module => $logs)
  <div class="st-card del-group" data-module="{{ $module }}" style="margin-bottom:16px;">
    <div class="st-card-hdr">
      <div class="st-card-hdr-text"><h3>{{ $module }}</h3><p>{{ $logs->count() }} deleted record(s)</p></div>
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;cursor:pointer;">
        <input type="checkbox" onchange="toggleGroupSelect(this,'log')"> Select All
      </label>
    </div>
    <div class="st-card-body">
      @foreach($logs as $log)
      @php
        $logDetail = [
          'title'      => $log->description ?: $log->module,
          'module'     => $log->module,
          'deleted_by' => $log->user->name ?? 'System',
          'deleted_at' => $log->created_at->format('M d, Y g:i A'),
          'fields'     => is_array($log->meta) ? $log->meta : [],
        ];
      @endphp
      <div class="del-rec-item">
        <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
          <input type="checkbox" class="del-select" data-type="log" data-id="{{ $log->id }}" {{ !$log->meta ? 'disabled title="Not restorable"' : '' }}>
          <div style="min-width:0;cursor:pointer;" onclick='openDelDetail(@json($logDetail))'>
            <div style="font-size:13px;font-weight:600;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $log->description }}</div>
            <div style="font-size:11px;color:#94a3b8;">by {{ $log->user->name ?? 'System' }} &bull; {{ $log->created_at->format('M d, Y g:i A') }}</div>
          </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
          @if($log->meta)
          <form method="POST" action="{{ route('settings.deleted.restore', $log->id) }}" onsubmit="return confirm('Restore this record?')">@csrf
            <button type="submit" class="st-btn st-btn-primary st-btn-sm">Restore</button>
          </form>
          @endif
          <form method="POST" action="{{ route('settings.deleted.purge', $log->id) }}" onsubmit="return confirm('Permanently delete?')">@csrf @method('DELETE')
            <button type="submit" class="st-btn st-btn-danger st-btn-sm">Delete</button>
          </form>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endforeach

</div>

{{-- Deleted-record detail popup --}}
<div id="delDetailModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this) closeDelDetail()">
  <div style="background:white;border-radius:14px;padding:22px 26px;width:480px;max-width:95vw;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">
      <div>
        <div id="delDetailTitle" style="font-size:15px;font-weight:700;color:#0f172a;"></div>
        <div id="delDetailModule" style="font-size:12px;color:#94a3b8;margin-top:2px;"></div>
      </div>
      <button type="button" onclick="closeDelDetail()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#6b7280;">&times;</button>
    </div>
    <div style="display:flex;gap:24px;margin-bottom:14px;">
      <div>
        <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;">Deleted By</div>
        <div id="delDetailBy" style="font-size:13px;color:#374151;font-weight:600;"></div>
      </div>
      <div>
        <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;">Deleted On</div>
        <div id="delDetailAt" style="font-size:13px;color:#374151;font-weight:600;"></div>
      </div>
    </div>
    <div id="delDetailFields" style="display:flex;flex-direction:column;gap:8px;"></div>
  </div>
</div>
      @endif

      



    

    @if($isAdmin || $canSeeS('settings.permissions'))

    {{-- PERMISSION REQUESTS PANEL --}}

    <div class="{{ $panelClass('permission-requests') }}" id="panel-permission-requests">

      <div class="st-page-header"><div class="st-page-title">Permission Requests</div><div class="st-page-sub">Review and approve or reject staff edit &amp; delete requests</div></div>

      @php

        $pendingPermReqs  = \App\Models\PermissionRequest::with('user')->where('status','pending')->orderBy('created_at','desc')->get();

        $reviewedPermReqs = \App\Models\PermissionRequest::with('user')->whereIn('status',['approved','rejected'])->orderBy('updated_at','desc')->limit(50)->get();

      @endphp

      @if($pendingPermReqs->isNotEmpty())

      <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px;">Pending — {{ $pendingPermReqs->count() }} Request(s)</div>

      @foreach($pendingPermReqs as $pr)

      <div class="perm-item pending-perm">

        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">

          <div>

            <span style="font-weight:700;font-size:13px;">{{ $pr->user->name ?? '—' }}</span>

            <span style="background:#fef3c7;color:#92400e;border-radius:4px;padding:1px 7px;font-size:11px;font-weight:700;margin:0 6px;">{{ strtoupper($pr->action) }}</span>

            <span style="font-size:12px;color:#64748b;">{{ $pr->module }}</span>

            @if($pr->record_label)<span style="font-size:12px;color:#94a3b8;"> — {{ $pr->record_label }}</span>@endif

          </div>

          <div style="font-size:11px;color:#94a3b8;">{{ $pr->created_at->format('M d, Y g:i A') }}</div>

        </div>

        @if($pr->reason)<div style="margin-top:8px;padding:8px 12px;background:white;border-radius:6px;border-left:3px solid #f59e0b;"><div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-bottom:3px;">Reason</div><div style="font-size:13px;color:#374151;">{{ $pr->reason }}</div></div>@endif

        <div class="perm-actions">

          <input type="text" id="note_{{ $pr->id }}" placeholder="Optional note for staff..." style="flex:1;padding:7px 10px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:12px;font-family:inherit;">

          @if($pr->record_url)<a href="{{ $pr->record_url }}" target="_blank" class="st-btn st-btn-sm" style="background:#f1f5f9;color:#374151;border:1.5px solid #e2e8f0;text-decoration:none;">View Record</a>@endif

          <form method="POST" action="{{ route('permission-requests.review', $pr->id) }}" onsubmit="event.preventDefault();submitPermReview({{ $pr->id }},'approved',this)">@csrf

            <input type="hidden" name="status" value="approved">

            <input type="hidden" name="admin_note" id="note_approve_{{ $pr->id }}">

            <button type="submit" class="st-btn st-btn-primary st-btn-sm" onclick="document.getElementById('note_approve_{{ $pr->id }}').value=document.getElementById('note_{{ $pr->id }}').value">&#10003; Approve</button>

          </form>

          <form method="POST" action="{{ route('permission-requests.review', $pr->id) }}" onsubmit="event.preventDefault();submitPermReview({{ $pr->id }},'rejected',this)">@csrf

            <input type="hidden" name="status" value="rejected">

            <input type="hidden" name="admin_note" id="note_reject_{{ $pr->id }}">

            <button type="submit" class="st-btn st-btn-danger st-btn-sm" onclick="document.getElementById('note_reject_{{ $pr->id }}').value=document.getElementById('note_{{ $pr->id }}').value">&#10005; Reject</button>

          </form>

        </div>

      </div>

      @endforeach

      @endif

      @if($reviewedPermReqs->isNotEmpty())

      <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;margin:16px 0 8px;">Reviewed — {{ $reviewedPermReqs->count() }} Request(s)</div>

      @foreach($reviewedPermReqs as $pr)

      <div class="perm-item {{ $pr->status === 'approved' ? 'approved-perm' : 'rejected-perm' }}">

        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">

          <div>

            <span style="font-weight:600;font-size:13px;">{{ $pr->user->name ?? '—' }}</span>

            <span style="font-size:11px;color:#64748b;margin:0 6px;">{{ strtoupper($pr->action) }}</span>

            <span style="font-size:12px;color:#94a3b8;">{{ $pr->module }}@if($pr->record_label) — {{ $pr->record_label }}@endif</span>

          </div>

          <span style="font-size:11px;font-weight:700;color:{{ $pr->status==='approved'?'#16a34a':'#dc2626' }};text-transform:uppercase;">{{ $pr->status }}</span>

        </div>

        @if($pr->admin_note)<div style="font-size:12px;color:#64748b;margin-top:4px;">Note: {{ $pr->admin_note }}</div>@endif

      </div>

      @endforeach

      @endif

      @if($pendingPermReqs->isEmpty() && $reviewedPermReqs->isEmpty())

      <div class="st-card"><div class="st-card-body"><div class="st-empty">No permission requests.</div></div></div>

      @endif

    </div>

    @endif

    @if($isAdmin || $canSeeS('settings.teams'))

    {{-- TEAMS PANEL --}}

    <div class="{{ $panelClass('teams') }}" id="panel-teams">

      <div class="st-page-header"><div class="st-page-title">Team Management</div><div class="st-page-sub">Manage sales teams, agents, and quotas</div></div>

      {{-- Add New Team --}}
      <div class="st-card" style="margin-bottom:20px;">
        <div class="st-card-hdr"><div class="st-card-hdr-text"><h3>Add New Team</h3></div></div>
        <div class="st-card-body">
          <form method="POST" action="{{ route('settings.teams.store') }}">@csrf
            <div class="st-form-grid">
              <div class="st-form-group"><label class="st-label">Team Name</label><input class="st-input" type="text" name="team_name" required></div>
              <div class="st-form-group"><label class="st-label">Sales Manager <span style="font-weight:400;color:#94a3b8;font-size:11px">(optional)</span></label><input class="st-input" type="text" name="sales_manager"></div>
              <div class="st-form-group"><label class="st-label">Team Leader</label><input class="st-input" type="text" name="leader_name"></div>
            </div>
            <div style="margin-top:14px;"><button type="submit" class="st-btn st-btn-primary">Add Team</button></div>
          </form>
        </div>
      </div>

      @foreach($salesTeams as $team)
      <div style="background:white;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.08);margin-bottom:20px;overflow:hidden;border:1px solid #e2e8f0;">

        {{-- Team Header --}}
        <div style="background:linear-gradient(135deg,#0f2444,#1e4575);padding:16px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
          <div>
            <div style="font-size:15px;font-weight:700;color:white;">{{ $team->team_name }}</div>
            <div style="font-size:12px;color:rgba(255,255,255,.6);margin-top:3px;">
              @if($team->sales_manager)<span>Manager: {{ $team->sales_manager }}</span> &bull; @endif
              <span>Leader: {{ $team->leader_name ?: '—' }}</span>
            </div>
          </div>
          <div style="display:flex;gap:8px;flex-shrink:0;">
            <button type="button" onclick="openEditTeam({{ $team->id }}, '{{ addslashes($team->team_name) }}', '{{ addslashes($team->leader_name) }}', '{{ addslashes($team->sales_manager) }}')"
              style="padding:6px 14px;background:rgba(255,255,255,.15);color:white;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">
              Edit
            </button>
            <form method="POST" action="{{ route('settings.teams.destroy', $team->id) }}" onsubmit="return confirm('Delete team?')" style="display:inline;">@csrf @method('DELETE')
              <button type="submit" style="padding:6px 14px;background:rgba(239,68,68,.2);color:#fca5a5;border:1px solid rgba(239,68,68,.3);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">Delete</button>
            </form>
          </div>
        </div>

        <div style="padding:16px 20px;">

          {{-- Agents Table --}}
          <div style="margin-bottom:16px;">
            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">
              Agents ({{ $team->agents->count() }})
            </div>
            @if($team->agents->isEmpty())
              <div style="color:#94a3b8;font-size:13px;padding:8px 0;">No agents yet.</div>
            @else
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
              <thead><tr style="background:#f8fafc;">
                <th style="padding:8px 12px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;border-bottom:1px solid #e2e8f0;">Name</th>
                <th style="padding:8px 12px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;border-bottom:1px solid #e2e8f0;">ID</th>
                <th style="padding:8px 12px;text-align:center;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;border-bottom:1px solid #e2e8f0;">Status</th>
                <th style="padding:8px 12px;text-align:right;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;border-bottom:1px solid #e2e8f0;">Actions</th>
              </tr></thead>
              <tbody>
                @foreach($team->agents as $agent)
                <tr style="border-bottom:1px solid #f1f5f9;" id="agent-row-{{ $agent->id }}">
                  <td style="padding:10px 12px;font-weight:600;color:#0f172a;">
                    <div style="display:flex;align-items:center;gap:8px;">
                      <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#1e4575,#2563eb);display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700;flex-shrink:0;">{{ strtoupper(substr($agent->name,0,1)) }}</div>
                      <span id="agent-name-{{ $agent->id }}">{{ $agent->name }}</span>
                    </div>
                  </td>
                  <td style="padding:10px 12px;color:#64748b;font-size:12px;" id="agent-empid-{{ $agent->id }}">
                    {{ $agent->employee_id ?: ($agent->user?->employee_id ?: '—') }}
                  </td>
                  <td style="padding:10px 12px;text-align:center;">
                    <div style="position:relative;display:inline-block;" id="status-wrap-{{ $agent->id }}">
                      <button type="button"
                        onclick="openAgentStatusDropdown({{ $agent->id }}, this)"
                        data-active="{{ $agent->is_active ? '1' : '0' }}"
                        id="status-btn-{{ $agent->id }}"
                        style="padding:3px 10px 3px 12px;border-radius:20px;font-size:11px;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:4px;
                        background:{{ $agent->is_active ? '#dcfce7' : '#fee2e2' }};
                        color:{{ $agent->is_active ? '#166534' : '#991b1b' }};">
                        <span id="status-label-{{ $agent->id }}">{{ $agent->is_active ? 'Active' : 'Inactive' }}</span>
                        <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><path d="M2 3.5l3 3 3-3"/></svg>
                      </button>
                      <div id="status-dropdown-{{ $agent->id }}"
                        style="display:none;position:absolute;top:calc(100% + 4px);left:50%;transform:translateX(-50%);background:white;border:1px solid #e2e8f0;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.12);z-index:999;min-width:110px;overflow:hidden;">
                        <button type="button" onclick="setAgentStatus({{ $agent->id }}, true)"
                          style="display:block;width:100%;padding:8px 14px;text-align:left;font-size:12px;font-weight:700;color:#166534;background:#f0fdf4;border:none;cursor:pointer;border-bottom:1px solid #e2e8f0;">
                          ✓ Active
                        </button>
                        <button type="button" onclick="setAgentStatus({{ $agent->id }}, false)"
                          style="display:block;width:100%;padding:8px 14px;text-align:left;font-size:12px;font-weight:700;color:#991b1b;background:#fff5f5;border:none;cursor:pointer;">
                          ✗ Inactive
                        </button>
                      </div>
                    </div>
                  </td>
                  <td style="padding:10px 12px;text-align:right;white-space:nowrap;">
                    <button type="button" onclick="openEditAgent({{ $agent->id }}, '{{ addslashes($agent->name) }}', '{{ addslashes($agent->employee_id ?: ($agent->user?->employee_id ?: '')) }}')"
                      style="padding:4px 10px;background:#eff6ff;color:#1e4575;border:1px solid #bfdbfe;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;margin-right:4px;">Edit</button>
                    <form method="POST" action="{{ route('settings.agents.destroy', $agent->id) }}" style="display:inline;" onsubmit="return confirm('Remove agent?')">@csrf @method('DELETE')
                      <button type="submit" style="padding:4px 10px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;">Delete</button>
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @endif

            {{-- Add Agent --}}
            <form method="POST" action="{{ route('settings.agents.store') }}" style="display:flex;gap:8px;margin-top:10px;">@csrf
              <input type="hidden" name="team_id" value="{{ $team->id }}">
              <input class="st-input" type="text" name="name" placeholder="Add agent name" style="flex:1;">
              <button type="submit" class="st-btn st-btn-primary st-btn-sm">+ Add</button>
            </form>
          </div>

          {{-- Monthly Quota --}}
          <div style="border-top:1px solid #f1f5f9;padding-top:14px;">
            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Monthly Quota</div>
            @foreach($team->quotas->take(3) as $q)
            <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;color:#374151;margin-bottom:6px;background:#f8fafc;padding:8px 12px;border-radius:8px;">
              <span style="color:#64748b;">{{ \Carbon\Carbon::parse($q->date_from)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($q->date_to)->format('M d, Y') }}</span>
              <span style="font-weight:700;color:#1e4575;">&#8369;{{ number_format($q->quota_amount,0) }}</span>
              <form method="POST" action="{{ route('settings.quotas.destroy', $q->id) }}" onsubmit="return confirm('Delete quota?')" style="display:inline;">@csrf @method('DELETE')
                <button type="submit" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:14px;line-height:1;">&times;</button>
              </form>
            </div>
            @endforeach
            <form method="POST" action="{{ route('settings.teams.quota', $team->id) }}" style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">@csrf
              <input class="st-input" type="date" name="date_from" style="flex:1;min-width:120px;">
              <input class="st-input" type="date" name="date_to" style="flex:1;min-width:120px;">
              <input class="st-input" type="number" name="quota_amount" placeholder="Amount" style="flex:1;min-width:100px;">
              <button type="submit" class="st-btn st-btn-primary st-btn-sm">Set</button>
            </form>
          </div>

        </div>
      </div>
      @endforeach

      {{-- Edit Team Modal --}}
      <div id="editTeamModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)closeEditTeam();">
        <div style="background:white;border-radius:14px;padding:24px 28px;width:440px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2);">
          <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">Edit Team</div>
          <form id="editTeamForm" method="POST">@csrf @method('PUT')
            <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:18px;">
              <div><label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Team Name</label><input class="st-input" type="text" id="edit_team_name" name="team_name" required></div>
              <div><label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Team Leader</label><input class="st-input" type="text" id="edit_leader_name" name="leader_name"></div>
              <div><label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Sales Manager <span style="font-weight:400;color:#94a3b8;">(optional)</span></label><input class="st-input" type="text" id="edit_sales_manager" name="sales_manager"></div>
            </div>
            <div style="display:flex;gap:10px;">
              <button type="submit" class="st-btn st-btn-primary" style="flex:1;">Save Changes</button>
              <button type="button" onclick="closeEditTeam()" style="flex:1;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;background:#f1f5f9;color:#374151;border:none;">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      {{-- Edit Agent Modal --}}
      <div id="editAgentModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)closeEditAgent();">
        <div style="background:white;border-radius:14px;padding:24px 28px;width:380px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2);">
          <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:18px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">Edit Agent</div>
          <div style="margin-bottom:12px;">
            <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Name</label>
            <input class="st-input" type="text" id="edit_agent_name_input">
          </div>
          <div style="margin-bottom:18px;">
            <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Employee ID</label>
            <input class="st-input" type="text" id="edit_agent_empid_input" placeholder="e.g. ARC-SP-0012">
          </div>
          <div style="display:flex;gap:10px;">
            <button type="button" onclick="saveEditAgent()" class="st-btn st-btn-primary" style="flex:1;">Save</button>
            <button type="button" onclick="closeEditAgent()" style="flex:1;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;background:#f1f5f9;color:#374151;border:none;">Cancel</button>
          </div>
        </div>
      </div>

    </div>

    @endif

    @if($isAdmin || $canSeeS('settings.period-lock'))

    {{-- PROPERTIES PANEL --}}
    <div class="{{ $panelClass('properties') }}" id="panel-properties">
      <div class="st-page-header"><div class="st-page-title">Property Management</div><div class="st-page-sub">Manage the property dropdown list for site visit forms</div></div>

      <div class="st-card">
        <div class="st-card-hdr"><div class="st-card-hdr-text"><h3>Add Property</h3></div></div>
        <div class="st-card-body">
          <form method="POST" action="{{ route('settings.properties.store') }}">@csrf
            <div class="st-form-grid">
              <div class="st-form-group"><label class="st-label">Property Name <span style="color:#ef4444">*</span></label><input class="st-input" type="text" name="name" required placeholder="e.g. Sunshine Village"></div>
              <div class="st-form-group"><label class="st-label">Developer <span style="font-weight:400;color:#94a3b8;font-size:11px">(optional)</span></label><input class="st-input" type="text" name="developer" placeholder="e.g. Figtree Properties"></div>
            </div>
            <div style="margin-top:14px;"><button type="submit" class="st-btn st-btn-primary">Add Property</button></div>
          </form>
        </div>
      </div>

      <div class="st-card" style="margin-top:16px;">
        <div class="st-card-hdr"><div class="st-card-hdr-text"><h3>Property List</h3><p>{{ $properties->count() }} properties</p></div></div>
        <div class="st-card-body" style="padding:0;overflow-x:auto;">
          @if($properties->isEmpty())
            <div class="st-empty" style="padding:20px;">No properties yet. Add one above.</div>
          @else
          <table class="st-user-table" style="min-width:400px;">
            <thead><tr><th>Property Name</th><th>Developer</th><th style="text-align:right">Actions</th></tr></thead>
            <tbody>
              @foreach($properties as $prop)
              <tr>
                <td style="font-weight:600;color:#0f172a;">{{ $prop->name }}</td>
                <td style="color:#64748b;font-size:12px;">{{ $prop->developer ?: '—' }}</td>
                <td style="text-align:right;">
                  <form method="POST" action="{{ route('settings.properties.destroy', $prop->id) }}" style="display:inline;" data-confirm="Remove {{ addslashes($prop->name) }}?">@csrf @method('DELETE')
                    <button type="submit" class="st-btn st-btn-danger st-btn-sm">Remove</button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @endif
        </div>
      </div>
    </div>

    {{-- PERIOD LOCK PANEL --}}

    <div class="{{ $panelClass('period-lock') }}" id="panel-period-lock">

      <div class="st-page-header"><div class="st-page-title">Period Lock</div><div class="st-page-sub">Lock periods to prevent editing of records</div></div>

      <div class="st-card"><div class="st-card-hdr"><div class="st-card-hdr-text"><h3>Lock a Period</h3></div></div>

      <div class="st-card-body">

        <form method="POST" action="{{ route('settings.period-lock.store') }}">@csrf

          <div class="st-form-grid">

            <div class="st-form-group"><label class="st-label">Month</label>

              <select class="st-input st-select" name="month">

                @foreach(range(1,12) as $m)<option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>@endforeach

              </select>

            </div>

            <div class="st-form-group"><label class="st-label">Year</label><input class="st-input" type="number" name="year" value="{{ date('Y') }}" min="2020" max="2099"></div>

            <div class="st-form-group"><label class="st-label">Module</label>

              <select class="st-input st-select" name="module">

                <option value="summary-report">Summary Report</option>

                <option value="commission-monitoring">Commission Monitoring</option>

                <option value="departmental-expenses">Departmental Expenses</option>

              </select>

            </div>

          </div>

          <div style="margin-top:14px;"><button type="submit" class="st-btn st-btn-primary">Lock Period</button></div>

        </form>

      </div></div>

      <div class="st-card"><div class="st-card-hdr"><div class="st-card-hdr-text"><h3>Locked Periods</h3></div></div>

      <div class="st-card-body">

        @forelse($periodLocks as $lock)

        <div class="period-lock-item">

          <div>

            <div style="font-size:13px;font-weight:600;color:#0f172a;">{{ date('F Y', mktime(0,0,0,$lock->month,1,$lock->year)) }}</div>

            <div style="font-size:12px;color:#64748b;">{{ $lock->module }}</div>

          </div>

          <form method="POST" action="{{ route('settings.period-lock.destroy', $lock->id) }}">@csrf @method('DELETE')

            <button type="submit" class="st-btn st-btn-danger st-btn-sm">Unlock</button>

          </form>

        </div>

        @empty

        <div class="st-empty">No locked periods.</div>

        @endforelse

      </div></div>

    </div>

    @endif

    @if($isAdmin || $canSeeS('settings.employee'))

    {{-- EMPLOYEE DIRECTORY PANEL --}}

    <div class="{{ $panelClass('employee-directory') }}" id="panel-employee-directory">

      <div class="st-page-header"><div class="st-page-title">Employee Data</div><div class="st-page-sub">Edit employment details for all active users</div></div>

      @if(session('emp_success'))<div style="background:#d1fae5;color:#065f46;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:500;">&#10003; {{ session('emp_success') }}</div>@endif

      {{-- Add New Employee --}}
      <div class="st-card" style="margin-bottom:18px;">
        <div class="st-card-hdr"><div class="st-card-hdr-text"><h3>Add New Employee</h3><p>Pre-register an employee record</p></div></div>
        <div class="st-card-body">
          <form method="POST" action="{{ route('settings.employee.add') }}">@csrf
            <div class="st-form-grid">
              <div class="st-form-group">
                <label class="st-label">Name <span style="color:#ef4444;">*</span></label>
                <input class="st-input" type="text" name="name" required placeholder="Full name">
              </div>
              <div class="st-form-group">
                <label class="st-label">Position <span style="color:#ef4444;">*</span></label>
                <input class="st-input" type="text" name="position" required placeholder="Job title / position">
              </div>
              <div class="st-form-group">
                <label class="st-label">Employee ID <span style="color:#ef4444;">*</span></label>
                <input class="st-input" type="text" name="employee_id" required placeholder="e.g. 0050">
              </div>
              <div class="st-form-group">
                <label class="st-label">Date Hired <span style="color:#ef4444;">*</span></label>
                <input class="st-input" type="date" name="date_hired" required>
              </div>
            </div>
            <div style="margin-top:14px;"><button type="submit" class="st-btn st-btn-primary">Add Employee</button></div>
          </form>
        </div>
      </div>

      {{-- Employee List Table --}}
      <div class="st-card">
        <div class="st-card-hdr"><div class="st-card-hdr-text"><h3>Employee Records</h3><p>{{ $activeUsers->count() }} employees on record</p></div></div>
        <div class="st-card-body" style="padding:0;overflow-x:auto;">
          @if($activeUsers->isEmpty())
            <div class="st-empty">No employees yet.</div>
          @else
          <table class="st-user-table" style="min-width:700px;white-space:nowrap;">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Position</th>
                <th>Employee ID</th>
                <th>Date Hired</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($activeUsers as $u)
              <tr id="emp-view-{{ $u->id }}">
                <td style="font-weight:600;">{{ $u->name }}</td>
                <td style="color:#64748b;font-size:12px;">{{ $u->email }}</td>
                <td>{{ $u->position ?: '—' }}</td>
                <td>{{ $u->employee_id ?: '—' }}</td>
                <td>{{ $u->date_hired ? $u->date_hired->format('M d, Y') : '—' }}</td>
                <td style="white-space:nowrap;">
                  @if($isAdmin || $canSeeS('settings.employee'))
                  <button type="button" class="st-btn st-btn-primary st-btn-sm" onclick="toggleEmpEdit({{ $u->id }})">Edit</button>
                  @if($u->id !== auth()->id())
                  <form method="POST" action="{{ route('settings.users.remove', $u->id) }}" style="display:inline;" onsubmit="return confirm('Remove {{ addslashes($u->name) }}?')">@csrf @method('DELETE')
                    <button type="submit" class="st-btn st-btn-danger st-btn-sm">Delete</button>
                  </form>
                  @endif
                  @endif
                </td>
              </tr>
              <tr id="emp-edit-{{ $u->id }}" style="display:none;background:#f0f4ff;">
                <td colspan="6" style="padding:12px 16px;white-space:normal;">
                  <form method="POST" action="{{ route('settings.users.employee-info', $u->id) }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">@csrf
                    <div style="flex:1.5;min-width:130px;"><label class="st-label" style="margin-bottom:3px;display:block;">Position</label><input class="st-input" type="text" name="position" value="{{ $u->position }}"></div>
                    <div style="flex:1;min-width:110px;"><label class="st-label" style="margin-bottom:3px;display:block;">Employee ID</label><input class="st-input" type="text" name="employee_id" value="{{ $u->employee_id }}"></div>
                    <div style="flex:1;min-width:140px;"><label class="st-label" style="margin-bottom:3px;display:block;">Date Hired</label><input class="st-input" type="date" name="date_hired" value="{{ $u->date_hired ? $u->date_hired->format('Y-m-d') : '' }}"></div>
                    <div style="display:flex;gap:6px;flex-shrink:0;">
                      <button type="submit" class="st-btn st-btn-primary st-btn-sm">Save</button>
                      <button type="button" class="st-btn st-btn-danger st-btn-sm" onclick="toggleEmpEdit({{ $u->id }})">Cancel</button>
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

    </div>

    @endif

    @if($isAdmin || $canSeeS('settings.personnel'))

    {{-- ARC PERSONNEL CONTACT LIST PANEL --}}
    <div class="{{ $panelClass('personnel-contacts') }}" id="panel-personnel-contacts">
      <div class="st-page-header" style="display:flex;align-items:flex-start;justify-content:space-between;">
        <div>
          <div class="st-page-title">ARC Personnel Contact List</div>
          <div class="st-page-sub">Directory of ARC personnel with their contact information</div>
        </div>
        <button type="button" class="st-btn st-btn-primary" onclick="openAddContactModal('', this)" style="flex-shrink:0;margin-top:4px;">+ Add New Group</button>
      </div>

      {{-- Contact Directory --}}

      @if($personnelContacts->isEmpty())
        <div class="st-card"><div class="st-card-body"><div class="st-empty">No contacts yet. Use "+ Add New Group" above.</div></div></div>
      @else
      @php $grouped = $personnelContacts->groupBy(fn($c) => $c->company ?: 'Others'); @endphp
      <div id="contact-groups-wrap">
      @foreach($grouped as $grpCompany => $contacts)
      @php $slug = Str::slug($grpCompany); @endphp
      <div class="st-card contact-group-card" style="margin-bottom:14px;" data-group="{{ $grpCompany }}">
        <div class="st-card-hdr" style="background:linear-gradient(135deg,#0f2444,#1a3a6b);border-radius:11px 11px 0 0;cursor:grab;" draggable="true">
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="color:rgba(212,160,58,.5);font-size:16px;cursor:grab;" title="Drag to reorder group">⠿</span>
            <div style="font-size:12px;font-weight:700;color:#d4a03a;text-transform:uppercase;letter-spacing:1px;">{{ $grpCompany }}</div>
          </div>
          <button type="button" class="st-btn st-btn-sm" style="background:rgba(212,160,58,.2);color:#d4a03a;border:1px solid rgba(212,160,58,.4);" onclick="openAddContactModal('{{ addslashes($grpCompany) }}', this)">+ Add</button>
        </div>
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:600px;white-space:nowrap;">
          <thead><tr style="background:#f8fafc;">
            <th style="padding:9px 8px;width:24px;border-bottom:1px solid #f1f5f9;"></th>
            <th style="padding:9px 16px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;border-bottom:1px solid #f1f5f9;">Name</th>
            <th style="padding:9px 16px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;border-bottom:1px solid #f1f5f9;">Contact No.</th>
            <th style="padding:9px 16px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;border-bottom:1px solid #f1f5f9;">Email</th>
            <th style="padding:9px 16px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;border-bottom:1px solid #f1f5f9;">Facebook</th>
            <th style="padding:9px 16px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;border-bottom:1px solid #f1f5f9;">Actions</th>
          </tr></thead>
          <tbody class="contact-tbody" data-group="{{ $grpCompany }}">
            @foreach($contacts as $contact)
            <tr style="border-bottom:1px solid #f8fafc;" id="contact-row-{{ $contact->id }}" data-id="{{ $contact->id }}" draggable="true">
              <td style="padding:11px 8px;color:#cbd5e1;cursor:grab;font-size:16px;text-align:center;" title="Drag to reorder">⠿</td>
              <td style="padding:11px 16px;font-size:13px;font-weight:600;color:#0f172a;">{{ $contact->name }}</td>
              <td style="padding:11px 16px;font-size:13px;color:#374151;">{{ $contact->phone ?: '—' }}</td>
              <td style="padding:11px 16px;font-size:13px;">@if($contact->email)<a href="https://mail.google.com/mail/?view=cm&to={{ urlencode($contact->email) }}" target="_blank" rel="noopener" style="color:#1e4575;text-decoration:none;">{{ $contact->email }}</a>@else —@endif</td>
              <td style="padding:11px 16px;font-size:13px;">@if($contact->facebook)
                @php $fbUrl = str_starts_with($contact->facebook, 'http') ? $contact->facebook : 'https://facebook.com/' . $contact->facebook; @endphp
                <a href="{{ $fbUrl }}" target="_blank" rel="noopener" style="color:#1877f2;text-decoration:none;">{{ $contact->facebook }}</a>
              @else —@endif</td>
              <td style="padding:11px 16px;white-space:nowrap;">
                @if($isAdmin || $canSeeS('settings.personnel'))
                <button type="button" class="st-btn st-btn-primary st-btn-sm" onclick="openContactModal({{ $contact->id }}, '{{ addslashes($contact->name) }}', '{{ addslashes($contact->company) }}', '{{ addslashes($contact->phone) }}', '{{ addslashes($contact->email) }}', '{{ addslashes($contact->facebook) }}', this)">Edit</button>
                <form method="POST" action="{{ route('settings.personnel-contacts.destroy', $contact->id) }}" style="display:inline;" onsubmit="return confirm('Remove this contact?')">@csrf @method('DELETE')
                  <button type="submit" class="st-btn st-btn-danger st-btn-sm">Delete</button>
                </form>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        </div>
      </div>
      @endforeach
      </div>
      @endif

      {{-- Add New Group Modal --}}
      <div id="contactAddModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this)closeAddContactModal();">
        <div class="contact-add-box" style="background:white;border-radius:12px;padding:20px 24px;width:460px;max-width:95vw;max-height:85vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,.2);border:1px solid #e2e8f0;margin:auto;">
          <div style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">Add New Contact</div>
          <form method="POST" action="{{ route('settings.personnel-contacts.store') }}">@csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
              <div class="st-form-group"><label class="st-label">Name <span style="color:#ef4444;">*</span></label><input class="st-input" type="text" name="name" required placeholder="Full name"></div>
              <div class="st-form-group"><label class="st-label">Company / Group</label><input class="st-input" id="addModalCompany" type="text" name="company" placeholder="e.g. Executives, Broker"></div>
              <div class="st-form-group"><label class="st-label">Contact No.</label><input class="st-input" type="text" name="phone" placeholder="+63 9XX XXX XXXX"></div>
              <div class="st-form-group"><label class="st-label">Email</label><input class="st-input" type="email" name="email" placeholder="email@example.com"></div>
            </div>
            <div class="st-form-group" style="margin-bottom:16px;"><label class="st-label">Facebook</label><input class="st-input" type="text" name="facebook" placeholder="Facebook name or URL"></div>
            <div style="display:flex;gap:10px;">
              <button type="submit" class="st-btn st-btn-primary" style="flex:1;">Add Contact</button>
              <button type="button" onclick="closeAddContactModal()" style="flex:1;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;background:#f1f5f9;color:#374151;border:none;">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      {{-- Edit Contact Modal (keep for edit only) --}}
      <div id="contactEditModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)closeContactModal();">
        <div class="contact-edit-box" style="background:white;border-radius:12px;padding:20px 24px;width:460px;max-width:95vw;box-shadow:0 8px 32px rgba(0,0,0,.2);border:1px solid #e2e8f0;">
          <div style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">Edit Contact</div>
          <form id="contactEditForm" method="POST">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
              <div class="st-form-group"><label class="st-label">Name <span style="color:#ef4444;">*</span></label><input class="st-input" type="text" id="edit_name" name="name" required></div>
              <div class="st-form-group"><label class="st-label">Company</label><input class="st-input" type="text" id="edit_company" name="company"></div>
              <div class="st-form-group"><label class="st-label">Contact No.</label><input class="st-input" type="text" id="edit_phone" name="phone"></div>
              <div class="st-form-group"><label class="st-label">Email</label><input class="st-input" type="email" id="edit_email" name="email"></div>
            </div>
            <div class="st-form-group" style="margin-bottom:14px;"><label class="st-label">Facebook</label><input class="st-input" type="text" id="edit_facebook" name="facebook"></div>
            <div style="display:flex;gap:10px;">
              <button type="submit" class="st-btn st-btn-primary" style="flex:1;">Save Changes</button>
              <button type="button" onclick="closeContactModal()" style="flex:1;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;background:#f1f5f9;color:#374151;border:none;">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    @endif

  </div>{{-- end st-content --}}
</div>{{-- end st-page-wrap --}}

<script>
function showPanel(name) {
    document.querySelectorAll('.st-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.st-nav-btn').forEach(b => b.classList.remove('active'));
    const panel = document.getElementById('panel-' + name);
    const btn   = document.getElementById('nav-' + name);
    if (panel) panel.classList.add('active');
    if (btn)   btn.classList.add('active');
}

function selectAllGroup(groupId, checked) {
    document.querySelectorAll('#' + groupId + ' input[type=checkbox]').forEach(cb => cb.checked = checked);
}

function selectVisUser(userId, btn, userName) {
    document.getElementById('vis_user_id').value = userId;
    document.querySelectorAll('#vis-user-tabs button').forEach(b => {
        b.style.background = '#fff';
        b.style.color = '#374151';
        b.style.borderColor = '#e5e7eb';
        const avatar = b.querySelector('div:first-child');
        if (avatar) { avatar.style.background = '#e8edf5'; avatar.style.color = '#1e4575'; }
    });
    btn.style.background = '#1e4575';
    btn.style.color = '#fff';
    btn.style.borderColor = '#1e4575';
    const avatar = btn.querySelector('div:first-child');
    if (avatar) { avatar.style.background = 'rgba(255,255,255,0.25)'; avatar.style.color = '#fff'; }
    // Update label and enable save
    const label = document.getElementById('vis-for-label');
    if (label) label.textContent = 'for ' + userName;
    const saveBtn = document.querySelector('#vis-form button[type=submit]');
    if (saveBtn) saveBtn.disabled = false;
    fetch('/api/user-visibility/' + userId)
        .then(r => r.json())
        .then(data => {
            const hidden = data.hidden_pages || [];
            document.querySelectorAll('#vis-form input[type=checkbox]').forEach(cb => {
                cb.checked = !hidden.includes(cb.value);
            });
        });
}

function filterVisUserTabs() {
    var q = (document.getElementById('visUserSearch')?.value || '').toLowerCase().trim();
    var dept = (document.getElementById('visDeptFilter')?.value || '').toLowerCase().trim();
    var tabs = document.querySelectorAll('#vis-user-tabs button');
    var visibleCount = 0;
    tabs.forEach(function(b) {
        var name = b.getAttribute('data-name') || '';
        var department = b.getAttribute('data-department') || '';
        var matchName = !q || name.includes(q);
        var matchDept = !dept || department === dept;
        var match = matchName && matchDept;
        b.style.display = match ? '' : 'none';
        if (match) visibleCount++;
    });
    var noResults = document.getElementById('vis-user-no-results');
    if (noResults) noResults.style.display = (visibleCount === 0) ? 'block' : 'none';
}

function addEmailRow() {
    const row = document.createElement('div');
    row.className = 'email-row';
    row.innerHTML = '<input class="st-input" type="email" name="notification_emails[]" placeholder="email@example.com" style="flex:1;"><button type="button" class="st-btn st-btn-danger st-btn-sm" onclick="this.closest(\'.email-row\').remove()">Remove</button>';
    document.getElementById('emailList').appendChild(row);
}
function toggleEmpEdit(id) {
    var view = document.getElementById('emp-view-' + id);
    var edit = document.getElementById('emp-edit-' + id);
    var isEditing = edit.style.display !== 'none';
    edit.style.display = isEditing ? 'none' : 'table-row';
}

function toggleEdit(id) {
    const viewCells = document.querySelectorAll('.view-mode-' + id);
    const editCell  = document.querySelector('.edit-mode-' + id);
    const isEditing = editCell.style.display !== 'none';
    viewCells.forEach(c => c.style.display = isEditing ? '' : 'none');
    editCell.style.display = isEditing ? 'none' : '';
}
function openAddContactModal(company, btn) { 
    var modal = document.getElementById('contactAddModal');
    modal.style.display = 'flex';
    var f = document.getElementById('addModalCompany'); 
    if(f) f.value = company || '';
    modal.style.display = 'flex';
}

function openContactModal(id, name, company, phone, email, facebook, btn) {
    document.getElementById('contactEditForm').action = '/settings/personnel-contacts/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_company').value = company;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_facebook').value = facebook;
    document.getElementById('contactEditModal').style.display = 'flex';
}

function toggleInlineAdd(slug) {
    var el = document.getElementById('inline-add-' + slug);
    if (!el) return;
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
    if (el.style.display === 'block') {
        var first = el.querySelector('input[name="name"]');
        if (first) first.focus();
    }
}
function closeAddContactModal() { document.getElementById('contactAddModal').style.display = 'none'; }

// Team Management JS
var _editTeamId = null, _editAgentId = null;
function openEditTeam(id, name, leader, manager) {
    _editTeamId = id;
    document.getElementById('editTeamForm').action = '/settings/teams/' + id;
    document.getElementById('edit_team_name').value = name;
    document.getElementById('edit_leader_name').value = leader;
    document.getElementById('edit_sales_manager').value = manager;
    document.getElementById('editTeamModal').style.display = 'flex';
}
function closeEditTeam() { document.getElementById('editTeamModal').style.display = 'none'; }

function openEditAgent(id, name, empid) {
    _editAgentId = id;
    document.getElementById('edit_agent_name_input').value = name;
    document.getElementById('edit_agent_empid_input').value = empid || '';
    document.getElementById('editAgentModal').style.display = 'flex';
}
function closeEditAgent() { document.getElementById('editAgentModal').style.display = 'none'; }
function saveEditAgent() {
    var name  = document.getElementById('edit_agent_name_input').value.trim();
    var empid = document.getElementById('edit_agent_empid_input').value.trim();
    if (!name) return;
    fetch('/settings/agents/' + _editAgentId, {
        method: 'PATCH',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
        body: JSON.stringify({name: name, employee_id: empid})
    }).then(r => r.json()).then(d => {
        if (d.success) {
            var el = document.getElementById('agent-name-' + _editAgentId);
            if (el) el.textContent = name;
            var idEl = document.getElementById('agent-empid-' + _editAgentId);
            if (idEl) idEl.textContent = (d.employee_id || empid || '—');
            closeEditAgent();
        }
    });
}
function openAgentStatusDropdown(id, btn) {
    // Close any other open dropdowns first
    document.querySelectorAll('[id^="status-dropdown-"]').forEach(function(d) {
        if (d.id !== 'status-dropdown-' + id) d.style.display = 'none';
    });
    var dd = document.getElementById('status-dropdown-' + id);
    dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
}
function setAgentStatus(id, active) {
    var btn = document.getElementById('status-btn-' + id);
    var dd  = document.getElementById('status-dropdown-' + id);
    dd.style.display = 'none';
    btn.disabled = true;
    btn.style.opacity = '0.6';
    var csrf = document.querySelector('meta[name=csrf-token]').content;
    fetch('/settings/agents/' + id + '/toggle', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf},
        body: JSON.stringify({set_active: active ? 1 : 0})
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            var isActive = d.is_active;
            btn.setAttribute('data-active', isActive ? '1' : '0');
            document.getElementById('status-label-' + id).textContent = isActive ? 'Active' : 'Inactive';
            btn.style.background = isActive ? '#dcfce7' : '#fee2e2';
            btn.style.color = isActive ? '#166534' : '#991b1b';
        }
        btn.disabled = false;
        btn.style.opacity = '1';
    })
    .catch(function(){
        btn.disabled = false;
        btn.style.opacity = '1';
    });
}
// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="status-wrap-"]')) {
        document.querySelectorAll('[id^="status-dropdown-"]').forEach(function(d){ d.style.display = 'none'; });
    }
});
function openContactModal(id, name, company, phone, email, facebook, btn) {
    document.getElementById('contactEditForm').action = '/settings/personnel-contacts/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_company').value = company;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_facebook').value = facebook;
    document.getElementById('contactEditModal').style.display = 'flex';
}
function closeContactModal() {
    document.getElementById('contactEditModal').style.display = 'none';
}

function submitPermReview(id, status, form) {
    const note = document.getElementById('note_' + id)?.value || '';
    const csrf = form.querySelector('[name=_token]')?.value || document.querySelector('meta[name=csrf-token]')?.content || '';
    fetch('/api/permission-requests/' + id + '/review', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ status: status, admin_note: note })
    })
    .then(r => r.json())
    .then(() => { window.location.reload(); })
    .catch(() => { window.location.reload(); });
}

// ── ARC Contact List drag-and-drop ──────────────────────────────────────────
(function() {
    let dragSrc = null;

    // Row drag within a tbody
    function initRowDrag(tbody) {
        tbody.querySelectorAll('tr[data-id]').forEach(row => {
            row.addEventListener('dragstart', e => {
                dragSrc = row;
                e.dataTransfer.effectAllowed = 'move';
                setTimeout(() => row.style.opacity = '0.4', 0);
            });
            row.addEventListener('dragend', () => {
                row.style.opacity = '';
                tbody.querySelectorAll('tr').forEach(r => r.style.background = '');
                saveRowOrder(tbody);
            });
            row.addEventListener('dragover', e => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                if (row !== dragSrc) row.style.background = '#e0f2fe';
            });
            row.addEventListener('dragleave', () => row.style.background = '');
            row.addEventListener('drop', e => {
                e.preventDefault();
                if (dragSrc && dragSrc !== row && dragSrc.closest('tbody') === tbody) {
                    const rows = [...tbody.querySelectorAll('tr[data-id]')];
                    const srcIdx = rows.indexOf(dragSrc);
                    const tgtIdx = rows.indexOf(row);
                    if (srcIdx < tgtIdx) tbody.insertBefore(dragSrc, row.nextSibling);
                    else tbody.insertBefore(dragSrc, row);
                }
                row.style.background = '';
            });
        });
    }

    function saveRowOrder(tbody) {
        const items = [...tbody.querySelectorAll('tr[data-id]')].map((r, i) => ({
            id: parseInt(r.dataset.id), sort_order: i
        }));
        fetch('/api/personnel-contacts/reorder', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            body: JSON.stringify({items})
        });
    }

    // Group drag
    let dragGroup = null;
    function initGroupDrag(wrap) {
        wrap.querySelectorAll('.contact-group-card').forEach(card => {
            const hdr = card.querySelector('.st-card-hdr');
            hdr.addEventListener('dragstart', e => {
                dragGroup = card;
                e.dataTransfer.effectAllowed = 'move';
                setTimeout(() => card.style.opacity = '0.4', 0);
            });
            hdr.addEventListener('dragend', () => {
                card.style.opacity = '';
                wrap.querySelectorAll('.contact-group-card').forEach(c => c.style.outline = '');
                saveGroupOrder(wrap);
            });
            card.addEventListener('dragover', e => {
                e.preventDefault();
                if (card !== dragGroup) card.style.outline = '2px solid #2563eb';
            });
            card.addEventListener('dragleave', () => card.style.outline = '');
            card.addEventListener('drop', e => {
                e.preventDefault();
                if (dragGroup && dragGroup !== card) {
                    const cards = [...wrap.querySelectorAll('.contact-group-card')];
                    const srcIdx = cards.indexOf(dragGroup);
                    const tgtIdx = cards.indexOf(card);
                    if (srcIdx < tgtIdx) wrap.insertBefore(dragGroup, card.nextSibling);
                    else wrap.insertBefore(dragGroup, card);
                }
                card.style.outline = '';
            });
        });
    }

    function saveGroupOrder(wrap) {
        // Collect all rows in new group order and save their sort_order
        const items = [];
        let order = 0;
        wrap.querySelectorAll('.contact-group-card').forEach(card => {
            card.querySelectorAll('tr[data-id]').forEach(row => {
                items.push({id: parseInt(row.dataset.id), sort_order: order++});
            });
        });
        fetch('/api/personnel-contacts/reorder', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            body: JSON.stringify({items})
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const wrap = document.getElementById('contact-groups-wrap');
        if (!wrap) return;
        initGroupDrag(wrap);
        wrap.querySelectorAll('.contact-tbody').forEach(initRowDrag);
    });
})();
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const panel  = params.get('panel');
    if (panel) { showPanel(panel); }
    else {
        @if(session('open_section'))
        showPanel('{{ session("open_section") }}');
        @else
        showPanel('profile');
        @endif
    }
});
function checkSettingsPwd(val) {
    var fill = document.getElementById('settings-pwd-fill');
    var text = document.getElementById('settings-pwd-text');
    if (!fill) return;
    var has8 = val.length >= 8;
    var hasUpper = /[A-Z]/.test(val);
    var hasLower = /[a-z]/.test(val);
    var hasNum = /[0-9]/.test(val);
    var hasSym = /[^A-Za-z0-9]/.test(val);
    var score = [has8, hasUpper, hasLower, hasNum, hasSym].filter(Boolean).length;
    var colors = ['#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
    var labels = ['Very Weak','Weak','Fair','Strong','Very Strong'];
    fill.style.width = (score / 5 * 100) + '%';
    fill.style.background = colors[score - 1] || '#e2e8f0';
    if (!val.length) { text.textContent = ''; return; }
    var reqs = [];
    if (!has8) reqs.push('8+ chars');
    if (!hasUpper) reqs.push('uppercase');
    if (!hasLower) reqs.push('lowercase');
    if (!hasNum) reqs.push('number');
    if (!hasSym) reqs.push('symbol');
    text.style.color = colors[score - 1] || '#94a3b8';
    text.textContent = (labels[score - 1] || '') + (reqs.length ? ' — needs: ' + reqs.join(', ') : ' ✓');
}
// Show/hide password toggle for the Change Password fields
function toggleSettingsPwdField(inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input) return;
    var showing = input.type === 'text';
    input.type = showing ? 'password' : 'text';
    btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
    btn.innerHTML = showing
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>'
        : '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.68 19.68 0 0 1 4.22-5.94M9.9 4.24A10.4 10.4 0 0 1 12 4c7 0 11 8 11 8a19.6 19.6 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
}
// ── Deleted Records ─────────────────────────────────────────────────────────
function openDelDetail(data) {
    document.getElementById('delDetailTitle').textContent = data.title || '—';
    document.getElementById('delDetailModule').textContent = data.module || '';
    document.getElementById('delDetailBy').textContent = data.deleted_by || 'Unknown';
    document.getElementById('delDetailAt').textContent = data.deleted_at || '—';
    var wrap = document.getElementById('delDetailFields');
    wrap.innerHTML = '';
    var fields = data.fields || {};
    var keys = Object.keys(fields).filter(k => fields[k] !== null && fields[k] !== undefined && fields[k] !== '');
    if (!keys.length) {
        wrap.innerHTML = '<div style="font-size:12px;color:#94a3b8;">No additional details available.</div>';
    } else {
        keys.forEach(function(k) {
            var row = document.createElement('div');
            row.style.cssText = 'display:flex;justify-content:space-between;gap:12px;padding:6px 10px;background:#f8fafc;border-radius:6px;font-size:12px;';
            var label = k.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
            row.innerHTML = '<span style="color:#64748b;font-weight:600;">' + label + '</span><span style="color:#374151;text-align:right;">' + String(fields[k]) + '</span>';
            wrap.appendChild(row);
        });
    }
    document.getElementById('delDetailModal').style.display = 'flex';
}
function closeDelDetail() { document.getElementById('delDetailModal').style.display = 'none'; }

function filterDeletedGroups() {
    var val = document.getElementById('delFilterModule').value;
    document.querySelectorAll('.del-group').forEach(function(g) {
        g.style.display = (!val || g.dataset.module === val) ? '' : 'none';
    });
}

function toggleGroupSelect(cb, type) {
    cb.closest('.del-group').querySelectorAll('.del-select[data-type="' + type + '"]:not(:disabled)')
      .forEach(function(c){ c.checked = cb.checked; });
    updateDelBulkBar();
}

document.addEventListener('change', function(e) {
    if (e.target.classList && e.target.classList.contains('del-select')) updateDelBulkBar();
});

function updateDelBulkBar() {
    var checked = document.querySelectorAll('.del-select:checked');
    document.getElementById('del-selected-count').textContent = checked.length + ' selected';
    document.getElementById('del-bulk-bar').style.display = checked.length ? 'flex' : 'none';
}

function clearDelSelection() {
    document.querySelectorAll('.del-select:checked').forEach(c => c.checked = false);
    updateDelBulkBar();
}

// Single-row Restore/Delete (e.g. Departmental Expenses rows). Reuses the same
// bulk endpoints as bulkAction() with a one-item payload. Uses the app's own
// confirm modal / toast (native confirm()/alert() won't work here — confirm()
// is globally overridden to always return true with no popup, see layouts/dashboard.blade.php).
async function delSingleAction(type, id, kind) {
    var message = kind === 'delete'
        ? 'Permanently delete this record? This cannot be undone.'
        : 'Restore this record?';
    var confirmed = window.showConfirmModal ? await window.showConfirmModal(message) : true;
    if (!confirmed) return;

    var url = kind === 'delete' ? '{{ route("settings.deleted.bulk-delete") }}' : '{{ route("settings.deleted.bulk-restore") }}';
    var csrf = document.querySelector('meta[name=csrf-token]').content;

    try {
        var res = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf, 'Accept':'application/json'},
            body: JSON.stringify({ items: [{ type: type, id: id }] })
        });
        var d = await res.json();
        if (window.showToast) { window.showToast(d.message || 'Done.', d.success ? 'success' : 'error'); }
        else { alert(d.message || 'Done.'); }
        setTimeout(function(){ window.location.reload(); }, 600);
    } catch (e) {
        if (window.showToast) { window.showToast('Something went wrong. Please try again.', 'error'); }
        else { alert('Something went wrong. Please try again.'); }
    }
}

async function bulkAction(kind) {
    var checked = document.querySelectorAll('.del-select:checked');
    if (!checked.length) return;

    var message = kind === 'delete'
        ? 'Permanently delete ' + checked.length + ' record(s)? This cannot be undone.'
        : 'Restore ' + checked.length + ' record(s)?';
    var confirmed = window.showConfirmModal ? await window.showConfirmModal(message) : true;
    if (!confirmed) return;

    var items = Array.from(checked).map(c => ({ type: c.dataset.type, id: parseInt(c.dataset.id) }));
    var url = kind === 'delete' ? '{{ route("settings.deleted.bulk-delete") }}' : '{{ route("settings.deleted.bulk-restore") }}';
    var csrf = document.querySelector('meta[name=csrf-token]').content;

    try {
        var res = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf, 'Accept':'application/json'},
            body: JSON.stringify({ items: items })
        });
        var d = await res.json();
        if (window.showToast) { window.showToast(d.message || 'Done.', d.success ? 'success' : 'error'); }
        else { alert(d.message || 'Done.'); }
        setTimeout(function(){ window.location.reload(); }, 600);
    } catch (e) {
        if (window.showToast) { window.showToast('Something went wrong. Please try again.', 'error'); }
        else { alert('Something went wrong. Please try again.'); }
    }
}
</script>
@endsection