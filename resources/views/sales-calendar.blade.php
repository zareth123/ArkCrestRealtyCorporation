@extends('layouts.dashboard')
@section('title', 'Sales & Marketing Calendar')

@section('content')
@php
    $monthNames  = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
    $dayNames    = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $firstDay    = (int) date('w', mktime(0,0,0,$month,1,$year));
    $daysInMonth = (int) date('t', mktime(0,0,0,$month,1,$year));
    $today       = date('Y-m-d');
    $prevMonth   = $month == 1 ? 12 : $month - 1;
    $prevYear    = $month == 1 ? $year - 1 : $year;
    $nextMonth   = $month == 12 ? 1 : $month + 1;
    $nextYear    = $month == 12 ? $year + 1 : $year;

    $typeConfig = [
        'sale'        => ['color'=>'#2563eb','bg'=>'#eff6ff','label'=>'Downpayment'],
        'reservation' => ['color'=>'#7c3aed','bg'=>'#f5f3ff','label'=>'Reservation Date'],
        'release'     => ['color'=>'#059669','bg'=>'#ecfdf5','label'=>'Commission Release'],
        'trip'        => ['color'=>'#A37929','bg'=>'#fffbeb','label'=>'Site Visit'],
        'downpayment' => ['color'=>'#0891b2','bg'=>'#ecfeff','label'=>'Downpayment Date'],
    ];
@endphp

<style>
/* ── Layout ── */
.sc-page { display:flex;flex-direction:column;height:calc(100vh - 62px - 20px);gap:0; }

/* ── Top bar ── */
.sc-topbar { display:flex;align-items:center;justify-content:space-between;padding:0 0 10px;flex-shrink:0; }
.sc-title   { font-size:26px;font-weight:700;color:#1e4575;letter-spacing:-.3px; }
.sc-sub     { font-size:12px;color:#94a3b8;margin-top:2px; }
.sc-controls{ display:flex;align-items:center;gap:8px;flex-wrap:wrap; }

.sc-nav-btn {
    display:inline-flex;align-items:center;justify-content:center;
    width:32px;height:32px;border-radius:8px;
    background:white;border:1.5px solid #e2e8f0;
    color:#1e4575;text-decoration:none;font-size:16px;font-weight:700;
    transition:all .2s;
}
.sc-nav-btn:hover { background:#1e4575;color:white;border-color:#1e4575; }
.sc-month-pill {
    background:linear-gradient(135deg,#1e4575,#2563eb);
    color:white;padding:6px 20px;border-radius:20px;
    font-size:14px;font-weight:700;letter-spacing:.3px;
    min-width:160px;text-align:center;
}
.sc-today-btn {
    padding:6px 14px;background:white;color:#1e4575;
    border:1.5px solid #1e4575;border-radius:8px;
    text-decoration:none;font-size:12px;font-weight:600;
    transition:all .2s;
}
.sc-today-btn:hover { background:#1e4575;color:white; }
.sc-year-sel {
    padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:8px;
    font-size:13px;font-weight:500;color:#374151;background:white;
    cursor:pointer;outline:none;
}
.sc-view-btn {
    padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;
    border:1.5px solid #e2e8f0;background:white;color:#64748b;
    cursor:pointer;transition:all .2s;text-decoration:none;
}
.sc-view-btn.active { background:#1e4575;color:white;border-color:#1e4575; }

/* ── Stats ── */
.sc-stats { display:flex;gap:10px;margin-bottom:12px;flex-shrink:0;flex-wrap:wrap; }

/* ── This Week ── */
.sc-week {
    border-radius:10px;border:1px solid #e8ecf0;
    box-shadow:0 1px 4px rgba(0,0,0,.04);margin-bottom:10px;flex-shrink:0;overflow:hidden;
}
.sc-week-hdr {
    display:flex;align-items:center;justify-content:space-between;
    padding:8px 16px;background:linear-gradient(135deg,#1e4575,#2563eb);
}
.sc-week-title { font-size:12px;font-weight:700;color:white;display:flex;align-items:center;gap:6px; }
.sc-week-range { font-size:11px;color:rgba(255,255,255,.7); }
.sc-week-body { display:flex;gap:0;min-height:0;background:white; }
.sc-week-col { flex:1;border-right:1px solid #f1f5f9; }
.sc-week-col:last-child { border-right:none; }
.sc-week-col-title {
    font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;
    padding:8px 14px;display:flex;align-items:center;gap:7px;border-bottom:1px solid #f1f5f9;
}
.sc-week-col.trips .sc-week-col-title { background:#fffbeb;color:#92400e; }
.sc-week-col.downs .sc-week-col-title { background:#ecfeff;color:#164e63; }
.sc-week-dot { width:9px;height:9px;border-radius:50%;flex-shrink:0; }
.sc-week-items { padding:6px 14px; }
.sc-week-item { display:flex;align-items:flex-start;gap:8px;padding:4px 0;border-bottom:1px solid #f8fafc; }
.sc-week-item:last-child { border-bottom:none; }
.sc-week-item-date { font-size:10px;font-weight:700;color:#64748b;min-width:32px;flex-shrink:0;padding-top:1px; }
.sc-week-item-name { font-size:11px;font-weight:600;color:#0f172a; }
.sc-week-item-sub { font-size:10px;color:#94a3b8; }
.sc-week-empty { font-size:11px;color:#cbd5e1;padding:6px 0;font-style:italic; }
.sc-stat {
    background:white;border:1px solid #e8ecf0;border-radius:10px;
    padding:10px 16px;display:flex;align-items:center;gap:10px;
    box-shadow:0 1px 4px rgba(0,0,0,.04);flex:1;min-width:140px;
}
.sc-stat-icon {
    width:34px;height:34px;border-radius:9px;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.sc-stat-val { font-size:18px;font-weight:700;color:#1e293b; }
.sc-stat-lbl { font-size:10px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.4px; }

/* ── Legend ── */
.sc-legend { display:flex;align-items:center;gap:14px;padding:6px 0 0;font-size:11px;color:#64748b;flex-shrink:0;flex-wrap:wrap; }
.sc-legend-dot { width:10px;height:10px;border-radius:3px;flex-shrink:0; }

/* ── Calendar grid ── */
.sc-grid-wrap {
    flex:1;background:white;border-radius:12px;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
    overflow:hidden;border:1px solid #e8ecf0;
    display:flex;flex-direction:column;min-height:0;
}
.sc-day-headers {
    display:grid;grid-template-columns:repeat(7,1fr);
    background:#f8fafc;border-bottom:2px solid #e8ecf0;flex-shrink:0;
}
.sc-day-hdr {
    padding:10px 0;text-align:center;
    font-size:11px;font-weight:700;color:#64748b;
    letter-spacing:.6px;text-transform:uppercase;
}
.sc-day-hdr.weekend { color:#94a3b8; }
.sc-days { display:grid;grid-template-columns:repeat(7,1fr);flex:1;min-height:0; }
.sc-cell {
    border-right:1px solid #d1d5db;border-bottom:1px solid #d1d5db;
    padding:5px 6px;display:flex;flex-direction:column;
    overflow:hidden;transition:background .15s;cursor:default;
}
.sc-cell:nth-child(7n) { border-right:none; }
.sc-cell.empty { background:#fafbfc; }
.sc-cell.weekend { background:#fafbfc; }
.sc-cell.today { background:linear-gradient(135deg,#eff6ff,#e8f0fe); }
.sc-cell:not(.empty):not(.today):hover { background:#f8faff; }
.sc-day-num {
    display:inline-flex;align-items:center;justify-content:center;
    width:22px;height:22px;border-radius:50%;
    font-size:12px;font-weight:700;color:#1e293b;
    align-self:flex-end;flex-shrink:0;margin-bottom:2px;
}    font-size:12px;font-weight:500;color:#374151;
    align-self:flex-end;flex-shrink:0;margin-bottom:2px;
}
.sc-cell.today .sc-day-num {
    background:linear-gradient(135deg,#1e4575,#2563eb);
    color:white;font-weight:700;
    box-shadow:0 2px 6px rgba(30,69,117,.3);
}
.sc-cell.weekend .sc-day-num { color:#94a3b8; }

/* ── Event chips ── */
.sc-event {
    border-radius:4px;padding:2px 5px;
    font-size:9px;margin-bottom:2px;cursor:pointer;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    flex-shrink:0;transition:opacity .15s;
    box-shadow:0 1px 3px rgba(0,0,0,.1);
    font-weight:600;
}
.sc-event:hover { opacity:.8; }
.sc-event.type-sale        { background:#2563eb;color:white; }
.sc-event.type-reservation { background:#7c3aed;color:white; }
.sc-event.type-release     { background:#059669;color:white; }
.sc-event.type-trip        { background:linear-gradient(135deg,#A37929,#d4a03a);color:white; }
.sc-event.type-downpayment { background:#0891b2;color:white; }
.sc-more {
    font-size:9px;color:#94a3b8;text-align:right;margin-top:1px;
    flex-shrink:0;font-weight:600;cursor:pointer;text-decoration:underline;
}
.sc-more:hover { color:#1e4575; }

/* ── List view ── */
.sc-list-wrap {
    flex:1;background:white;border-radius:12px;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
    border:1px solid #e8ecf0;overflow-y:auto;
}
.sc-list-empty { display:flex;flex-direction:column;align-items:center;justify-content:center;height:200px;color:#94a3b8; }
.sc-list-group { border-bottom:1px solid #f1f5f9; }
.sc-list-date-hdr {
    padding:10px 18px;background:#f8fafc;
    font-size:11px;font-weight:700;color:#64748b;
    text-transform:uppercase;letter-spacing:.5px;
    border-bottom:1px solid #f1f5f9;
    position:sticky;top:0;z-index:1;
}
.sc-list-item {
    display:flex;align-items:center;gap:14px;
    padding:12px 18px;border-bottom:1px solid #f8fafc;
    transition:background .15s;cursor:pointer;
}
.sc-list-item:hover { background:#f8faff; }
.sc-list-item:last-child { border-bottom:none; }
.sc-list-badge {
    padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;
    text-transform:uppercase;letter-spacing:.4px;flex-shrink:0;
}
.sc-list-main { flex:1;min-width:0; }
.sc-list-name { font-size:13px;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.sc-list-meta { font-size:11px;color:#94a3b8;margin-top:1px; }
.sc-list-amount { font-size:13px;font-weight:700;color:#1e4575;flex-shrink:0; }

/* ── Modal ── */
.sc-modal-overlay {
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);
    z-index:9999;align-items:center;justify-content:center;
}
.sc-modal-box {
    background:white;border-radius:16px;width:460px;max-width:95vw;
    overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2);
    animation:scModalIn .2s ease;
}
@keyframes scModalIn { from{transform:scale(.95);opacity:0} to{transform:scale(1);opacity:1} }
.sc-modal-hdr {
    padding:18px 22px;display:flex;align-items:center;justify-content:space-between;
}
.sc-modal-close {
    background:rgba(255,255,255,.15);border:none;color:white;
    width:28px;height:28px;border-radius:7px;cursor:pointer;font-size:16px;line-height:1;
}
.sc-modal-body { padding:20px 22px; }
.sc-modal-grid { display:grid;grid-template-columns:1fr 1fr;gap:10px; }
.sc-modal-field {
    background:#f8fafc;border-radius:8px;padding:10px 12px;
    border:1px solid #f1f5f9;
}
.sc-modal-field-lbl { font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px; }
.sc-modal-field-val { font-size:13px;font-weight:600;color:#1e293b; }
.sc-modal-field.full { grid-column:1/-1; }
</style>

<div class="sc-page">

    {{-- Top Bar --}}
    <div class="sc-topbar" style="background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:16px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
        <div style="position:relative;z-index:2;">
            <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;">Sales & Marketing</div>
            <h1 style="font-size:24px;font-weight:700;color:white;margin:0 0 6px;">Calendar</h1>
            <p style="font-size:13px;color:rgba(255,255,255,.75);margin:0;">{{ $monthNames[$month] }} {{ $year }} &bull; All sales events at a glance</p>
        </div>
        <div class="sc-controls" style="position:relative;z-index:2;">
            <form method="GET" action="{{ route('sales-calendar') }}" style="display:flex;align-items:center;gap:6px;">
                <input type="hidden" name="view" value="{{ $view }}">
                <select name="month" class="sc-month-sel" onchange="this.form.submit()" style="background:rgba(255,255,255,.15);color:white;border:1.5px solid rgba(255,255,255,.3);border-radius:8px;padding:6px 10px;font-size:13px;font-weight:600;">
                    @foreach($monthNames as $num => $name)
                        @if($num > 0)
                        <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }} style="color:#1e4575;background:white;">{{ $name }}</option>
                        @endif
                    @endforeach
                </select>
                <select name="year" class="sc-year-sel" onchange="this.form.submit()" style="background:rgba(255,255,255,.15);color:white;border:1.5px solid rgba(255,255,255,.3);border-radius:8px;padding:6px 10px;font-size:13px;font-weight:600;">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }} style="color:#1e4575;background:white;">{{ $y }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('sales-calendar', ['month'=>date('n'),'year'=>date('Y'),'view'=>$view]) }}" class="sc-today-btn" style="background:rgba(255,255,255,.2);color:white;border:1.5px solid rgba(255,255,255,.3);">Today</a>
            </a>
            <a href="{{ route('sales-calendar', ['month'=>$month,'year'=>$year,'view'=>'list']) }}" style="{{ $view=='list' ? 'background:rgba(255,255,255,.25);color:white;border-color:rgba(255,255,255,.4);' : 'background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);border-color:rgba(255,255,255,.2);' }} padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;border:1.5px solid;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:13px;height:13px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                List
            </a>
        </div>
        <div style="position:absolute;top:0;right:0;width:300px;height:100%;pointer-events:none;">
            <div style="position:absolute;width:220px;height:220px;top:-60px;right:-40px;border-radius:50%;background:rgba(255,255,255,.06);"></div>
            <div style="position:absolute;width:140px;height:140px;top:20px;right:120px;border-radius:50%;background:rgba(255,255,255,.04);"></div>
        </div>
    </div>

    {{-- MONTH VIEW --}}
    @if($view === 'month')
    <div class="sc-grid-wrap">
        <div class="sc-day-headers">
            @foreach($dayNames as $i => $d)
            <div class="sc-day-hdr {{ in_array($i,[0,6]) ? 'weekend' : '' }}">{{ $d }}</div>
            @endforeach
        </div>
        <div class="sc-days">
            @for($i = 0; $i < $firstDay; $i++)
            <div class="sc-cell empty"></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $dateStr   = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $isToday   = $dateStr === $today;
                $events    = $eventsByDay->get($day, collect());
                $col       = ($firstDay + $day - 1) % 7;
                $isWeekend = $col === 0 || $col === 6;
                $cls       = $isToday ? 'today' : ($isWeekend ? 'weekend' : '');
            @endphp
            <div class="sc-cell {{ $cls }}">
                <span class="sc-day-num">{{ $day }}</span>
                @foreach($events->take(5) as $ev)
                <div class="sc-event type-{{ $ev['type'] }}"
                     onclick="showScModal({{ json_encode($ev) }})"
                     title="{{ $ev['label'] }}{{ $ev['sub'] ? ' — '.$ev['sub'] : '' }}">
                    {{ $ev['label'] }}
                </div>
                @endforeach
                @if($events->count() > 5)
                <div class="sc-more" onclick="event.stopPropagation(); showScDayEvents('{{ $dateStr }}')">+{{ $events->count()-5 }} more</div>
                @endif
            </div>
            @endfor

            @php $rem = ($firstDay + $daysInMonth) % 7; @endphp
            @if($rem > 0)
                @for($i = 0; $i < (7 - $rem); $i++)
                <div class="sc-cell empty"></div>
                @endfor
            @endif
        </div>
    </div>
    @endif

    {{-- LIST VIEW --}}
    @if($view === 'list')
    <div class="sc-list-wrap">
        @if($allEvents->isEmpty())
        <div class="sc-list-empty">
            <svg fill="none" stroke="#cbd5e1" viewBox="0 0 24 24" style="width:40px;height:40px;margin-bottom:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <div style="font-size:14px;font-weight:600;">No events this month</div>
        </div>
        @else
        @php $grouped = $allEvents->groupBy('date'); @endphp
        @foreach($grouped as $date => $dayEvents)
        @php $dt = \Carbon\Carbon::parse($date); @endphp
        <div class="sc-list-group">
            <div class="sc-list-date-hdr">
                {{ $dt->format('l, F j, Y') }}
                <span style="margin-left:8px;background:#e2e8f0;border-radius:20px;padding:1px 8px;font-size:10px;">{{ $dayEvents->count() }} event{{ $dayEvents->count()>1?'s':'' }}</span>
            </div>
            @foreach($dayEvents as $ev)
            @php
                $cfg = $typeConfig[$ev['type']] ?? ['color'=>'#64748b','bg'=>'#f1f5f9','label'=>$ev['type']];
            @endphp
            <div class="sc-list-item" onclick="showScModal({{ json_encode($ev) }})">
                <span class="sc-list-badge" style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};">
                    {{ $cfg['label'] }}
                </span>
                <div class="sc-list-main">
                    <div class="sc-list-name">{{ $ev['label'] }}</div>
                    <div class="sc-list-meta">
                        {{ $ev['sub'] ?? '—' }}
                        @if(!empty($ev['agent'])) &bull; {{ $ev['agent'] }} @endif
                        @if(!empty($ev['time'])) &bull; {{ \Carbon\Carbon::parse($ev['time'])->format('g:i A') }} @endif
                    </div>
                </div>
                @if($ev['amount'])
                <div class="sc-list-amount">&#8369;{{ number_format($ev['amount'], 2) }}</div>
                @endif
                @if(!empty($ev['status']))
                <span style="font-size:10px;padding:2px 8px;border-radius:20px;background:#f1f5f9;color:#64748b;font-weight:600;">{{ $ev['status'] }}</span>
                @endif
            </div>
            @endforeach
        </div>
        @endforeach
        @endif
    </div>
    @endif

{{-- Day Events Modal --}}
<div id="scDayModal" class="sc-modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="sc-modal-box" style="max-height:80vh;display:flex;flex-direction:column;">
        <div class="sc-modal-hdr" style="background:linear-gradient(135deg,#1e4575dd,#1e4575);flex-shrink:0;">
            <div>
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px;opacity:.75;color:white;">All Events</div>
                <div id="scDayModalTitle" style="color:white;font-size:16px;font-weight:700;"></div>
            </div>
            <button class="sc-modal-close" onclick="document.getElementById('scDayModal').style.display='none'">&times;</button>
        </div>
        <div class="sc-modal-body" style="overflow-y:auto;" id="scDayModalBody"></div>
    </div>
</div>

{{-- Event Detail Modal --}}
<div id="scModal" class="sc-modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="sc-modal-box">
        <div class="sc-modal-hdr" id="scModalHdr">
            <div>
                <div id="scModalType" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px;opacity:.75;color:white;"></div>
                <div id="scModalTitle" style="color:white;font-size:16px;font-weight:700;"></div>
            </div>
            <button class="sc-modal-close" onclick="document.getElementById('scModal').style.display='none'">&times;</button>
        </div>
        <div class="sc-modal-body">
            <div class="sc-modal-grid" id="scModalGrid"></div>
        </div>
    </div>
</div>

<script>
const scTypeConfig = @json($typeConfig);
const scAllEvents = @json($allEvents);

function showScModal(ev) {
    const cfg = scTypeConfig[ev.type] || {color:'#1e4575', bg:'#eff6ff', label: ev.type};
    const fmt = v => v ? '\u20B1' + parseFloat(v).toLocaleString('en-US',{minimumFractionDigits:2}) : '—';
    const fmtDate = v => { if(!v) return '—'; try { return new Date(v).toLocaleDateString('en-US',{month:'long',day:'numeric',year:'numeric'}); } catch(e){ return v; } };

    document.getElementById('scModalHdr').style.background = `linear-gradient(135deg, ${cfg.color}dd, ${cfg.color})`;
    document.getElementById('scModalType').textContent = cfg.label;
    document.getElementById('scModalTitle').textContent = ev.label || '—';

    const fields = [];
    if (ev.type === 'trip') {
        fields.push(['Client', ev.label||'—', false]);
        fields.push(['Property', ev.sub||'—', false]);
        fields.push(['Agent', ev.agent||'—', false]);
        fields.push(['Date', fmtDate(ev.date), false]);
        if (ev.time) fields.push(['Time', ev.time, false]);
        fields.push(['Status', ev.status ? ev.status.charAt(0).toUpperCase()+ev.status.slice(1) : '—', false]);
    } else {
        fields.push(['Client', ev.label||'—', false]);
        fields.push(['Project', ev.sub||'—', false]);
        fields.push(['Agent', ev.agent||'—', false]);
        fields.push(['Date', fmtDate(ev.date), false]);
        if (ev.amount) fields.push(['Amount', fmt(ev.amount), true]);
        fields.push(['Status', ev.status ? ev.status.charAt(0).toUpperCase()+ev.status.slice(1) : '—', false]);
    }

    document.getElementById('scModalGrid').innerHTML = fields.map(([lbl,val,hi]) => `
        <div class="sc-modal-field">
            <div class="sc-modal-field-lbl">${lbl}</div>
            <div class="sc-modal-field-val" style="color:${hi ? cfg.color : '#1e293b'};font-weight:${hi?'700':'600'}">${val}</div>
        </div>
    `).join('');

    document.getElementById('scModal').style.display = 'flex';
}

function showScDayEvents(dateStr) {
    const dayEvents = scAllEvents.filter(e => e.date && e.date.slice(0,10) === dateStr);
    window._scDayEvents = dayEvents;

    const dt = new Date(dateStr + 'T00:00:00');
    document.getElementById('scDayModalTitle').textContent = dt.toLocaleDateString('en-US',{month:'long',day:'numeric',year:'numeric'});

    document.getElementById('scDayModalBody').innerHTML = dayEvents.map((ev, i) => {
        const cfg = scTypeConfig[ev.type] || {color:'#1e4575', bg:'#eff6ff', label: ev.type};
        return `
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border-radius:8px;border:1px solid #f1f5f9;margin-bottom:8px;cursor:pointer;"
             onclick="document.getElementById('scDayModal').style.display='none';showScModal(window._scDayEvents[${i}])">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:${cfg.bg};color:${cfg.color};">${cfg.label}</span>
                <span style="font-size:13px;font-weight:600;color:#1e293b;">${ev.label || '—'}</span>
            </div>
        </div>`;
    }).join('') || '<div style="text-align:center;color:#94a3b8;font-size:13px;padding:20px;">No events found.</div>';

    document.getElementById('scDayModal').style.display = 'flex';
}
</script>
@endsection
