<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consolidated Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 14px; margin: 0; }
        .header p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 12px; }
        th, td { border: 1px solid #333; padding: 3px 6px; text-align: left; }
        th { background: #f0f0f0; }
        .summary { margin: 15px 0; }
        .site-section { page-break-before: always; margin-top: 20px; }
        .site-section:first-of-type { page-break-before: auto; }
        h3 { margin: 12px 0 4px; font-size: 11px; }
        h2 { margin: 0 0 8px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT. ARKANANTA</h1>
        <p>{{ $header_code }}</p>
        <p>Laporan Konsolidasi Operasional</p>
        <p>Periode: {{ $date_from }} — {{ $date_to }}</p>
        <p>Site: {{ $sites->pluck('code')->join(', ') }}</p>
    </div>

    <div class="summary">
        <strong>Total OB:</strong> {{ number_format($totals['ob'], 2) }} Bcm |
        <strong>Total Coal:</strong> {{ number_format($totals['coal'], 2) }} Ton |
        <strong>Total Hauling:</strong> {{ number_format($totals['hauling'], 2) }} Ton |
        <strong>Total Fuel:</strong> {{ number_format($totals['fuel_liters'], 2) }} L |
        <strong>SR:</strong> {{ $totals['sr'] ?? '-' }}
    </div>

    <h3>Ringkasan per Site</h3>
    <table>
        <thead>
            <tr>
                <th>Site</th>
                <th>OB (Bcm)</th>
                <th>Coal (Ton)</th>
                <th>Hauling (Ton)</th>
                <th>Fuel (L)</th>
                <th>SR</th>
            </tr>
        </thead>
        <tbody>
            @foreach($per_site as $row)
            <tr>
                <td>{{ $row['site']->code }} — {{ $row['site']->name }}</td>
                <td>{{ number_format($row['totals']['ob'], 2) }}</td>
                <td>{{ number_format($row['totals']['coal'], 2) }}</td>
                <td>{{ number_format($row['totals']['hauling'], 2) }}</td>
                <td>{{ number_format($row['totals']['fuel_liters'], 2) }}</td>
                <td>{{ $row['totals']['sr'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @foreach($per_site as $row)
    <div class="site-section">
        <h2>{{ $row['site']->name }} ({{ $row['site']->code }})</h2>
        <p>
            <strong>OB:</strong> {{ number_format($row['totals']['ob'], 2) }} Bcm |
            <strong>Coal:</strong> {{ number_format($row['totals']['coal'], 2) }} Ton |
            <strong>Fuel:</strong> {{ number_format($row['totals']['fuel_liters'], 2) }} L |
            <strong>SR:</strong> {{ $row['totals']['sr'] ?? '-' }}
        </p>

        <h3>Produksi</h3>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>PIT</th>
                    <th>Shift</th>
                    <th>OB (Bcm)</th>
                    <th>Coal (Ton)</th>
                    <th>Coal Hauling</th>
                    <th>Truck</th>
                </tr>
            </thead>
            <tbody>
                @foreach($row['entries'] as $entry)
                    @foreach($entry->productionRecords as $record)
                    <tr>
                        <td>{{ $entry->production_date->format('d/m/Y') }}</td>
                        <td>{{ $record->pit->code }}</td>
                        <td>{{ $record->shift->name }}</td>
                        <td>{{ number_format($record->ob_removal_bcm, 2) }}</td>
                        <td>{{ number_format($record->coal_getting_ton, 2) }}</td>
                        <td>{{ number_format($record->coal_hauling_ton, 2) }}</td>
                        <td>{{ $record->truck_count }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <h3>Fuel</h3>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Unit</th>
                    <th>Jenis BBM</th>
                    <th>Shift</th>
                    <th>Liters</th>
                    <th>Jam Kerja</th>
                </tr>
            </thead>
            <tbody>
                @foreach($row['entries'] as $entry)
                    @foreach($entry->fuelRecords as $record)
                    <tr>
                        <td>{{ $entry->production_date->format('d/m/Y') }}</td>
                        <td>{{ $record->unit_code }}</td>
                        <td>{{ $record->fuelType?->name ?? '-' }}</td>
                        <td>{{ $record->shift->name }}</td>
                        <td>{{ number_format($record->liters, 2) }}</td>
                        <td>{{ number_format($record->working_hours, 2) }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <h3>Deployment</h3>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Unit</th>
                    <th>PIT</th>
                    <th>Shift</th>
                    <th>OB (Bcm)</th>
                    <th>Coal (Ton)</th>
                    <th>Operator</th>
                </tr>
            </thead>
            <tbody>
                @foreach($row['entries'] as $entry)
                    @foreach($entry->equipmentDeployments as $record)
                    <tr>
                        <td>{{ $entry->production_date->format('d/m/Y') }}</td>
                        <td>{{ $record->unit_code }}</td>
                        <td>{{ $record->pit->code }}</td>
                        <td>{{ $record->shift->name }}</td>
                        <td>{{ number_format($record->prod_ob_bcm, 2) }}</td>
                        <td>{{ number_format($record->prod_coal_ton, 2) }}</td>
                        <td>{{ $record->operator_name }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <h3>Info Site</h3>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Cuaca</th>
                    <th>Hujan (jam)</th>
                    <th>Licin (jam)</th>
                    <th>MP Plan</th>
                    <th>MP Aktual</th>
                    <th>Stok BBM (L)</th>
                    <th>Catatan Keselamatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($row['entries'] as $entry)
                    @if($entry->siteInfo)
                    <tr>
                        <td>{{ $entry->production_date->format('d/m/Y') }}</td>
                        <td>{{ $entry->siteInfo->weather }}</td>
                        <td>{{ $entry->siteInfo->rain_hours }}</td>
                        <td>{{ $entry->siteInfo->slippery_hours }}</td>
                        <td>{{ $entry->siteInfo->manpower_plan }}</td>
                        <td>{{ $entry->siteInfo->manpower_actual }}</td>
                        <td>{{ number_format($entry->siteInfo->fuel_stock_liters ?? 0, 2) }}</td>
                        <td>{{ $entry->siteInfo->safety_notes }}</td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <p style="margin-top: 30px; font-size: 9px;">Generated: {{ $generated_at }}</p>
</body>
</html>
