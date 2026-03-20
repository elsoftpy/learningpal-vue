<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Teacher Hours Report') }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 95px 32px 56px 32px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0f172a;
            margin: 0;
        }
        .letterhead {
            position: fixed;
            top: -78px;
            left: 0;
            right: 0;
            display: table;
            width: 100%;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 8px;
        }
        .letterhead-left,
        .letterhead-right {
            display: table-cell;
            vertical-align: middle;
        }
        .letterhead-right {
            text-align: right;
        }
        .logo {
            height: 46px;
        }
        .title {
            font-size: 18px;
            font-weight: 700;
            text-align: right;
        }
        .page-indicator-space {
            height: 12px;
        }
        .meta {
            margin-bottom: 12px;
        }
        .meta-grid {
            width: 100%;
            display: table;
        }
        .meta-col {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .meta-col.right {
            text-align: right;
        }
        .meta-line {
            margin: 0 0 4px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }
        thead {
            display: table-header-group;
        }
        tfoot {
            display: table-row-group;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        th {
            background: #2563eb;
            color: #ffffff;
            text-align: left;
            font-weight: 700;
        }
        td.hours {
            text-align: right;
        }
        .total {
            margin-top: 10px;
            font-weight: 700;
            text-align: right;
        }
        .page-footer {
            position: fixed;
            bottom: -34px;
            left: 0;
            right: 0;
            text-align: right;
            font-size: 10px;
            color: #475569;
        }
    </style>
</head>
<body>
<div class="letterhead">
    <div class="letterhead-left">
        @if(!empty($logoBase64))
            <img src="{{ $logoBase64 }}" alt="Brand Logo" class="logo">
        @endif
    </div>
    <div class="letterhead-right">
        <div class="title">{{ __('Teacher Hours Report') }}</div>
        <div class="page-indicator-space"></div>
    </div>
</div>

<div class="meta">
    <div class="meta-grid">
        <div class="meta-col">
            <p class="meta-line"><strong>{{ __('Teacher') }}:</strong> {{ $teacherName }}</p>
        </div>
        <div class="meta-col right">
            <p class="meta-line"><strong>{{ __('Generated At') }}:</strong> {{ $generatedAt ?? '' }}</p>
        </div>
    </div>
    <p class="meta-line"><strong>{{ __('From') }}:</strong> {{ $fromDate }}</p>
    <p class="meta-line"><strong>{{ __('To') }}:</strong> {{ $toDate }}</p>
</div>

<table>
    <thead>
    <tr>
        <th>{{ __('Teacher') }}</th>
        <th>{{ __('Course') }}</th>
        <th>{{ __('Date') }}</th>
        <th>{{ __('Hours') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        <tr>
            <td>{{ $row['teacher'] }}</td>
            <td>{{ $row['course'] }}</td>
            <td>{{ $row['date'] }}</td>
            <td class="hours">{{ number_format((float) $row['hours'], 2, '.', '') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<p class="total">{{ __('Total Hours') }}: {{ number_format((float) $totalHours, 2, '.', '') }}</p>

<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
        $size = 9;
        $text = "{{ __('Page') }} {PAGE_NUM} / {PAGE_COUNT}";
        $rightMargin = 32;
        // Estimate width with a realistic max pattern, then right-align to the printable margin.
        $textWidth = $fontMetrics->getTextWidth('Page 999 / 999', $font, $size);
        $x = $pdf->get_width() - $rightMargin - $textWidth;
        $y = 40;
        $pdf->page_text($x, $y, $text, $font, $size, [0.28, 0.35, 0.45]);
    }
</script>
</body>
</html>
