@extends('layouts.dashboard')

@section('title', 'News & Updates Posting')

@section('content')
@php
    if (!auth()->user()->isAdmin()) abort(403);
@endphp

<div class="news-admin-page">
    <section class="news-admin-banner">
        <div>
            <span class="news-admin-kicker">Admin Publishing</span>
            <h1>News &amp; Updates Posting</h1>
            <p>Create, edit, and publish announcements that automatically appear on the public News &amp; Updates page.</p>
        </div>
        <a href="{{ route('news-updates') }}" target="_blank" rel="noopener noreferrer" class="news-admin-public-link">
            View Public Page
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7v7m0-7L10 14M5 7v12h12v-5"/>
            </svg>
        </a>
    </section>

    @if(session('news_success'))
        <div class="news-admin-alert success">{{ session('news_success') }}</div>
    @endif

    @if(session('news_error'))
        <div class="news-admin-alert error">{{ session('news_error') }}</div>
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
                <h2>Create a New Post</h2>
            </div>
            <span class="news-admin-hint">Up to 10 images/videos per upload</span>
        </div>

        <form method="POST" action="{{ route('admin.news-updates.store') }}" enctype="multipart/form-data" class="news-post-form">
            @csrf

            <div class="news-admin-field full">
                <label for="news-title">Post Title</label>
                <input
                    id="news-title"
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    maxlength="180"
                    required
                    placeholder="Enter the news or announcement title"
                >
            </div>

            <div class="news-admin-field full">
                <label for="news-description">Description</label>
                <textarea
                    id="news-description"
                    name="description"
                    rows="7"
                    maxlength="30000"
                    required
                    placeholder="Write the complete news or announcement details..."
                >{{ old('description') }}</textarea>
            </div>

            <div class="news-admin-field">
                <label for="news-status">Posting Status</label>
                <select id="news-status" name="status" required>
                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Save as Draft</option>
                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Publish</option>
                </select>
            </div>

            <div class="news-admin-field news-auto-date-note">
                <label>Post Date &amp; Time</label>
                <div class="news-auto-date-box">
                    Automatically recorded when you publish the post.
                </div>
                <small>Editing a published post keeps its original posting date and time.</small>
            </div>

            <div class="news-admin-field full">
                <label for="news-media">Attach Images or Videos</label>
                <label class="news-upload-box" for="news-media">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5.5 5.5 0 0116.9 6.1 4.5 4.5 0 0118 15H7zm5-7v8m0-8-3 3m3-3 3 3"/>
                    </svg>
                    <strong>Select files</strong>
                    <span>JPG, PNG, GIF, WEBP, MP4, MOV, or WEBM — maximum 100 MB each</span>
                    <span class="news-selected-files" data-selected-files>No files selected</span>
                </label>
                <input
                    id="news-media"
                    type="file"
                    name="media[]"
                    accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/webm"
                    multiple
                    hidden
                    data-media-input
                >
            </div>

            <div class="news-admin-actions full">
                <button type="reset" class="news-admin-btn secondary">Clear Form</button>
                <button type="submit" class="news-admin-btn primary">Save News Post</button>
            </div>
        </form>
    </section>

    <section class="news-admin-panel">
        <div class="news-admin-panel-head">
            <div>
                <span class="news-admin-section-number">02</span>
                <h2>All Posts</h2>
            </div>
            <span class="news-admin-hint">{{ $posts->total() }} total post{{ $posts->total() === 1 ? '' : 's' }}</span>
        </div>

        @forelse($posts as $post)
            <article class="news-admin-post-card">
                <div class="news-admin-post-summary">
                    <div class="news-admin-post-main">
                        <div class="news-admin-post-status-row">
                            <span class="news-status-badge {{ strtolower($post->publication_state) }}">
                                {{ $post->publication_state }}
                            </span>
                            <span class="news-admin-post-date">
                                @if($post->published_at)
                                    {{ $post->published_at->format('M d, Y · g:i A') }}
                                @else
                                    Not published
                                @endif
                            </span>
                        </div>

                        <h3>{{ $post->title }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($post->description, 220) }}</p>

                        <div class="news-admin-post-meta">
                            <span>Created {{ $post->created_at->diffForHumans() }}</span>
                            <span>{{ $post->media->count() }} attachment{{ $post->media->count() === 1 ? '' : 's' }}</span>
                            @if($post->creator)
                                <span>By {{ $post->creator->name }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="news-admin-post-controls">
                        <button type="button" class="news-admin-btn secondary small" onclick="toggleNewsEdit({{ $post->id }})">
                            Edit
                        </button>
                        <form
                            method="POST"
                            action="{{ route('admin.news-updates.destroy', $post) }}"
                            onsubmit="return confirm('Delete this news post and all of its attached media permanently?');"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="news-admin-btn danger small">Delete</button>
                        </form>
                    </div>
                </div>

                @if($post->media->isNotEmpty())
                    <div class="news-admin-media-strip">
                        @foreach($post->media as $media)
                            <div class="news-admin-media-item">
                                @if($media->media_type === 'image')
                                    <img src="{{ $media->url }}" alt="{{ $post->title }} attachment">
                                @else
                                    <video src="{{ $media->url }}" controls preload="metadata"></video>
                                @endif

                                <div class="news-admin-media-caption">
                                    <span title="{{ $media->original_name }}">{{ \Illuminate\Support\Str::limit($media->original_name, 28) }}</span>
                                    <small>{{ $media->size_label }}</small>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('admin.news-updates.media.destroy', $media) }}"
                                    onsubmit="return confirm('Remove this attachment from the post?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="news-media-remove" title="Remove attachment" aria-label="Remove attachment">
                                        ×
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="news-admin-edit-panel" id="newsEditPanel{{ $post->id }}">
                    <form
                        method="POST"
                        action="{{ route('admin.news-updates.update', $post) }}"
                        enctype="multipart/form-data"
                        class="news-post-form"
                    >
                        @csrf
                        @method('PUT')

                        <div class="news-admin-field full">
                            <label for="edit-title-{{ $post->id }}">Post Title</label>
                            <input
                                id="edit-title-{{ $post->id }}"
                                type="text"
                                name="title"
                                value="{{ $post->title }}"
                                maxlength="180"
                                required
                            >
                        </div>

                        <div class="news-admin-field full">
                            <label for="edit-description-{{ $post->id }}">Description</label>
                            <textarea
                                id="edit-description-{{ $post->id }}"
                                name="description"
                                rows="7"
                                maxlength="30000"
                                required
                            >{{ $post->description }}</textarea>
                        </div>

                        <div class="news-admin-field">
                            <label for="edit-status-{{ $post->id }}">Posting Status</label>
                            <select id="edit-status-{{ $post->id }}" name="status" required>
                                <option value="draft" {{ $post->status === 'draft' ? 'selected' : '' }}>Save as Draft</option>
                                <option value="published" {{ $post->status === 'published' ? 'selected' : '' }}>Publish</option>
                            </select>
                        </div>

                        <div class="news-admin-field news-auto-date-note">
                            <label>Post Date &amp; Time</label>
                            <div class="news-auto-date-box">
                                @if($post->published_at)
                                    Posted {{ $post->published_at->format('M d, Y \a\t g:i A') }}
                                @else
                                    The date and time will be recorded when this draft is published.
                                @endif
                            </div>
                            <small>Editing does not change the original posting time.</small>
                        </div>

                        <div class="news-admin-field full">
                            <label for="edit-media-{{ $post->id }}">Add More Images or Videos</label>
                            <label class="news-upload-box compact" for="edit-media-{{ $post->id }}">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14m-7-7h14"/>
                                </svg>
                                <strong>Add attachments</strong>
                                <span>Existing attachments will remain unless removed above.</span>
                                <span class="news-selected-files" data-selected-files>No files selected</span>
                            </label>
                            <input
                                id="edit-media-{{ $post->id }}"
                                type="file"
                                name="media[]"
                                accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/quicktime,video/webm"
                                multiple
                                hidden
                                data-media-input
                            >
                        </div>

                        <div class="news-admin-actions full">
                            <button type="button" class="news-admin-btn secondary" onclick="toggleNewsEdit({{ $post->id }})">Cancel</button>
                            <button type="submit" class="news-admin-btn primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </article>
        @empty
            <div class="news-admin-empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l6 6v8a2 2 0 01-2 2zM15 4v6h6M7 14h10M7 17h7"/>
                </svg>
                <h3>No news posts yet</h3>
                <p>Create the first post using the form above.</p>
            </div>
        @endforelse

        @if($posts->hasPages())
            <nav class="news-admin-pagination" aria-label="News post pages">
                @if($posts->onFirstPage())
                    <span class="disabled">Previous</span>
                @else
                    <a href="{{ $posts->previousPageUrl() }}">Previous</a>
                @endif

                <span>Page {{ $posts->currentPage() }} of {{ $posts->lastPage() }}</span>

                @if($posts->hasMorePages())
                    <a href="{{ $posts->nextPageUrl() }}">Next</a>
                @else
                    <span class="disabled">Next</span>
                @endif
            </nav>
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
.news-admin-field textarea{
    resize:vertical;
    line-height:1.55;
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
.news-auto-date-box{
    min-height:42px;
    padding:11px 12px;
    border:1.5px solid #d9e2ec;
    border-radius:9px;
    background:#f8fafc;
    color:#52657d;
    font-size:12px;
    line-height:1.5;
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
.news-upload-box.compact{
    min-height:118px;
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
.news-admin-media-strip{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(155px,1fr));
    gap:12px;
    padding:0 22px 22px;
}
.news-admin-media-item{
    position:relative;
    overflow:hidden;
    border:1px solid #dbe4ee;
    border-radius:9px;
    background:#f8fafc;
}
.news-admin-media-item img,
.news-admin-media-item video{
    display:block;
    width:100%;
    height:115px;
    object-fit:cover;
    background:#0f1e31;
}
.news-admin-media-caption{
    min-width:0;
    padding:8px 10px;
}
.news-admin-media-caption span,
.news-admin-media-caption small{
    display:block;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.news-admin-media-caption span{
    color:#33475f;
    font-size:10px;
    font-weight:700;
}
.news-admin-media-caption small{
    margin-top:2px;
    color:#8a99ac;
    font-size:9px;
}
.news-media-remove{
    position:absolute;
    top:7px;
    right:7px;
    width:26px;
    height:26px;
    border:0;
    border-radius:50%;
    background:rgba(190,24,24,.92);
    color:#fff;
    cursor:pointer;
    font-size:18px;
    line-height:1;
    box-shadow:0 3px 10px rgba(0,0,0,.18);
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
    .news-admin-media-strip{
        grid-template-columns:1fr;
        padding:0 16px 16px;
    }
}
</style>

<script>
function toggleNewsEdit(postId) {
    var panel = document.getElementById('newsEditPanel' + postId);
    if (!panel) return;

    panel.classList.toggle('open');

    if (panel.classList.contains('open')) {
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

document.querySelectorAll('[data-media-input]').forEach(function(input) {
    input.addEventListener('change', function() {
        var label = input.closest('.news-admin-field').querySelector('[data-selected-files]');
        if (!label) return;

        if (!input.files || input.files.length === 0) {
            label.textContent = 'No files selected';
            return;
        }

        var names = Array.prototype.map.call(input.files, function(file) {
            return file.name;
        });

        label.textContent = input.files.length + ' selected: ' + names.join(', ');
    });
});

</script>
@endsection
