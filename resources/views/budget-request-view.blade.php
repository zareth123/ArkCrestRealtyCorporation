@extends('layouts.print-layout')

@section('content')
@php
    $data = $formData ?? [];

    $deptMap = [
        'ADMIN' => 'ADMINISTRATIVE',
        'SALES & MARKETING' => 'SALES & MARKETING',
        'HR' => 'HUMAN RESOURCES',
        'FINANCE' => 'FINANCE',
        'EXECUTIVE' => 'EXECUTIVE',
    ];
    $deptRaw = $data['department'] ?? $expense->department;
    $deptDisplay = $data['department_display']
        ?? ($deptMap[strtoupper($deptRaw)] ?? strtoupper($deptRaw)) . ' DEPARTMENT';

    $requestorName = $data['requestor_name'] ?? $expense->requestor_name;
    $category      = $data['category'] ?? $expense->category;
    $remarks       = $data['remarks'] ?? '';

    $amountRequested = $expense->requested_amount;
    $releaseStatus = $data['release_status'] ?? $expense->release_status ?? 'NOT YET RELEASED';
    $liquidationStatus = $data['liquidation_status'] ?? $expense->status ?? 'NOT YET LIQUIDATED';

    $fmtDate = function ($value) {
        if (empty($value)) return '';
        try { return \Carbon\Carbon::parse($value)->format('m/d/Y'); }
        catch (\Exception $e) { return ''; }
    };

    $dateRequested       = $fmtDate($data['date_requested'] ?? $expense->date_requested);
    $targetDateReleased  = $fmtDate($data['target_date_released'] ?? null);
    $actualDateReleased  = $fmtDate($data['actual_date_released'] ?? $expense->date_released);

    $liquidationItems = $data['liquidation_items'] ?? [];
    // 15 rows matches the original printed Budget Request & Liquidation
    // Form (short bond / letter size, 8.5in x 11in) so the whole form
    // still fits on a single page when printed.
    $totalRows = 15;

    $totalExpenses    = $data['total_expenses'] ?? $expense->total_expenses;
    $lessCashAdvance  = $data['less_cash_advance'] ?? '';
    $amountReturned   = $data['amount_returned'] ?? $expense->amount_returned;

    $approvedBy = $data['approved_by'] ?? '';
    $releasedBy = $data['released_by'] ?? '';
    $receivedBy = $data['received_by'] ?? '';
    $dateChecked     = $fmtDate($data['date_checked'] ?? null);
    $dateReleasedSig = $fmtDate($data['date_released_sig'] ?? null);
    $dateReceivedSig = $fmtDate($data['date_received_sig'] ?? null);
@endphp

<div class="frm-card">
    <!-- Header -->
    <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:6px;">
        <div style="display:flex;align-items:center;gap:14px;justify-content:center;">
            <img src="{{ asset('images/ArkCrest_Logo.png') }}" alt="Logo" style="width:80px;height:80px;object-fit:contain;flex-shrink:0;">
            <div style="text-align:center;">
                <div style="font-size:24px;font-weight:700;text-decoration:underline;color:#000;text-transform:uppercase;letter-spacing:.5px;">{{ $deptDisplay }}</div>
                <div style="font-size:24px;font-weight:700;color:#2563eb;margin-top:10px;letter-spacing:.5px;">BUDGET REQUEST FORM</div>
            </div>
        </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:4px;margin-top:10px;">
        <div style="display:flex;gap:6px;align-items:center;font-size:9px;">
            <span class="status-chip">RELEASE: {{ $releaseStatus }}</span>
            <span class="status-chip">LIQUIDATION: {{ $liquidationStatus }}</span>
        </div>
        <div style="text-align:right;font-size:16px;font-weight:700;color:#dc2626;letter-spacing:.3px;">Control Number: {{ $expense->control_number }}</div>
    </div>

    <table class="info-tbl">
        <tr>
            <td class="lbl">Name:</td>
            <td colspan="3">{{ $requestorName }}</td>
        </tr>
        <tr>
            <td class="lbl">Amount Requested: &#8369;</td>
            <td>{{ number_format($amountRequested ?? 0, 2) }}</td>
            <td class="lbl">Target Date Released:</td>
            <td>{{ $targetDateReleased }}</td>
        </tr>
        <tr>
            <td class="lbl">Particular :</td>
            <td>{{ $category }}</td>
            <td class="lbl">Actual Date Released:</td>
            <td>{{ $actualDateReleased }}</td>
        </tr>
        <tr>
            <td class="lbl">Date Requested:</td>
            <td>{{ $dateRequested }}</td>
            <td class="lbl">Remarks:</td>
            <td>{{ $remarks }}</td>
        </tr>
    </table>

    <!-- Note -->
    <div class="frm-note">
        <strong>Note:</strong> (a) For amount less than <strong>&#8369;1,000.00</strong> disbursement will be processed with in the day<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(b) Amount of <strong>&#8369;1,000.00</strong> or more than will be disbursed at least one week after the submission.
    </div>

    <!-- Liquidation Report -->
    <hr style="border:none;border-top:1.5px solid #000;margin:10px 0 8px 0;">
    <p class="liq-hdr" style="color:#2563eb;font-weight:700;font-size:20px;text-align:center;margin:0 0 8px 0;letter-spacing:.5px;">LIQUIDATION REPORT</p>
    <table class="liq-tbl">
        <thead>
            <tr>
                <th style="width:13%">DATE</th>
                <th style="width:20%">RECEIPT / INVOICE NO.</th>
                <th style="width:47%">PARTICULARS</th>
                <th style="width:20%">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @for($i = 0; $i < $totalRows; $i++)
                @php $row = $liquidationItems[$i] ?? null; @endphp
                <tr>
                    <td>{{ $row ? $fmtDate($row['date'] ?? null) : '' }}</td>
                    <td>{{ $row['receipt'] ?? '' }}</td>
                    <td style="text-align:left;padding-left:5px;">{{ $row['particulars'] ?? '' }}</td>
                    <td class="amt">&#8369; {{ isset($row['amount']) && $row['amount'] !== '' ? $row['amount'] : '' }}</td>
                </tr>
            @endfor
        </tbody>
    </table>
    <table class="totals-tbl" style="width:100%;border-collapse:collapse;font-size:11px;font-weight:700;margin-top:10px;">
        <tr>
            <td style="border:1px solid #000;padding:5px 7px;width:50%;">TOTAL EXPENSES: &#8369; {{ $totalExpenses !== null && $totalExpenses !== '' ? number_format((float)$totalExpenses, 2) : '' }}</td>
            <td style="border:1px solid #000;padding:5px 7px;width:50%;">LESS CASH ADVANCE: &#8369; {{ $lessCashAdvance }}</td>
        </tr>
        <tr>
            <td colspan="2" style="border:1px solid #000;padding:5px 7px;">AMOUNT TO BE RETURNED: &#8369; {{ $amountReturned !== null && $amountReturned !== '' ? number_format((float)$amountReturned, 2) : '' }}</td>
        </tr>
    </table>

    <!-- Certification -->
    <p style="font-size:11px;font-weight:700;margin:6px 0 8px 0;padding:0;line-height:1.4;">This is to certify that the foregoing expenses were disbursed in conformity with the above stated purpose(s).</p>

    <!-- Signatures -->
    <table class="sigs">
        <tr>
            <td style="padding:4px 8px;font-size:11px;font-weight:400;">Checked &amp; Approved by:</td>
            <td style="padding:4px 8px;font-size:11px;font-weight:400;">Released by:</td>
            <td style="padding:4px 8px;font-size:11px;font-weight:400;">Received by:</td>
        </tr>
        <tr>
            <td style="padding:4px 8px;height:50px;vertical-align:bottom;font-size:11px;font-weight:700;text-align:center;text-transform:uppercase;">{{ $approvedBy }}</td>
            <td style="padding:4px 8px;height:50px;vertical-align:bottom;font-size:11px;font-weight:700;text-align:center;text-transform:uppercase;">{{ $releasedBy }}</td>
            <td style="padding:4px 8px;height:50px;vertical-align:bottom;font-size:11px;font-weight:700;text-align:center;text-transform:uppercase;">{{ $receivedBy }}</td>
        </tr>
        <tr>
            <td style="padding:4px 8px;font-size:11px;">Date: {{ $dateChecked }}</td>
            <td style="padding:4px 8px;font-size:11px;">Date: {{ $dateReleasedSig }}</td>
            <td style="padding:4px 8px;font-size:11px;">Date: {{ $dateReceivedSig }}</td>
        </tr>
    </table>
</div>

<style>
.frm-card{background:white;padding:32px 48px;border:1px solid #ccc;font-family:Arial,sans-serif;color:#000;width:816px;box-sizing:border-box;margin:0 auto;}
.info-tbl{width:100%;border-collapse:collapse;font-size:12px;margin-bottom:0}
.info-tbl td{border:1px solid #000;padding:5px 7px}
.info-tbl td.lbl{font-weight:700;white-space:nowrap;background:#fafafa;width:1%}
.status-chip{display:inline-block;padding:2px 7px;border:1px solid #64748b;border-radius:999px;font-size:9px;font-weight:700;letter-spacing:.2px;}
.frm-note{font-size:11px;color:#dc2626;margin:4px 0 4px;line-height:1.5}
.liq-tbl{width:100%;border-collapse:collapse;font-size:11px}
.liq-tbl th,.liq-tbl td{border:1px solid #000;padding:1px 4px;text-align:center;height:20px;}
.liq-tbl th{background:#f0f0f0;font-weight:700;font-size:11px}
.liq-tbl td.amt{text-align:left;padding-left:5px}
.sigs{width:100%;border-collapse:collapse;font-size:11px;margin-top:0}
.sigs td{border:1px solid #000;padding:4px 8px;vertical-align:top;width:33.33%}

@media print{
    body *{visibility:hidden}
    .frm-card, .frm-card *{visibility:visible}
    .frm-card{position:absolute;top:0;left:0;width:100%;max-width:8.5in;padding:0.35in 0.45in;border:none;font-size:11px;margin:0 auto;}
    @page{size:8.5in 11in;margin:0}
}
</style>
@endsection
