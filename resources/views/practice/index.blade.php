@extends('layouts.academy')
@section('title', 'Persuasion Practice · ArkCrest Sales Academy')

@section('content')
@php
    $diffMeta = [
        'EASY'   => ['label' => 'Easy',   'num' => '01', 'accent' => '#4ade80', 'pillBg' => 'rgba(74,222,128,.12)'],
        'MEDIUM' => ['label' => 'Medium', 'num' => '02', 'accent' => '#f0b429', 'pillBg' => 'rgba(240,180,41,.14)'],
        'HARD'   => ['label' => 'Hard',   'num' => '03', 'accent' => '#f87171', 'pillBg' => 'rgba(248,113,113,.12)'],
    ];
    $totalScenarios = $stats['total_scenarios'] ?? 0;
    $sessionsCompleted = $stats['sessions_completed'] ?? 0;
    $bestScore = $stats['best_score'] ?? null;
@endphp

<style>
.pa-page { display:flex; flex-direction:column; gap:28px; font-family:inherit; }

/* ── Hero ─────────────────────────────────────────────────────────── */
.pa-hero {
    position:relative; overflow:hidden; border-radius:20px;
    background:
        radial-gradient(1100px 420px at 88% -10%, rgba(240,180,41,.14), transparent 60%),
        linear-gradient(155deg, #0a1224 0%, #0f1c3d 55%, #0a1224 100%);
    padding:44px 44px 40px;
    display:grid; grid-template-columns:1fr 300px; gap:32px; align-items:center;
    box-shadow:0 18px 40px rgba(10,18,36,.35);
}
.pa-hero::after {
    content:''; position:absolute; inset:0; pointer-events:none; opacity:.5;
    background-image:radial-gradient(1px 1px at 20% 30%, rgba(255,255,255,.18) 1px, transparent 0),
        radial-gradient(1px 1px at 70% 65%, rgba(255,255,255,.12) 1px, transparent 0),
        radial-gradient(1px 1px at 40% 80%, rgba(255,255,255,.10) 1px, transparent 0);
}
.pa-eyebrow-row { display:flex; align-items:center; gap:10px; margin-bottom:16px; position:relative; z-index:2; }
.pa-eyebrow-dash { width:22px; height:1.5px; background:#f0b429; display:inline-block; }
.pa-eyebrow { font-size:11.5px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#f0b429; }
.pa-headline { position:relative; z-index:2; font-family:Georgia,'Times New Roman',serif; font-size:38px; line-height:1.18; color:#f4f6fb; margin:0 0 14px; font-weight:400; }
.pa-headline em { font-style:italic; color:#f0b429; font-weight:400; }
.pa-sub { position:relative; z-index:2; font-size:14px; line-height:1.6; color:rgba(226,232,245,.72); max-width:520px; margin:0 0 24px; }
.pa-hero-actions { position:relative; z-index:2; display:flex; gap:12px; }
.pa-btn-gold {
    display:inline-flex; align-items:center; gap:8px; padding:12px 22px; border-radius:10px;
    background:linear-gradient(135deg,#f7ca4d,#e6a917); color:#1a1400; font-weight:700; font-size:13px;
    border:none; cursor:pointer; text-decoration:none; box-shadow:0 8px 20px rgba(230,169,23,.28);
}
.pa-btn-gold:hover { filter:brightness(1.05); }
.pa-btn-ghost {
    display:inline-flex; align-items:center; gap:8px; padding:12px 20px; border-radius:10px;
    background:rgba(255,255,255,.06); color:#e6ebf5; font-weight:600; font-size:13px;
    border:1px solid rgba(255,255,255,.16); text-decoration:none;
}
.pa-btn-ghost:hover { background:rgba(255,255,255,.11); }

/* ── Hero side panel ─────────────────────────────────────────────── */
.pa-panel {
    position:relative; z-index:2; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.12);
    border-radius:16px; padding:22px; backdrop-filter:blur(6px);
}
.pa-panel-label { font-size:10.5px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:rgba(226,232,245,.55); margin-bottom:6px; }
.pa-panel-title { font-size:16px; font-weight:700; color:#f4f6fb; margin-bottom:16px; }
.pa-stat-row { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
.pa-stat {
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08); border-radius:10px;
    padding:12px 6px; text-align:center;
}
.pa-stat-num { font-size:19px; font-weight:800; color:#f0b429; line-height:1.1; }
.pa-stat-label { font-size:9.5px; letter-spacing:.5px; text-transform:uppercase; color:rgba(226,232,245,.55); margin-top:4px; }

/* ── Difficulty sections ──────────────────────────────────────────── */
.pa-section-head { display:flex; align-items:baseline; gap:12px; margin-bottom:14px; }
.pa-section-title { font-size:19px; font-weight:700; color:#0f172a; margin:0; }
.pa-section-sub { font-size:12.5px; color:#94a3b8; }

.pa-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(270px,1fr)); gap:16px; margin-bottom:8px; }

.pa-card {
    position:relative; background:#fff; border:1px solid #e8ecf2; border-radius:16px; padding:20px;
    display:flex; flex-direction:column; gap:12px; transition:box-shadow .18s, transform .18s;
}
.pa-card:hover { box-shadow:0 14px 30px rgba(15,23,42,.08); transform:translateY(-3px); }
.pa-card-badge {
    width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center;
    font-size:12px; font-weight:800; flex-shrink:0;
}
.pa-card-head { display:flex; align-items:center; gap:12px; }
.pa-card-avatar {
    width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#0f1c3d,#1e3a6b);
    display:flex; align-items:center; justify-content:center; color:#f0b429; font-weight:800; font-size:15px; flex-shrink:0;
}
.pa-card-name { font-size:14.5px; font-weight:700; color:#101828; line-height:1.3; }
.pa-card-buyer { font-size:11.5px; color:#94a3b8; }
.pa-card-tagline { font-size:12.5px; color:#54607a; line-height:1.55; flex:1; }
.pa-card-btn {
    border:none; background:#0f1c3d; color:#f4f6fb; padding:10px 14px; border-radius:9px;
    font-size:12.5px; font-weight:700; cursor:pointer; width:100%; text-align:center;
    transition:background .15s;
}
.pa-card-btn:hover { background:#1a2c54; }

.pa-empty {
    text-align:center; color:#94a3b8; font-size:13px; padding:26px; background:#fafbfc;
    border-radius:14px; border:1px dashed #e2e8f0; margin-bottom:8px;
}

@media (max-width: 860px) {
    .pa-hero { grid-template-columns:1fr; padding:30px 24px; }
    .pa-headline { font-size:28px; }
}
@media (max-width: 380px) {
    .pa-stat-row { grid-template-columns:1fr; gap:6px; }
}
</style>

<div class="pa-page">
    <div class="pa-hero">
        <div>
            <div class="pa-eyebrow-row">
                <span class="pa-eyebrow-dash"></span>
                <span class="pa-eyebrow">ArkCrest Sales Academy</span>
            </div>
            <h1 class="pa-headline">Practice the pitch.<br>Handle the pushback. <em>Close with integrity.</em></h1>
            <p class="pa-sub">Roleplay live against an AI buyer persona built from real objections agents hear in the field. Pick a difficulty, work the conversation, and get scored on rapport, objection handling, and closing technique.</p>
            <div class="pa-hero-actions">
                <a href="#pa-easy" class="pa-btn-gold">▶ Start Practicing</a>
                <a href="{{ route('practice.history') }}" class="pa-btn-ghost">☰ My Past Sessions</a>
            </div>
        </div>

        <div class="pa-panel">
            <div class="pa-panel-label">Your Progress</div>
            <div class="pa-panel-title">Persuasion Practice</div>
            <div class="pa-stat-row">
                <div class="pa-stat">
                    <div class="pa-stat-num">{{ $totalScenarios }}</div>
                    <div class="pa-stat-label">Scenarios</div>
                </div>
                <div class="pa-stat">
                    <div class="pa-stat-num">{{ $sessionsCompleted }}</div>
                    <div class="pa-stat-label">Completed</div>
                </div>
                <div class="pa-stat">
                    <div class="pa-stat-num">{{ $bestScore !== null ? $bestScore : '—' }}</div>
                    <div class="pa-stat-label">Best Score</div>
                </div>
            </div>
        </div>
    </div>

    @forelse($diffMeta as $key => $meta)
        <div id="pa-{{ strtolower($key) }}">
            <div class="pa-section-head">
                <span class="pa-card-badge" style="background:{{ $meta['pillBg'] }};color:{{ $meta['accent'] }};">{{ $meta['num'] }}</span>
                <h2 class="pa-section-title">{{ $meta['label'] }} Buyers</h2>
                <span class="pa-section-sub">{{ ($scenarios[$key] ?? collect())->count() }} scenario{{ ($scenarios[$key] ?? collect())->count() === 1 ? '' : 's' }}</span>
            </div>

            @if(($scenarios[$key] ?? collect())->isEmpty())
                <div class="pa-empty">No {{ strtolower($meta['label']) }} scenarios available yet.</div>
            @else
                <div class="pa-grid">
                    @foreach($scenarios[$key] as $scenario)
                        <div class="pa-card">
                            <div class="pa-card-head">
                                <div class="pa-card-avatar">{{ strtoupper(substr($scenario->buyer_name, 0, 1)) }}</div>
                                <div>
                                    <div class="pa-card-name">{{ $scenario->name }}</div>
                                    <div class="pa-card-buyer">{{ $scenario->buyer_name }}</div>
                                </div>
                            </div>
                            <div class="pa-card-tagline">{{ $scenario->tagline }}</div>
                            <form method="POST" action="{{ route('practice.start', $scenario) }}">
                                @csrf
                                <button type="submit" class="pa-card-btn">Start Practice</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="pa-empty">No scenarios have been set up yet.</div>
    @endforelse
</div>
@endsection