@extends('layouts.dashboard')
@section('title', 'Edit History')
@section('content')
<style>
.eh-header{background:linear-gradient(135deg,#1e4575 0%,#2563eb 60%,#1e4575 100%);border-radius:16px;padding:28px 32px;margin-bottom:24px;position:relative;overflow:hidden;box-shadow:0 6px 24px rgba(30,69,117,.2)}
.eh-header h1{font-size:22px;font-weight:700;color:white;margin:0 0 4px;position:relative;z-index:2}
.eh-header p{font-size:13px;color:rgba(255,255,255,.75);margin:0;position:relative;z-index:2}
.eh-deco{position:absolute;top:-40px;right:-40px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.05)}

.eh-card{background:white;border-radius:12px;border:1px solid #e8ecf0;box-shadow:0 1px 4px rgba(0,0,0,.05);overflow:hidden;margin-bottom:20px}

.eh-filters{padding:16px 18px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;border-bottom:1px solid #f1f5f9;background:#f8fafc}
.eh-field{display:flex;flex-direction:column;gap:4px}
.eh-field label{font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.4px}
.eh-field select,.eh-field input{padding:8px 11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;color:#374151;background:white;min-width:150px}
.eh-field select:focus,.eh-field input:focus{outline:none;border-color:#1e4575}
.eh-field.eh-search{flex:1;min-width:220px}
.eh-btn{padding:9px 16px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;border:none;white-space:nowrap}
.eh-btn-primary{background:#1e4575;color:white}
.eh-btn-primary:hover{background:#163458}
.eh-btn-ghost{background:white;color:#1e4575;border:1.5px solid #d0d5dd !important;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}
.eh-btn-ghost:hover{background:#eef2f7}

.eh-table-wrap{overflow-x:auto}
.eh-table{width:100%;border-collapse:collapse;min-width:900px}
.eh-table thead tr{background:#1e4575}
.eh-table thead th{padding:11px 16px;text-align:left;font-size:10px;font-weight:700;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.7px;white-space:nowrap}
.eh-table tbody tr{border-bottom:1px solid #f1f5f9;vertical-align:top}
.eh-table tbody tr:hover{background:#f8fafc}
.eh-table td{padding:12px 16px;font-size:13px;color:#374151}
.eh-time{white-space:nowrap;color:#374151;font-weight:600}
.eh-time small{display:block;font-weight:400;color:#94a3b8;font-size:11px}
.eh-editor-name{font-weight:700;color:#0f172a}
.eh-editor-email{display:block;font-size:11px;color:#94a3b8}
.eh-module{display:inline-block;font-size:11px;font-weight:700;color:#1e4575;background:#eef2f7;border-radius:6px;padding:3px 9px;margin-bottom:4px}
.eh-record-label{font-weight:600;color:#0f172a}
.eh-record-id{color:#94a3b8;font-size:11px}
.eh-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:capitalize}
.eh-badge-create{background:#dcfce7;color:#166534}
.eh-badge-update{background:#fef3c7;color:#92400e}
.eh-badge-delete{background:#fee2e2;color:#991b1b}
.eh-badge-restore{background:#dbeafe;color:#1e40af}
.eh-diff-list{display:flex;flex-direction:column;gap:6px;max-width:420px}
.eh-diff-row{font-size:12px;line-height:1.5;background:#f8fafc;border:1px solid #f1f5f9;border-radius:6px;padding:6px 9px}
.eh-diff-field{font-weight:700;color:#1e4575;text-transform:capitalize}
.eh-diff-old{color:#991b1b;background:#fef2f2;border-radius:4px;padding:1px 5px;text-decoration:line-through;word-break:break-word}
.eh-diff-new{color:#166534;background:#f0fdf4;border-radius:4px;padding:1px 5px;word-break:break-word}
.eh-diff-arrow{color:#94a3b8;margin:0 4px}
.eh-diff-more{font-size:11px;color:#1e4575;font-weight:700;background:none;border:none;padding:2px 0;cursor:pointer;text-align:left;text-decoration:underline}
.eh-diff-more:hover{color:#163458}
.eh-empty{text-align:center;padding:48px;color:#94a3b8;font-size:13px}
.eh-check-col{width:36px;text-align:center}
.eh-actions-col{width:170px}
.eh-row-actions{display:flex;gap:6px;flex-wrap:wrap}
.eh-row-btn{padding:6px 12px;border-radius:7px;font-size:11.5px;font-weight:700;cursor:pointer;border:none;white-space:nowrap}
.eh-row-btn-undo{background:#dbeafe;color:#1e40af}
.eh-row-btn-undo:hover{background:#bfdbfe}
.eh-row-btn-delete{background:#fee2e2;color:#991b1b}
.eh-row-btn-delete:hover{background:#fecaca}
.eh-row-btn:disabled{opacity:.5;cursor:not-allowed}
.eh-bulkbar{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:12px 18px;border-bottom:1px solid #f1f5f9;background:#f8fafc}
.eh-bulkbar-count{font-size:12px;color:#64748b;font-weight:600}
.eh-bulkbar-count strong{color:#0f172a}
.eh-bulkbar-actions{display:flex;gap:8px}
.eh-bulk-btn{padding:8px 14px;border-radius:8px;font-size:12.5px;font-weight:700;cursor:pointer;border:none;white-space:nowrap}
.eh-bulk-btn-undo{background:#1e40af;color:white}
.eh-bulk-btn-undo:hover:not(:disabled){background:#1e3a8a}
.eh-bulk-btn-delete{background:#dc2626;color:white}
.eh-bulk-btn-delete:hover:not(:disabled){background:#b91c1c}
.eh-bulk-btn:disabled{opacity:.45;cursor:not-allowed}
.eh-footer{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;flex-wrap:wrap;gap:10px}
.eh-count{font-size:12px;color:#94a3b8}
.eh-pagenav{display:flex;gap:6px;align-items:center}
.eh-pagenav a,.eh-pagenav span{padding:6px 11px;border-radius:6px;font-size:12px;font-weight:600;color:#1e4575;border:1.5px solid #e2e8f0;text-decoration:none}
.eh-pagenav a:hover{background:#eef2f7}
.eh-pagenav .eh-page-current{background:#1e4575;color:white;border-color:#1e4575}
.eh-pagenav .eh-page-disabled{color:#c2cbd6;pointer-events:none}
@media (max-width:640px){.eh-filters{flex-direction:column;align-items:stretch}.eh-field select,.eh-field input{min-width:0;width:100%}}
</style>

<div class="eh-header">
  <div class="eh-deco"></div>
  <h1>Edit History</h1>
  <p>Centralized audit trail of every create, update, and delete across all modules — Administrator only.</p>
</div>

<div class="eh-card">
  <form method="GET" action="{{ route('settings.edit-history') }}" class="eh-filters">
    <div class="eh-field">
      <label>Module</label>
      <select name="module">
        <option value="">All Modules</option>
        @foreach($modules as $m)
          <option value="{{ $m }}" {{ ($filters['module'] ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
        @endforeach
      </select>
    </div>
    <div class="eh-field">
      <label>Editor</label>
      <select name="user_id">
        <option value="">All Users</option>
        @foreach($editors as $ed)
          <option value="{{ $ed->id }}" {{ (string)($filters['user_id'] ?? '') === (string)$ed->id ? 'selected' : '' }}>{{ $ed->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="eh-field">
      <label>From</label>
      <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
    </div>
    <div class="eh-field">
      <label>To</label>
      <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
    </div>
    <div class="eh-field eh-search">
      <label>Search</label>
      <input type="text" name="search" placeholder="Search description, editor, or field values..." value="{{ $filters['search'] ?? '' }}">
    </div>
    <button type="submit" class="eh-btn eh-btn-primary">Apply Filters</button>
    <a href="{{ route('settings.edit-history') }}" class="eh-btn eh-btn-ghost">Reset</a>
  </form>

  <div class="eh-bulkbar">
    <div class="eh-bulkbar-count"><strong id="ehSelCount">0</strong> selected</div>
    <div class="eh-bulkbar-actions">
      <button type="button" id="ehBulkUndoBtn" class="eh-bulk-btn eh-bulk-btn-undo" disabled onclick="ehBulkAction('restore')">Undo Selected</button>
      <button type="button" id="ehBulkDeleteBtn" class="eh-bulk-btn eh-bulk-btn-delete" disabled onclick="ehBulkAction('delete')">Delete Selected</button>
    </div>
  </div>

  <div class="eh-table-wrap">
    <table class="eh-table">
      <thead>
        <tr>
          <th class="eh-check-col"><input type="checkbox" id="ehSelectAll" onclick="ehToggleSelectAll(this)" title="Select all on this page"></th>
          <th style="width:130px;">Timestamp</th>
          <th style="width:150px;">Editor</th>
          <th style="width:170px;">Module / Record</th>
          <th style="width:80px;">Action</th>
          <th>Changed Fields (Before &rarr; After)</th>
          <th class="eh-actions-col">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          @php
            $meta = is_array($log->meta) ? $log->meta : [];
            $changes = $meta['changes'] ?? [];
          @endphp
          <tr data-log-id="{{ $log->id }}">
            <td class="eh-check-col"><input type="checkbox" class="eh-row-check" value="{{ $log->id }}" onclick="ehToggleRow(this)"></td>
            <td class="eh-time">
              {{ $log->created_at->format('M d, Y') }}
              <small>{{ $log->created_at->format('h:i A') }} &bull; {{ $log->created_at->diffForHumans() }}</small>
            </td>
            <td>
              <span class="eh-editor-name">{{ $log->user->name ?? 'System' }}</span>
              <span class="eh-editor-email">{{ $log->user->email ?? '—' }}</span>
            </td>
            <td>
              <span class="eh-module">{{ $log->module }}</span><br>
              @if($meta['record_label'] ?? null)
                <span class="eh-record-label">{{ $meta['record_label'] }}</span>
              @else
                <span class="eh-record-label">{{ $meta['record_type'] ?? 'Record' }}</span>
              @endif
              <div class="eh-record-id">{{ $meta['record_type'] ?? '' }} #{{ $meta['record_id'] ?? $log->id }}</div>
            </td>
            <td><span class="eh-badge eh-badge-{{ $log->action }}">{{ $log->action }}</span></td>
            <td>
              @if(count($changes))
                <div class="eh-diff-list" data-diff-list>
                  @foreach($changes as $field => $vals)
                    <div class="eh-diff-row" @if($loop->index >= 6) style="display:none;" data-diff-extra @endif>
                      <span class="eh-diff-field">{{ str_replace('_',' ', $field) }}:</span>
                      @if(($vals['old'] ?? null) !== null && $vals['old'] !== '')
                        <span class="eh-diff-old">{{ $vals['old'] }}</span>
                      @else
                        <span style="color:#94a3b8;">—</span>
                      @endif
                      <span class="eh-diff-arrow">&rarr;</span>
                      @if(($vals['new'] ?? null) !== null && $vals['new'] !== '')
                        <span class="eh-diff-new">{{ $vals['new'] }}</span>
                      @else
                        <span style="color:#94a3b8;">—</span>
                      @endif
                    </div>
                  @endforeach
                  @if(count($changes) > 6)
                    @php $moreLabel = '+ ' . (count($changes) - 6) . ' more field(s) changed — view all'; @endphp
                    <button type="button" class="eh-diff-more" data-more-label="{{ $moreLabel }}" onclick="ehToggleDiff(this)">{{ $moreLabel }}</button>
                  @endif
                </div>
              @else
                <span style="color:#94a3b8;font-size:12px;">{{ $log->description }}</span>
              @endif
            </td>
            <td class="eh-actions-col">
              <div class="eh-row-actions">
                @if($log->can_undo)
                  <button type="button" class="eh-row-btn eh-row-btn-undo" onclick="ehSingleAction({{ $log->id }}, 'restore', '{{ $log->action }}')">{{ $log->action === 'update' ? 'Revert' : 'Undo' }}</button>
                @endif
                <button type="button" class="eh-row-btn eh-row-btn-delete" onclick="ehSingleAction({{ $log->id }}, 'delete')">Delete</button>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7"><div class="eh-empty">No edit history found for the selected filters.</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="eh-footer">
    <div class="eh-count">
      @if($logs->total() > 0)
        Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} entries
      @else
        No entries
      @endif
    </div>
    <div class="eh-pagenav">
      @if($logs->onFirstPage())
        <span class="eh-page-disabled">&laquo; Prev</span>
      @else
        <a href="{{ $logs->previousPageUrl() }}">&laquo; Prev</a>
      @endif
      <span class="eh-page-current">{{ $logs->currentPage() }}</span>
      <span>of {{ $logs->lastPage() }}</span>
      @if($logs->hasMorePages())
        <a href="{{ $logs->nextPageUrl() }}">Next &raquo;</a>
      @else
        <span class="eh-page-disabled">Next &raquo;</span>
      @endif
    </div>
  </div>
</div>

<script>
(function() {
  function ehCsrf() {
    return document.querySelector('meta[name=csrf-token]')?.content || '';
  }

  function ehChecks() {
    return Array.from(document.querySelectorAll('.eh-row-check'));
  }

  function ehUpdateBulkBar() {
    const checked = ehChecks().filter(c => c.checked);
    const n = checked.length;
    const countEl = document.getElementById('ehSelCount');
    const undoBtn = document.getElementById('ehBulkUndoBtn');
    const deleteBtn = document.getElementById('ehBulkDeleteBtn');
    if (countEl) countEl.textContent = n;
    if (undoBtn) undoBtn.disabled = n === 0;
    if (deleteBtn) deleteBtn.disabled = n === 0;
    const all = ehChecks();
    const selectAll = document.getElementById('ehSelectAll');
    if (selectAll) selectAll.checked = all.length > 0 && n === all.length;
  }

  window.ehToggleSelectAll = function(cb) {
    ehChecks().forEach(c => { c.checked = cb.checked; });
    ehUpdateBulkBar();
  };

  window.ehToggleRow = function() {
    ehUpdateBulkBar();
  };

  window.ehToggleDiff = function(btn) {
    const list = btn.closest('[data-diff-list]');
    const hidden = list.querySelectorAll('[data-diff-extra]');
    const isCollapsed = hidden.length > 0 && hidden[0].style.display === 'none';
    hidden.forEach(row => { row.style.display = isCollapsed ? '' : 'none'; });
    btn.textContent = isCollapsed ? 'Show fewer fields' : btn.dataset.moreLabel;
  };

  async function ehCallBulk(action, ids) {
    const url = action === 'restore'
      ? '{{ route('settings.deleted.bulkRestore') }}'
      : '{{ route('settings.deleted.bulkDelete') }}';
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': ehCsrf(), 'Accept': 'application/json' },
      body: JSON.stringify({ items: ids.map(id => ({ type: 'log', id: id })) })
    });

    let result;
    try {
      result = await res.json();
    } catch (e) {
      // Server returned something that wasn't JSON (e.g. a 419/500 error page) —
      // treat it as a hard failure rather than letting res.json() throw upstream.
      throw new Error(`Unexpected response (status ${res.status}).`);
    }
    return result;
  }

  window.ehSingleAction = async function(id, action, logAction) {
    const message = action === 'restore'
      ? (logAction === 'update'
          ? 'Revert this edit back to its previous values?'
          : 'Undo this deletion and restore the underlying record?')
      : 'Permanently delete this entry from the Edit History log? This cannot be undone.';
    const confirmed = window.showConfirmModal ? await window.showConfirmModal(message) : window.confirm(message);
    if (!confirmed) return;

    try {
      const result = await ehCallBulk(action, [id]);
      if (result.success) {
        window.showToast ? window.showToast(result.message || 'Done.', 'success') : alert(result.message || 'Done.');
        setTimeout(() => location.reload(), 600);
      } else {
        window.showToast ? window.showToast(result.message || 'Action failed.', 'error') : alert(result.message || 'Action failed.');
      }
    } catch (e) {
      window.showToast ? window.showToast(e.message || 'Something went wrong. Please try again.', 'error') : alert('Something went wrong. Please try again.');
    }
  };

  window.ehBulkAction = async function(action) {
    const ids = ehChecks().filter(c => c.checked).map(c => c.value);
    if (!ids.length) return;

    const message = action === 'restore'
      ? `Undo/revert ${ids.length} selected entr${ids.length === 1 ? 'y' : 'ies'}?`
      : `Permanently delete ${ids.length} selected entr${ids.length === 1 ? 'y' : 'ies'} from the Edit History log? This cannot be undone.`;
    const confirmed = window.showConfirmModal ? await window.showConfirmModal(message) : window.confirm(message);
    if (!confirmed) return;

    try {
      const result = await ehCallBulk(action, ids);
      // Backend reports counts, not just a single pass/fail flag — use them so a
      // partial success doesn't get shown (or hidden) as a blanket error.
      const doneCount = result.restored ?? result.deleted ?? 0;
      const failCount = result.failed ?? 0;
      const anySucceeded = doneCount > 0;

      let message2 = result.message || 'Done.';
      if (failCount > 0 && Array.isArray(result.errors) && result.errors.length) {
        message2 += ' (' + result.errors.slice(0, 3).join('; ') + (result.errors.length > 3 ? '…' : '') + ')';
      }
      window.showToast ? window.showToast(message2, anySucceeded ? 'success' : 'error') : alert(message2);

      // Only reload if something actually changed — otherwise the list would
      // refresh for no reason after a fully-failed batch.
      if (anySucceeded) {
        setTimeout(() => location.reload(), 600);
      }
    } catch (e) {
      window.showToast ? window.showToast(e.message || 'Something went wrong. Please try again.', 'error') : alert('Something went wrong. Please try again.');
    }
  };

  ehUpdateBulkBar();
})();
</script>
@endsection