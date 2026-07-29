<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CCR Hourly Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { font-size: 14px; margin-bottom: 4px; }
        .meta { margin-bottom: 12px; color: #555; }
        .kpi { margin-bottom: 12px; }
        .kpi span { margin-right: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 3px 5px; text-align: right; }
        th { background: #f0f0f0; }
        td:first-child, th:first-child { text-align: left; }
        tr.total { font-weight: bold; background: #fafafa; }
    </style>
</head>
<body>
    <h1>CCR Hourly — {{ $site->code }} — {{ $material->label() }}</h1>
    <div class="meta">Tanggal: {{ $date->format('d M Y') }}</div>

    <div class="kpi">
        <span>DTD: {{ number_format($dtd['actual'], 0, ',', '.') }} / {{ number_format($dtd['plan'] ?? 0, 0, ',', '.') }} Mton</span>
        <span>MTD: {{ number_format($mtd['actual'], 0, ',', '.') }} / {{ number_format($mtd['plan'] ?? 0, 0, ',', '.') }} Mton</span>
        @if($hourlyTarget)
            <span>Target/jam: {{ number_format($hourlyTarget, 0, ',', '.') }} Mton</span>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
                <tr class="{{ $index === count($rows) - 1 ? 'total' : '' }}">
                    @foreach($row as $cell)
                        <td>{{ is_numeric($cell) ? number_format($cell, 0, ',', '.') : $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
