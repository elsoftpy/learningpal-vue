<?php

namespace App\Http\Controllers\Academics\Reports;

use App\Exports\MonthlyClassesReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\MonthlyClassesReportRequest;
use App\Services\Academics\Reports\MonthlyClassesReportService;
use App\Services\Utilities\ResponseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class MonthlyClassesReportController extends Controller
{
    public function index(MonthlyClassesReportRequest $request, MonthlyClassesReportService $reportService)
    {
        $data = $reportService->buildReportData(
            courseId: (int) $request->input('course_id'),
            month: (string) $request->input('month'),
            studentId: $request->filled('student_id') ? (int) $request->input('student_id') : null,
        );

        if ($data['reports']->isEmpty()) {
            return ResponseService::error(
                message: __('No monthly class reports found for the selected filters.'),
                errors: [
                    'report' => [__('No monthly class reports found for the selected filters.')],
                ],
                statusCode: 422,
            );
        }

        return ResponseService::success(
            message: __('Monthly classes report retrieved successfully.'),
            data: [
                'course' => $data['course'],
                'month' => $data['month'],
                'reports' => $data['reports']->values()->all(),
                'report_count' => $data['reports']->count(),
            ],
        );
    }

    public function monthOptions(Request $request, MonthlyClassesReportService $reportService)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ]);

        return ResponseService::success(
            message: __('Monthly report months retrieved successfully.'),
            data: [
                'months' => $reportService->monthOptions((int) $validated['course_id'])->all(),
            ],
        );
    }

    public function studentOptions(Request $request, MonthlyClassesReportService $reportService)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        return ResponseService::success(
            message: __('Monthly report students retrieved successfully.'),
            data: [
                'students' => $reportService->studentOptions(
                    courseId: (int) $validated['course_id'],
                    month: $validated['month'] ?? null,
                )->all(),
            ],
        );
    }

    public function exportExcel(MonthlyClassesReportRequest $request, MonthlyClassesReportService $reportService)
    {
        $data = $reportService->buildReportData(
            courseId: (int) $request->input('course_id'),
            month: (string) $request->input('month'),
            studentId: $request->filled('student_id') ? (int) $request->input('student_id') : null,
        );

        if ($data['reports']->isEmpty()) {
            return ResponseService::error(
                message: __('No monthly class reports found for the selected filters.'),
                errors: [
                    'report' => [__('No monthly class reports found for the selected filters.')],
                ],
                statusCode: 422,
            );
        }

        $fileName = $this->buildFileName($data['course']['name'], $data['month']['value']).'.xlsx';

        return Excel::download(new MonthlyClassesReportExport($data['reports']), $fileName);
    }

    public function exportPdf(MonthlyClassesReportRequest $request, MonthlyClassesReportService $reportService)
    {
        $data = $reportService->buildReportData(
            courseId: (int) $request->input('course_id'),
            month: (string) $request->input('month'),
            studentId: $request->filled('student_id') ? (int) $request->input('student_id') : null,
        );

        if ($data['reports']->isEmpty()) {
            return ResponseService::error(
                message: __('No monthly class reports found for the selected filters.'),
                errors: [
                    'report' => [__('No monthly class reports found for the selected filters.')],
                ],
                statusCode: 422,
            );
        }

        $pdf = Pdf::loadView('reports.monthly_classes_pdf', [
            'course' => $data['course'],
            'month' => $data['month'],
            'reports' => $data['reports']->values()->all(),
            'generatedAt' => $this->formatDateTimeForLocale(now()->setTimezone(config('app.timezone'))),
            'logoBase64' => $this->resolveLogoBase64(),
        ])
            ->setOption('isPhpEnabled', true)
            ->setOption('defaultPaperSize', 'a4')
            ->setPaper('a4', 'landscape');

        $fileName = $this->buildFileName($data['course']['name'], $data['month']['value']).'.pdf';

        return $pdf->download($fileName);
    }

    private function buildFileName(string $courseName, string $month): string
    {
        $safeCourse = $this->safeFilePart($courseName);
        $safeMonth = $this->safeFilePart(Carbon::createFromFormat('Y-m', $month)->translatedFormat('F_Y'));

        return "{$safeCourse}_monthly_classes_{$safeMonth}";
    }

    private function safeFilePart(string $value): string
    {
        return (string) Str::of($value)
            ->replaceMatches('/[^\pL\pN]+/u', '_')
            ->trim('_');
    }

    private function formatDateTimeForLocale(Carbon $date): string
    {
        return $date->format(match (app()->getLocale()) {
            'es', 'pt' => 'd/m/Y H:i:s',
            'en' => 'm-d-Y h:i:s A',
            default => 'Y-m-d H:i:s',
        });
    }

    private function resolveLogoBase64(): ?string
    {
        $logoPath = resource_path('js/images/brandLogo.png');
        if (! file_exists($logoPath)) {
            return null;
        }

        $mimeType = mime_content_type($logoPath) ?: 'image/png';
        $contents = file_get_contents($logoPath);
        if ($contents === false) {
            return null;
        }

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }
}