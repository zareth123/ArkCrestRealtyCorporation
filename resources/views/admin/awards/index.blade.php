@extends('layouts.dashboard')

@section('title', 'Awards Management')

@section('content')
@php
    $__u = auth()->user();
    if (!$__u || (!$__u->isAdmin() && in_array('admin.awards', $__u->hidden_pages ?? []))) {
        abort(403);
    }
@endphp

<div class="news-admin-page">
    <section class="news-admin-banner">
        <div>
            <span class="news-admin-kicker">Admin Publishing</span>
            <h1>Awards Management</h1>
            <p>Create, edit, and publish the awards and recognitions shown in the "Our Awards" section of the public landing page.</p>
        </div>
        <a href="{{ route('landing') }}#awards" target="_blank" rel="noopener noreferrer" class="news-admin-public-link">
            View Public Page
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7v7m0-7L10 14M5 7v12h12v-5"/>
            </svg>
        </a>
    </section>

    @if(session('award_success'))
        <div class="news-admin-alert success">{{ session('award_success') }}</div>
    @endif

    @if(session('award_error'))
        <div class="news-admin-alert error">{{ session('award_error') }}</div>
    @endif

    @if($errors->any())
        <div class="news-admin-alert error">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="news-admin-panel">
        <div class="news-admin-panel-head">
            <div>
                <span class="news-admin-section-number">01</span>
                <h2>Add a New Award</h2>
            </div>
            <span class="news-admin-hint">Only "Published" awards appear on the landing page</span>
        </div>

        <form method="POST" action="{{ route('admin.awards.store') }}" enctype="multipart/form-data" class="news-post-form" onsubmit="return confirm('Save this award?');">
            @csrf

            <div class="news-admin-field">
                <label for="award-recipient">Recipient Name</label>
                <input
                    id="award-recipient"
                    type="text"
                    name="recipient_name"
                    value="{{ old('recipient_name') }}"
                    maxlength="120"
                    required
                    placeholder="e.g. Carl Angel Buakaew"
                >
                <small>Use the team name (e.g. "ArkCrest Realty") for team-wide awards.</small>
            </div>

            <div class="news-admin-field">
                <label for="award-title">Award Title</label>
                <input
                    id="award-title"
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    maxlength="160"
                    required
                    placeholder="e.g. Top 1 Sales Agent of 2025"
                >
            </div>

            <div class="news-admin-field">
                <label for="award-status">Posting Status</label>
                <select id="award-status" name="status" required>
                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Save as Draft</option>
                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Publish</option>
                </select>
            </div>

            <div class="news-admin-field">
                <label for="award-order">Display Order</label>
                <input
                    id="award-order"
                    type="number"
                    name="sort_order"
                    value="{{ old('sort_order', 0) }}"
                    min="0"
                    max="9999"
                >
                <small>Lower numbers appear first.</small>
            </div>

            <div class="news-admin-field full">
                <label for="award-image">Award Image</label>
                <label class="news-upload-box" for="award-image">
                    <img class="news-upload-preview" data-preview hidden alt="Selected image preview">
                    <svg data-upload-icon fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5.5 5.5 0 0116.9 6.1 4.5 4.5 0 0118 15H7zm5-7v8m0-8-3 3m3-3 3 3"/>
                    </svg>
                    <strong data-upload-label>Select an image</strong>
                    <span data-upload-hint>JPG, PNG, GIF, or WEBP — max 25 MB</span>
                    <span class="news-selected-files" data-selected-files>No file selected</span>
                </label>
                <input
                    id="award-image"
                    type="file"
                    name="image"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    hidden
                    data-media-input
                >
                <small>If no image is uploaded, the default award emblem will be shown instead.</small>
            </div>

            <div class="news-admin-actions full">
                <button type="reset" class="news-admin-btn secondary">Clear Form</button>
                <button type="submit" class="news-admin-btn primary">Save Award</button>
            </div>
        </form>
    </section>

    <section class="news-admin-panel">
        <div class="news-admin-panel-head">
            <div>
                <span class="news-admin-section-number">02</span>
                <h2>All Awards</h2>
            </div>
            <span class="news-admin-hint">{{ $awards->total() }} total award{{ $awards->total() === 1 ? '' : 's' }}</span>
        </div>

        @forelse($awards as $award)
            <article class="news-admin-post-card">
                <div class="news-admin-post-summary">
                    <div class="news-admin-post-main">
                        <div class="news-admin-post-status-row">
                            <span class="news-status-badge {{ $award->status === 'published' ? 'published' : 'draft' }}">
                                {{ $award->status === 'published' ? 'Published' : 'Draft' }}
                            </span>
                            <span class="news-admin-post-date">Order: {{ $award->sort_order }}</span>
                        </div>

                        <h3>{{ $award->title }}</h3>
                        <p style="margin-bottom:4px;color:#4a5f78;font-weight:700;">{{ $award->recipient_name }}</p>

                        <div class="news-admin-post-meta">
                            <span>Created {{ $award->created_at->diffForHumans() }}</span>
                            @if($award->creator)
                                <span>By {{ $award->creator->name }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="news-admin-post-controls">
                        @if($award->has_image)
                            <img
                                src="{{ $award->image_url }}"
                                alt="{{ $award->title }}"
                                style="width:44px;height:44px;border-radius:8px;object-fit:cover;margin-right:6px;"
                            >
                        @endif
                        <button type="button" class="news-admin-btn secondary small" onclick="toggleNewsEdit({{ $award->id }})">
                            Edit
                        </button>
                        @if($__u->isAdmin())
                        <form
                            method="POST"
                            action="{{ route('admin.awards.destroy', $award) }}"
                            onsubmit="return confirm('Delete this award permanently?');"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="news-admin-btn danger small">Delete</button>
                        </form>
                        @endif
                    </div>
                </div>

                <div class="news-admin-edit-panel" id="newsEditPanel{{ $award->id }}">
                    <form
                        method="POST"
                        action="{{ route('admin.awards.update', $award) }}"
                        enctype="multipart/form-data"
                        class="news-post-form"
                    >
                        @csrf
                        @method('PUT')

                        <div class="news-admin-field">
                            <label for="edit-recipient-{{ $award->id }}">Recipient Name</label>
                            <input
                                id="edit-recipient-{{ $award->id }}"
                                type="text"
                                name="recipient_name"
                                value="{{ $award->recipient_name }}"
                                maxlength="120"
                                required
                            >
                        </div>

                        <div class="news-admin-field">
                            <label for="edit-title-{{ $award->id }}">Award Title</label>
                            <input
                                id="edit-title-{{ $award->id }}"
                                type="text"
                                name="title"
                                value="{{ $award->title }}"
                                maxlength="160"
                                required
                            >
                        </div>

                        <div class="news-admin-field">
                            <label for="edit-status-{{ $award->id }}">Posting Status</label>
                            <select id="edit-status-{{ $award->id }}" name="status" required>
                                <option value="draft" {{ $award->status === 'draft' ? 'selected' : '' }}>Save as Draft</option>
                                <option value="published" {{ $award->status === 'published' ? 'selected' : '' }}>Publish</option>
                            </select>
                        </div>

                        <div class="news-admin-field">
                            <label for="edit-order-{{ $award->id }}">Display Order</label>
                            <input
                                id="edit-order-{{ $award->id }}"
                                type="number"
                                name="sort_order"
                                value="{{ $award->sort_order }}"
                                min="0"
                                max="9999"
                            >
                        </div>

                        <div class="news-admin-field full">
                            <label for="edit-image-{{ $award->id }}">Replace Award Image</label>
                            <label class="news-upload-box" for="edit-image-{{ $award->id }}">
                                <img class="news-upload-preview" data-preview hidden alt="Selected image preview">
                                <svg data-upload-icon fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5.5 5.5 0 0116.9 6.1 4.5 4.5 0 0118 15H7zm5-7v8m0-8-3 3m3-3 3 3"/>
                                </svg>
                                <strong data-upload-label>Select an image</strong>
                                <span data-upload-hint>JPG, PNG, GIF, or WEBP — max 25 MB</span>
                                <span class="news-selected-files" data-selected-files>No file selected</span>
                            </label>
                            <input
                                id="edit-image-{{ $award->id }}"
                                type="file"
                                name="image"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                hidden
                                data-media-input
                            >
                            @if($award->has_image)
                                <small>Uploading a new image replaces the current one.</small>
                            @endif
                        </div>

                        <div class="news-admin-actions full">
                            @if($award->has_image)
                                <button
                                    type="submit"
                                    form="award-image-destroy-{{ $award->id }}"
                                    class="news-admin-btn secondary"
                                    onclick="return confirm('Remove this award image?');"
                                >Remove Image</button>
                            @endif
                            <button type="button" class="news-admin-btn secondary" onclick="toggleNewsEdit({{ $award->id }})">Cancel</button>
                            <button type="submit" class="news-admin-btn primary">Save Changes</button>
                        </div>
                    </form>

                    @if($award->has_image)
                        <form
                            id="award-image-destroy-{{ $award->id }}"
                            method="POST"
                            action="{{ route('admin.awards.image.destroy', $award) }}"
                            style="display:none;"
                        >
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="news-admin-empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <h3>No awards yet</h3>
                <p>Add your first award using the form above.</p>
            </div>
        @endforelse

        @if($awards->hasPages())
            <div class="news-admin-pagination">
                {{ $awards->links() }}
            </div>
        @endif
    </section>
</div>

<style>
.news-admin-page{
    display:flex;
    flex-direction:column;
    gap:22px;
}
.news-admin-banner{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:24px;
    padding:32px 36px;
    border-radius:20px;
    background:linear-gradient(135deg,#163a63 0%,#2563eb 62%,#173f70 100%);
    box-shadow:0 12px 34px rgba(30,69,117,.24);
    color:#fff;
}
.news-admin-kicker{
    display:block;
    margin-bottom:7px;
    color:#f7df9a;
    font-size:10px;
    font-weight:800;
    letter-spacing:1.6px;
    text-transform:uppercase;
}
.news-admin-banner h1{
    margin:0;
    color:#fff;
    font-size:29px;
}
.news-admin-banner p{
    max-width:760px;
    margin:7px 0 0;
    color:rgba(255,255,255,.76);
    font-size:13px;
}
.news-admin-public-link{
    display:inline-flex;
    align-items:center;
    gap:8px;
    flex-shrink:0;
    padding:11px 15px;
    border:1px solid rgba(255,255,255,.35);
    border-radius:9px;
    color:#fff;
    font-size:12px;
    font-weight:700;
    text-decoration:none;
    transition:.2s ease;
}
.news-admin-public-link:hover{
    background:#fff;
    color:#163a63;
}
.news-admin-public-link svg{
    width:17px;
    height:17px;
}
.news-admin-alert{
    padding:13px 16px;
    border-radius:9px;
    font-size:13px;
}
.news-admin-alert.success{
    border:1px solid #86efac;
    background:#dcfce7;
    color:#166534;
}
.news-admin-alert.error{
    border:1px solid #fecaca;
    background:#fef2f2;
    color:#b91c1c;
}
.news-admin-alert ul{
    margin:8px 0 0 20px;
}
.news-admin-panel{
    padding:26px 28px;
    border:1px solid #dbe4ef;
    border-radius:14px;
    background:#fff;
    box-shadow:0 5px 18px rgba(15,39,69,.07);
}
.news-admin-panel-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    margin-bottom:22px;
    padding-bottom:16px;
    border-bottom:1px solid #e8eef5;
}
.news-admin-panel-head > div{
    display:flex;
    align-items:center;
    gap:11px;
}
.news-admin-panel-head h2{
    margin:0;
    color:#173f70;
    font-size:18px;
}
.news-admin-section-number{
    color:#c3942d;
    font-family:monospace;
    font-size:11px;
    font-weight:800;
}
.news-admin-hint{
    color:#7b8ca3;
    font-size:11px;
}
.news-post-form{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:18px;
}
.news-admin-field{
    display:flex;
    flex-direction:column;
    gap:7px;
}
.news-admin-field.full,
.news-admin-actions.full{
    grid-column:1/-1;
}
.news-admin-field label{
    color:#273b55;
    font-size:11px;
    font-weight:800;
    letter-spacing:.35px;
    text-transform:uppercase;
}
.news-admin-field input,
.news-admin-field select,
.news-admin-field textarea{
    width:100%;
    border:1.5px solid #d9e2ec;
    border-radius:9px;
    background:#fff;
    color:#17283d;
    font-family:inherit;
    font-size:13px;
    outline:none;
    padding:11px 12px;
    transition:border-color .18s ease,box-shadow .18s ease;
}
.news-admin-field input:focus,
.news-admin-field select:focus,
.news-admin-field textarea:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.11);
}
.news-admin-field small{
    color:#8a99ac;
    font-size:10px;
}
.news-upload-box{
    display:flex!important;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    min-height:150px;
    padding:22px;
    border:2px dashed #b8c8dc;
    border-radius:12px;
    background:#f8fbff;
    cursor:pointer;
    text-align:center;
    text-transform:none!important;
    transition:.2s ease;
}
.news-upload-box:hover{
    border-color:#2563eb;
    background:#f1f6ff;
}
.news-upload-box svg{
    width:34px;
    height:34px;
    margin-bottom:8px;
    color:#2563eb;
}
.news-upload-box strong{
    color:#173f70;
    font-size:13px;
}
.news-upload-box > span{
    margin-top:4px;
    color:#7b8ca3;
    font-size:10px;
    font-weight:500;
}
.news-selected-files{
    display:block;
    max-width:100%;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    color:#b17d12!important;
    font-weight:700!important;
}
.news-upload-preview[hidden]{
    display:none;
}
.news-upload-preview{
    width:96px;
    height:96px;
    object-fit:cover;
    border-radius:12px;
    margin:0 auto 10px;
    display:block;
    border:1px solid rgba(0,0,0,.08);
}
.news-admin-actions{
    display:flex;
    justify-content:flex-end;
    gap:10px;
}
.news-admin-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:39px;
    padding:9px 16px;
    border:0;
    border-radius:8px;
    cursor:pointer;
    font-family:inherit;
    font-size:12px;
    font-weight:800;
    text-decoration:none;
    transition:.18s ease;
}
.news-admin-btn.primary{
    background:linear-gradient(135deg,#1e4575,#2563eb);
    color:#fff;
}
.news-admin-btn.primary:hover{
    transform:translateY(-1px);
    box-shadow:0 7px 16px rgba(37,99,235,.22);
}
.news-admin-btn.secondary{
    border:1px solid #cbd6e2;
    background:#f8fafc;
    color:#3e526b;
}
.news-admin-btn.danger{
    border:1px solid #fecaca;
    background:#fff5f5;
    color:#dc2626;
}
.news-admin-btn.small{
    min-height:34px;
    padding:7px 12px;
    font-size:10px;
}
.news-admin-post-card{
    overflow:hidden;
    margin-bottom:16px;
    border:1px solid #dce5ef;
    border-radius:12px;
    background:#fff;
}
.news-admin-post-summary{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:24px;
    padding:22px;
}
.news-admin-post-main{
    min-width:0;
    flex:1;
}
.news-admin-post-status-row{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:10px;
}
.news-status-badge{
    display:inline-flex;
    align-items:center;
    padding:4px 9px;
    border-radius:999px;
    font-size:9px;
    font-weight:900;
    letter-spacing:.8px;
    text-transform:uppercase;
}
.news-status-badge.published{
    background:#dcfce7;
    color:#15803d;
}
.news-status-badge.draft{
    background:#e5e7eb;
    color:#4b5563;
}
.news-admin-post-date{
    color:#8a99ac;
    font-size:10px;
}
.news-admin-post-main h3{
    margin:0;
    color:#173f70;
    font-size:18px;
}
.news-admin-post-main > p{
    margin:8px 0 0;
    color:#617187;
    font-size:12px;
    line-height:1.55;
}
.news-admin-post-meta{
    display:flex;
    flex-wrap:wrap;
    gap:7px 18px;
    margin-top:14px;
    color:#8a99ac;
    font-size:10px;
}
.news-admin-post-controls{
    display:flex;
    align-items:center;
    gap:8px;
    flex-shrink:0;
}
.news-admin-edit-panel{
    display:none;
    padding:22px;
    border-top:1px solid #dce5ef;
    background:#f7faff;
}
.news-admin-edit-panel.open{
    display:block;
}
.news-admin-empty{
    padding:48px 20px;
    text-align:center;
    color:#8190a3;
}
.news-admin-empty svg{
    width:48px;
    height:48px;
    margin-bottom:12px;
    color:#b8c5d4;
}
.news-admin-empty h3{
    margin:0;
    color:#40536a;
    font-size:16px;
}
.news-admin-empty p{
    margin:5px 0 0;
    font-size:12px;
}
.news-admin-pagination{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:14px;
    margin-top:22px;
    color:#697b91;
    font-size:11px;
}
.news-admin-pagination a,
.news-admin-pagination .disabled{
    padding:7px 11px;
    border:1px solid #d6e0eb;
    border-radius:7px;
    text-decoration:none;
}
.news-admin-pagination a{
    background:#fff;
    color:#1e4575;
    font-weight:800;
}
.news-admin-pagination .disabled{
    color:#aab5c2;
    background:#f6f8fa;
}
@media(max-width:800px){
    .news-admin-banner{
        align-items:flex-start;
        flex-direction:column;
        padding:25px;
    }
    .news-post-form{
        grid-template-columns:1fr;
    }
    .news-admin-field.full,
    .news-admin-actions.full{
        grid-column:auto;
    }
    .news-admin-post-summary{
        flex-direction:column;
    }
    .news-admin-post-controls{
        width:100%;
    }
    .news-admin-post-controls .news-admin-btn,
    .news-admin-post-controls form{
        flex:1;
    }
    .news-admin-post-controls form .news-admin-btn{
        width:100%;
    }
}
@media(max-width:520px){
    .news-admin-panel{
        padding:20px 16px;
    }
    .news-admin-panel-head{
        align-items:flex-start;
        flex-direction:column;
    }
    .news-admin-actions{
        flex-direction:column-reverse;
    }
    .news-admin-actions .news-admin-btn{
        width:100%;
    }
}
</style>

<script>
function toggleNewsEdit(id) {
    var panel = document.getElementById('newsEditPanel' + id);
    if (!panel) return;

    panel.classList.toggle('open');

    if (panel.classList.contains('open')) {
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

document.querySelectorAll('[data-media-input]').forEach(function(input) {
    input.addEventListener('change', function() {
        var field = input.closest('.news-admin-field');
        if (!field) return;

        var label = field.querySelector('[data-selected-files]');
        var preview = field.querySelector('[data-preview]');
        var icon = field.querySelector('[data-upload-icon]');

        if (!input.files || input.files.length === 0) {
            if (label) label.textContent = 'No file selected';
            if (preview) { preview.hidden = true; preview.removeAttribute('src'); }
            if (icon) icon.hidden = false;
            return;
        }

        var file = input.files[0];
        if (label) label.textContent = file.name;

        if (preview) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.hidden = false;
                if (icon) icon.hidden = true;
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endsection