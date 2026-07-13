@extends('layouts.dashboard')

@section('content')
<div class="export-container">
    <div class="export-banner">
        <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:6px;">Admin</div>
        <h1 style="font-size:28px;font-weight:700;color:#fff;margin:0 0 6px;">Export Records</h1>
        <p style="font-size:14px;color:rgba(255,255,255,.75);margin:0;">Generate downloadable PDF or CSV reports from any module — all records or a specific date range.</p>
    </div>

    <div class="export-card">
        <form id="exportForm" method="POST" action="{{ route('admin.export.download') }}">
            @csrf
            <div class="export-form-group">
                <label>Module</label>
                <select name="module" required>
                    <option value="">— Select a module —</option>
                    @foreach($modules as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="export-form-group">
                <label>Records to Export</label>
                <div class="export-radio-row">
                    <label class="export-radio"><input type="radio" name="range_type" value="all" checked onchange="exportToggleRange(false)"> Export All Records</label>
                    <label class="export-radio"><input type="radio" name="range_type" value="range" onchange="exportToggleRange(true)"> Export by Date Range</label>
                </div>
            </div>

            <div class="export-form-group export-date-row" id="exportDateRow" style="display:none;">
                <div>
                    <label>Start Date</label>
                    <input type="date" name="start_date" id="exportStartDate">
                </div>
                <div>
                    <label>End Date</label>
                    <input type="date" name="end_date" id="exportEndDate">
                </div>
            </div>

            <div class="export-form-group">
                <label>Format</label>
                <div class="export-radio-row">
                    <label class="export-radio"><input type="radio" name="format" value="csv" checked> CSV</label>
                    <label class="export-radio"><input type="radio" name="format" value="pdf"> PDF</label>
                </div>
            </div>

            <button type="submit" class="export-btn">Export Records</button>
        </form>
    </div>
</div>

<style>
    .export-container { padding: 0; }
    .export-banner {
        background: linear-gradient(135deg, #1e4575 0%, #2563eb 60%, #1e4575 100%);
        border-radius: 20px; padding: 32px 36px; margin-bottom: 24px;
        box-shadow: 0 8px 32px rgba(30,69,117,.25);
    }
    .export-card {
        background: white; border-radius: 12px; padding: 28px 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 2px solid #1e4575;
        max-width: 560px;
    }
    .export-form-group { margin-bottom: 20px; }
    .export-form-group label {
        display: block; font-size: 12px; font-weight: 700; color: #1e4575;
        text-transform: uppercase; letter-spacing: .4px; margin-bottom: 8px;
    }
    .export-form-group select,
    .export-form-group input[type="date"] {
        width: 100%; padding: 10px 14px; border: 2px solid #d0d5dd; border-radius: 8px;
        font-size: 14px; color: #374151; box-sizing: border-box;
    }
    .export-radio-row { display: flex; gap: 20px; flex-wrap: wrap; }
    .export-radio { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: #374151; cursor: pointer; text-transform: none; }
    .export-date-row { display: flex; gap: 16px; }
    .export-date-row > div { flex: 1; }
    .export-btn {
        padding: 11px 24px; background: #1e4575; color: white; border: none; border-radius: 8px;
        font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(30,69,117,0.3);
    }
    .export-btn:hover { background: #152e4d; }
</style>

<script>
    function exportToggleRange(show) {
        document.getElementById('exportDateRow').style.display = show ? 'flex' : 'none';
        document.getElementById('exportStartDate').required = show;
        document.getElementById('exportEndDate').required = show;
    }
</script>
@endsection