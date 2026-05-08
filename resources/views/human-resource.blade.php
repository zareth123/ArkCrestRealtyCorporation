@extends('layouts.dashboard')
@section('title', 'Human Resource')
@section('content')

<div class="welcome-banner" style="background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:28px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25);">
    <div style="position:relative;z-index:2;">
        <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;">Human Resource</div>
        <h1 style="font-size:28px;font-weight:700;color:white;margin:0 0 8px;">Happy ArkCrest Morning, {{ auth()->user()->preferred_address ? auth()->user()->preferred_address.' '.auth()->user()->name : auth()->user()->name }}! 👥</h1>
        <p style="font-size:14px;color:rgba(255,255,255,.75);margin:0;display:flex;align-items:center;gap:8px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            HR Overview — {{ date('F Y') }}
        </p>
    </div>
    <div style="position:absolute;top:0;right:0;width:300px;height:100%;pointer-events:none;">
        <div style="position:absolute;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.05);top:-60px;right:-40px;"></div>
        <div style="position:absolute;width:150px;height:150px;border-radius:50%;background:rgba(255,255,255,.05);bottom:-30px;right:80px;"></div>
    </div>
</div>

{{-- Stats Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:32px;">
    <div style="background:white;border-radius:12px;padding:28px 24px;display:flex;align-items:center;gap:20px;box-shadow:0 2px 8px rgba(0,0,0,.08);border-left:5px solid #1e4575;">
        <div style="width:52px;height:52px;border-radius:12px;background:linear-gradient(135deg,#1e4575,#2563eb);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Total Employees</div>
            <div style="font-size:32px;font-weight:800;color:#0f172a;line-height:1;">{{ $totalEmployees }}</div>
            <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Active staff members</div>
        </div>
    </div>
    <div style="background:white;border-radius:12px;padding:28px 24px;display:flex;align-items:center;gap:20px;box-shadow:0 2px 8px rgba(0,0,0,.08);border-left:5px solid #A37929;">
        <div style="width:52px;height:52px;border-radius:12px;background:linear-gradient(135deg,#A37929,#d4a03a);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div>
            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Total Agents</div>
            <div style="font-size:32px;font-weight:800;color:#0f172a;line-height:1;">{{ $totalAgents }}</div>
            <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Users with sales-related positions</div>
        </div>
    </div>
</div>

{{-- HR Forms --}}
<div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    HR Forms
</div>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:28px;margin-bottom:32px;">

    @php
    $forms = [
        ['key'=>'dayoff',   'title'=>'Change Day-Off Form',    'color'=>'#1e4575', 'color2'=>'#2563eb',
         'fields'=>['Name','Position','Prev. Day-Off','Department','New Day-Off','Date (Week)'],
         'extra'=>'Reason box · Approved by · Acknowledged by'],
        ['key'=>'absences', 'title'=>'Absences Report Form',   'color'=>'#A37929', 'color2'=>'#d4a03a',
         'fields'=>['Name','Department','Date Today'],
         'extra'=>'Explanation box · Assessed by · Acknowledged by'],
        ['key'=>'voucher',  'title'=>'Allowance Voucher ARCS', 'color'=>'#0f2444', 'color2'=>'#1e4575',
         'fields'=>['Employee Name','Designation','Pay Period','Department'],
         'extra'=>'Earnings · Deductions · Net Pay · Signatures'],
    ];
    @endphp

    @foreach($forms as $form)
    <div onclick="openHrForm('{{ $form['key'] }}')"
        style="cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:10px;"
        onmouseover="this.querySelector('.doc-paper').style.transform='scale(1.04) translateY(-6px)';this.querySelector('.doc-paper').style.boxShadow='6px 6px 0 #b0b8c4,0 12px 40px rgba(0,0,0,.18)'"
        onmouseout="this.querySelector('.doc-paper').style.transform='scale(1) translateY(0)';this.querySelector('.doc-paper').style.boxShadow='4px 4px 0 #d1d5db,0 4px 16px rgba(0,0,0,.1)'">

        {{-- Paper thumbnail --}}
        <div class="doc-paper" style="width:100%;background:white;border:1px solid #e2e8f0;border-radius:3px;box-shadow:4px 4px 0 #d1d5db,0 4px 16px rgba(0,0,0,.1);transition:all .25s ease;padding:16px 14px;position:relative;overflow:hidden;min-height:200px;">
            {{-- Color accent bar --}}
            <div style="position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,{{ $form['color'] }},{{ $form['color2'] }});"></div>
            {{-- Logo + title --}}
            <div style="display:flex;align-items:center;gap:7px;margin-top:6px;margin-bottom:10px;">
                <img src="{{ asset('images/ArkCrest_Logo.png') }}" style="width:20px;height:20px;object-fit:contain;opacity:.6;flex-shrink:0;">
                <span style="font-size:8px;font-weight:800;color:{{ $form['color'] }};text-transform:uppercase;letter-spacing:.4px;line-height:1.2;">{{ $form['title'] }}</span>
            </div>
            <div style="height:1px;background:#f1f5f9;margin-bottom:8px;"></div>
            {{-- Field lines --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px 8px;margin-bottom:8px;">
                @foreach($form['fields'] as $f)
                <div style="font-size:6.5px;color:#94a3b8;">{{ $f }}:<span style="display:inline-block;width:40px;border-bottom:1px solid #e2e8f0;margin-left:2px;">&nbsp;</span></div>
                @endforeach
            </div>
            {{-- Content area --}}
            <div style="border:1px solid #f1f5f9;border-radius:2px;height:40px;margin-bottom:8px;background:#fafafa;"></div>
            {{-- Footer --}}
            <div style="font-size:6px;color:#cbd5e1;line-height:1.6;">{{ $form['extra'] }}</div>
        </div>

        <div style="font-size:13px;font-weight:600;color:#374151;text-align:center;">{{ $form['title'] }}</div>
        <div style="font-size:11px;color:#94a3b8;">Click to open &amp; print</div>
    </div>
    @endforeach
</div>

{{-- Form Modal --}}
<div id="hrFormModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:flex-start;justify-content:center;overflow-y:auto;padding:30px 20px;" onclick="if(event.target===this)closeHrForm()">
    <div style="background:white;border-radius:14px;width:680px;max-width:96vw;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;">
        <div id="hrFormHeader" style="padding:14px 20px;display:flex;align-items:center;justify-content:space-between;">
            <span id="hrFormTitle" style="font-size:14px;font-weight:700;color:white;"></span>
            <div style="display:flex;gap:8px;">
                <button onclick="printHrForm()" style="padding:6px 14px;background:rgba(255,255,255,.2);color:white;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">🖨 Print</button>
                <button onclick="closeHrForm()" style="padding:6px 12px;background:rgba(255,255,255,.15);color:white;border:none;border-radius:8px;font-size:16px;cursor:pointer;">&times;</button>
            </div>
        </div>
        <div id="hrFormContent" style="padding:32px 40px;font-family:'Times New Roman',serif;font-size:13px;color:#111;"></div>
    </div>
</div>

<script>
var _hrLogo = "{{ asset('images/ArkCrest_Logo.png') }}";
function openHrForm(type) {
    var modal = document.getElementById('hrFormModal');
    var content = document.getElementById('hrFormContent');
    var title = document.getElementById('hrFormTitle');
    var header = document.getElementById('hrFormHeader');
    var colors = {dayoff:'linear-gradient(135deg,#1e4575,#2563eb)', absences:'linear-gradient(135deg,#A37929,#d4a03a)', voucher:'linear-gradient(135deg,#0f2444,#1e4575)'};
    header.style.background = colors[type] || colors.dayoff;
    modal.style.display = 'flex';
    if (type==='dayoff')   { title.textContent='Change Day-Off Form';    content.innerHTML=hrFormDayOff(); }
    else if (type==='absences') { title.textContent='Absences Report Form';   content.innerHTML=hrFormAbsences(); }
    else if (type==='voucher')  { title.textContent='Allowance Voucher ARCS'; content.innerHTML=hrFormVoucher(); }
}
function closeHrForm() { document.getElementById('hrFormModal').style.display='none'; }
function printHrForm() {
    var content = document.getElementById('hrFormContent').innerHTML;
    var win = window.open('','_blank');
    win.document.write('<html><head><title>HR Form</title><style>@page{size:letter;margin:.75in}body{font-family:"Times New Roman",serif;font-size:13px;color:#111;margin:0}table{border-collapse:collapse;width:100%}td,th{border:1px solid #111;padding:4px 8px}.nb td,.nb th{border:none}input,textarea{font-family:"Times New Roman",serif;font-size:13px;color:#111;}@media print{body{margin:0}input,textarea{border:none!important;outline:none!important;}}</style></head><body>'+content+'</body></html>');
    win.document.close(); win.focus(); setTimeout(function(){win.print();},400);
}
function _ul(w){return '<span style="display:inline-block;min-width:'+(w||160)+'px;border-bottom:1px solid #111;margin-left:4px;">&nbsp;</span>';}
function _inp(w,ph){return '<input type="text" placeholder="'+(ph||'')+'" style="display:inline-block;width:'+(w||160)+'px;border:none;border-bottom:1px solid #111;margin-left:4px;font-family:inherit;font-size:inherit;outline:none;background:transparent;padding:0 2px;">';}
function _ta(h){return '<textarea style="width:100%;height:'+(h||80)+'px;border:1px solid #111;font-family:inherit;font-size:inherit;resize:none;padding:4px;box-sizing:border-box;"></textarea>';}

function _dayOffCopy(){
    return '<div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">'+
        '<img src="'+_hrLogo+'" style="width:48px;height:48px;object-fit:contain;">'+
        '<h2 style="font-size:20px;font-weight:bold;margin:0;flex:1;text-align:center;">Change Day-Off Form</h2></div>'+
        '<table class="nb" style="margin-bottom:8px;font-size:12px;width:100%;"><tr>'+
        '<td>Name:'+_inp(180)+'</td><td>Position:'+_inp(130)+'</td></tr><tr>'+
        '<td>Previous Day-Off Schedule:'+_inp(110)+'</td><td>Department:'+_inp(130)+'</td></tr><tr>'+
        '<td>New Day-Off Schedule:'+_inp(120)+'</td><td>Date (Week):'+_inp(130)+'</td></tr></table>'+
        '<div style="margin-bottom:4px;font-size:12px;">Reason:</div>'+
        _ta(70)+
        '<table class="nb" style="font-size:12px;margin-top:12px;"><tr>'+
        '<td style="width:50%;">Approved by : <strong><u>Mr. Edwin Mojica</u></strong><br><small>(Chief Operating Officer)</small></td>'+
        '<td>Acknowledged by : <strong><u>Mr. Jossen Fernandez</u></strong><br><small>(President)</small></td></tr></table>';
}

function hrFormDayOff(){
    return _dayOffCopy()+
        '<hr style="margin:18px 0;border:none;border-top:1px dashed #999;">'+
        _dayOffCopy();
}

function hrFormAbsences(){
    return '<div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">'+
        '<img src="'+_hrLogo+'" style="width:56px;height:56px;object-fit:contain;">'+
        '<h2 style="font-size:22px;font-weight:bold;margin:0;flex:1;text-align:center;">Absences Report Form</h2></div>'+
        '<table class="nb" style="margin-bottom:10px;width:100%;"><tr>'+
        '<td>Name:'+_inp(200)+'</td><td>Department:'+_inp(160)+'</td></tr><tr>'+
        '<td>Date today:'+_inp(200)+'</td><td></td></tr></table>'+
        '<div style="margin:12px 0 5px;">Explanation:</div>'+
        _ta(300)+
        '<table class="nb" style="margin-top:24px;"><tr>'+
        '<td style="width:50%;">Assessed by:'+_inp(160)+'</td>'+
        '<td>Acknowledged by:'+_inp(160)+'</td></tr></table>';
}

function hrFormVoucher(){
    var c=function(label){
        return '<p style="font-style:italic;margin:0 0 4px;font-size:12px;">'+label+'</p>'+
        '<table style="margin-bottom:14px;font-size:12px;"><tr>'+
        '<td colspan="4" style="text-align:center;padding:6px;">'+
        '<div style="display:flex;align-items:center;justify-content:center;gap:8px;">'+
        '<img src="'+_hrLogo+'" style="width:26px;height:26px;object-fit:contain;">'+
        '<div><strong>ArkCrest Realty Corporation</strong><br>Allowance Voucher ARCS &nbsp;&nbsp; (36-2026)</div></div></td></tr>'+
        '<tr><td>Employee Name:</td><td><input type="text" style="width:100%;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;"></td>'+
        '<td>Designation:</td><td><input type="text" style="width:100%;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;"></td></tr>'+
        '<tr><td>Pay Period:</td><td><input type="text" style="width:100%;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;"></td>'+
        '<td>Department:</td><td><input type="text" style="width:100%;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;"></td></tr>'+
        '<tr><td><strong>Earnings</strong></td><td><strong>Amount</strong></td><td><strong>Deductions</strong></td><td><strong>Amount</strong></td></tr>'+
        '<tr><td>Basic Pay:</td><td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td>'+
        '<td>Number of Absences:</td><td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td></tr>'+
        '<tr><td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td><td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td>'+
        '<td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td><td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td></tr>'+
        '<tr><td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td><td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td>'+
        '<td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td><td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td></tr>'+
        '<tr><td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td><td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td>'+
        '<td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td><td><input type="text" style="width:100%;border:none;font-family:inherit;font-size:inherit;outline:none;"></td></tr>'+
        '<tr><td colspan="2" style="text-align:right;font-weight:bold;">Total Earnings: <input type="text" style="width:80px;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;"></td>'+
        '<td style="font-weight:bold;text-align:right;">Total Deductions: <input type="text" style="width:60px;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;"></td><td></td></tr>'+
        '<tr><td colspan="3" style="text-align:right;font-weight:bold;">Net Pay: &#8369;</td>'+
        '<td><input type="text" style="width:100%;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;font-weight:bold;"></td></tr></table>'+
        '<table class="nb" style="font-size:12px;margin-bottom:6px;"><tr>'+
        '<td style="width:33%;">Prepared by:<br><br><u>Mr. Lourd Thristan Lobendino</u><br><small>Human Resource Associate</small></td>'+
        '<td style="width:33%;">Approved by:<br><br><u>Mr. Edwin Mojica</u><br><small>Chief Operating Officer</small></td>'+
        '<td>Received by:<br><br><input type="text" style="width:120px;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;"><br><small>&nbsp;</small></td></tr></table>';
    };
    return c("Employer\'s Copy")+
        '<hr style="margin:16px 0;border:none;border-top:1px dashed #999;">'+
        c("Employee\'s Copy");
}
</script>

@endsection
