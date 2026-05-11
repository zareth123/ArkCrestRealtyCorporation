<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="{{ asset('images/ArkCrest_Logo.png') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Login - ARCKREST REALTY CORPORATION</title>
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html,body{height:100%;overflow:hidden;font-family:"Segoe UI",system-ui,sans-serif}
body{display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0a1628 0%,#1a3a6b 50%,#0f2a4a 100%)}
.card{position:relative;width:920px;max-width:96vw;height:600px;border-radius:24px;overflow:hidden;box-shadow:0 40px 100px rgba(0,0,0,.55);background:white;display:flex}
.overlay{position:absolute;top:0;left:0;width:50%;height:100%;background:linear-gradient(150deg,#1e4575 0%,#0f2a4a 100%);display:flex;flex-direction:column;align-items:center;justify-content:space-between;padding:40px 36px;text-align:center;transition:transform .7s cubic-bezier(.4,0,.2,1);z-index:10;overflow:hidden}
.overlay::before{content:"";position:absolute;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(163,121,41,.18),transparent 65%);top:-80px;right:-80px;pointer-events:none;animation:pulse 4s ease-in-out infinite}
.card.reg .overlay{transform:translateX(100%) scale(1.02)}
.card:not(.reg) .overlay{transform:translateX(0) scale(1)}
.brand{display:flex;flex-direction:column;align-items:center;gap:10px;position:relative;z-index:1}
.brand-logo{width:80px;height:80px}
.brand-logo img{width:100%;height:100%;object-fit:contain}
.brand-name{font-size:32px;font-weight:800;letter-spacing:1px;text-transform:uppercase;line-height:1.3;text-align:center;white-space:normal;background:linear-gradient(90deg,#ffffff,#d4a855,#ffffff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;background-size:200% auto;animation:shimmer 3s linear infinite}
.ov-body{position:relative;z-index:1}
.ov-eyebrow{font-size:22px;font-weight:700;color:white;margin-bottom:8px;line-height:1.1}
.ov-body h2{font-size:17px;font-weight:600;color:rgba(255,255,255,.75);line-height:1.2;margin-bottom:18px;white-space:nowrap}
.ov-body h2 span{color:#d4a855;font-weight:600}
.ov-body p{font-size:13px;color:rgba(255,255,255,.55);line-height:1.7;margin-bottom:28px}
.btn-outline{display:inline-block;padding:12px 36px;border:none;color:#0f172a;border-radius:30px;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer;background:linear-gradient(135deg,#d4a855,#f0c96a);box-shadow:0 4px 20px rgba(212,168,85,.4);transition:all .25s}
.btn-outline:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(212,168,85,.5)}
.ov-footer{position:relative;z-index:1;font-size:10px;color:rgba(255,255,255,.3);letter-spacing:.5px}
.forms-area{position:absolute;top:0;right:0;width:50%;height:100%;overflow:hidden;transition:transform .7s cubic-bezier(.4,0,.2,1);border-left:1px solid rgba(30,69,117,.08)}
.card.reg .forms-area{transform:translateX(-100%)}
.form-panel{position:absolute;top:0;left:0;width:100%;height:100%;display:flex;flex-direction:column;padding:32px 44px;background:linear-gradient(160deg,#f8fafc 0%,#ffffff 60%,#f0f4ff 100%);transition:transform .7s cubic-bezier(.4,0,.2,1),opacity .5s ease}
.form-signin{transform:translateX(0);opacity:1;justify-content:center}
.form-register{transform:translateX(0);opacity:0;pointer-events:none;justify-content:flex-start}
.card.reg .form-signin{opacity:0;pointer-events:none}
.card.reg .form-register{opacity:1;pointer-events:all}
.form-title{font-size:22px;font-weight:800;color:#0f172a;margin-bottom:2px;letter-spacing:-0.5px}
.form-sub{font-size:11px;color:#94a3b8;margin-bottom:14px;line-height:1.5}
.alert-error{background:#fef2f2;border-left:3px solid #ef4444;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:12px}
.alert-success{background:#f0fdf4;border-left:3px solid #22c55e;color:#16a34a;padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:12px}
.field{margin-bottom:9px}
.field label{display:block;font-size:10px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.7px;margin-bottom:4px}
.field-wrap{position:relative}
.field-wrap svg:not(.eye-icon){position:absolute;left:12px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#9ca3af;pointer-events:none}
.field-wrap .pwd-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:6px;display:flex;align-items:center;justify-content:center;border-radius:6px;transition:all .2s;z-index:2;}
.field-wrap .pwd-toggle:hover{color:#1e4575;background:rgba(30,69,117,.06);}
.eye-icon{width:18px!important;height:18px!important;position:static!important;transform:none!important;pointer-events:none;}
.field input,.field select{width:100%;padding:13px 12px 13px 36px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:12px;color:#111827;background:white;transition:all .2s;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.field input.has-toggle{padding-right:40px;}
.field input:focus,.field select:focus{outline:none;border-color:#1e4575;box-shadow:0 0 0 3px rgba(30,69,117,.08)}
.field input::placeholder{color:#b0bec5}
.field select{appearance:none;cursor:pointer}
.row-opts{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
/* Toggle Switch for Remember Me */
.toggle-wrap{display:flex;align-items:center;gap:10px;cursor:pointer;}
.toggle-wrap input[type=checkbox]{display:none;}
.toggle-switch{
    width:36px;height:20px;
    background:#d1d5db;
    border-radius:20px;
    position:relative;
    transition:background .25s;
    flex-shrink:0;
}
.toggle-switch::after{
    content:'';
    position:absolute;
    width:14px;height:14px;
    background:white;
    border-radius:50%;
    top:3px;left:3px;
    transition:transform .25s;
    box-shadow:0 1px 3px rgba(0,0,0,.2);
}
.toggle-wrap input:checked + .toggle-switch{background:#1e4575;}
.toggle-wrap input:checked + .toggle-switch::after{transform:translateX(16px);}
.toggle-label{font-size:12px;font-weight:500;color:#374151;user-select:none;}

/* Privacy Policy Agreement */
.privacy-box{
    margin-bottom:16px;
    border-radius:10px;
    overflow:hidden;
    border:1px solid #e2e8f0;
}
.privacy-box-header{
    background:linear-gradient(135deg,#1e4575,#2563eb);
    padding:8px 14px;
    display:flex;
    align-items:center;
    gap:7px;
}
.privacy-box-header span{
    font-size:10px;
    font-weight:700;
    color:white;
    text-transform:uppercase;
    letter-spacing:.8px;
}
.privacy-box-body{
    padding:10px 14px;
    background:white;
    display:flex;
    align-items:flex-start;
    gap:10px;
}
.privacy-box-body input[type=checkbox]{
    width:15px;height:15px;
    accent-color:#1e4575;
    cursor:pointer;
    flex-shrink:0;
    margin-top:2px;
}
.privacy-box-body p{
    font-size:11px;
    color:#4b5563;
    line-height:1.6;
    margin:0;
}
.privacy-box-body a{
    color:#1e4575;
    font-weight:700;
    text-decoration:none;
    border-bottom:1.5px solid #1e4575;
}
.btn-primary{width:100%;padding:13px;background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);background-size:200% auto;color:white;border:none;border-radius:12px;font-size:13px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;cursor:pointer;box-shadow:0 6px 20px rgba(30,69,117,.35);transition:all .3s;animation:btnShimmer 3s linear infinite}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(30,69,117,.4)}
.step-circle{width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;transition:all .3s;cursor:pointer}
.step-circle.active{background:linear-gradient(135deg,#1e4575,#2563eb);color:white;box-shadow:0 2px 8px rgba(30,69,117,.3);border:none}
.step-circle.done{background:#d1fae5;color:#059669;border:none}
.step-circle.inactive{background:#f9fafb;color:#94a3b8;border:2px solid #e2e8f0}
.step-line{flex:1;height:2px;margin-top:11px;margin-left:4px;margin-right:4px;transition:background .3s}
.step-line.done{background:#2563eb}
.step-line.inactive{background:#e2e8f0}
@keyframes shimmer{0%{background-position:200% center}100%{background-position:-200% center}}
@keyframes btnShimmer{0%{background-position:200% center}100%{background-position:-200% center}}
@keyframes pulse{0%,100%{transform:scale(1);opacity:.18}50%{transform:scale(1.08);opacity:.25}}
</style>
</head>
<body>

<div class="card" id="authCard">
  <div class="overlay">
    <div class="brand">
      <div class="brand-logo"><img src="{{ asset('images/ArkCrest_Logo.png') }}" alt="Arckrest"></div>
      <div class="brand-name">ARCKREST REALTY CORPORATION</div>
    </div>
    <div class="ov-body">
      <div class="ov-eyebrow" id="ovEyebrow">Welcome</div>
      <h2 id="ovTitle">New to the <span>System?</span></h2>
      <p id="ovText">ArkCrest Realty: Making your real estate dreams a reality with expert support and exceptional service.</p>
      <button class="btn-outline" onclick="toggleMode()" id="ovBtn">Create an Account</button>
    </div>
    <div class="ov-footer">&copy; {{ date('Y') }} Arckrest Realty Corporation</div>
  </div>
  <div class="forms-area">
    {{-- SIGN IN --}}
    <div class="form-panel form-signin">
      <div class="form-title">Welcome back</div>
      <div class="form-sub">Login to dive into the system!</div>
      @if(session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
      @if(session('inactive_agent'))
      <div style="background:#fff7ed;border-left:3px solid #f97316;border-radius:8px;padding:12px 14px;margin-bottom:12px;font-size:12px;color:#9a3412;">
        <div style="font-weight:700;margin-bottom:4px;">⚠ Account Inactive</div>
        <div>Your account has been set to inactive. Please contact the executives to reactivate your access.</div>
      </div>
      @endif
      @if($errors->any() && !old('name'))<div class="alert-error">{{ $errors->first() }}</div>@endif
      <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="field"><label>Email Address</label><div class="field-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><input type="email" name="email" value="{{ old('email') }}" placeholder="yourname@email.com" autofocus required></div></div>
        <div class="field"><label>Password</label><div class="field-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg><input type="password" id="login_password" name="password" class="has-toggle" placeholder="Enter your password" required><button type="button" class="pwd-toggle" onclick="toggleLoginPwd()" title="Show/hide password"><svg id="eye_show" class="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg><svg id="eye_hide" class="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg></button></div></div>
        <div class="row-opts">
          <label class="toggle-wrap">
            <input type="checkbox" name="remember">
            <span class="toggle-switch"></span>
            <span class="toggle-label">Remember me</span>
          </label>
          <a href="#" onclick="event.preventDefault();showForgotModal()" style="font-size:11px;font-weight:600;color:#1e4575;text-decoration:none;border-bottom:1px solid rgba(30,69,117,.3);transition:border-color .2s;" onmouseover="this.style.borderColor='#1e4575'" onmouseout="this.style.borderColor='rgba(30,69,117,.3)'">Forgot password?</a>
        </div>
        <div class="privacy-box">
          <div class="privacy-box-header">
            <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:12px;height:12px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span>Terms & Privacy Agreement</span>
          </div>
          <div class="privacy-box-body">
            <input type="checkbox" id="loginPrivacy">
            <p>I have read and agree to the <a href="#" onclick="event.preventDefault();showPrivacyModal()">Privacy Policy &amp; System Usage Policy</a> of Arckrest Realty Corporation.</p>
          </div>
        </div>
        <button type="submit" class="btn-primary">Sign In</button>
      </form>
    </div>
    {{-- REGISTER --}}
    <div class="form-panel form-register" style="display:flex;flex-direction:column;">
      <div style="display:flex;align-items:flex-start;margin-bottom:10px;flex-shrink:0;">
        <div style="display:flex;flex-direction:column;align-items:center;gap:3px;">
          <div class="step-circle active" id="sc1" style="cursor:pointer;" onclick="navStep(1)">1</div>
          <span style="font-size:8px;font-weight:700;color:#1e4575;white-space:nowrap;">Your Info</span>
        </div>
        <div class="step-line inactive" id="sl1"></div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:3px;">
          <div class="step-circle inactive" id="sc2" style="cursor:pointer;" onclick="navStep(2)">2</div>
          <span style="font-size:8px;color:#94a3b8;white-space:nowrap;">Security</span>
        </div>
        <div class="step-line inactive" id="sl2"></div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:3px;">
          <div class="step-circle inactive" id="sc3">3</div>
          <span style="font-size:8px;color:#94a3b8;white-space:nowrap;">Verify</span>
        </div>
        <div class="step-line inactive" id="sl3"></div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:3px;">
          <div class="step-circle inactive" id="sc4">4</div>
          <span style="font-size:8px;color:#94a3b8;white-space:nowrap;">Approval</span>
        </div>
      </div>
      <div class="form-title" id="regTitle" style="flex-shrink:0;">Create Account</div>
      <div class="form-sub" id="regSub" style="flex-shrink:0;">Step 1 of 4 &mdash; Personal Details</div>
      @if($errors->any() && old('name'))<div class="alert-error" style="flex-shrink:0;">{{ $errors->first() }}</div>@endif
      <div style="flex:1;">
        <div id="rs1">
          <div class="field"><label>Full Name</label><div class="field-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><input type="text" id="r_name" placeholder="Your full name" value="{{ old('name') }}"></div></div>
          <div class="field"><label>Preferred Name / Address</label><div class="field-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg><select id="r_address" style="appearance:none;-webkit-appearance:none;"><option value="">Select salutation...</option><option value="Mr.">Mr.</option><option value="Ms.">Ms.</option><option value="Mrs.">Mrs.</option><option value="Sir">Sir</option><option value="Ma'am">Ma'am</option></select></div></div>
          <div class="field"><label>Position / Job Title</label><div class="field-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><input type="text" id="r_pos" placeholder="e.g. Finance Officer" value="{{ old('position') }}"></div></div>
          <div class="field"><label>Employee ID</label><div class="field-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/></svg><input type="text" id="r_empid" placeholder="e.g. EMP-001" value="{{ old('employee_id') }}"></div></div>
          <div class="field"><label>Date Hired</label><div class="field-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><input type="date" id="r_hired" value="{{ old('date_hired') }}"></div></div>
        </div>
        <div id="rs2" style="display:none;">
          <form method="POST" action="{{ route('register.post') }}" id="regForm">
            @csrf
            <input type="hidden" name="name" id="h_name">
            <input type="hidden" name="preferred_address" id="h_address">
            <input type="hidden" name="position" id="h_pos">
            <input type="hidden" name="employee_id" id="h_empid">
            <input type="hidden" name="date_hired" id="h_hired">
            <div class="field"><label>Email Address</label><div class="field-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><input type="email" name="email" id="r_email" placeholder="yourname@email.com" required></div></div>
            <div class="field"><label>Password</label><div class="field-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg><input type="password" name="password" id="r_password" class="has-toggle" placeholder="Min. 8 chars, upper, lower, number, symbol" required oninput="checkPwdStrength(this.value)"><button type="button" class="pwd-toggle" onclick="togglePwd('r_password',this)"><svg class="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button></div>
            <div id="pwd-strength-bar" style="height:3px;border-radius:2px;margin-top:4px;background:#e2e8f0;overflow:hidden;"><div id="pwd-strength-fill" style="height:100%;width:0;transition:width .3s,background .3s;"></div></div>
            <div id="pwd-strength-text" style="font-size:10px;color:#94a3b8;margin-top:2px;"></div>
            </div>
            <div class="field"><label>Confirm Password</label><div class="field-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg><input type="password" name="password_confirmation" id="r_password_confirmation" class="has-toggle" placeholder="Repeat password" required><button type="button" class="pwd-toggle" onclick="togglePwd('r_password_confirmation',this)"><svg class="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button></div></div>
            <div class="field"><label>Security Question</label><div style="position:relative;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#9ca3af;pointer-events:none;z-index:1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><select name="security_question" required style="width:100%;padding:13px 12px 13px 36px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:12px;color:#111827;background:white;box-shadow:0 1px 3px rgba(0,0,0,.04);appearance:none;cursor:pointer;"><option value="">Select a security question...</option><option>What is your mother's maiden name?</option><option>What was the name of your first pet?</option><option>What city were you born in?</option><option>What is your favorite book?</option><option>What was the name of your elementary school?</option></select></div></div>
            <div class="field"><label>Security Answer</label><div class="field-wrap"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg><input type="text" name="security_answer" placeholder="Your answer" required></div></div>
          </form>
        </div>
        <div id="rs3" style="display:none;text-align:center;padding-top:24px;">
          <svg fill="none" stroke="#1e4575" viewBox="0 0 24 24" style="width:48px;height:48px;margin-bottom:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:8px;">Check your email</div>
          <div style="font-size:12px;color:#64748b;line-height:1.6;">A 6-digit verification code has been sent to your email.</div>
        </div>
        <div id="rs4" style="display:none;text-align:center;padding-top:24px;">
          <svg fill="none" stroke="#059669" viewBox="0 0 24 24" style="width:48px;height:48px;margin-bottom:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:8px;">Account Submitted!</div>
          <div style="font-size:12px;color:#64748b;line-height:1.6;margin-bottom:16px;">Pending admin approval. Check your email for updates.</div>
          <a href="{{ route('login') }}" style="font-size:12px;color:#1e4575;font-weight:600;text-decoration:none;">Back to Sign In &rarr;</a>
        </div>
      </div>
      <div style="flex-shrink:0;margin-top:10px;">
        <div id="rbtn1"><button type="button" class="btn-primary" onclick="goStep(2)">Next &rarr; Security</button></div>
        <div id="rbtn2" style="display:none;"><button type="submit" form="regForm" class="btn-primary">Send Verification Code</button></div>
      </div>
    </div>
  </div>
<script>
var isReg=false,curStep=1,filled={1:false,2:false};
document.querySelector('form[action*="login"').addEventListener('submit',function(e){
  if(!document.getElementById('loginPrivacy').checked){
    e.preventDefault();
    showLoginToast('Please read and agree to the Privacy Policy & System Usage Policy before signing in.', 'warning');
  }
});

// Block register form if password is weak
document.addEventListener('DOMContentLoaded', function() {
  var regForm = document.getElementById('regForm');
  if (regForm) {
    regForm.addEventListener('submit', function(e) {
      var pwd = document.getElementById('r_password');
      if (pwd && pwd.value) {
        var strong = checkPwdStrength(pwd.value);
        if (!strong) {
          e.preventDefault();
          showLoginToast('Password is too weak. Must have uppercase, lowercase, number, and symbol.', 'warning');
          return false;
        }
      }
    });
  }
});
function toggleMode(){
  isReg=!isReg;
  var c=document.getElementById('authCard');
  if(isReg){c.classList.add('reg');document.getElementById('ovEyebrow').textContent='Welcome Back';document.getElementById('ovTitle').innerHTML='Already have an <span>Account?</span>';document.getElementById('ovBtn').textContent='Sign In';}
  else{c.classList.remove('reg');document.getElementById('ovEyebrow').textContent='Welcome';document.getElementById('ovTitle').innerHTML='New to the <span>System?</span>';document.getElementById('ovBtn').textContent='Create an Account';}
}
function setStep(n){
  [1,2,3,4].forEach(function(i){
    var s=document.getElementById('rs'+i);
    if(s)s.style.display=(i===n)?'block':'none';
  });
  var b1=document.getElementById('rbtn1'),b2=document.getElementById('rbtn2');
  if(b1)b1.style.display=(n===1)?'block':'none';
  if(b2)b2.style.display=(n===2)?'block':'none';
  var titles={1:['Create Account','Step 1 of 4 — Tell us about yourself'],2:['Set Your Password','Step 2 of 4 — Secure your account'],3:['Verify Email','Step 3 of 4 — Check your inbox'],4:['Pending Approval','Step 4 of 4 — Almost there!']};
  document.getElementById('regTitle').textContent=titles[n][0];
  document.getElementById('regSub').textContent=titles[n][1];
  [1,2,3,4].forEach(function(i){
    var sc=document.getElementById('sc'+i);
    if(!sc)return;
    sc.className='step-circle '+(i<n?'done':(i===n?'active':'inactive'));
    if(i<n)sc.innerHTML='&#10003;';
    else if(i>n)sc.innerHTML=i;
    else sc.innerHTML=i;
  });
  [1,2,3].forEach(function(i){
    var sl=document.getElementById('sl'+i);
    if(sl)sl.className='step-line '+(i<n?'done':'inactive');
  });
  curStep=n;
}
function navStep(n){
  if(n<curStep||(n===2&&filled[1])||(n===1)){setStep(n);}
}
function goStep(n){
  if(n===2){
    var name=document.getElementById('r_name').value.trim();
    var addr=document.getElementById('r_address').value.trim();
    var pos=document.getElementById('r_pos').value.trim();
    var empid=document.getElementById('r_empid').value.trim();
    var hired=document.getElementById('r_hired').value;
    if(!name||!addr||!pos||!empid||!hired){showLoginToast('Please fill in all fields.','warning');return;}
    document.getElementById('h_name').value=name;
    document.getElementById('h_address').value=addr;
    document.getElementById('h_pos').value=pos;
    document.getElementById('h_empid').value=empid;
    document.getElementById('h_hired').value=hired;
    filled[1]=true;
    sessionStorage.setItem('reg_name',name);
    sessionStorage.setItem('reg_address',addr);
    sessionStorage.setItem('reg_pos',pos);
    sessionStorage.setItem('reg_empid',empid);
    sessionStorage.setItem('reg_hired',hired);
  }
  if(n===1){
    var fn=document.getElementById('r_name');var fa=document.getElementById('r_address');
    var fp=document.getElementById('r_pos');var fi=document.getElementById('r_empid');var fd=document.getElementById('r_hired');
    if(fn)fn.value=sessionStorage.getItem('reg_name')||'';
    if(fa)fa.value=sessionStorage.getItem('reg_address')||'';
    if(fp)fp.value=sessionStorage.getItem('reg_pos')||'';
    if(fi)fi.value=sessionStorage.getItem('reg_empid')||'';
    if(fd)fd.value=sessionStorage.getItem('reg_hired')||'';
  }
  setStep(n);
}
(function(){
  var backStep=sessionStorage.getItem('reg_back_step');
  if(!backStep)return;
  sessionStorage.removeItem('reg_back_step');
  var fm={r_name:'reg_name',r_address:'reg_address',r_pos:'reg_pos',r_empid:'reg_empid',r_hired:'reg_hired'};
  var hm={h_name:'reg_name',h_address:'reg_address',h_pos:'reg_pos',h_empid:'reg_empid',h_hired:'reg_hired'};
  Object.keys(fm).forEach(function(id){var el=document.getElementById(id);if(el)el.value=sessionStorage.getItem(fm[id])||'';});
  Object.keys(hm).forEach(function(id){var el=document.getElementById(id);if(el)el.value=sessionStorage.getItem(hm[id])||'';});
  filled[1]=true;window._regRestored=true;toggleMode();setStep(parseInt(backStep));
})();
function showPrivacyModal(){
  document.getElementById('privacyModal').style.display='flex';
}
function closePrivacyModal(){
  document.getElementById('privacyModal').style.display='none';
}
function togglePwd(id, btn) {
    var input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        btn.style.color = '#1e4575';
    } else {
        input.type = 'password';
        btn.style.color = '#9ca3af';
    }
}
function checkPwdStrength(val) {
    var fill = document.getElementById('pwd-strength-fill');
    var text = document.getElementById('pwd-strength-text');
    if (!fill) return;
    var has8 = val.length >= 8;
    var hasUpper = /[A-Z]/.test(val);
    var hasLower = /[a-z]/.test(val);
    var hasNum = /[0-9]/.test(val);
    var hasSym = /[^A-Za-z0-9]/.test(val);
    var score = [has8, hasUpper, hasLower, hasNum, hasSym].filter(Boolean).length;
    var colors = ['#ef4444','#f97316','#eab308','#22c55e','#16a34a'];
    var labels = ['Very Weak','Weak','Fair','Strong','Very Strong'];
    var pct = (score / 5) * 100;
    fill.style.width = pct + '%';
    fill.style.background = colors[score - 1] || '#e2e8f0';
    var reqs = [];
    if (!has8) reqs.push('8+ chars');
    if (!hasUpper) reqs.push('uppercase');
    if (!hasLower) reqs.push('lowercase');
    if (!hasNum) reqs.push('number');
    if (!hasSym) reqs.push('symbol');
    if (val.length === 0) { text.textContent = ''; return; }
    text.style.color = colors[score - 1] || '#94a3b8';
    text.textContent = (labels[score - 1] || '') + (reqs.length ? ' — needs: ' + reqs.join(', ') : ' ✓');
    return score >= 4; // strong enough
}
</script>
@if(request()->is('register') || old('name') || request()->get('register'))
<script>if(!window._regRestored)toggleMode();</script>
@endif
@if(old('name') && !request()->get('register'))
<script>
(function(){
  document.getElementById('h_name').value="{{ old('name') }}";
  document.getElementById('h_address').value="{{ old('preferred_address') }}";
  document.getElementById('h_pos').value="{{ old('position') }}";
  document.getElementById('h_empid').value="{{ old('employee_id') }}";
  document.getElementById('h_hired').value="{{ old('date_hired') }}";
  filled[1]=true;
  setStep(2);
})();
</script>
@endif
@if(session('success') && !request()->get('register'))
<script>
['reg_name','reg_address','reg_pos','reg_empid','reg_hired','reg_back_step'].forEach(function(k){sessionStorage.removeItem(k);});
</script>
@endif
{{-- Privacy Policy Modal --}}
<div id="privacyModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:white;border-radius:16px;width:560px;max-width:95vw;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.4);">
    <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
      <div style="font-size:16px;font-weight:700;color:#0f172a;">Privacy Policy & System Usage Policy</div>
      <button onclick="closePrivacyModal()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;line-height:1;">&times;</button>
    </div>
    <div style="padding:20px 24px;overflow-y:auto;font-size:13px;color:#374151;line-height:1.9;white-space:pre-wrap;">@php echo htmlspecialchars(\DB::table('app_settings')->where('key','privacy_policy')->value('value') ?? "Data Privacy Notice\n\nArckrest Realty Corporation is committed to protecting the privacy and confidentiality of all personal information collected through this system.\n\nInformation We Collect\n\nWe collect your full name, email address, employee ID, position, and date hired for account management and system access purposes.\n\nHow We Use Your Information\n\n- To manage and authenticate your system account\n- To track activity logs for security and audit purposes\n- To send email notifications related to your account\n- To generate internal reports and analytics\n\nSystem Usage Policy\n\n- Keep your login credentials confidential at all times.\n- Unauthorized access or sharing of credentials is strictly prohibited.\n- All data entered must be accurate and truthful.\n- Misuse may result in account suspension or termination.\n- This system is for authorized Arckrest Realty Corporation employees only."); @endphp</div>
    <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
      <button onclick="closePrivacyModal();document.getElementById('loginPrivacy').checked=true;" style="padding:10px 24px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;">I Agree</button>
      <button onclick="closePrivacyModal()" style="padding:10px 20px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:600;color:#374151;cursor:pointer;">Close</button>
    </div>
  </div>
</div>

<!-- Forgot Password Modal -->
<div id="forgotModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99999;align-items:center;justify-content:center;" onclick="if(event.target===this)closeForgotModal()">
    <div style="background:white;border-radius:16px;width:420px;max-width:95vw;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.25);">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);padding:18px 22px;display:flex;align-items:center;gap:12px;">
            <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg fill="none" stroke="white" viewBox="0 0 24 24" style="width:17px;height:17px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div style="flex:1;">
                <div style="color:rgba(255,255,255,.65);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;">Account Recovery</div>
                <div style="color:white;font-size:15px;font-weight:700;margin-top:1px;" id="forgotModalTitle">Reset Password</div>
            </div>
            <button onclick="closeForgotModal()" style="background:rgba(255,255,255,.15);border:none;color:white;width:26px;height:26px;border-radius:6px;cursor:pointer;font-size:16px;line-height:1;">&times;</button>
        </div>

        <!-- Step 1: Enter Email -->
        <div id="forgotStep1" style="padding:22px;">
            <p style="font-size:12px;color:#64748b;margin-bottom:16px;line-height:1.6;">Enter your registered email address to continue.</p>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:10px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.6px;margin-bottom:5px;">Email Address</label>
                <input type="email" id="fp_email" placeholder="yourname@email.com" style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;outline:none;box-sizing:border-box;font-family:inherit;">
            </div>
            <div id="fp_error1" style="color:#dc2626;font-size:11px;margin-bottom:10px;display:none;"></div>
            <button onclick="forgotStep1()" id="fp_btn1" style="width:100%;padding:11px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;">Continue</button>
        </div>

        <!-- Step 1b: Choose Method -->
        <div id="forgotStep1b" style="padding:22px;display:none;">
            <p style="font-size:12px;color:#64748b;margin-bottom:16px;line-height:1.6;">How would you like to verify your identity?</p>
            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:14px;">
                <button onclick="chooseForgotMethod('question')" style="display:flex;align-items:center;gap:12px;padding:14px 16px;border:1.5px solid #e2e8f0;border-radius:10px;background:white;cursor:pointer;text-align:left;font-family:inherit;transition:border-color .2s;" onmouseover="this.style.borderColor='#1e4575'" onmouseout="this.style.borderColor='#e2e8f0'">
                    <div style="width:36px;height:36px;background:#eff6ff;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg fill="none" stroke="#1e4575" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#0f172a;">Security Question</div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:1px;">Answer your security question</div>
                    </div>
                </button>
                <button onclick="chooseForgotMethod('email')" style="display:flex;align-items:center;gap:12px;padding:14px 16px;border:1.5px solid #e2e8f0;border-radius:10px;background:white;cursor:pointer;text-align:left;font-family:inherit;transition:border-color .2s;" onmouseover="this.style.borderColor='#1e4575'" onmouseout="this.style.borderColor='#e2e8f0'">
                    <div style="width:36px;height:36px;background:#eff6ff;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg fill="none" stroke="#1e4575" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#0f172a;">Send Reset via Email</div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:1px;">Get a reset link sent to your email</div>
                    </div>
                </button>
            </div>
            <div id="fp_error1b" style="color:#dc2626;font-size:11px;margin-bottom:10px;display:none;"></div>
        </div>

        <!-- Step 1c: Email sent confirmation -->
        <div id="forgotStep1c" style="padding:22px;display:none;text-align:center;">
            <div style="width:56px;height:56px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg fill="none" stroke="#16a34a" viewBox="0 0 24 24" style="width:28px;height:28px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:8px;">Check your email</div>
            <div style="font-size:12px;color:#64748b;line-height:1.6;">A password reset link has been sent to your email address. Please check your inbox.</div>
            <button onclick="closeForgotModal()" style="margin-top:16px;padding:10px 24px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;">Close</button>
        </div>

        <!-- Step 2: Answer Security Question -->
        <div id="forgotStep2" style="padding:22px;display:none;">
            <div style="background:#f0f4ff;border-radius:9px;padding:12px 14px;margin-bottom:16px;border-left:3px solid #1e4575;">
                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Security Question</div>
                <div style="font-size:13px;font-weight:600;color:#1e293b;" id="fp_question_text">—</div>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:10px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.6px;margin-bottom:5px;">Your Answer</label>
                <input type="text" id="fp_answer" placeholder="Enter your answer" style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;outline:none;box-sizing:border-box;font-family:inherit;">
            </div>
            <div id="fp_error2" style="color:#dc2626;font-size:11px;margin-bottom:10px;display:none;"></div>
            <button onclick="forgotStep2()" id="fp_btn2" style="width:100%;padding:11px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border:none;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;">Verify Answer</button>
        </div>

        <!-- Step 3: New Password -->
        <div id="forgotStep3" style="padding:22px;display:none;">
            <p style="font-size:12px;color:#64748b;margin-bottom:16px;">Identity verified. Set your new password.</p>
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:10px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.6px;margin-bottom:5px;">New Password</label>
                <input type="password" id="fp_newpwd" placeholder="Min. 8 characters" style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;outline:none;box-sizing:border-box;font-family:inherit;">
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:10px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.6px;margin-bottom:5px;">Confirm Password</label>
                <input type="password" id="fp_confirmpwd" placeholder="Repeat new password" style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;outline:none;box-sizing:border-box;font-family:inherit;">
            </div>
            <div id="fp_error3" style="color:#dc2626;font-size:11px;margin-bottom:10px;display:none;"></div>
            <button onclick="forgotStep3()" id="fp_btn3" style="width:100%;padding:11px;background:linear-gradient(135deg,#16a34a,#22c55e);color:white;border:none;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;">Reset Password</button>
        </div>
    </div>
</div>

<script>
var _fpToken = '', _fpEmail = '';

function showForgotModal() {
    document.getElementById('forgotModal').style.display = 'flex';
    document.getElementById('forgotStep1').style.display = 'block';
    document.getElementById('forgotStep1b').style.display = 'none';
    document.getElementById('forgotStep1c').style.display = 'none';
    document.getElementById('forgotStep2').style.display = 'none';
    document.getElementById('forgotStep3').style.display = 'none';
    document.getElementById('fp_email').value = '';
    document.getElementById('fp_error1').style.display = 'none';
    document.getElementById('forgotModalTitle').textContent = 'Reset Password';
}

function chooseForgotMethod(method) {
    if (method === 'question') {
        // Proceed to security question step
        fetch('/forgot-password/question', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({email: _fpEmail})
        }).then(r=>r.json()).then(d=>{
            if (d.question) {
                document.getElementById('fp_question_text').textContent = d.question;
                document.getElementById('fp_answer').value = '';
                document.getElementById('fp_error2').style.display = 'none';
                document.getElementById('forgotStep1b').style.display = 'none';
                document.getElementById('forgotStep2').style.display = 'block';
                document.getElementById('forgotModalTitle').textContent = 'Verify Identity';
            } else {
                document.getElementById('fp_error1b').textContent = d.message || 'No security question set.';
                document.getElementById('fp_error1b').style.display = 'block';
            }
        }).catch(()=>{
            document.getElementById('fp_error1b').textContent = 'Something went wrong.';
            document.getElementById('fp_error1b').style.display = 'block';
        });
    } else {
        // Email method — show confirmation
        document.getElementById('forgotStep1b').style.display = 'none';
        document.getElementById('forgotStep1c').style.display = 'block';
        document.getElementById('forgotModalTitle').textContent = 'Sending...';
        // Actually send reset email
        fetch('/forgot-password/send-email', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||''},
            body: JSON.stringify({email: _fpEmail})
        }).then(r=>r.json()).then(d=>{
            document.getElementById('forgotModalTitle').textContent = 'Email Sent';
        }).catch(()=>{
            document.getElementById('forgotModalTitle').textContent = 'Email Sent';
        });
    }
}
function closeForgotModal() {
    document.getElementById('forgotModal').style.display = 'none';
}
function forgotStep1() {
    var email = document.getElementById('fp_email').value.trim();
    if (!email) { showFpError(1,'Please enter your email address.'); return; }
    var btn = document.getElementById('fp_btn1');
    btn.disabled = true; btn.textContent = 'Checking...';
    // Just verify email exists, then show method selection
    fetch('/forgot-password/question', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||''},
        body:JSON.stringify({email, check_only: true})
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false; btn.textContent='Continue';
        if(d.success || d.question){
            _fpEmail = email;
            document.getElementById('fp_error1b').style.display = 'none';
            document.getElementById('forgotStep1').style.display = 'none';
            document.getElementById('forgotStep1b').style.display = 'block';
            document.getElementById('forgotModalTitle').textContent = 'Choose Method';
        } else { showFpError(1, d.message||'Email not found.'); }
    }).catch(()=>{ btn.disabled=false; btn.textContent='Continue'; showFpError(1,'Something went wrong.'); });
}
function forgotStep2() {
    var answer = document.getElementById('fp_answer').value.trim();
    if (!answer) { showFpError(2,'Please enter your answer.'); return; }
    var btn = document.getElementById('fp_btn2');
    btn.disabled=true; btn.textContent='Verifying...';
    fetch('/forgot-password/verify', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||''},
        body:JSON.stringify({email:_fpEmail, answer})
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false; btn.textContent='Verify Answer';
        if(d.success){
            _fpToken = d.token;
            document.getElementById('fp_newpwd').value='';
            document.getElementById('fp_confirmpwd').value='';
            document.getElementById('fp_error3').style.display='none';
            document.getElementById('forgotStep2').style.display='none';
            document.getElementById('forgotStep3').style.display='block';
            document.getElementById('forgotModalTitle').textContent='New Password';
        } else { showFpError(2, d.message||'Incorrect answer.'); }
    }).catch(()=>{ btn.disabled=false; btn.textContent='Verify Answer'; showFpError(2,'Something went wrong.'); });
}
function forgotStep3() {
    var pwd = document.getElementById('fp_newpwd').value;
    var cpwd = document.getElementById('fp_confirmpwd').value;
    if(pwd.length < 8){ showFpError(3,'Password must be at least 8 characters.'); return; }
    if(pwd !== cpwd){ showFpError(3,'Passwords do not match.'); return; }
    var btn = document.getElementById('fp_btn3');
    btn.disabled=true; btn.textContent='Resetting...';
    fetch('/forgot-password/reset', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||''},
        body:JSON.stringify({email:_fpEmail, token:_fpToken, password:pwd, password_confirmation:cpwd})
    }).then(r=>r.json()).then(d=>{
        btn.disabled=false; btn.textContent='Reset Password';
        if(d.success){
            closeForgotModal();
            showLoginToast('Password reset successfully! You can now sign in.','success');
        } else { showFpError(3, d.message||'Failed to reset.'); }
    }).catch(()=>{ btn.disabled=false; btn.textContent='Reset Password'; showFpError(3,'Something went wrong.'); });
}
function showFpError(step, msg) {
    var el = document.getElementById('fp_error'+step);
    el.textContent = msg; el.style.display = 'block';
}
</script>
<div id="loginToast" style="display:none;position:fixed;top:24px;right:24px;z-index:99999;min-width:320px;max-width:420px;background:white;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.18);overflow:hidden;animation:loginToastIn .35s cubic-bezier(.34,1.56,.64,1) both;">
    <div id="loginToastBar" style="height:4px;background:linear-gradient(90deg,#1e4575,#2563eb);"></div>
    <div style="padding:14px 18px;display:flex;align-items:flex-start;gap:12px;">
        <div id="loginToastIcon" style="width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#eff6ff;">
            <svg fill="none" stroke="#1e4575" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div style="flex:1;min-width:0;">
            <div id="loginToastTitle" style="font-size:13px;font-weight:700;color:#111827;margin-bottom:2px;">Notice</div>
            <div id="loginToastMsg" style="font-size:12px;color:#6b7280;line-height:1.5;"></div>
        </div>
        <button onclick="hideLoginToast()" style="background:none;border:none;color:#9ca3af;cursor:pointer;font-size:18px;line-height:1;padding:0;flex-shrink:0;">&times;</button>
    </div>
</div>

<style>
@keyframes loginToastIn {
    from { opacity:0; transform:translateX(60px) scale(.95); }
    to   { opacity:1; transform:translateX(0) scale(1); }
}
@keyframes loginToastOut {
    from { opacity:1; transform:translateX(0); }
    to   { opacity:0; transform:translateX(60px); }
}
</style>

<script>
var _loginToastTimer;
function showLoginToast(msg, type) {
    type = type || 'info';
    var configs = {
        info:    { bg:'#eff6ff', stroke:'#1e4575', bar:'linear-gradient(90deg,#1e4575,#2563eb)', title:'Notice',  icon:'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
        warning: { bg:'#fffbeb', stroke:'#d97706', bar:'linear-gradient(90deg,#d97706,#f59e0b)', title:'Attention', icon:'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' },
        error:   { bg:'#fef2f2', stroke:'#dc2626', bar:'linear-gradient(90deg,#dc2626,#ef4444)', title:'Error',   icon:'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' },
        success: { bg:'#f0fdf4', stroke:'#16a34a', bar:'linear-gradient(90deg,#16a34a,#22c55e)', title:'Success', icon:'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    };
    var c = configs[type] || configs.info;
    document.getElementById('loginToastBar').style.background = c.bar;
    document.getElementById('loginToastIcon').style.background = c.bg;
    document.getElementById('loginToastIcon').innerHTML = '<svg fill="none" stroke="'+c.stroke+'" viewBox="0 0 24 24" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="'+c.icon+'"/></svg>';
    document.getElementById('loginToastTitle').textContent = c.title;
    document.getElementById('loginToastMsg').textContent = msg;
    var t = document.getElementById('loginToast');
    t.style.display = 'block';
    t.style.animation = 'loginToastIn .35s cubic-bezier(.34,1.56,.64,1) both';
    clearTimeout(_loginToastTimer);
    _loginToastTimer = setTimeout(hideLoginToast, 5000);
}
function hideLoginToast() {
    var t = document.getElementById('loginToast');
    t.style.animation = 'loginToastOut .3s ease forwards';
    setTimeout(function(){ t.style.display='none'; }, 300);
}
function toggleLoginPwd() {
    var inp = document.getElementById('login_password');
    var show = document.getElementById('eye_show');
    var hide = document.getElementById('eye_hide');
    if (inp.type === 'password') {
        inp.type = 'text';
        show.style.display = 'none';
        hide.style.display = 'block';
    } else {
        inp.type = 'password';
        show.style.display = 'block';
        hide.style.display = 'none';
    }
}
</script>
</body>
</html>
