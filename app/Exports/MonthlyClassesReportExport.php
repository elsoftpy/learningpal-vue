<?php

namespace App\Exports;

use App\Exports\Sheets\MonthlyClassesStudentReportSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MonthlyClassesReportExport implements WithMultipleSheets
{
    public function __construct(private readonly Collection $reports)
    {
    }

    public function sheets(): array
    {
        return $this->reports
            ->values()
            ->map(fn (array $report) => new MonthlyClassesStudentReportSheet($report))
            ->all();
    }
}