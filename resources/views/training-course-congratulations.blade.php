@extends('layouts.academy')

@section('title', 'Course Completed! · ArkCrest Sales Academy')

@section('content')
    <canvas id="crsConfettiCanvas" style="position:fixed; inset:0; width:100%; height:100%; pointer-events:none; z-index:1200;"></canvas>

    <div class="crs-congrats-card">
        <div class="crs-congrats-icon">🎉</div>
        <h1>Congratulations!</h1>
        <p>Congratulations on successfully completing the ArkCrest Real Estate Agent Training Online Course!</p>
        <p>You have completed all learning modules and successfully passed every required assessment. Your dedication and hard work have prepared you with the knowledge and skills needed to represent ArkCrest professionally.</p>
        <p>Your Course Completion Certificate is now available and can be accessed anytime from the navigation menu.</p>

        <a href="{{ route('agent-training') }}" class="crs-congrats-btn">Continue to Training Home</a>
    </div>

    <style>
        .crs-congrats-card {
            max-width: 560px;
            margin: 40px auto 0;
            padding: 44px 36px;
            border-radius: 20px;
            text-align: center;
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 20px 60px rgba(8, 21, 35, .08);
        }
        .crs-congrats-icon { font-size: 46px; line-height: 1; }
        .crs-congrats-card h1 { margin: 14px 0 18px; color: #14243a; font-size: 26px; }
        .crs-congrats-card p { margin: 0 0 14px; color: #536278; font-size: 13.5px; line-height: 1.7; }
        .crs-congrats-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 18px;
            padding: 14px 30px;
            border-radius: 10px;
            color: #14243a;
            background: linear-gradient(120deg, var(--gold), var(--gold-light));
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            transition: .15s ease;
        }
        .crs-congrats-btn:hover { filter: brightness(1.03); }

        @media (max-width: 560px) {
            .crs-congrats-card { padding: 32px 22px; }
        }
    </style>
@endsection

@push('academy-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/canvas-confetti/1.9.3/confetti.browser.min.js"></script>
<script>
    (function () {
        var canvas = document.getElementById('crsConfettiCanvas');
        if (!canvas || typeof confetti === 'undefined') return;

        // Plays once on load — no loop, no re-trigger on scroll/resize.
        var burst = confetti.create(canvas, { resize: true, useWorker: true });
        burst({ particleCount: 110, spread: 70, startVelocity: 45, origin: { y: 0.6 } });
        setTimeout(function () {
            burst({ particleCount: 80, spread: 100, startVelocity: 35, origin: { y: 0.5 } });
        }, 250);
    })();
</script>
@endpush
