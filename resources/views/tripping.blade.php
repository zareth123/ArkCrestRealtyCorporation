<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="{{ asset('images/ArkCrest_Logo.png') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Site Visit Form -- ArkCrest Realty</title>
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html,body{height:100%;overflow:hidden;font-family:"Segoe UI",system-ui,sans-serif}
body{display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0a1628 0%,#1a3a6b 50%,#0f2a4a 100%)}
.card{position:relative;width:860px;max-width:96vw;height:600px;border-radius:24px;overflow:hidden;box-shadow:0 40px 100px rgba(0,0,0,.55);background:white;display:flex}
.overlay{width:38%;background:linear-gradient(150deg,#1e4575 0%,#0f2a4a 100%);display:flex;flex-direction:column;align-items:center;justify-content:space-between;padding:40px 30px;text-align:center;position:relative;overflow:hidden;flex-shrink:0}
.overlay::before{content:"";position:absolute;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(163,121,41,.18),transparent 65%);top:-70px;right:-70px;pointer-events:none;animation:pulse 4s ease-in-out infinite}
.brand{display:flex;flex-direction:column;align-items:center;gap:8px;position:relative;z-index:1}
.brand-logo{width:72px;height:72px}.brand-logo img{width:100%;height:100%;object-fit:contain}
.brand-name{font-size:22px;font-weight:800;letter-spacing:1px;text-transform:uppercase;line-height:1.3;text-align:center;background:linear-gradient(90deg,#fff,#d4a855,#fff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;background-size:200% auto;animation:shimmer 3s linear infinite}
.ov-body{position:relative;z-index:1}
.ov-tag{display:inline-block;background:rgba(212,168,85,.18);border:1px solid rgba(212,168,85,.35);color:#d4a855;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:10px}
.ov-title{font-size:18px;font-weight:700;color:white;margin-bottom:8px}
.ov-body p{font-size:12px;color:rgba(255,255,255,.5);line-height:1.7}
.ov-footer{position:relative;z-index:1;font-size:10px;color:rgba(255,255,255,.25);letter-spacing:.5px}
.form-panel{flex:1;display:flex;flex-direction:column;padding:24px 36px;background:linear-gradient(160deg,#f8fafc 0%,#fff 60%,#f0f4ff 100%);overflow-y:auto}
.form-panel::-webkit-scrollbar{width:3px}.form-panel::-webkit-scrollbar-thumb{background:#e2e8f0;border-radius:4px}
.form-title{font-size:20px;font-weight:800;color:#0f172a;letter-spacing:-.5px}
.form-sub{font-size:11px;color:#94a3b8;margin-bottom:14px}
.alert-success{background:#f0fdf4;border-left:3px solid #22c55e;color:#16a34a;padding:8px 12px;border-radius:8px;font-size:12px;margin-bottom:12px}
.alert-error{background:#fef2f2;border-left:3px solid #ef4444;color:#dc2626;padding:8px 12px;border-radius:8px;font-size:12px;margin-bottom:12px}
.dup-warn{display:none;background:#fff7ed;border-left:3px solid #f97316;color:#9a3412;padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:8px;line-height:1.6}
.section-label{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;margin:10px 0 6px;display:flex;align-items:center;gap:8px;background:linear-gradient(90deg,#1e4575,#2563eb);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.section-label::after{content:"";flex:1;height:1px;background:linear-gradient(90deg,#dbeafe,transparent)}
.field{margin-bottom:8px;position:relative}
.field label{display:block;font-size:10px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.6px;margin-bottom:3px}
.field input,.field select{width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:12px;color:#111827;background:white;transition:all .2s;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.field input:focus,.field select:focus{outline:none;border-color:#1e4575;box-shadow:0 0 0 3px rgba(30,69,117,.08)}
.field input::placeholder{color:#b0bec5}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.ac-wrap{position:relative}
.ac-list{position:absolute;top:100%;left:0;right:0;background:white;border:1.5px solid #e2e8f0;border-top:none;border-radius:0 0 9px 9px;box-shadow:0 8px 24px rgba(0,0,0,.1);z-index:100;max-height:160px;overflow-y:auto;display:none}
.ac-list.open{display:block}
.ac-item{padding:8px 12px;font-size:12px;color:#374151;cursor:pointer;transition:background .15s}
.ac-item:hover{background:#f0f4ff;color:#1e4575;font-weight:600}
.ac-empty{padding:8px 12px;font-size:12px;color:#94a3b8;font-style:italic}
.reveal-field{display:none}.reveal-field.show{display:block;animation:fadeSlide .2s ease}
@keyframes fadeSlide{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}
.phone-wrap{display:flex;border:1.5px solid #e2e8f0;border-radius:9px;overflow:visible;background:white;box-shadow:0 1px 3px rgba(0,0,0,.04);transition:border-color .2s,box-shadow .2s;position:relative}
.phone-wrap:focus-within{border-color:#1e4575;box-shadow:0 0 0 3px rgba(30,69,117,.08)}
.phone-prefix{padding:8px 10px;background:#f8fafc;border-right:1.5px solid #e2e8f0;white-space:nowrap;display:flex;align-items:center;gap:6px;cursor:pointer;border-radius:7px 0 0 7px;user-select:none;transition:background .15s;min-width:82px}
.phone-prefix:hover{background:#f1f5f9}
.phone-prefix img.pflag{width:22px;height:15px;border-radius:2px;object-fit:cover;box-shadow:0 1px 3px rgba(0,0,0,.15)}
.phone-prefix .pcode{font-size:12px;font-weight:700;color:#1e4575}
.phone-prefix .parrow{color:#94a3b8;font-size:10px}
.phone-wrap .pnum{flex:1;padding:9px 12px;border:none;outline:none;font-size:12px;color:#111827;background:transparent;border-radius:0 7px 7px 0}
.phone-wrap .pnum::placeholder{color:#b0bec5}
.country-drop{position:absolute;top:calc(100% + 4px);left:0;width:270px;background:white;border:1.5px solid #e2e8f0;border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,.15);z-index:500;display:none;overflow:hidden}
.country-drop.open{display:block}
.csearch{width:100%;padding:9px 12px;border:none;border-bottom:1.5px solid #f1f5f9;font-size:12px;outline:none;color:#111827}
.csearch::placeholder{color:#b0bec5}
.clist{max-height:180px;overflow-y:auto}
.clist::-webkit-scrollbar{width:3px}.clist::-webkit-scrollbar-thumb{background:#e2e8f0;border-radius:4px}
.copt{padding:8px 12px;font-size:12px;color:#374151;cursor:pointer;display:flex;align-items:center;gap:10px;transition:background .15s}
.copt:hover,.copt.sel{background:#f0f4ff;color:#1e4575;font-weight:600}
.copt img{width:22px;height:15px;border-radius:2px;object-fit:cover;box-shadow:0 1px 3px rgba(0,0,0,.1);flex-shrink:0}
.copt .cn{flex:1}.copt .cc{color:#94a3b8;font-size:11px}
.btn-primary{width:100%;padding:11px;background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);background-size:200% auto;color:white;border:none;border-radius:12px;font-size:13px;font-weight:700;letter-spacing:1px;text-transform:uppercase;cursor:pointer;box-shadow:0 6px 20px rgba(30,69,117,.3);transition:all .3s;margin-top:6px;animation:btnShimmer 3s linear infinite}
.btn-primary:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 24px rgba(30,69,117,.4)}
.btn-primary:disabled{opacity:.5;cursor:not-allowed;animation:none}
.logout-row{text-align:center;margin-top:12px}
.btn-signout{display:inline-flex;align-items:center;gap:6px;padding:8px 20px;background:white;color:#dc2626;border:1.5px solid #fecaca;border-radius:8px;font-size:11px;font-weight:600;cursor:pointer;transition:all .2s;letter-spacing:.3px}
.btn-signout:hover{background:#fef2f2;border-color:#dc2626}
.logout-row a{color:#1e4575;font-weight:700;text-decoration:none;border-bottom:1.5px solid #1e4575}
@keyframes shimmer{0%{background-position:200% center}100%{background-position:-200% center}}
@keyframes btnShimmer{0%{background-position:200% center}100%{background-position:-200% center}}
@keyframes pulse{0%,100%{transform:scale(1);opacity:.18}50%{transform:scale(1.08);opacity:.25}}
</style>
</head>
<body>
<div class="card">
    <div class="overlay">
        <div class="brand">
            <div class="brand-logo"><img src="{{ asset('images/ArkCrest_Logo.png') }}" alt="ArkCrest"></div>
            <div class="brand-name">ARCKREST REALTY CORPORATION</div>
        </div>
        <div id="greetingText" style="font-size:16px;font-weight:700;color:white;text-align:center;margin:10px 0 0;padding:0 20px;">
            @php $firstName = auth()->check() ? explode(' ', auth()->user()->name)[0] : ''; @endphp
            Happy ArkCrest Morning{{ $firstName ? ', '.$firstName : '' }}!
        </div>
        <div class="ov-body">
            <div class="ov-tag">Site Visit</div>
            <div class="ov-title">Schedule a Property Visit</div>
            <p>Book a site visit with ArkCrest Realty. Our team will confirm your appointment shortly.</p>
        </div>
        <div class="ov-footer">&copy; {{ date('Y') }} Arckrest Realty Corporation</div>
    </div>
    <div class="form-panel">
        <div class="form-title">Site Visit Form</div>
        <div class="form-sub">Fill in the details below to schedule your visit.</div>
        @if(session('trip_success'))
            <div class="alert-success">&#10003; Site visit scheduled successfully!</div>
        @endif
        @if($errors->any())
            <div class="alert-error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif
        <form method="POST" action="{{ route('tripping.store') }}" id="tripForm">
            @csrf
            <div class="section-label">Agent</div>
            <div class="field">
                <label>Agent ID</label>
                <input type="text" name="agent_name" value="{{ old('agent_name', auth()->check() ? auth()->user()->employee_id : '') }}" required placeholder="Enter agent employee ID..." {{ auth()->check() ? 'readonly style=background:#f8fafc;color:#64748b' : '' }}>
            </div>
            <div class="field">
                <label>Team</label>
                @if(auth()->check() && auth()->user()->team_name)
                    {{-- Already has a team — show readonly --}}
                    <input type="text" value="{{ auth()->user()->team_name }}" readonly style="width:100%;padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;color:#64748b;background:#f8fafc;outline:none;">
                    <input type="hidden" name="team_name" value="{{ auth()->user()->team_name }}">
                @else
                    {{-- No team yet — show dropdown, save on submit --}}
                    <select name="team_name" id="teamSelect" style="width:100%;padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;color:#1e293b;background:#fff;outline:none;">
                        <option value="">— Select Team (optional) —</option>
                        @foreach($teams as $team)
                            <option value="{{ $team }}" {{ old('team_name') == $team ? 'selected' : '' }}>{{ $team }}</option>
                        @endforeach
                    </select>
                    @auth
                    <script>
                    document.getElementById('tripForm').addEventListener('submit', function() {
                        var team = document.getElementById('teamSelect').value;
                        if (team) {
                            fetch('{{ route("tripping.save-team") }}', {
                                method: 'POST',
                                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                                body: JSON.stringify({team_name: team})
                            });
                        }
                    });
                    </script>
                    @endauth
                @endif
            </div>
            <div class="section-label">Client</div>
            <div class="field">
                <label>Client Name</label>
                <input type="text" id="clientNameInput" name="client_name" autocomplete="off" value="{{ old('client_name') }}" required placeholder="Enter client full name...">
            </div>
            <div class="reveal-field" id="clientEmailField">
                <div class="field">
                    <label>Client Email <span style="color:#94a3b8;font-weight:400;text-transform:none;font-size:9px">(optional)</span></label>
                    <input type="email" name="client_email" value="{{ old('client_email') }}" placeholder="email@example.com" autocomplete="off">
                </div>
            </div>
            <div class="reveal-field" id="clientPhoneField">
                <div class="field">
                    <label>Client Phone <span style="color:#94a3b8;font-weight:400;text-transform:none;font-size:9px">(optional)</span></label>
                    <div class="phone-wrap">
                        <div class="phone-prefix" id="phonePrefix" onclick="toggleCountryDrop()">
                            <img class="pflag" id="phoneFlag" src="https://flagcdn.com/w20/ph.png" alt="PH">
                            <span class="pcode" id="phoneCode">+63</span>
                            <span class="parrow">&#9660;</span>
                        </div>
                        <div class="country-drop" id="countryDrop">
                            <input class="csearch" id="csearchInput" placeholder="Search country..." oninput="filterCountries(this.value)">
                            <div class="clist" id="clistEl"></div>
                        </div>
                        <input type="hidden" name="client_phone_code" id="clientPhoneCode" value="+63">
                        <input type="text" class="pnum" name="client_phone" id="clientPhoneInput" autocomplete="off"
                            value="{{ old('client_phone') }}" placeholder="9XX XXX XXXX" maxlength="15" inputmode="numeric">
                    </div>
                </div>
            </div>
            <div class="reveal-field" id="clientAddressField">
                <div class="field">
                    <label>Client Address <span style="color:#94a3b8;font-weight:400;text-transform:none;font-size:9px">(optional)</span></label>
                    <input type="text" name="client_address" value="{{ old('client_address') }}" placeholder="Home or office address">
                </div>
            </div>
            <div class="section-label">Property</div>
            <div class="field ac-wrap">
                <label>Property Name</label>
                @if($properties->isNotEmpty())
                <select name="property_name" id="propertyNameInput" required style="width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:12px;color:#111827;background:white;transition:all .2s;box-shadow:0 1px 3px rgba(0,0,0,.04);" onchange="onPropertySelect(this)">
                    <option value="">— Select Property —</option>
                    @foreach($properties as $prop)
                    <option value="{{ $prop->name }}" data-developer="{{ $prop->developer }}" {{ old('property_name') == $prop->name ? 'selected' : '' }}>{{ $prop->name }}{{ $prop->developer ? ' ('.$prop->developer.')' : '' }}</option>
                    @endforeach
                </select>
                @else
                <input type="text" id="propertyNameInput" name="property_name" value="{{ old('property_name') }}" required placeholder="Type to search or enter new property..." autocomplete="off">
                <div class="ac-list" id="propertyAcList"></div>
                @endif
            </div>
            <div class="reveal-field" id="companyField">
                <div class="field">
                    <label>Company / Developer <span style="color:#94a3b8;font-weight:400;text-transform:none;font-size:9px">(optional)</span></label>
                    <input type="text" name="company_name" id="companyName" value="{{ old('company_name') }}" placeholder="Developer or company name">
                </div>
            </div>
            <div class="section-label">Schedule</div>
            <div class="row2">
                <div class="field">
                    <label>Visit Date</label>
                    <input type="date" name="tripping_date" value="{{ old('tripping_date') }}" required min="{{ date('Y-m-d') }}">
                </div>
                <div class="field">
                    <label>Visit Time</label>
                    <input type="time" name="tripping_time" value="{{ old('tripping_time') }}" required>
                </div>
            </div>
            <div class="field">
                <label>Mode of Visit</label>
                <input type="text" name="tripping_type" list="visitTypeOptions" required value="{{ old('tripping_type') }}" placeholder="Select or type mode of visit...">
                <datalist id="visitTypeOptions">
                    <option value="Actual (On-site)">
                    <option value="Online (Virtual)">
                </datalist>
            </div>
            <div class="dup-warn" id="dupWarning"></div>
            <button type="submit" class="btn-primary" id="submitBtn">Submit Site Visit</button>
        </form>
                @auth
        <div class="logout-row">
            @php
                $u = auth()->user();
                $pos = strtolower($u->position ?? '');
                $salesPositions = ['sales agent', 'sales manager', 'sales person', 'salesperson', 'sales team leader', 'sales personnel'];
                $isSalesPerson = in_array($pos, $salesPositions);
                $hasSystemAccess = !$isSalesPerson && ($u->isAdmin() || !in_array('forms', $u->hidden_pages ?? []));
            @endphp
            @if($hasSystemAccess)
            {{-- Staff/Admin with system access: Back only --}}
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="btn-signout" style="color:#1e4575;border-color:#bfdbfe;">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back
            </a>
            @else
            {{-- Sales persons with limited access: Sign Out only --}}
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="btn-signout">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign Out
                </button>
            </form>
            @endif
        </div>
        @endauth
        @guest
        <div class="logout-row">
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="btn-signout">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
        @endguest
    </div>
</div>
<script>
var COUNTRIES=[
    {iso:'ph',name:'Philippines',code:'+63',len:10,ph:'9XX XXX XXXX'},
    {iso:'us',name:'United States',code:'+1',len:10,ph:'XXX XXX XXXX'},
    {iso:'gb',name:'United Kingdom',code:'+44',len:10,ph:'XXXX XXX XXXX'},
    {iso:'au',name:'Australia',code:'+61',len:9,ph:'XXX XXX XXX'},
    {iso:'ca',name:'Canada',code:'+1',len:10,ph:'XXX XXX XXXX'},
    {iso:'sg',name:'Singapore',code:'+65',len:8,ph:'XXXX XXXX'},
    {iso:'jp',name:'Japan',code:'+81',len:10,ph:'XX XXXX XXXX'},
    {iso:'kr',name:'South Korea',code:'+82',len:10,ph:'XX XXXX XXXX'},
    {iso:'cn',name:'China',code:'+86',len:11,ph:'XXX XXXX XXXX'},
    {iso:'hk',name:'Hong Kong',code:'+852',len:8,ph:'XXXX XXXX'},
    {iso:'ae',name:'UAE',code:'+971',len:9,ph:'XX XXX XXXX'},
    {iso:'sa',name:'Saudi Arabia',code:'+966',len:9,ph:'XX XXX XXXX'},
    {iso:'in',name:'India',code:'+91',len:10,ph:'XXXXX XXXXX'},
    {iso:'my',name:'Malaysia',code:'+60',len:9,ph:'XX XXX XXXX'},
    {iso:'id',name:'Indonesia',code:'+62',len:10,ph:'XXX XXXX XXXX'},
    {iso:'th',name:'Thailand',code:'+66',len:9,ph:'XX XXX XXXX'},
    {iso:'nz',name:'New Zealand',code:'+64',len:9,ph:'XX XXX XXXX'},
    {iso:'de',name:'Germany',code:'+49',len:10,ph:'XXX XXXXXXX'},
    {iso:'fr',name:'France',code:'+33',len:9,ph:'X XX XX XX XX'},
    {iso:'it',name:'Italy',code:'+39',len:10,ph:'XXX XXX XXXX'}
];
var selCountry=COUNTRIES[0];
function flagUrl(iso){return 'https://flagcdn.com/w20/'+iso.toLowerCase()+'.png';}
function updatePhoneInput(c){
    var inp=document.getElementById('clientPhoneInput');
    inp.maxLength=c.len;
    inp.placeholder=c.ph;
    inp.value='';
    phoneError('');
}
function renderCountries(list){
    var el=document.getElementById('clistEl');el.innerHTML='';
    list.forEach(function(c){
        var d=document.createElement('div');
        d.className='copt'+(c.iso===selCountry.iso?' sel':'');
        d.innerHTML='<img src="'+flagUrl(c.iso)+'" alt="'+c.iso+'"><span class="cn">'+c.name+'</span><span class="cc">'+c.code+'</span>';
        d.addEventListener('click',function(){
            selCountry=c;
            document.getElementById('phoneFlag').src=flagUrl(c.iso);
            document.getElementById('phoneFlag').alt=c.iso.toUpperCase();
            document.getElementById('phoneCode').textContent=c.code;
            document.getElementById('clientPhoneCode').value=c.code;
            updatePhoneInput(c);
            closeCountryDrop();
        });
        el.appendChild(d);
    });
}
function filterCountries(q){renderCountries(COUNTRIES.filter(function(c){return c.name.toLowerCase().indexOf(q.toLowerCase())>-1||c.code.indexOf(q)>-1;}));}
function toggleCountryDrop(){var d=document.getElementById('countryDrop');if(d.classList.contains('open')){closeCountryDrop();return;}d.classList.add('open');document.getElementById('csearchInput').value='';renderCountries(COUNTRIES);setTimeout(function(){document.getElementById('csearchInput').focus();},50);}
function closeCountryDrop(){document.getElementById('countryDrop').classList.remove('open');}
document.addEventListener('click',function(e){var w=document.querySelector('.phone-wrap');if(w&&!w.contains(e.target))closeCountryDrop();});
function phoneError(msg){var el=document.getElementById('phoneErrMsg');if(!el){el=document.createElement('div');el.id='phoneErrMsg';el.style.cssText='font-size:11px;color:#dc2626;margin-top:3px;';document.getElementById('clientPhoneInput').closest('.field').appendChild(el);}el.textContent=msg;}
document.getElementById('clientPhoneInput').addEventListener('input',function(){
    var v=this.value.replace(/\D/g,'');
    if(v.charAt(0)==='0')v=v.slice(1);
    this.value=v.slice(0,selCountry.len);
    if(this.value.length>0&&this.value.length<selCountry.len){phoneError('Needs '+selCountry.len+' digits for '+selCountry.name);}else{phoneError('');}
});
document.getElementById('clientPhoneInput').addEventListener('blur',function(){
    if(this.value.length>0&&this.value.length<selCountry.len){phoneError('Must be exactly '+selCountry.len+' digits for '+selCountry.name);}else{phoneError('');}
});
function setupAc(inputId,listId,fetchUrl,onSelect){var input=document.getElementById(inputId),list=document.getElementById(listId),timer;input.addEventListener('input',function(){clearTimeout(timer);var q=input.value.trim();if(!q){list.classList.remove('open');return;}timer=setTimeout(function(){fetch(fetchUrl+'?q='+encodeURIComponent(q)).then(function(r){return r.json();}).then(function(data){list.innerHTML='';if(!data.length){list.innerHTML='<div class="ac-empty">No existing records</div>';}else{data.forEach(function(item){var d=document.createElement('div');d.className='ac-item';d.textContent=item;d.addEventListener('mousedown',function(e){e.preventDefault();onSelect(item,input,list);});list.appendChild(d);});}list.classList.add('open');});},250);});document.addEventListener('click',function(e){if(!input.contains(e.target)&&!list.contains(e.target))list.classList.remove('open');});}
setupAc('propertyNameInput','propertyAcList','/api/tripping/properties',function(name,input,list){input.value=name;list.classList.remove('open');document.getElementById('companyField').classList.add('show');fetch('/api/tripping/property-details?name='+encodeURIComponent(name)).then(function(r){return r.json();}).then(function(d){document.getElementById('companyName').value=d.company||'';});checkDuplicate();});
function onPropertySelect(sel) {
    var opt = sel.options[sel.selectedIndex];
    var dev = opt ? opt.getAttribute('data-developer') : '';
    if (sel.value) {
        document.getElementById('companyField').classList.add('show');
        if (dev) document.getElementById('companyName').value = dev;
        checkDuplicate();
    }
}
document.getElementById('propertyNameInput').addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();document.getElementById('companyField').classList.add('show');checkDuplicate();}});
document.getElementById('propertyNameInput').addEventListener('blur',function(){setTimeout(function(){if(document.getElementById('propertyNameInput').value.trim()){document.getElementById('companyField').classList.add('show');checkDuplicate();}},300);});
document.getElementById('clientNameInput').addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();revealClientFields();checkDuplicate();}});
document.getElementById('clientNameInput').addEventListener('blur',function(){setTimeout(function(){if(document.getElementById('clientNameInput').value.trim()){revealClientFields();checkDuplicate();}},300);});
var greetTimer;
var _defaultGreeting = 'Happy ArkCrest Morning{{ $firstName ? ", ".$firstName : "" }}!';function updateGreeting(){
    var empId=document.querySelector('[name="agent_name"]').value.trim();
    var g=document.getElementById('greetingText');
    if(!g) return;
    if(!empId){g.textContent=_defaultGreeting;return;}
    clearTimeout(greetTimer);
    greetTimer=setTimeout(function(){
        fetch('/api/tripping/agent-details?employee_id='+encodeURIComponent(empId))
        .then(function(r){return r.json();})
        .then(function(d){
            if(d.found){
                var display=(d.salutation?d.salutation+' ':'')+d.name;
                g.textContent='Happy ArkCrest Morning, '+display+'!';
            } else {
                g.textContent=_defaultGreeting;
            }
        }).catch(function(){g.textContent=_defaultGreeting;});
    },400);
}
document.querySelector('[name="agent_name"]').addEventListener('input',updateGreeting);
document.addEventListener('DOMContentLoaded',updateGreeting);
function revealClientFields(){document.getElementById('clientEmailField').classList.add('show');document.getElementById('clientPhoneField').classList.add('show');document.getElementById('clientAddressField').classList.add('show');}
document.getElementById('tripForm').addEventListener('submit', function(e) {
    var errors = [];
    var clientName = document.getElementById('clientNameInput').value.trim();
    var propertyName = document.getElementById('propertyNameInput').value.trim();
    var phone = document.getElementById('clientPhoneInput').value.trim();
    var date = document.querySelector('[name="tripping_date"]').value;
    var time = document.querySelector('[name="tripping_time"]').value;
    var mode = document.querySelector('[name="tripping_type"]').value.trim();

    if (!clientName) errors.push('Client name is required.');
    if (!propertyName) errors.push('Property name is required.');
    if (!date) errors.push('Visit date is required.');
    if (!time) errors.push('Visit time is required.');
    if (!mode) errors.push('Mode of visit is required.');

    if (errors.length) {
        e.preventDefault();
        var warn = document.getElementById('dupWarning');
        warn.innerHTML = errors.map(function(m){ return '&#9888; ' + m; }).join('<br>');
        warn.style.display = 'block';
        warn.style.background = '#fef2f2';
        warn.style.borderColor = '#ef4444';
        warn.style.color = '#dc2626';
        warn.scrollIntoView({behavior:'smooth', block:'center'});
    }
});

var dupTimer;
function checkDuplicate(){clearTimeout(dupTimer);dupTimer=setTimeout(function(){var client=document.getElementById('clientNameInput').value.trim(),property=document.getElementById('propertyNameInput').value.trim(),warn=document.getElementById('dupWarning'),btn=document.getElementById('submitBtn');if(!client||!property){warn.style.display='none';btn.disabled=false;return;}fetch('/api/tripping/check-duplicate?client_name='+encodeURIComponent(client)+'&property_name='+encodeURIComponent(property)).then(function(r){return r.json();}).then(function(d){if(d.duplicate){warn.innerHTML='&#9888; '+client+' already has an active tripping for '+property+(d.date?' on '+d.date:'')+'. Status: '+d.status+'.';warn.style.display='block';btn.disabled=true;}else{warn.style.display='none';btn.disabled=false;}});},400);}
@if(old('client_name')) revealClientFields(); @endif
@if(old('property_name')) document.getElementById('companyField').classList.add('show'); @endif
</script>
</body>
</html>