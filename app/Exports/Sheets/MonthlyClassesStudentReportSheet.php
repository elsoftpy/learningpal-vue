<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class MonthlyClassesStudentReportSheet implements WithEvents, WithTitle
{
    private const COLOR_BLUE = 'FF2563EB';

    private const COLOR_GRAY = 'FF4B5563';

    private const COLOR_WHITE = 'FFFFFFFF';

    private const COLOR_BORDER = 'FFD1D5DB';

    public function __construct(private readonly array $report)
    {
    }

    public function title(): string
    {
        $title = $this->report['student_name'] ?? 'Report';

        return mb_substr($title, 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sessionStartRow = 6;
                $sessionCount = count($this->report['sessions'] ?? []);
                $sessionEndRow = max($sessionStartRow, $sessionStartRow + $sessionCount - 1);
                $totalsRow = $sessionEndRow + 1;
                $labelsRow = $totalsRow + 1;
                $feedbackEndRow = max(8, $labelsRow);

                $sheet->setShowGridlines(false);
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                $sheet->getPageMargins()
                    ->setTop(0.4)
                    ->setRight(0.3)
                    ->setLeft(0.3)
                    ->setBottom(0.4);

                foreach ([
                    'A' => 20,
                    'B' => 28,
                    'C' => 14,
                    'D' => 14,
                    'E' => 14,
                    'F' => 38,
                    'G' => 3,
                    'H' => 3,
                    'I' => 3,
                    'J' => 14,
                    'K' => 14,
                    'L' => 14,
                    'M' => 14,
                    'N' => 14,
                    'O' => 12,
                    'P' => 12,
                ] as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }

                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('E2:F2');
                $sheet->mergeCells('J1:P1');
                $sheet->mergeCells('J3:N3');
                $sheet->mergeCells("J5:P5");
                $sheet->mergeCells("J6:P{$feedbackEndRow}");

                $sheet->setCellValue('A1', 'DETALLE MENSUAL DE CLASES');
                $sheet->setCellValue('A2', 'Level');
                $sheet->setCellValue('B2', 'Student');
                $sheet->setCellValue('C2', 'Hours per class');
                $sheet->setCellValue('D2', 'Classes in month');
                $sheet->setCellValue('E2', 'Previous carryover');
                $sheet->setCellValue('A3', $this->report['level'] ?? '');
                $sheet->setCellValue('B3', $this->report['student_name'] ?? '');
                $sheet->setCellValue('C3', $this->formatDecimal($this->report['hours_per_class'] ?? 0));
                $sheet->setCellValue('D3', (int) ($this->report['classes_in_month'] ?? 0));
                $sheet->setCellValue('E3', (int) ($this->report['previous_carryover'] ?? 0));

                $sheet->setCellValue('A4', $this->report['month_name'] ?? '');
                $sheet->setCellValue('B4', 'Total hours');
                $sheet->setCellValue('C4', $this->formatDecimal($this->report['total_hours_in_month'] ?? 0));
                $sheet->setCellValue('D4', 'Hours in favor');
                $sheet->setCellValue('E4', $this->formatDecimal($this->report['hours_in_favor'] ?? 0));

                $sheet->setCellValue('A5', 'Teacher');
                $sheet->setCellValue('B5', 'Course');
                $sheet->setCellValue('C5', 'Date');
                $sheet->setCellValue('D5', 'Hours');
                $sheet->setCellValue('E5', 'Attendance');
                $sheet->setCellValue('F5', 'Progress');

                foreach (($this->report['sessions'] ?? []) as $index => $session) {
                    $row = $sessionStartRow + $index;

                    $sheet->setCellValue("A{$row}", $session['teacher'] ?? '');
                    $sheet->setCellValue("B{$row}", $session['course'] ?? '');
                    $sheet->setCellValue("C{$row}", $session['display_date'] ?? $session['date'] ?? '');
                    $sheet->setCellValue("D{$row}", $this->formatDecimal($session['hours'] ?? 0));
                    $sheet->setCellValue("E{$row}", $session['attendance'] ?? '');
                    $sheet->setCellValue("F{$row}", $session['progress'] ?? '');
                }

                $sheet->setCellValue("D{$totalsRow}", $this->formatDecimal($this->report['totals']['hours'] ?? 0));
                $sheet->setCellValue("E{$totalsRow}", $this->formatDecimal($this->report['totals']['attendance'] ?? 0));
                $sheet->setCellValue("F{$totalsRow}", $this->formatPercentage($this->report['totals']['attendance_percentage'] ?? 0));
                $sheet->setCellValue("D{$labelsRow}", 'Hours');
                $sheet->setCellValue("E{$labelsRow}", 'Attendance');
                $sheet->setCellValue("F{$labelsRow}", 'Attendance %');

                $sheet->setCellValue('J1', 'RESUMEN DE CLASES');
                $sheet->setCellValue('O2', 'Total');
                $sheet->setCellValue('P2', '%');
                $sheet->setCellValue('J3', 'Attendance');
                $sheet->setCellValue('O3', $this->formatDecimal($this->report['totals']['attendance'] ?? 0));
                $sheet->setCellValue('P3', $this->formatPercentage($this->report['totals']['attendance_percentage'] ?? 0));
                $sheet->setCellValue('J5', 'FEEDBACK');
                $sheet->setCellValue('J6', $this->report['feedback'] ?? '');

                $sheet->getStyle("A1:F1")->applyFromArray($this->titleStyle(self::COLOR_GRAY));
                $sheet->getStyle("J1:P1")->applyFromArray($this->titleStyle(self::COLOR_BLUE));
                $sheet->getStyle("A2:F2")->applyFromArray($this->titleStyle(self::COLOR_BLUE));
                $sheet->getStyle("A4:F4")->applyFromArray($this->titleStyle(self::COLOR_GRAY));
                $sheet->getStyle("A5:F5")->applyFromArray($this->titleStyle(self::COLOR_BLUE));
                $sheet->getStyle("J2:P6")->applyFromArray($this->titleStyle(self::COLOR_BLUE));
                $sheet->getStyle("A3:F3")->applyFromArray($this->bodyStyle());
                $sheet->getStyle("A6:F{$sessionEndRow}")->applyFromArray($this->bodyStyle());
                $sheet->getStyle("D{$totalsRow}:F{$labelsRow}")->applyFromArray($this->bodyStyle());
                $sheet->getStyle("J6:P{$feedbackEndRow}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("F6:F{$sessionEndRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("A1:P{$feedbackEndRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A1:P{$feedbackEndRow}")->getFont()->setName('Arial')->setSize(10);
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(5)->setRowHeight(22);
                $sheet->getRowDimension(6)->setRowHeight(36);

                for ($row = 6; $row <= $sessionEndRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(36);
                }

                for ($row = 1; $row <= $feedbackEndRow; $row++) {
                    if ($row < 6 || $row > $sessionEndRow) {
                        $sheet->getStyle("G{$row}:I{$row}")->applyFromArray($this->blankDividerStyle());
                    }
                }
            },
        ];
    }

    private function titleStyle(string $fillColor): array
    {
        return [
            'font' => [
                'bold' => true,
                'color' => ['argb' => self::COLOR_WHITE],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => $fillColor],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => self::COLOR_BORDER],
                ],
            ],
        ];
    }

    private function bodyStyle(): array
    {
        return [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => self::COLOR_BORDER],
                ],
            ],
        ];
    }

    private function blankDividerStyle(): array
    {
        return [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFFFF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_NONE,
                ],
            ],
        ];
    }

    private function formatDecimal(float|int|string $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function formatPercentage(float|int|string $value): string
    {
        return number_format((float) $value, 2, '.', '').'%';
    }
}