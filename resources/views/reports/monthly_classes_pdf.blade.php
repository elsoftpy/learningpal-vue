<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Detalle mensual de clases</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 84px 22px 26px 22px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 0;
        }
        .letterhead {
            position: fixed;
            top: -68px;
            left: 0;
            right: 0;
            display: table;
            width: 100%;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 6px;
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
            height: 42px;
        }
        .meta {
            margin-bottom: 10px;
            font-size: 9px;
            color: #475569;
        }
        .report-page {
            page-break-inside: avoid;
        }
        .report-page + .report-page {
            page-break-before: always;
        }
        .layout {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .left-pane {
            display: table-cell;
            width: 68%;
            vertical-align: top;
            padding-right: 10px;
            box-sizing: border-box;
        }
        .right-pane {
            display: table-cell;
            width: 32%;
            vertical-align: top;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px 7px;
            vertical-align: top;
            word-wrap: break-word;
        }
        .title-gray {
            background: #4b5563;
            color: #ffffff;
            font-weight: 700;
            text-align: center;
        }
        .title-blue,
        .header-blue,
        .summary-blue {
            background: #2563eb;
            color: #ffffff;
            font-weight: 700;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .sessions td:nth-child(4),
        .sessions td:nth-child(5),
        .sessions td:nth-child(6) {
            text-align: center;
        }
        .feedback-box {
            min-height: 160px;
            white-space: pre-wrap;
        }
        .footer {
            position: fixed;
            bottom: -10px;
            right: 0;
            font-size: 9px;
            color: #64748b;
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
        <div><strong>{{ $course['display_name'] ?? $course['name'] ?? '' }}</strong></div>
        <div>{{ $month['label'] ?? '' }}</div>
        <div>{{ $generatedAt ?? '' }}</div>
    </div>
</div>

@foreach($reports as $report)
    <div class="report-page">
        <div class="meta">
            <strong>Student:</strong> {{ $report['student_name'] ?? '' }}
            &nbsp;|&nbsp;
            <strong>Level:</strong> {{ $report['level'] ?? '' }}
            &nbsp;|&nbsp;
            <strong>Month:</strong> {{ $report['month_label'] ?? '' }}
        </div>

        <div class="layout">
            <div class="left-pane">
                    <table>
                        <tr>
                            <th colspan="6" class="title-gray">DETALLE MENSUAL DE CLASES</th>
                        </tr>
                        <tr class="header-blue center">
                            <th>Level</th>
                            <th>Student</th>
                            <th>Hours per class</th>
                            <th>Classes in month</th>
                            <th colspan="2">Previous carryover</th>
                        </tr>
                        <tr>
                            <td>{{ $report['level'] ?? '' }}</td>
                            <td>{{ $report['student_name'] ?? '' }}</td>
                            <td class="center">{{ number_format((float) ($report['hours_per_class'] ?? 0), 2, '.', '') }}</td>
                            <td class="center">{{ $report['classes_in_month'] ?? 0 }}</td>
                            <td colspan="2" class="center">{{ $report['previous_carryover'] ?? 0 }}</td>
                        </tr>
                        <tr class="title-gray center">
                            <td>{{ $report['month_name'] ?? '' }}</td>
                            <td>Total hours</td>
                            <td>{{ number_format((float) ($report['total_hours_in_month'] ?? 0), 2, '.', '') }}</td>
                            <td>Hours in favor</td>
                            <td>{{ number_format((float) ($report['hours_in_favor'] ?? 0), 2, '.', '') }}</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr class="header-blue center">
                            <th>Teacher</th>
                            <th>Course</th>
                            <th>Date</th>
                            <th>Hours</th>
                            <th>Attendance</th>
                            <th>Progress</th>
                        </tr>
                        @forelse(($report['sessions'] ?? []) as $session)
                            <tr class="sessions">
                                <td>{{ $session['teacher'] ?? '' }}</td>
                                <td>{{ $session['course'] ?? '' }}</td>
                                <td>{{ $session['display_date'] ?? '' }}</td>
                                <td>{{ number_format((float) ($session['hours'] ?? 0), 2, '.', '') }}</td>
                                <td>{{ $session['attendance'] ?? '' }}</td>
                                <td>{{ $session['progress'] ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="center">No session records</td>
                            </tr>
                        @endforelse
                        <tr>
                            <td colspan="3"></td>
                            <td class="center">{{ number_format((float) ($report['totals']['hours'] ?? 0), 2, '.', '') }}</td>
                            <td class="center">{{ number_format((float) ($report['totals']['attendance'] ?? 0), 2, '.', '') }}</td>
                            <td class="center">{{ number_format((float) ($report['totals']['attendance_percentage'] ?? 0), 2, '.', '') }}%</td>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                            <td class="center"><strong>Hours</strong></td>
                            <td class="center"><strong>Attendance</strong></td>
                            <td class="center"><strong>Attendance %</strong></td>
                        </tr>
                    </table>
            </div>
            <div class="right-pane">
                    <table>
                        <tr>
                            <th colspan="7" class="title-blue">RESUMEN DE CLASES</th>
                        </tr>
                        <tr class="summary-blue center">
                            <td colspan="5"></td>
                            <td>Total</td>
                            <td>%</td>
                        </tr>
                        <tr class="summary-blue">
                            <td colspan="5">Attendance</td>
                            <td class="center">{{ number_format((float) ($report['totals']['attendance'] ?? 0), 2, '.', '') }}</td>
                            <td class="center">{{ number_format((float) ($report['totals']['attendance_percentage'] ?? 0), 2, '.', '') }}%</td>
                        </tr>
                        <tr>
                            <td colspan="7" style="border:none; height: 12px;"></td>
                        </tr>
                        <tr>
                            <th colspan="7" class="title-blue">FEEDBACK</th>
                        </tr>
                        <tr>
                            <td colspan="7" class="feedback-box">{{ $report['feedback'] ?? '' }}</td>
                        </tr>
                    </table>
            </div>
        </div>
    </div>
@endforeach

<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
        $size = 8;
        $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
        $x = $pdf->get_width() - 86;
        $y = $pdf->get_height() - 18;
        $pdf->page_text($x, $y, $text, $font, $size, [0.39, 0.45, 0.51]);
    }
</script>
</body>
</html>