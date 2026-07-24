<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Report - {{ $entry->site->code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 14px; margin: 0; }
        .header p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 4px 8px; text-align: left; }
        th { background: #f0f0f0; }
        .summary { margin: 15px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT. ARKANANTA</h1>
        <p>{{ $header_code }}</p>
        <p>Daily Production Report - {{ $entry->site->name }} ({{ $entry->site->code }})</p>
        <p>Tanggal: {{ $entry->production_date->format('d/m/Y') }}</p>
    </div>

    <div class="summary">
        <strong>MTD OB:</strong> {{ number_format($mtd_ob, 2) }} Bcm |
        <strong>MTD Coal:</strong> {{ number_format($mtd_coal, 2) }} Ton |
        <strong>SR:</strong> {{ $sr ?? '-' }}
    </div>

    <h3>Produksi</h3>
    <table>
        <thead>
            <tr>
                <th>PIT</th>
                <th>Shift</th>
                <th>OB (Bcm)</th>
                <th>Coal (Ton)</th>
                <th>Coal Hauling</th>
                <th>Truck</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entry->productionRecords as $record)
            <tr>
                <td>{{ $record->pit->code }}</td>
                <td>{{ $record->shift->name }}</td>
                <td>{{ number_format($record->ob_removal_bcm, 2) }}</td>
                <td>{{ number_format($record->coal_getting_ton, 2) }}</td>
                <td>{{ number_format($record->coal_hauling_ton, 2) }}</td>
                <td>{{ $record->truck_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($entry->siteInfo)
    <h3>Info Site</h3>
    <p>Cuaca: {{ $entry->siteInfo->weather }} | Hujan: {{ $entry->siteInfo->rain_hours }} jam | Licin: {{ $entry->siteInfo->slippery_hours }} jam</p>
    @endif

    <p style="margin-top: 30px; font-size: 9px;">Generated: {{ $generated_at }}</p>
</body>
</html>
