<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherHoursExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(private readonly Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows->map(function (array $row) {
            return [
                'teacher' => $row['teacher'] ?? '',
                'course' => $row['course'] ?? '',
                'date' => $row['date'] ?? '',
                'hours' => $row['hours'] ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return [
            __('Teacher'),
            __('Course'),
            __('Date'),
            __('Hours'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:D1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:D1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF2563EB');

        return [];
    }
}
