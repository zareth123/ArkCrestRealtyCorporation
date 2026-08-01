@extends('layouts.academy')
@section('title', 'Practice Session · ArkCrest Sales Academy')

@section('content')
<style>
.pc-page { display:flex;flex-direction:column;height:calc(100vh - 154px);gap:14px; }
.pc-topbar {
    background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);
    border-radius:16px;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;
    flex-shrink:0;box-shadow:0 6px 20px rgba(30,69,117,.2);
}
.pc-buyer-name { font-size:16px;font-weight:700;color:white; }
.pc-buyer-sub { font-size:12px;color:rgba(255,255,255,.7); }
.pc-diff-pill { padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;background:rgba(255,255,255,.18);color:white; }
.pc-end-btn {
    border:1.5px solid rgba(255,255,255,.4);background:rgba(255,255,255,.12);color:white;
    padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;
}
.pc-end-btn:hover { background:rgba(255,255,255,.22); }

.pc-body { flex:1;display:flex;flex-direction:column;background:white;border:1px solid #e8ecf0;border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.05); }
.pc-messages { flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:10px; }

.pc-bubble-row { display:flex; }
.pc-bubble-row.agent { justify-content:flex-end; }
.pc-bubble-row.buyer { justify-content:flex-start; }
.pc-bubble {
    max-width:65%;padding:10px 14px;border-radius:14px;font-size:13.5px;line-height:1.5;
}
.pc-bubble-row.agent .pc-bubble { background:linear-gradient(135deg,#1e4575,#2563eb);color:white;border-bottom-right-radius:4px; }
.pc-bubble-row.buyer .pc-bubble { background:#f1f5f9;color:#1e293b;border-bottom-left-radius:4px; }
.pc-bubble-error { background:#fff7ed !important; border:1px solid #fed7aa; }
.pc-error-note { font-size:11.5px; font-weight:700; color:#c2410c; margin-bottom:6px; }
.pc-retry-btn {
    margin-top:8px; display:inline-block; border:none; background:#0f1c3d; color:#f4f6fb;
    font-size:12px; font-weight:700; padding:6px 14px; border-radius:7px; cursor:pointer;
}
.pc-retry-btn:hover { background:#1a2c54; }
.pc-retry-btn:disabled { opacity:.6; cursor:default; }

.pc-inputbar { flex-shrink:0;border-top:1px solid #f1f5f9;padding:14px 18px;display:flex;flex-direction:column;gap:8px; }
.pc-inputbar-row { display:flex;gap:10px;align-items:flex-end; }
.pc-attach-btn {
    flex-shrink:0;border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;
    width:42px;height:42px;border-radius:10px;font-size:16px;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
}
.pc-attach-btn:hover { background:#eef2f7; }
.pc-attach-btn:disabled { opacity:.5;cursor:not-allowed; }
.pc-image-preview { display:none;align-items:center;gap:8px; }
.pc-image-preview.active { display:flex; }
.pc-image-preview img { width:52px;height:52px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0; }
.pc-image-preview-remove {
    border:none;background:#fee2e2;color:#b91c1c;width:22px;height:22px;border-radius:50%;
    font-size:12px;font-weight:700;cursor:pointer;line-height:1;
}
.pc-bubble img.pc-msg-image { max-width:220px;max-height:220px;border-radius:10px;display:block;object-fit:cover;cursor:zoom-in; }
.pc-bubble img.pc-msg-image + div { margin-top:6px; }
.pc-lightbox-overlay {
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:10000;
    align-items:center;justify-content:center;cursor:zoom-out;padding:30px;
}
.pc-lightbox-overlay img { max-width:90vw;max-height:90vh;border-radius:8px;box-shadow:0 20px 60px rgba(0,0,0,.4); }
.pc-lightbox-close {
    position:absolute;top:20px;right:26px;color:white;font-size:28px;background:none;border:none;cursor:pointer;
    line-height:1;
}
.pc-send-fail {
    margin-top:4px;font-size:11.5px;color:#c2410c;display:flex;align-items:center;gap:8px;justify-content:flex-end;
}
.pc-send-fail button {
    border:none;background:#0f1c3d;color:#f4f6fb;font-size:11px;font-weight:700;
    padding:3px 10px;border-radius:6px;cursor:pointer;
}
.pc-input {
    flex:1;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:13.5px;
    resize:none;font-family:inherit;
}
.pc-input:focus { outline:none;border-color:#2563eb; }
.pc-send-btn {
    border:none;background:linear-gradient(135deg,#1e4575,#2563eb);color:white;
    padding:0 20px;height:42px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;
    flex-shrink:0;
}
.pc-send-btn:disabled { opacity:.5;cursor:not-allowed; }
.pc-typing { font-size:12px;color:#94a3b8;font-style:italic;padding:0 20px 8px; }

/* Scorecard */
.pc-score-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center; }
.pc-score-box { background:white;border-radius:16px;width:480px;max-width:95vw;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2); }
.pc-score-hdr { padding:20px 24px;background:linear-gradient(135deg,#1e4575,#2563eb);color:white; }
.pc-score-outcome { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;opacity:.85; }
.pc-score-overall { font-size:28px;font-weight:700;margin-top:4px; }
.pc-score-body { padding:20px 24px;display:flex;flex-direction:column;gap:14px; }
.pc-score-rubric { display:grid;grid-template-columns:1fr 1fr;gap:10px; }
.pc-score-metric { background:#f8fafc;border-radius:10px;padding:10px 12px;border:1px solid #f1f5f9; }
.pc-score-metric-lbl { font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px; }
.pc-score-metric-val { font-size:18px;font-weight:700;color:#1e4575; }
.pc-score-summary { font-size:13px;color:#374151;line-height:1.6; }
.pc-score-suggestions { list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px; }
.pc-score-suggestions li { font-size:12.5px;color:#374151;padding:8px 12px;background:#f8fafc;border-radius:8px;border-left:3px solid #2563eb; }
.pc-score-actions { padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;gap:10px; }
.pc-score-actions a {
    flex:1;text-align:center;padding:10px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;
}
.pc-btn-primary { background:linear-gradient(135deg,#1e4575,#2563eb);color:white; }
.pc-btn-secondary { background:#f1f5f9;color:#374151; }
</style>

<div class="pc-page">
    <div class="pc-topbar">
        <div>
            <div class="pc-buyer-name">{{ $session->scenario->buyer_name }}</div>
            <div class="pc-buyer-sub">{{ $session->scenario->name }}</div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="pc-diff-pill">{{ ucfirst(strtolower($session->difficulty)) }}</span>
            @if(!$session->is_finished)
            <button type="button" class="pc-end-btn" onclick="ppEndSession()">End Session</button>
            @endif
        </div>
    </div>

    <div class="pc-body">
        <div class="pc-messages" id="ppMessages">
            @foreach($session->messages as $m)
                <div class="pc-bubble-row {{ strtolower($m->sender) }}">
                    @if($m->is_error)
                        <div class="pc-bubble pc-bubble-error">
                            <div class="pc-error-note">⚠ Couldn't reach the buyer — this isn't a real reply.</div>
                            <div>{{ $m->message }}</div>
                            @if(!$session->is_finished && $m->id === $session->messages->last()->id)
                                <button type="button" class="pc-retry-btn" onclick="ppRetryMessage(this, this.closest('.pc-bubble-row'))">Retry</button>
                            @endif
                        </div>
                    @else
                        <div class="pc-bubble">
                            @if($m->image_path)
                                <img src="{{ $m->image_url }}" class="pc-msg-image" alt="Attached image" onclick="ppOpenLightbox('{{ $m->image_url }}')">
                            @endif
                            @if(trim($m->message) !== '')
                                <div>{{ $m->message }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="pc-typing" id="ppTyping" style="display:none;">{{ $session->scenario->buyer_name }} is typing…</div>
        <div class="pc-inputbar">
            <div class="pc-image-preview" id="ppImagePreview">
                <img id="ppImagePreviewThumb" src="" alt="Selected image">
                <button type="button" class="pc-image-preview-remove" onclick="ppClearImage()">✕</button>
            </div>
            <div class="pc-inputbar-row">
                <input type="file" id="ppImageInput" accept="image/png,image/jpeg,image/gif,image/bmp,image/webp" style="display:none;" onchange="ppOnImageSelected(this)">
                <button type="button" class="pc-attach-btn" id="ppAttachBtn" title="Attach an image" onclick="document.getElementById('ppImageInput').click()" {{ $session->is_finished ? 'disabled' : '' }}>📎</button>
                <textarea id="ppInput" class="pc-input" rows="1" placeholder="Type your message…" {{ $session->is_finished ? 'disabled' : '' }}></textarea>
                <button type="button" id="ppSendBtn" class="pc-send-btn" onclick="ppSendMessage()" {{ $session->is_finished ? 'disabled' : '' }}>Send</button>
            </div>
        </div>
    </div>
</div>

<div class="pc-score-overlay" id="ppScoreOverlay">
    <div class="pc-score-box" id="ppScoreBox"></div>
</div>

<div class="pc-lightbox-overlay" id="ppLightboxOverlay" onclick="ppCloseLightbox()">
    <button type="button" class="pc-lightbox-close" onclick="ppCloseLightbox()">✕</button>
    <img id="ppLightboxImg" src="" alt="Full-size image" onclick="event.stopPropagation()">
</div>

<script>
const ppSessionId = {{ $session->id }};
const ppMessageUrl = @json(route('practice.message', $session));
const ppRetryUrl = @json(route('practice.retry', $session));
const ppEndUrl = @json(route('practice.end', $session));
const ppCsrf = document.querySelector('meta[name=csrf-token]').content;
const ppBuyerName = @json($session->scenario->buyer_name);

@if($session->is_finished && $session->scorecard)
document.addEventListener('DOMContentLoaded', () => ppShowScorecard($session->status ?? 'NOT_SOLD', @json($session->scorecard)));
@endif

function ppOpenLightbox(url) {
    document.getElementById('ppLightboxImg').src = url;
    document.getElementById('ppLightboxOverlay').style.display = 'flex';
}

function ppCloseLightbox() {
    document.getElementById('ppLightboxOverlay').style.display = 'none';
    document.getElementById('ppLightboxImg').src = '';
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') ppCloseLightbox();
});

function ppAppendBubble(sender, text, isError = false, imageUrl = null) {
    const wrap = document.getElementById('ppMessages');
    const row = document.createElement('div');
    row.className = 'pc-bubble-row ' + sender.toLowerCase();
    const bubble = document.createElement('div');
    bubble.className = 'pc-bubble' + (isError ? ' pc-bubble-error' : '');

    if (imageUrl) {
        const img = document.createElement('img');
        img.src = imageUrl;
        img.className = 'pc-msg-image';
        img.alt = 'Attached image';
        img.onclick = () => ppOpenLightbox(imageUrl);
        bubble.appendChild(img);
    }

    if (isError) {
        const warn = document.createElement('div');
        warn.className = 'pc-error-note';
        warn.textContent = "⚠ Couldn't reach the buyer — this isn't a real reply.";
        bubble.appendChild(warn);

        const textEl = document.createElement('div');
        textEl.textContent = text;
        bubble.appendChild(textEl);

        const retryBtn = document.createElement('button');
        retryBtn.type = 'button';
        retryBtn.className = 'pc-retry-btn';
        retryBtn.textContent = 'Retry';
        retryBtn.onclick = () => ppRetryMessage(retryBtn, row);
        bubble.appendChild(retryBtn);
    } else if (text) {
        const textEl = document.createElement('div');
        textEl.textContent = text;
        bubble.appendChild(textEl);
    }

    row.appendChild(bubble);
    wrap.appendChild(row);
    wrap.scrollTop = wrap.scrollHeight;
    return row;
}

/** Pulls the most useful human-readable error out of a failed fetch response body. */
function ppExtractErrorMessage(data) {
    if (data && data.errors) {
        const first = Object.values(data.errors)[0];
        if (Array.isArray(first) && first.length) return first[0];
    }
    if (data && data.message) return data.message;
    if (data && data.error) return data.error;
    return 'Something went wrong. Please try again.';
}

/** Marks an agent's own message row as failed-to-send, with the real reason and a Retry action. */
function ppMarkSendFailed(row, message, onRetry) {
    const existing = row.querySelector('.pc-send-fail');
    if (existing) existing.remove();

    const note = document.createElement('div');
    note.className = 'pc-send-fail';

    const label = document.createElement('span');
    label.textContent = '⚠ Not sent — ' + message;
    note.appendChild(label);

    const retryBtn = document.createElement('button');
    retryBtn.type = 'button';
    retryBtn.textContent = 'Retry';
    retryBtn.onclick = () => {
        note.remove();
        onRetry();
    };
    note.appendChild(retryBtn);

    row.appendChild(note);
}

async function ppRetryMessage(btn, row) {
    btn.disabled = true;
    btn.textContent = 'Retrying…';
    ppSetSending(true);

    try {
        const res = await fetch(ppRetryUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': ppCsrf, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (!res.ok) {
            btn.disabled = false;
            btn.textContent = 'Retry';
            ppSetSending(false);
            return;
        }

        row.remove();
        ppAppendBubble('BUYER', data.buyer_message.message, data.buyer_message.is_error);
        ppSetSending(false);

        if (data.session_ended) {
            document.getElementById('ppSendBtn').disabled = true;
            document.getElementById('ppInput').disabled = true;
            ppShowScorecard(data.session.status, data.session.scorecard);
        }
    } catch (e) {
        btn.disabled = false;
        btn.textContent = 'Retry';
        ppSetSending(false);
    }
}

function ppSetSending(sending) {
    document.getElementById('ppSendBtn').disabled = sending;
    document.getElementById('ppInput').disabled = sending;
    document.getElementById('ppAttachBtn').disabled = sending;
    document.getElementById('ppTyping').style.display = sending ? 'block' : 'none';
}

let ppSelectedImage = null;

function ppOnImageSelected(input) {
    const file = input.files && input.files[0];
    if (!file) return;

    if (file.size > 8 * 1024 * 1024) {
        alert('Image is too large — please pick one under 8MB.');
        input.value = '';
        return;
    }

    ppSelectedImage = file;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('ppImagePreviewThumb').src = e.target.result;
        document.getElementById('ppImagePreview').classList.add('active');
    };
    reader.readAsDataURL(file);
}

function ppClearImage() {
    ppSelectedImage = null;
    document.getElementById('ppImageInput').value = '';
    document.getElementById('ppImagePreview').classList.remove('active');
    document.getElementById('ppImagePreviewThumb').src = '';
}

async function ppSendMessage() {
    const input = document.getElementById('ppInput');
    const text = input.value.trim();
    if (!text && !ppSelectedImage) return;

    const imageFile = ppSelectedImage;
    const localImageUrl = imageFile ? URL.createObjectURL(imageFile) : null;
    const row = ppAppendBubble('AGENT', text, false, localImageUrl);

    input.value = '';
    ppClearImage();

    await ppSubmitMessage(row, text, imageFile);
}

async function ppSubmitMessage(row, text, imageFile) {
    const formData = new FormData();
    formData.append('message', text);
    if (imageFile) formData.append('image', imageFile);

    ppSetSending(true);

    try {
        const res = await fetch(ppMessageUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': ppCsrf, 'Accept': 'application/json' },
            body: formData,
        });

        let data = {};
        try { data = await res.json(); } catch (e) { /* non-JSON error body, e.g. a raw 429 */ }

        if (!res.ok) {
            const reason = res.status === 429
                ? "you're sending messages a bit too fast — wait a moment"
                : ppExtractErrorMessage(data);
            ppMarkSendFailed(row, reason, () => ppSubmitMessage(row, text, imageFile));
            ppSetSending(false);
            return;
        }

        ppAppendBubble('BUYER', data.buyer_message.message, data.buyer_message.is_error);
        ppSetSending(false);

        if (data.session_ended) {
            document.getElementById('ppSendBtn').disabled = true;
            document.getElementById('ppInput').disabled = true;
            document.getElementById('ppAttachBtn').disabled = true;
            ppShowScorecard(data.session.status, data.session.scorecard);
        }
    } catch (e) {
        ppMarkSendFailed(row, 'connection error', () => ppSubmitMessage(row, text, imageFile));
        ppSetSending(false);
    }
}

document.getElementById('ppInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        ppSendMessage();
    }
});

async function ppEndSession() {
    if (!confirm('End this practice session now?')) return;
    ppSetSending(true);
    try {
        const res = await fetch(ppEndUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': ppCsrf, 'Accept': 'application/json' },
            body: JSON.stringify({ status: 'ABANDONED' }),
        });
        const data = await res.json();
        document.getElementById('ppSendBtn').disabled = true;
        document.getElementById('ppInput').disabled = true;
        document.getElementById('ppAttachBtn').disabled = true;
        ppShowScorecard(data.session.status, data.session.scorecard);
    } catch (e) {
        ppSetSending(false);
        alert('Could not end the session — please try again.');
    }
}

function ppShowScorecard(status, scorecard) {
    scorecard = scorecard || {};
    const outcomeLabel = status === 'SOLD' ? 'Sold! 🎉' : (status === 'ABANDONED' ? 'Ended Early' : 'Not Sold');
    const overall = (scorecard.overall_score ?? '—');
    const metrics = [
        ['Rapport', scorecard.rapport],
        ['Objection Handling', scorecard.objection_handling],
        ['Product Knowledge', scorecard.product_knowledge],
        ['Closing Technique', scorecard.closing_technique],
    ];
    const suggestions = (scorecard.suggestions || []).map(s => `<li>${s}</li>`).join('') || '<li>No specific suggestions this time.</li>';

    document.getElementById('ppScoreBox').innerHTML = `
        <div class="pc-score-hdr">
            <div class="pc-score-outcome">${outcomeLabel}</div>
            <div class="pc-score-overall">${overall}${overall !== '—' ? '/100' : ''}</div>
        </div>
        <div class="pc-score-body">
            <div class="pc-score-rubric">
                ${metrics.map(([lbl, val]) => `
                    <div class="pc-score-metric">
                        <div class="pc-score-metric-lbl">${lbl}</div>
                        <div class="pc-score-metric-val">${val ?? '—'}</div>
                    </div>
                `).join('')}
            </div>
            <div class="pc-score-summary">${scorecard.summary || 'No summary available.'}</div>
            <ul class="pc-score-suggestions">${suggestions}</ul>
        </div>
        <div class="pc-score-actions">
            <a href="{{ route('practice') }}" class="pc-btn-secondary">Back to Scenarios</a>
            <a href="{{ route('practice.history') }}" class="pc-btn-primary">View History</a>
        </div>
    `;
    document.getElementById('ppScoreOverlay').style.display = 'flex';
}
</script>
@endsection