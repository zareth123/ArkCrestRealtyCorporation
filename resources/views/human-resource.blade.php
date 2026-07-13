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
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,380px));gap:20px;margin-bottom:32px;">
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
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:24px;margin-bottom:32px;">

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

    // Department dropdown source shared by all three HR forms below.
    // If this view's controller already shares a $departments collection
    // (same one used on the Forms/Budget Request page), we use it as-is;
    // otherwise we fall back to a reasonable default list. Swap the
    // fallback array for your real department source if this controller
    // doesn't pass $departments.
    $hrDepartmentList = isset($departments)
        ? $departments->pluck('name')->values()
        : ['Human Resources','Sales','Marketing','Finance','IT','Operations','Executive','Legal'];
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
<div id="hrFormModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this)closeHrForm()">
    <div style="background:white;border-radius:14px;width:95vw;max-width:1100px;max-height:90vh;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;display:flex;flex-direction:column;">
        <div id="hrFormHeader" style="padding:14px 20px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px;flex-shrink:0;">
            <span id="hrFormTitle" style="font-size:14px;font-weight:700;color:white;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%;"></span>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <button onclick="saveHrForm()" style="padding:6px 14px;background:rgba(255,255,255,.2);color:white;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">💾 Save</button>
                <button onclick="printHrForm()" style="padding:6px 14px;background:rgba(255,255,255,.2);color:white;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">🖨 Print</button>
                <button onclick="closeHrForm()" style="padding:6px 12px;background:rgba(255,255,255,.15);color:white;border:none;border-radius:8px;font-size:16px;cursor:pointer;">&times;</button>
            </div>
        </div>
        <div id="hrFormContent" style="padding:32px 40px;font-family:'Times New Roman',serif;font-size:13px;color:#111;flex:1;overflow-y:auto;"></div>
    </div>
</div>

{{-- Confirmation Modal (used for delete confirmations) --}}
<div id="confirmModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:10002;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this)_confirmModalCancel()">
    <div style="background:white;border-radius:12px;max-width:380px;width:100%;box-shadow:0 20px 50px rgba(0,0,0,.3);padding:24px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div style="width:36px;height:36px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div style="font-size:15px;font-weight:700;color:#0f172a;">Confirm Deletion</div>
        </div>
        <div id="confirmModalMessage" style="font-size:13px;color:#475569;margin-bottom:20px;line-height:1.5;"></div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button onclick="_confirmModalCancel()" style="padding:8px 16px;background:#f1f5f9;color:#334155;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Cancel</button>
            <button onclick="_confirmModalYes()" style="padding:8px 16px;background:#dc2626;color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Delete</button>
        </div>
    </div>
</div>

<script>
var _hrLogo = "{{ asset('images/ArkCrest_Logo.png') }}";
var _hrCsrf = document.querySelector('meta[name=csrf-token]').content;
var _hrFormType = null;
var _editingFormId = null; // set when editing an existing saved form, so Save updates instead of creating a new one

// Shared department list for the Department dropdowns on all three HR forms.
window._hrDepartments = @json($hrDepartmentList);

/* ---------- reusable confirmation modal (used in place of window.confirm for deletes) ---------- */
var _confirmModalCallback = null;
function _showConfirmModal(message, onConfirm) {
    document.getElementById('confirmModalMessage').textContent = message;
    _confirmModalCallback = onConfirm;
    document.getElementById('confirmModal').style.display = 'flex';
}
function _confirmModalYes() {
    var cb = _confirmModalCallback;
    document.getElementById('confirmModal').style.display = 'none';
    _confirmModalCallback = null;
    if (cb) cb();
}
function _confirmModalCancel() {
    document.getElementById('confirmModal').style.display = 'none';
    _confirmModalCallback = null;
}

/* ---------- lightweight toast notifications (used for save/delete feedback) ---------- */
function _showToast(message, isError) {
    var toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:'+(isError ? '#dc2626' : '#16a34a')+';color:white;padding:12px 20px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.2);z-index:10001;opacity:0;transform:translateY(10px);transition:all .25s ease;max-width:320px;';
    document.body.appendChild(toast);
    requestAnimationFrame(function () {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });
    setTimeout(function () {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(function () { toast.remove(); }, 300);
    }, 2600);
}

function openHrForm(type) {
    var modal = document.getElementById('hrFormModal');
    var content = document.getElementById('hrFormContent');
    var title = document.getElementById('hrFormTitle');
    var header = document.getElementById('hrFormHeader');
    modal.setAttribute('data-type', type);
    var colors = {dayoff:'linear-gradient(135deg,#1e4575,#2563eb)', absences:'linear-gradient(135deg,#A37929,#d4a03a)', voucher:'linear-gradient(135deg,#0f2444,#1e4575)'};
    header.style.background = colors[type] || colors.dayoff;
    modal.style.display = 'flex';

    // reset any "view only" / "editing" state from a previous open
    _editingFormId = null;
    var saveBtn = document.querySelector('#hrFormHeader [onclick="saveHrForm()"]');
    if (saveBtn) saveBtn.style.display = '';

    if (type==='dayoff')   { title.textContent='Change Day-Off Form';    content.innerHTML=hrFormDayOff(); }
    else if (type==='absences') { title.textContent='Absences Report Form';   content.innerHTML=hrFormAbsences(); }
    else if (type==='voucher')  { title.textContent='Allowance Voucher ARCS'; content.innerHTML=hrFormVoucher(); }
}
function closeHrForm() {
    document.getElementById('hrFormModal').style.display='none';
    _editingFormId = null;
}
function saveHrForm() {
    var type = document.getElementById('hrFormModal').getAttribute('data-type');
    var fields = {};
    document.querySelectorAll('#hrFormContent input, #hrFormContent textarea, #hrFormContent select').forEach(function(el, i) {
        var key = el.getAttribute('data-field') || ('field_'+i);
        fields[key] = el.value;
    });
    var csrf = document.querySelector('meta[name=csrf-token]').content;
    var isEditing = !!_editingFormId;
    var oldId = _editingFormId;

    // Always create the (possibly edited) record via the existing create endpoint.
    fetch('/api/hr-forms', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
        body: JSON.stringify({type: type, data: fields})
    }).then(function(r){return r.json();}).then(function(d){
        if (!d.success) {
            _showToast('Failed to save the form.', true);
            return;
        }
        var finish = function () {
            var btn = document.querySelector('#hrFormHeader [onclick="saveHrForm()"]');
            if (btn) { btn.textContent = isEditing ? '✓ Updated!' : '✓ Saved!'; btn.style.background='rgba(34,197,94,.3)'; setTimeout(function(){ btn.textContent='💾 Save'; btn.style.background='rgba(255,255,255,.2)'; },2000); }
            _showToast(isEditing ? 'Form updated successfully.' : 'Form saved successfully.');
            _editingFormId = null;
            loadSavedForms();
        };
        if (isEditing && oldId) {
            // The new version saved fine — now remove the old version using the existing delete endpoint.
            // If this cleanup call fails for some reason, the updated form is still safely saved.
            fetch('/api/hr-forms/'+oldId, {method:'DELETE',headers:{'X-CSRF-TOKEN':csrf}})
                .then(finish)
                .catch(finish);
        } else {
            finish();
        }
    }).catch(function(){
        _showToast('Failed to save the form.', true);
    });
}
function printHrForm() {
    // Auto-save before printing
    var type = document.getElementById('hrFormModal').getAttribute('data-type');
    var fields = {};
    document.querySelectorAll('#hrFormContent input, #hrFormContent textarea, #hrFormContent select').forEach(function(el, i) {
        var key = el.getAttribute('data-field') || ('field_'+i);
        fields[key] = el.value;
    });
    var csrf = document.querySelector('meta[name=csrf-token]').content;
    var isEditing = !!_editingFormId;
    var oldId = _editingFormId;

    fetch('/api/hr-forms', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
        body: JSON.stringify({type: type, data: fields})
    }).then(function(r){return r.json();}).then(function(d){
        if (!d.success) {
            _showToast('Failed to save before printing.', true);
            return;
        }
        _editingFormId = null;
        // If editing, clean up the old record
        if (isEditing && oldId) {
            fetch('/api/hr-forms/'+oldId, {method:'DELETE',headers:{'X-CSRF-TOKEN':csrf}})
                .finally(function(){ loadSavedForms(); _doPrint(); });
        } else {
            loadSavedForms();
            _doPrint();
        }
    }).catch(function(){
        _showToast('Network error. Could not save before printing.', true);
    });
}

// Formats a native <input type="date"> value ("YYYY-MM-DD") into a
// friendly, unambiguous printed form (e.g. "July 10, 2026"). Used only
// for the *printed* copy — the interactive form still shows the native
// picker/typing UI.
function _fmtDate(v) {
    if (!v) return '';
    var parts = v.split('-');
    if (parts.length !== 3) return v;
    var d = new Date(parseInt(parts[0],10), parseInt(parts[1],10)-1, parseInt(parts[2],10));
    if (isNaN(d.getTime())) return v;
    return d.toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' });
}

function _doPrint() {
    var source = document.getElementById('hrFormContent');
    var clone = source.cloneNode(true);
    var liveFields = source.querySelectorAll('input, textarea, select');
    var cloneFields = clone.querySelectorAll('input, textarea, select');
    liveFields.forEach(function (live, i) {
        var c = cloneFields[i];
        if (!c) return;
        if (live.tagName === 'TEXTAREA') {
            c.textContent = live.value;
        } else if (live.tagName === 'SELECT') {
            // Selects don't print interactively well — swap for plain text
            // showing the chosen department (or blank if none picked).
            var span = document.createElement('span');
            span.textContent = live.value || '';
            span.style.cssText = 'display:inline-block;width:'+ (live.style.width||'160px') +';border-bottom:1px solid #111;margin-left:4px;';
            c.parentNode.replaceChild(span, c);
        } else if (live.type === 'date') {
            var dSpan = document.createElement('span');
            dSpan.textContent = _fmtDate(live.value);
            dSpan.style.cssText = 'display:inline-block;width:'+ (live.style.width||'160px') +';border-bottom:1px solid #111;margin-left:4px;';
            c.parentNode.replaceChild(dSpan, c);
        } else {
            c.setAttribute('value', live.value);
        }
    });
    var content = clone.innerHTML;
    var win = window.open('','_blank');
    var printHtml = '<html><head><title>HR Form</title><style>@page{size:letter;margin:.75in}body{font-family:"Times New Roman",serif;font-size:13px;color:#111;margin:0}table{border-collapse:collapse;width:100%}td,th{border:1px solid #111;padding:4px 8px}.nb td,.nb th{border:none}input,textarea{font-family:"Times New Roman",serif;font-size:13px;color:#111;}'
        + '@media print{body{margin:0}input,textarea{outline:none!important;}}</style>'
        + '<' + '/head><body>'
        + content
        + '</body></html>';
    win.document.write(printHtml);
    win.document.close(); win.focus(); setTimeout(function(){win.print();},400);
}
function _ul(w){return '<span style="display:inline-block;min-width:'+(w||160)+'px;border-bottom:1px solid #111;margin-left:4px;">&nbsp;</span>';}

/* data-field lets us keep two printed "copies" of the same form in sync as the user types
   (see the delegated input listener near the bottom of this script). Pass a field name only
   when a value should mirror across copies; omit it for one-off/static inputs. */
function _inp(w,ph,field){
    var attr = field ? ' data-field="'+field+'"' : '';
    return '<input type="text" placeholder="'+(ph||'')+'"'+attr+' style="display:inline-block;width:'+(w||160)+'px;border:none;border-bottom:1px solid #111;margin-left:4px;font-family:inherit;font-size:inherit;outline:none;background:transparent;padding:0 2px;">';
}
function _ta(h,field){
    var attr = field ? ' data-field="'+field+'"' : '';
    return '<textarea'+attr+' style="width:100%;height:'+(h||80)+'px;border:1px solid #111;font-family:inherit;font-size:inherit;resize:none;padding:4px;box-sizing:border-box;"></textarea>';
}

// Native calendar-picker date field. Typing is still possible, but native
// <input type="date"> only ever accepts numeric day/month/year segments —
// free text like "next Monday" or "TBD" simply can't be entered — so this
// satisfies the "calendar picker + numeric typing only" requirement for
// every date field across all three HR forms without extra JS validation.
function _dateInp(w,field){
    var attr = field ? ' data-field="'+field+'"' : '';
    return '<input type="date"'+attr+' style="display:inline-block;width:'+(w||160)+'px;border:none;border-bottom:1px solid #111;margin-left:4px;font-family:inherit;font-size:inherit;outline:none;background:transparent;padding:0 2px;">';
}

// Department dropdown, populated from window._hrDepartments.
function _deptSelect(w,field){
    var attr = field ? ' data-field="'+field+'"' : '';
    var opts = '<option value="">Select Department</option>';
    (window._hrDepartments||[]).forEach(function(d){
        opts += '<option value="'+d+'">'+d+'</option>';
    });
    return '<select'+attr+' style="display:inline-block;width:'+(w||160)+'px;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;background:transparent;padding:0 2px;">'+opts+'</select>';
}

// Strips anything that isn't a digit or a single decimal point, so
// "Amount" / count fields (Basic Pay, Number of Absences, Number of Late
// Arrivals, Number of Days Rendered, all Earnings/Deductions amounts,
// totals, Net Pay) only ever hold a number — no letters or symbols can be
// typed in beside them.
function _restrictNumeric(el){
    var v = el.value.replace(/[^0-9.]/g,'');
    var firstDot = v.indexOf('.');
    if(firstDot !== -1){
        v = v.slice(0,firstDot+1) + v.slice(firstDot+1).replace(/\./g,'');
    }
    el.value = v;
}
// Numeric-only input, used for every Amount/count field on the Voucher form.
function _numInp(w,field,extraStyle){
    var attr = field ? ' data-field="'+field+'"' : '';
    return '<input type="text" inputmode="decimal" oninput="_restrictNumeric(this)"'+attr+' style="width:'+(w||'100%')+';border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;'+(extraStyle||'')+'">';
}

function _dayOffCopy(label){
    return (label ? '<p style="font-style:italic;margin:0 0 4px;font-size:11px;">'+label+'</p>' : '')
        + '<div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">'+
        '<img src="'+_hrLogo+'" style="width:48px;height:48px;object-fit:contain;">'+
        '<h2 style="font-size:20px;font-weight:bold;margin:0;flex:1;text-align:center;">Change Day-Off Form</h2></div>'+
        '<table class="nb" style="margin-bottom:8px;font-size:12px;width:100%;"><tr>'+
        '<td>Name:'+_inp(180,'','name')+'</td><td>Position:'+_inp(130,'','position')+'</td></tr><tr>'+
        '<td>Previous Day-Off Schedule:'+_dateInp(150,'prev_dayoff')+'</td><td>Department:'+_deptSelect(150,'department')+'</td></tr><tr>'+
        '<td>New Day-Off Schedule:'+_dateInp(150,'new_dayoff')+'</td><td>Date (Week):'+_dateInp(150,'date_week')+'</td></tr></table>'+
        '<div style="margin-bottom:4px;font-size:12px;">Reason:</div>'+
        _ta(70,'reason')+
        '<table class="nb" style="font-size:12px;margin-top:12px;"><tr>'+
        '<td style="width:50%;">Approved by : <strong><u>Mr. Edwin Mojica</u></strong><br><small>(Chief Operating Officer)</small></td>'+
        '<td>Acknowledged by : <strong><u>Mr. Jossen Fernandez</u></strong><br><small>(President)</small></td></tr></table>';
}

function hrFormDayOff(){
    return _dayOffCopy('Company Copy')+
        '<hr style="margin:18px 0;border:none;border-top:1px dashed #999;">'+
        _dayOffCopy("Employee's Copy");
}

function hrFormAbsences(){
    return '<div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">'+
        '<img src="'+_hrLogo+'" style="width:56px;height:56px;object-fit:contain;">'+
        '<h2 style="font-size:22px;font-weight:bold;margin:0;flex:1;text-align:center;">Absences Report Form</h2></div>'+
        '<table class="nb" style="margin-bottom:10px;width:100%;"><tr>'+
        '<td>Name:'+_inp(200)+'</td><td>Department:'+_deptSelect(200)+'</td></tr><tr>'+
        '<td>Date today:'+_dateInp(200)+'</td><td></td></tr></table>'+
        '<div style="margin:12px 0 5px;">Explanation:</div>'+
        _ta(300)+
        '<table class="nb" style="margin-top:24px;"><tr>'+
        '<td style="width:50%;">Assessed by:'+_inp(160)+'</td>'+
        '<td>Acknowledged by:'+_inp(160)+'</td></tr></table>';
}

function hrFormVoucher(){
    var c=function(label){
        return '<p style="font-style:italic;margin:0 0 4px;font-size:12px;">'+label+'</p>'+
        '<table style="margin-bottom:14px;font-size:12px;width:100%;"><tr>'+
        '<td colspan="4" style="text-align:center;padding:6px;">'+
        '<div style="display:flex;align-items:center;justify-content:center;gap:8px;">'+
        '<img src="'+_hrLogo+'" style="width:26px;height:26px;object-fit:contain;">'+
        '<div><strong>ArkCrest Realty Corporation</strong><br>Allowance Voucher ARCS &nbsp;&nbsp; (36-2026)</div></div></td></tr>'+
        '<tr><td>Employee Name:</td><td><input type="text" data-field="emp_name" style="width:100%;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;"></td>'+
        '<td>Designation:</td><td><input type="text" data-field="designation" style="width:100%;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;"></td></tr>'+
        '<tr><td>Pay Period:</td><td><div style="display:flex;align-items:center;gap:4px;">'+
        '<input type="date" data-field="pay_period_from" style="flex:1;min-width:0;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;">'+
        '<span style="flex-shrink:0;">to</span>'+
        '<input type="date" data-field="pay_period_to" style="flex:1;min-width:0;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;"></div></td>'+
        '<td>Department:</td><td>'+_deptSelectFullWidth('department')+'</td></tr>'+

        // Earnings/Deductions side-by-side. Days Rendered pairs with Absences,
        // Basic Pay pairs with Late Arrivals, then a combined Totals row and
        // a final Net Pay row spanning the full width.
        '<tr><td style="padding-top:10px;"><strong>Earnings</strong></td><td style="padding-top:10px;"><strong>Amount</strong></td><td style="padding-top:10px;"><strong>Deductions</strong></td><td style="padding-top:10px;"><strong>Amount</strong></td></tr>'+
        '<tr><td>Number of Days Rendered:</td><td>'+_numInp(null,'days_rendered')+'</td><td>Number of Absences:</td><td>'+_numInp(null,'absences_count')+'</td></tr>'+
        '<tr><td>Basic Pay:</td><td>'+_numInp(null,'basic_pay')+'</td><td>Number of Late Arrivals:</td><td>'+_numInp(null,'late_arrivals_count')+'</td></tr>'+
        '<tr><td style="text-align:right;font-weight:bold;padding-top:8px;">Total Earnings:</td><td style="padding-top:8px;">'+_numInp(null,'total_earnings',';font-weight:bold;')+'</td>'+
        '<td style="text-align:right;font-weight:bold;padding-top:8px;">Total Deductions:</td><td style="padding-top:8px;">'+_numInp(null,'total_deductions',';font-weight:bold;')+'</td></tr>'+
        '<tr><td colspan="3" style="text-align:right;font-weight:bold;padding-top:12px;">Net Pay: &#8369;</td>'+
        '<td style="padding-top:12px;">'+_numInp('100%','net_pay',';font-weight:bold;')+'</td></tr></table>'+
        '<table class="nb" style="font-size:12px;margin-bottom:6px;"><tr>'+
        '<td style="width:33%;">Prepared by:<br><br><u>Mr. Lourd Thristan Lobendino</u><br><small>Human Resource Associate</small></td>'+
        '<td style="width:33%;">Approved by:<br><br><u>Mr. Edwin Mojica</u><br><small>Chief Operating Officer</small></td>'+
        '<td style="width:33%;">Received by:<br><br><textarea data-field="received_by" rows="1" style="width:100%;min-width:80px;max-width:100%;box-sizing:border-box;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;resize:none;overflow:hidden;white-space:pre-wrap;word-break:break-word;line-height:1.3;" oninput="this.style.height=\'auto\';this.style.height=this.scrollHeight+\'px\';"></textarea><br><small>&nbsp;</small></td></tr></table>';
    };
    return c("Employer\'s Copy")+
        '<hr style="margin:16px 0;border:none;border-top:1px dashed #999;">'+
        c("Employee\'s Copy");
}

// Full-width variant of _deptSelect for use inside a table cell that
// already provides its own width (matches the Employee Name / Designation
// inputs directly above it on the Voucher form).
function _deptSelectFullWidth(field){
    var attr = field ? ' data-field="'+field+'"' : '';
    var opts = '<option value="">Select Department</option>';
    (window._hrDepartments||[]).forEach(function(d){
        opts += '<option value="'+d+'">'+d+'</option>';
    });
    return '<select'+attr+' style="width:100%;border:none;border-bottom:1px solid #111;font-family:inherit;font-size:inherit;outline:none;">'+opts+'</select>';
}

/* Keep matching fields in sync across the two printed copies of a form as the user types.
   Delegated on the (always-present) content container, so it works regardless of which
   form is currently rendered inside it. */
document.getElementById('hrFormContent').addEventListener('input', function(e){
    var el = e.target;
    var field = el.getAttribute('data-field');
    if (!field) return;
    document.querySelectorAll('#hrFormContent [data-field="'+field+'"]').forEach(function(other){
        if (other !== el) other.value = el.value;
    });
});
</script>

{{-- Saved Forms Section --}}
<div style="margin-top:32px;">
    <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
        Saved Forms
    </div>

    {{-- Folder Tabs --}}
    <div style="display:flex;gap:4px;margin-bottom:0;border-bottom:2px solid #e2e8f0;">
        @foreach(['dayoff'=>'Change Day-Off','absences'=>'Absences Report','voucher'=>'Allowance Voucher'] as $ftype => $flabel)
        <button onclick="switchFolder('{{ $ftype }}')" id="folder-tab-{{ $ftype }}"
            style="padding:8px 18px;border:none;border-radius:8px 8px 0 0;font-size:12px;font-weight:600;cursor:pointer;transition:all .2s;
            {{ $ftype === 'dayoff' ? 'background:#1e4575;color:white;' : 'background:#f1f5f9;color:#64748b;' }}">
            {{ $flabel }}
        </button>
        @endforeach
    </div>

    {{-- Folder Content --}}
    @foreach(['dayoff','absences','voucher'] as $ftype)
    <div id="folder-{{ $ftype }}" style="{{ $ftype === 'dayoff' ? '' : 'display:none;' }}background:white;border-radius:0 8px 8px 8px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:16px;">
        <div id="bulk-actions-{{ $ftype }}" style="display:none;align-items:center;justify-content:space-between;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;margin-bottom:12px;">
            <span style="font-size:13px;color:#991b1b;font-weight:600;"><span id="bulk-count-{{ $ftype }}">0</span> selected</span>
            <button onclick="deleteSelectedForms('{{ $ftype }}')" style="padding:6px 14px;background:#dc2626;color:white;border:none;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;">Delete Selected</button>
        </div>
        <div id="saved-list-{{ $ftype }}" style="font-size:13px;color:#94a3b8;text-align:center;padding:20px;">Loading...</div>
    </div>
    @endforeach
</div>

<script>
var _csrf2 = document.querySelector('meta[name=csrf-token]').content;
var _activeFolder = 'dayoff';
var _savedFormsCache = {}; // type -> last-fetched list, used by the View button

function switchFolder(type) {
    _activeFolder = type;
    ['dayoff','absences','voucher'].forEach(function(t) {
        var tab = document.getElementById('folder-tab-'+t);
        var folder = document.getElementById('folder-'+t);
        if (t === type) {
            tab.style.background = '#1e4575'; tab.style.color = 'white';
            folder.style.display = 'block';
        } else {
            tab.style.background = '#f1f5f9'; tab.style.color = '#64748b';
            folder.style.display = 'none';
        }
    });
    loadSavedForms(type);
}

function loadSavedForms(type) {
    type = type || _activeFolder;
    var container = document.getElementById('saved-list-'+type);
    if (!container) return;
    var bulkBar = document.getElementById('bulk-actions-'+type);
    if (bulkBar) bulkBar.style.display = 'none';
    fetch('/api/hr-forms?type='+type)
    .then(function(r){return r.json();})
    .then(function(list){
        _savedFormsCache[type] = list;
        if (!list.length) {
            container.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:20px;font-size:13px;">No saved forms yet.</div>';
            return;
        }
        container.innerHTML = '<div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">'+
            '<table style="width:100%;min-width:680px;border-collapse:collapse;font-size:13px;">'+
            '<thead><tr style="background:#f8fafc;">'+
            '<th style="padding:8px 12px;border-bottom:1px solid #e2e8f0;width:32px;"><input type="checkbox" id="selectAll-'+type+'" onclick="toggleSelectAll(\''+type+'\')"></th>'+
            '<th style="padding:8px 12px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;border-bottom:1px solid #e2e8f0;white-space:nowrap;">Title</th>'+
            '<th style="padding:8px 12px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;border-bottom:1px solid #e2e8f0;white-space:nowrap;">Saved By</th>'+
            '<th style="padding:8px 12px;text-align:left;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;border-bottom:1px solid #e2e8f0;white-space:nowrap;">Date</th>'+
            '<th style="padding:8px 12px;border-bottom:1px solid #e2e8f0;"></th></tr></thead><tbody>'+
            list.map(function(f, idx){
                return '<tr style="border-bottom:1px solid #f1f5f9;">'+
                    '<td style="padding:10px 12px;"><input type="checkbox" class="row-checkbox-'+type+'" value="'+f.id+'" onclick="updateBulkDeleteBar(\''+type+'\')"></td>'+
                    '<td style="padding:10px 12px;font-weight:600;color:#0f172a;word-break:break-word;">'+f.title+'</td>'+
                    '<td style="padding:10px 12px;color:#64748b;white-space:nowrap;">'+f.created_by+'</td>'+
                    '<td style="padding:10px 12px;color:#94a3b8;font-size:12px;white-space:nowrap;">'+f.created_at+'</td>'+
                    '<td style="padding:10px 12px;text-align:right;white-space:nowrap;">'+
                    '<button onclick="viewSavedForm(\''+type+'\','+idx+')" style="padding:4px 10px;background:#e0e7ff;color:#3730a3;border:1px solid #c7d2fe;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;margin-right:6px;">View</button>'+
                    '<button onclick="editSavedForm(\''+type+'\','+idx+')" style="padding:4px 10px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;margin-right:6px;">Edit</button>'+
                    '<button onclick="deleteSavedForm('+f.id+',\''+type+'\')" style="padding:4px 10px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;">Delete</button>'+
                    '</td></tr>';
            }).join('')+'</tbody></table></div>';
    });
}

/* ---------- View ---------- */
function viewSavedForm(type, idx) {
    var list = _savedFormsCache[type] || [];
    var f = list[idx];
    if (!f) return;
    openHrForm(type);
    document.getElementById('hrFormTitle').textContent = (f.title || '') + ' — View Only';
    setTimeout(function() {
        var inputs = document.querySelectorAll('#hrFormContent input, #hrFormContent textarea, #hrFormContent select');
        var data = f.data || {};
        inputs.forEach(function(el, i) {
            var key = el.getAttribute('data-field') || ('field_'+i);
            if (data[key] !== undefined) el.value = data[key];
            el.setAttribute('readonly', 'readonly');
            if (el.tagName === 'SELECT') el.setAttribute('disabled', 'disabled');
            el.style.background = '#f1f5f9';
            el.style.cursor = 'default';
        });
        var saveBtn = document.querySelector('#hrFormHeader [onclick="saveHrForm()"]');
        if (saveBtn) saveBtn.style.display = 'none';
    }, 100);
}

/* ---------- Edit ---------- */
function editSavedForm(type, idx) {
    var list = _savedFormsCache[type] || [];
    var f = list[idx];
    if (!f) return;
    openHrForm(type); // opens a fresh, fully-editable form and resets _editingFormId to null
    _editingFormId = f.id; // now mark it as an edit so Save updates this record instead of creating a new one
    document.getElementById('hrFormTitle').textContent = (f.title || '') + ' — Editing';
    setTimeout(function() {
        var inputs = document.querySelectorAll('#hrFormContent input, #hrFormContent textarea, #hrFormContent select');
        var data = f.data || {};
        inputs.forEach(function(el, i) {
            var key = el.getAttribute('data-field') || ('field_'+i);
            if (data[key] !== undefined) el.value = data[key];
        });
    }, 100);
}

/* ---------- Single delete ---------- */
function deleteSavedForm(id, type) {
    _showConfirmModal('Are you sure you want to delete this saved form? This action cannot be undone.', function () {
        fetch('/api/hr-forms/'+id, {method:'DELETE',headers:{'X-CSRF-TOKEN':_csrf2}})
        .then(function(r){return r.json();})
        .then(function(res){
            if (res && res.success === false) {
                _showToast('Failed to delete the form.', true);
            } else {
                _showToast('Form deleted successfully.');
            }
            loadSavedForms(type);
        })
        .catch(function(){
            _showToast('Failed to delete the form.', true);
        });
    });
}

/* ---------- Select all / bulk delete ---------- */
function toggleSelectAll(type) {
    var master = document.getElementById('selectAll-'+type);
    document.querySelectorAll('.row-checkbox-'+type).forEach(function(cb){ cb.checked = master.checked; });
    updateBulkDeleteBar(type);
}

function updateBulkDeleteBar(type) {
    var checked = document.querySelectorAll('.row-checkbox-'+type+':checked');
    var all = document.querySelectorAll('.row-checkbox-'+type);
    var bar = document.getElementById('bulk-actions-'+type);
    var countEl = document.getElementById('bulk-count-'+type);
    var master = document.getElementById('selectAll-'+type);

    if (bar) bar.style.display = checked.length > 0 ? 'flex' : 'none';
    if (countEl) countEl.textContent = checked.length;
    if (master) {
        master.checked = all.length > 0 && checked.length === all.length;
        master.indeterminate = checked.length > 0 && checked.length < all.length;
    }
}

function deleteSelectedForms(type) {
    var checked = document.querySelectorAll('.row-checkbox-'+type+':checked');
    if (!checked.length) return;
    var ids = Array.prototype.map.call(checked, function(cb){ return cb.value; });
    _showConfirmModal('Delete '+ids.length+' selected form(s)? This action cannot be undone.', function () {
        Promise.all(ids.map(function(id){
            return fetch('/api/hr-forms/'+id, {method:'DELETE',headers:{'X-CSRF-TOKEN':_csrf2}});
        })).then(function(){
            _showToast(ids.length+' form(s) deleted successfully.');
            loadSavedForms(type);
        }).catch(function(){
            _showToast('Some forms could not be deleted.', true);
            loadSavedForms(type);
        });
    });
}

// Load on page ready
document.addEventListener('DOMContentLoaded', function(){ loadSavedForms('dayoff'); });
</script>

@endsection