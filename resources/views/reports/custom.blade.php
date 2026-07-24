<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Custom Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT. ARKANANTA</h1>
        <p>{{ $header_code }}</p>
        <p>Custom Period Report</p>
        <p>{{ $filters['date_from'] }} — {{ $filters['date_to'] }}</p>
    </div>
</body>
</html>
