<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Helvetica, Arial, sans-serif; color: #333; font-size: 10px; }
    h1 { color: #1E4575; font-size: 18px; margin: 0 0 2px 0; }
    .meta { font-size: 10px; color: #6b7280; margin-bottom: 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #d0d5dd; padding: 5px 7px; text-align: left; word-break: break-word; }
    th { background: #1E4575; color: #fff; font-size: 9px; text-transform: uppercase; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    .empty-note { color: #9ca3af; font-style: italic; padding: 12px 0; }
</style>
</head>
<body>
    <h1>{{ $moduleLabel }} — Export</h1>
    <div class="meta">Range: {{ $rangeLabel }} &middot; Generated: {{ $generatedAt }} &middot; {{ $rows->count() }} record(s)</div>

    @if($rows->isEmpty())
        <div class="empty-note">No records found for the selected range.</div>
    @else
        <table>
            <thead>
                <tr>
                    @foreach($headers as $h)
                        <th>{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell ?? '-' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>