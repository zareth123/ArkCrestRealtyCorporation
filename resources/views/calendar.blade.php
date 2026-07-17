@extends('layouts.dashboard')

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
    $totalEvents = collect($releasesByDay ?? [])->sum(fn($e) => count($e));
    $view        = $view ?? 'month';
@endphp

<style>
.cal-page { display:flex;flex-direction:column;height:calc(100vh - 62px - 20px);gap:0; }

/* Top bar */
.cal-topbar {
    display:flex;align-items:center;justify-content:space-between;
    padding:0 0 16px;flex-shrink:0;
}
.cal-page-title { font-size:28px;font-weight:700;color:#1e4575;letter-spacing:-.3px; }
.cal-page-sub { font-size:12px;color:#94a3b8;margin-top:2px; }
.cal-controls { display:flex;align-items:center;gap:10px; }
.cal-nav-btn {
    display:inline-flex;align-items:center;justify-content:center;
    width:32px;height:32px;border-radius:8px;
    background:white;border:1.5px solid #e2e8f0;
    color:#1e4575;text-decoration:none;font-size:16px;font-weight:700;
    transition:all .2s;
}
.cal-nav-btn:hover { background:#1e4575;color:white;border-color:#1e4575; }
.cal-month-pill {
    background:linear-gradient(135deg,#1e4575,#2563eb);
    color:white;padding:6px 20px;border-radius:20px;
    font-size:14px;font-weight:700;letter-spacing:.3px;
    min-width:160px;text-align:center;
}
.cal-today-btn {
    padding:6px 14px;background:white;color:#1e4575;
    border:1.5px solid #1e4575;border-radius:8px;
    text-decoration:none;font-size:12px;font-weight:600;
    transition:all .2s;
}
.cal-today-btn:hover { background:#1e4575;color:white; }
.cal-year-sel {
    padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:8px;
    font-size:13px;font-weight:500;color:#374151;background:white;
    cursor:pointer;outline:none;
}

/* Stats bar */
.cal-stats {
    display:flex;gap:12px;margin-bottom:14px;flex-shrink:0;
}
.cal-stat-card {
    background:white;border:1px solid #e8ecf0;border-radius:10px;
    padding:10px 16px;display:flex;align-items:center;gap:10px;
    box-shadow:0 1px 4px rgba(0,0,0,.04);
}
.cal-stat-icon {
    width:32px;height:32px;border-radius:8px;
    background:linear-gradient(135deg,#e8edf5,#dce6f5);
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.cal-stat-val { font-size:16px;font-weight:700;color:#1e4575; }
.cal-stat-lbl { font-size:10px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.4px; }

/* Calendar grid */
.cal-grid-wrap {
    flex:1;background:white;border-radius:12px;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
    overflow:hidden;border:1px solid #e8ecf0;
    display:flex;flex-direction:column;min-height:0;
}
.cal-day-headers {
    display:grid;grid-template-columns:repeat(7,1fr);
    background:#f8fafc;border-bottom:2px solid #e8ecf0;
    flex-shrink:0;
}
.cal-day-hdr {
    padding:10px 0;text-align:center;
    font-size:11px;font-weight:700;color:#64748b;
    letter-spacing:.6px;text-transform:uppercase;
}
.cal-day-hdr.weekend { color:#94a3b8; }
.cal-days {
    display:grid;grid-template-columns:repeat(7,1fr);
    flex:1;min-height:0;
}
.cal-cell {
    border-right:1px solid #d1d5db;border-bottom:1px solid #d1d5db;
    padding:6px 7px;display:flex;flex-direction:column;
    overflow:hidden;transition:background .15s;
}
.cal-cell:nth-child(7n) { border-right:none; }
.cal-cell.empty { background:#fafbfc; }
.cal-cell.weekend { background:#fafbfc; }
.cal-cell.today { background:linear-gradient(135deg,#eff6ff,#e8f0fe); }
.cal-cell:not(.empty):not(.today):hover { background:#f8faff; }
.cal-day-num {
    display:inline-flex;align-items:center;justify-content:center;
    width:22px;height:22px;border-radius:50%;
    font-size:12px;font-weight:700;color:#1e293b;
    align-self:flex-end;flex-shrink:0;margin-bottom:3px;
}
.cal-cell.today .cal-day-num {
    background:linear-gradient(135deg,#1e4575,#2563eb);
    color:white;font-weight:700;
    box-shadow:0 2px 6px rgba(30,69,117,.3);
}
.cal-cell.weekend .cal-day-num { color:#94a3b8; }
.cal-event {
    border-radius:4px;padding:2px 6px;
    font-size:10px;margin-bottom:2px;cursor:pointer;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    flex-shrink:0;transition:opacity .15s;
    box-shadow:0 1px 3px rgba(0,0,0,.15);
    font-weight:600;background:#059669;color:white;
}
.cal-event:hover { opacity:.85; }
.cal-more {
    font-size:9px;color:#94a3b8;text-align:right;
    margin-top:1px;flex-shrink:0;font-weight:600;
    cursor:pointer;text-decoration:underline;
}
.cal-more:hover { color:#1e4575; }

/* Legend */
.cal-legend {
    display:flex;align-items:center;gap:16px;
    padding:10px 0 0;font-size:11px;color:#64748b;flex-shrink:0;
}
</style>

<div class="cal-page">
    {{-- Top Bar --}}
    <div class="cal-topbar" style="background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:20px;padding:36px 40px;margin-bottom:16px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(30,69,117,.25);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
        <div style="position:relative;z-index:2;">
            <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;">Finance</div>
            <h1 style="font-size:24px;font-weight:700;color:white;margin:0 0 6px;">Calendar</h1>
            <p style="font-size:13px;color:rgba(255,255,255,.75);margin:0;">Commission release schedule &bull; {{ $monthNames[$month] }} {{ $year }}</p>
        </div>
        <div class="cal-controls" style="position:relative;z-index:2;">
            <form method="GET" action="{{ route('calendar') }}" style="display:flex;align-items:center;gap:6px;">
                <select name="month" class="cal-month-sel" onchange="this.form.submit()" style="background:rgba(255,255,255,.15);color:white;border:1.5px solid rgba(255,255,255,.3);border-radius:8px;padding:6px 10px;font-size:13px;font-weight:600;">
                    @foreach($monthNames as $num => $name)
                        @if($num > 0)
                        <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }} style="color:#1e4575;background:white;">{{ $name }}</option>
                        @endif
                    @endforeach
                </select>
                <select name="year" class="cal-year-sel" onchange="this.form.submit()" style="background:rgba(255,255,255,.15);color:white;border:1.5px solid rgba(255,255,255,.3);border-radius:8px;padding:6px 10px;font-size:13px;font-weight:600;">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }} style="color:#1e4575;background:white;">{{ $y }}</option>
                    @endforeach
                </select>
            </form>
            
            <a href="{{ route('calendar', ['month'=>date('n'),'year'=>date('Y')]) }}" class="cal-today-btn" style="background:rgba(255,255,255,.2);color:white;border:1.5px solid rgba(255,255,255,.3);">Today</a>
            </a>
            <a href="{{ route('calendar', ['month'=>$month,'year'=>$year,'view'=>'list']) }}" style="{{ ($view??'month')=='list' ? 'background:rgba(255,255,255,.25);color:white;border-color:rgba(255,255,255,.4);' : 'background:rgba(255,255,255,.1);color:rgba(255,255,255,.8);border-color:rgba(255,255,255,.2);' }} padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;border:1.5px solid;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:13px;height:13px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                List
            </a>
        </div>
        <div style="position:absolute;top:0;right:0;width:300px;height:100%;pointer-events:none;">
            <div style="position:absolute;width:220px;height:220px;top:-60px;right:-40px;border-radius:50%;background:rgba(255,255,255,.06);"></div>
            <div style="position:absolute;width:140px;height:140px;top:20px;right:120px;border-radius:50%;background:rgba(255,255,255,.04);"></div>
        </div>
    </div>

    {{-- Calendar Grid / List --}}
    @if($view === 'list')
    <div style="background:white;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid #e8ecf0;overflow:hidden;flex:1;">
        @if($releases->isEmpty())
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:200px;color:#94a3b8;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:40px;height:40px;margin-bottom:10px;opacity:.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            No releases for {{ $monthNames[$month] }} {{ $year }}
        </div>
        @else
        <div class="tbl-wrap" style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
        <table style="width:100%;border-collapse:collapse;min-width:700px;">
            <thead><tr style="background:linear-gradient(135deg,#0f2a4a,#1e4575);">
                @foreach(['Date Released','Agent','Client','Project','Net TCP','Commission','Status'] as $h)
                <th style="padding:12px 16px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.7px;white-space:nowrap;">{{ $h }}</th>
                @endforeach
            </tr></thead>
            <tbody>
            @foreach($releases as $r)
            <tr style="border-bottom:1px solid #f1f5f9;cursor:pointer;" onclick="showEventDetail('{{ $r->_type }}', {{ $r->id }})" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:11px 16px;font-size:13px;font-weight:600;color:#059669;white-space:nowrap;">{{ $r->date_released ? $r->date_released->format('M d, Y') : ' ' }}</td>
                <td style="padding:11px 16px;font-size:13px;color:#0f172a;font-weight:600;">{{ $r->agent_name ?? ' ' }}</td>
                <td style="padding:11px 16px;font-size:13px;color:#374151;">{{ $r->client_name ?? ' ' }}</td>
                <td style="padding:11px 16px;font-size:13px;color:#374151;">{{ $r->project_name ?? ' ' }}</td>
                <td style="padding:11px 16px;font-size:13px;color:#374151;">{{ $r->net_tcp ? '?'.number_format($r->net_tcp,2) : ' ' }}</td>
                <td style="padding:11px 16px;font-size:13px;font-weight:700;color:#059669;">{{ $r->commission ? '?'.number_format($r->commission,2) : ' ' }}</td>
                <td style="padding:11px 16px;"><span style="background:#dcfce7;color:#166534;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;">{{ $r->status ?? ' ' }}</span></td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
    @else
    <div class="cal-grid-wrap">
        <div class="cal-day-headers">
            @foreach($dayNames as $i => $d)
            <div class="cal-day-hdr {{ in_array($i,[0,6]) ? 'weekend' : '' }}">{{ $d }}</div>
            @endforeach
        </div>
        <div class="cal-days">
            @for($i = 0; $i < $firstDay; $i++)
            <div class="cal-cell empty"></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $dateStr   = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $isToday   = $dateStr === $today;
                $events    = $releasesByDay->get($day, collect());
                $col       = ($firstDay + $day - 1) % 7;
                $isWeekend = $col === 0 || $col === 6;
                $cls       = $isToday ? 'today' : ($isWeekend ? 'weekend' : '');
            @endphp
            <div class="cal-cell {{ $cls }}">
                <span class="cal-day-num">{{ $day }}</span>
                @foreach($events->take(2) as $event)
                <div class="cal-event" onclick="showEventDetail('{{ $event->_type }}', {{ $event->id }})" title="{{ $event->client_name }}">
                    {{ $event->client_name }}
                </div>
                @endforeach
                @if($events->count() > 2)
                <div class="cal-more" onclick="event.stopPropagation(); showDayEvents('{{ $dateStr }}')">+{{ $events->count()-2 }} more</div>
                @endif
            </div>
            @endfor

            @php $rem = ($firstDay + $daysInMonth) % 7; @endphp
            @if($rem > 0)
                @for($i = 0; $i < (7 - $rem); $i++)
                <div class="cal-cell empty"></div>
                @endfor
            @endif
        </div>
    </div>
    @endif

</div>

{{-- Day Events Modal --}}
<div id="calDayModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:white;border-radius:14px;width:480px;max-width:95vw;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);padding:18px 22px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div>
                <div style="color:rgba(255,255,255,.65);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px;">All Releases</div>
                <div style="color:white;font-size:16px;font-weight:700;" id="calDayModalTitle"> </div>
            </div>
            <button onclick="document.getElementById('calDayModal').style.display='none'" style="background:rgba(255,255,255,.15);border:none;color:white;width:28px;height:28px;border-radius:7px;cursor:pointer;font-size:16px;line-height:1;">&times;</button>
        </div>
        <div style="padding:14px 16px;overflow-y:auto;" id="calDayModalBody"></div>
    </div>
</div>

{{-- Event Detail Modal --}}
<div id="calEventModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:white;border-radius:14px;width:440px;max-width:95vw;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="background:linear-gradient(135deg,#1e4575,#2563eb);padding:18px 22px;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="color:rgba(255,255,255,.65);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px;">Release Details</div>
                <div style="color:white;font-size:16px;font-weight:700;" id="calModalTitle"> </div>
            </div>
            <button onclick="document.getElementById('calEventModal').style.display='none'" style="background:rgba(255,255,255,.15);border:none;color:white;width:28px;height:28px;border-radius:7px;cursor:pointer;font-size:16px;line-height:1;">&times;</button>
        </div>
        <div style="padding:20px 22px;" id="calEventBody"></div>
    </div>
</div>

<script>
const calEvents = @json($releases->values());
function showEventDetail(type, id) {
    const ev = calEvents.find(e => e.id == id && e._type == type);
    if (!ev) return;
    const fmt = v => v ? '\u20B1' + parseFloat(v).toLocaleString('en-US',{minimumFractionDigits:2}) : ' ';
    const fmtDate = v => { if(!v) return ' '; try { return new Date(v).toLocaleDateString('en-US',{month:'long',day:'numeric',year:'numeric'}); } catch(e){ return v; } };
    document.getElementById('calModalTitle').textContent = ev.client_name || ' ';
    document.getElementById('calEventBody').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            ${[
                ['Date Released', fmtDate(ev.date_released), false],
                ['Agent', ev.agent_name||' ', false],
                ['Project', ev.project_name||' ', false],
                ['Net TCP', fmt(ev.net_tcp), false],
                ['Commission', fmt(ev.commission), true],
                ['Status', ev.status||' ', false],
            ].map(([lbl,val,highlight]) => `
                <div style="background:#f8fafc;border-radius:8px;padding:10px 12px;border:1px solid #f1f5f9;">
                    <div style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">${lbl}</div>
                    <div style="font-size:13px;font-weight:${highlight?'700':'600'};color:${highlight?'#059669':'#1e293b'};">${val}</div>
                </div>
            `).join('')}
        </div>`;
    document.getElementById('calEventModal').style.display = 'flex';
}

function showDayEvents(dateStr) {
    const dayEvents = calEvents.filter(e => e.date_released && e.date_released.slice(0,10) === dateStr);
    const fmt = v => v ? '\u20B1' + parseFloat(v).toLocaleString('en-US',{minimumFractionDigits:2}) : ' ';
    const dt = new Date(dateStr + 'T00:00:00');
    document.getElementById('calDayModalTitle').textContent = dt.toLocaleDateString('en-US',{month:'long',day:'numeric',year:'numeric'});
    document.getElementById('calDayModalBody').innerHTML = dayEvents.map(ev => `
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border-radius:8px;border:1px solid #f1f5f9;margin-bottom:8px;cursor:pointer;"
             onclick="document.getElementById('calDayModal').style.display='none';showEventDetail('${ev._type}', ${ev.id})">
            <div>
                <div style="font-size:13px;font-weight:700;color:#1e293b;">${ev.client_name || ' '}</div>
                <div style="font-size:11px;color:#94a3b8;">${ev.agent_name || ' '}</div>
            </div>
            <div style="font-size:12px;font-weight:700;color:#059669;">${fmt(ev.commission)}</div>
        </div>
    `).join('') || '<div style="text-align:center;color:#94a3b8;font-size:13px;padding:20px;">No releases found.</div>';
    document.getElementById('calDayModal').style.display = 'flex';
}
</script>
@endsection
