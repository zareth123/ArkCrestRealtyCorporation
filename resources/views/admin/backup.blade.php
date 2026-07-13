@extends('layouts.dashboard')

@section('content')
@php
    if (!auth()->user()->isAdmin()) abort(403);
@endphp

<div class="backup-container">

    <div class="backup-banner">
        <div>
            <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:6px;">Admin</div>
            <h1 style="font-size:28px;font-weight:700;color:#fff;margin:0 0 6px;">Backup &amp; Restore</h1>
            <p style="font-size:14px;color:rgba(255,255,255,.75);margin:0;">Create, download, and restore full system backups</p>
        </div>
    </div>

    @if(session('backup_success'))
        <div class="backup-flash backup-flash-success">✔ {{ session('backup_success') }}</div>
    @endif
    @if(session('backup_error'))
        <div class="backup-flash backup-flash-error">⚠ {{ session('backup_error') }}</div>
    @endif

    {{-- Create backup actions --}}
    <div class="backup-card">
        <h2 class="backup-card-title">Create a New Backup</h2>
        <p style="font-size:13px;color:#6b7280;margin:0 0 16px;">
            A CSV backup includes every table in the system ({{ count($tables) }} tables), each saved as its own .csv file bundled into one .zip archive, and can be used to restore the database later.
            A PDF export is for viewing/printing only and cannot be used to restore.
        </p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <form method="POST" action="{{ route('backup.create-csv') }}" onsubmit="return confirmAndLoad(this, 'Create a new CSV backup of the entire system now?', 'Creating backup…')">
                @csrf
                <button type="submit" class="backup-btn backup-btn-primary">
                    🗂 Create CSV Backup
                </button>
            </form>
            <form method="POST" action="{{ route('backup.create-pdf') }}" onsubmit="return confirmAndLoad(this, 'Create a PDF export of the entire system now? This is for viewing only.', 'Generating PDF…')">
                @csrf
                <button type="submit" class="backup-btn backup-btn-secondary">
                    📄 Create PDF Export
                </button>
            </form>
        </div>
    </div>

    {{-- Backup list --}}
    <div class="backup-card">
        <h2 class="backup-card-title">Available Backups</h2>

        @if(empty($backups))
            <div style="color:#9ca3af;font-size:13px;padding:20px 0;text-align:center;">No backups yet. Create one above.</div>
        @else
        <div style="overflow-x:auto;">
            <table class="backup-table">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Type</th>
                        <th>Date Created</th>
                        <th>File Size</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $b)
                    <tr>
                        <td style="font-family:monospace;font-size:12px;">{{ $b['filename'] }}</td>
                        <td>
                            @if($b['type'] === 'csv')
                                <span class="backup-badge backup-badge-csv">CSV (ZIP)</span>
                            @else
                                <span class="backup-badge backup-badge-pdf">PDF</span>
                            @endif
                        </td>
                        <td>{{ $b['created_at']->format('M d, Y g:i A') }}</td>
                        <td>{{ $b['size_human'] }}</td>
                        <td>
                            <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                                <a href="{{ route('backup.download', $b['filename']) }}" class="backup-action-btn backup-action-download">DOWNLOAD</a>

                                @if($b['type'] === 'csv')
                                    <button type="button" class="backup-action-btn backup-action-restore"
                                        onclick="openRestoreModal('{{ $b['filename'] }}')">RESTORE</button>
                                @endif

                                <form method="POST" action="{{ route('backup.destroy', $b['filename']) }}"
                                      onsubmit="return confirm('Delete this backup file permanently? This cannot be undone.');"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="backup-action-btn backup-action-delete">DELETE</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Upload & restore from local device --}}
    <div class="backup-card">
        <h2 class="backup-card-title">Restore from a File on This Device</h2>
        <p style="font-size:13px;color:#6b7280;margin:0 0 16px;">
            Upload a previously downloaded <strong>.zip</strong> backup file (containing one .csv per table) from your computer to restore the system with it.
        </p>
        <form id="uploadRestoreForm" method="POST" action="{{ route('backup.upload-restore') }}" enctype="multipart/form-data"
              onsubmit="return handleUploadRestoreSubmit(event)">
            @csrf
            <input type="hidden" name="confirm_text" id="uploadConfirmText" value="">
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                <input type="file" name="restore_file" id="uploadRestoreFile" accept=".zip" required
                    style="padding:8px 12px;border:1.5px solid #d0d5dd;border-radius:8px;font-size:13px;">
                <button type="submit" class="backup-btn backup-btn-danger">⬆ Upload &amp; Restore</button>
            </div>
        </form>
    </div>

</div>

{{-- Restore confirmation modal (typed confirmation, used for both list-restore and upload-restore) --}}
<div id="restoreModal" class="backup-modal-overlay">
    <div class="backup-modal-box">
        <div class="backup-modal-header">
            <h3>⚠ Confirm Restore</h3>
        </div>
        <div class="backup-modal-body">
            <p style="font-size:14px;color:#374151;margin:0 0 12px;">
                This will <strong>overwrite existing data</strong> in every table found in this backup file.
                This action <strong>cannot be undone</strong>. We strongly recommend creating a fresh backup first.
            </p>
            <p style="font-size:13px;color:#374151;margin:0 0 8px;">
                Type <strong>RESTORE</strong> below to confirm:
            </p>
            <input type="text" id="restoreConfirmInput" placeholder="Type RESTORE"
                style="width:100%;padding:10px 12px;border:2px solid #d0d5dd;border-radius:8px;font-size:14px;box-sizing:border-box;">
        </div>
        <div class="backup-modal-footer">
            <button type="button" class="backup-btn backup-btn-secondary" onclick="closeRestoreModal()">Cancel</button>
            <button type="button" class="backup-btn backup-btn-danger" onclick="submitRestore()">Restore Now</button>
        </div>
    </div>
</div>

{{-- Hidden form used to submit "restore from existing list item" with the typed confirmation --}}
<form id="listRestoreForm" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="confirm_text" id="listRestoreConfirmText" value="">
</form>

{{-- Full-page loading overlay shown while a backup/restore/export request is processing --}}
<div id="backupLoadingOverlay" class="backup-loading-overlay">
    <div class="backup-loading-box">
        <div class="backup-spinner"></div>
        <div id="backupLoadingText" style="margin-top:14px;font-size:14px;font-weight:600;color:#1e4575;">Processing…</div>
        <div style="margin-top:6px;font-size:12px;color:#6b7280;">Please don't close this tab.</div>
    </div>
</div>

<style>
    .backup-container { padding: 0; }

    .backup-banner {
        background: linear-gradient(135deg, #1e4575 0%, #2563eb 60%, #1e4575 100%);
        border-radius: 20px;
        padding: 32px 36px;
        margin-bottom: 24px;
        box-shadow: 0 8px 32px rgba(30,69,117,.25);
    }

    .backup-flash {
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 16px;
    }
    .backup-flash-success { background:#dcfce7; color:#166534; }
    .backup-flash-error   { background:#fee2e2; color:#dc2626; }

    .backup-card {
        background: white;
        border-radius: 12px;
        padding: 28px 30px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 2px solid #1e4575;
    }
    .backup-card-title {
        font-size: 17px;
        font-weight: 700;
        color: #1e4575;
        margin: 0 0 8px;
    }

    .backup-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 11px 20px; border-radius: 8px;
        font-size: 14px; font-weight: 600; cursor: pointer; border: none;
        transition: all .2s;
    }
    .backup-btn-primary   { background:#1e4575; color:#fff; }
    .backup-btn-primary:hover { background:#152e4d; }
    .backup-btn-secondary { background:#f3f4f6; color:#374151; border:2px solid #d0d5dd; }
    .backup-btn-secondary:hover { background:#e5e7eb; }
    .backup-btn-danger    { background:#dc2626; color:#fff; }
    .backup-btn-danger:hover { background:#b91c1c; }

    .backup-table { width:100%; border-collapse: collapse; font-size: 13px; }
    .backup-table thead { background: linear-gradient(135deg, #1e4575, #2563eb); }
    .backup-table th { padding:10px 12px; text-align:left; color:#fff; font-size:11px; text-transform:uppercase; letter-spacing:.4px; }
    .backup-table td { padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#374151; }
    .backup-table tbody tr:hover { background:#f9fafb; }

    .backup-badge { padding:3px 10px; border-radius:10px; font-size:10px; font-weight:700; letter-spacing:.4px; }
    .backup-badge-csv  { background:#dcfce7; color:#166534; }
    .backup-badge-pdf  { background:#fee2e2; color:#991b1b; }

    .backup-action-btn {
        display:inline-flex; align-items:center; justify-content:center;
        padding:6px 10px; border:none; border-radius:5px; cursor:pointer;
        font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.4px;
        text-decoration:none; box-shadow:0 2px 4px rgba(0,0,0,0.1);
    }
    .backup-action-download { background:#1e4575; color:#fff; }
    .backup-action-restore  { background:#f59e0b; color:#fff; }
    .backup-action-delete   { background:#ef4444; color:#fff; }

    .backup-modal-overlay {
        display:none; position:fixed; inset:0; background:rgba(0,0,0,.55);
        z-index:9999; align-items:center; justify-content:center;
    }
    .backup-modal-overlay.active { display:flex; }
    .backup-modal-box { background:#fff; border-radius:14px; width:460px; max-width:92vw; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.3); }
    .backup-modal-header { background:#dc2626; padding:16px 22px; }
    .backup-modal-header h3 { margin:0; color:#fff; font-size:15px; font-weight:700; }
    .backup-modal-body { padding:20px 22px; }
    .backup-modal-footer { padding:14px 22px; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end; gap:10px; }

    .backup-loading-overlay {
        display:none; position:fixed; inset:0; background:rgba(255,255,255,.85);
        z-index:10000; align-items:center; justify-content:center;
    }
    .backup-loading-overlay.active { display:flex; }
    .backup-loading-box { text-align:center; }
    .backup-spinner {
        width:48px; height:48px; border:5px solid #e5e7eb; border-top-color:#1e4575;
        border-radius:50%; margin:0 auto; animation: backupSpin 0.8s linear infinite;
    }
    @keyframes backupSpin { to { transform: rotate(360deg); } }
</style>

<script>
let _restoreTargetFilename = null;

function showBackupLoading(text) {
    document.getElementById('backupLoadingText').textContent = text || 'Processing…';
    document.getElementById('backupLoadingOverlay').classList.add('active');
}

// Used by the "Create CSV Backup" / "Create PDF Export" forms.
function confirmAndLoad(form, confirmMessage, loadingText) {
    if (!confirm(confirmMessage)) return false;
    showBackupLoading(loadingText);
    return true;
}

// Restore-from-existing-list-item flow
function openRestoreModal(filename) {
    _restoreTargetFilename = filename;
    document.getElementById('restoreConfirmInput').value = '';
    document.getElementById('restoreModal').classList.add('active');
}
function closeRestoreModal() {
    document.getElementById('restoreModal').classList.remove('active');
    _restoreTargetFilename = null;
}
function submitRestore() {
    const typed = document.getElementById('restoreConfirmInput').value.trim();
    if (typed !== 'RESTORE') {
        alert('Please type RESTORE exactly to confirm.');
        return;
    }
    if (_restoreTargetFilename === '__uploaded__') {
        document.getElementById('uploadConfirmText').value = 'RESTORE';
        closeRestoreModal();
        showBackupLoading('Uploading file and restoring system… this may take a moment.');
        document.getElementById('uploadRestoreForm').submit();
        return;
    }
    if (!_restoreTargetFilename) return;

    const form = document.getElementById('listRestoreForm');
    form.action = '{{ url("/settings/backup") }}/' + encodeURIComponent(_restoreTargetFilename) + '/restore';
    document.getElementById('listRestoreConfirmText').value = 'RESTORE';
    closeRestoreModal();
    showBackupLoading('Restoring system from backup… this may take a moment.');
    form.submit();
}

// Upload-and-restore flow: intercept submit, ask for typed confirmation via the same modal pattern
function handleUploadRestoreSubmit(e) {
    e.preventDefault();
    const fileInput = document.getElementById('uploadRestoreFile');
    if (!fileInput.files.length) {
        alert('Please choose a .zip file first.');
        return false;
    }
    _restoreTargetFilename = '__uploaded__';
    document.getElementById('restoreConfirmInput').value = '';
    document.getElementById('restoreModal').classList.add('active');
    return false;
}
</script>
@endsection