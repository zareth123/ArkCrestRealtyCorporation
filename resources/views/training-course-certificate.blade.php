@extends('layouts.academy')

@section('title', 'Course Completion Certificate · ArkCrest Sales Academy')

@section('content')
    <div class="crs-cert-page">
        <div class="crs-cert-head">
            <div class="crs-cert-eyebrow">🎓 Course Completion Certificate</div>
            <h1>{{ $trainingName }}, your certificate is ready.</h1>
            <p>This is a preview of your personalized ArkCrest Real Estate Agent Training certificate. The design stays exactly as issued — only your name and completion date are filled in.</p>
            <a href="{{ route('agent-training.certificate.download') }}" class="crs-cert-download-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                Download PDF
            </a>
        </div>

        <div class="crs-cert-frame">
            <canvas id="crsCertCanvas" aria-label="Certificate of Completion preview"></canvas>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        (function () {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            const canvas = document.getElementById('crsCertCanvas');
            const ctx = canvas.getContext('2d');
            let pdfDoc = null;
            let renderTask = null;

            async function renderPage() {
                if (!pdfDoc) return;
                const frame = canvas.parentElement;
                const cssWidth = frame.clientWidth;
                const cssHeight = frame.clientHeight;
                if (!cssWidth || !cssHeight) return;

                const page = await pdfDoc.getPage(1);
                const baseViewport = page.getViewport({ scale: 1 });
                const scale = Math.min(cssWidth / baseViewport.width, cssHeight / baseViewport.height);
                const dpr = window.devicePixelRatio || 1;
                const viewport = page.getViewport({ scale: scale * dpr });

                canvas.width = viewport.width;
                canvas.height = viewport.height;
                canvas.style.width = (viewport.width / dpr) + 'px';
                canvas.style.height = (viewport.height / dpr) + 'px';

                if (renderTask) {
                    try { renderTask.cancel(); } catch (e) {}
                }
                renderTask = page.render({ canvasContext: ctx, viewport: viewport });
                try { await renderTask.promise; } catch (e) {}
            }

            pdfjsLib.getDocument('{{ route('agent-training.certificate.preview') }}').promise
                .then(function (doc) {
                    pdfDoc = doc;
                    renderPage();
                });

            let resizeTimer;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(renderPage, 120);
            });
        })();
    </script>

    <style>
        .crs-cert-page { max-width: 900px; margin: 0 auto; }
        .crs-cert-head { text-align: center; margin-bottom: 22px; }
        .crs-cert-eyebrow { color: var(--gold); font-size: 11.5px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; }
        .crs-cert-head h1 { margin: 8px 0 10px; color: #14243a; font-size: 24px; }
        .crs-cert-head p { max-width: 560px; margin: 0 auto 20px; color: #536278; font-size: 13px; line-height: 1.7; }
        .crs-cert-download-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 26px;
            border-radius: 10px;
            color: #14243a;
            background: linear-gradient(120deg, var(--gold), var(--gold-light));
            font-size: 12.5px;
            font-weight: 800;
            text-decoration: none;
            transition: .15s ease;
        }
        .crs-cert-download-btn:hover { filter: brightness(1.03); }
        .crs-cert-download-btn svg { width: 16px; height: 16px; }

        .crs-cert-frame {
            position: relative;
            width: 100%;
            padding-top: 77.27%; /* 792:612 Letter-landscape aspect ratio */
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 20px 60px rgba(8, 21, 35, .08);
        }
        .crs-cert-frame canvas {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            user-select: none;
        }
    </style>
@endsection
