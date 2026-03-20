<?php

namespace App\Http\Controllers\Academics\Reports;

use App\Exports\TeacherHoursExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherHoursReportRequest;
use App\Models\ClassRecord;
use App\Models\Course;
use App\Models\Teacher;
use App\Services\Utilities\ResponseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class TeacherHoursReportController extends Controller
{
    public function index(TeacherHoursReportRequest $request)
    {
        $page = (int) ($request->input('page', 1));
        $perPage = (int) ($request->input('per_page', 15));
        $sortField = (string) $request->input('sort_field', 'course');
        $sortOrder = (string) $request->input('sort_order', 'asc');

        [$startDate, $endDate, $filterType] = $this->resolveDateRange($request);

        $teacher = $this->resolveTeacher((int) $request->input('teacher_id'));
        $reportQuery = $this->buildReportQuery($request, $startDate, $endDate);

        switch ($sortField) {
            case 'date':
                $reportQuery->orderBy('date', $sortOrder);
                break;

            case 'hours':
                $reportQuery->orderBy('duration_minutes', $sortOrder);
                break;

            case 'course':
            default:
                $reportQuery->orderBy(
                    Course::query()
                        ->select('name')
                        ->whereColumn('courses.id', 'class_records.course_id')
                        ->limit(1),
                    $sortOrder
                );
                break;
        }

        if ($sortField !== 'date') {
            $reportQuery->orderBy('date', 'asc');
        }

        if (! (clone $reportQuery)->exists()) {
            return ResponseService::error(
                message: __('No teacher hours records found for the selected filters.'),
                errors: [
                    'report' => [__('No teacher hours records found for the selected filters.')],
                ],
                statusCode: 422
            );
        }

        $totalMinutes = (clone $reportQuery)->sum('duration_minutes');

        $paginated = $reportQuery->paginate($perPage, ['*'], 'page', $page);

        $rows = $paginated->getCollection()->map(function (ClassRecord $record) {
            return [
                'id' => $record->id,
                'teacher' => $record->teacher?->profile?->full_name,
                'course' => $record->course?->name,
                'date' => $record->date?->format('Y-m-d'),
                'hours' => round(((int) $record->duration_minutes) / 60, 2),
            ];
        })->values();

        return ResponseService::success(
            message: __('Teacher hours report retrieved successfully.'),
            data: [
                'teacher' => [
                    'id' => $teacher->id,
                    'full_name' => $teacher->profile?->full_name,
                ],
                'filters' => [
                    'type' => $filterType,
                    'from_date' => $startDate,
                    'to_date' => $endDate,
                ],
                'sort' => [
                    'field' => $sortField,
                    'order' => $sortOrder,
                ],
                'rows' => $rows,
                'total_hours' => round(((int) $totalMinutes) / 60, 2),
                'total' => $paginated->total(),
                'pagination' => [
                    'page' => $paginated->currentPage(),
                    'per_page' => $paginated->perPage(),
                    'last_page' => $paginated->lastPage(),
                ],
            ]
        );
    }

    public function exportExcel(TeacherHoursReportRequest $request)
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $teacher = $this->resolveTeacher((int) $request->input('teacher_id'));
        $rows = $this->buildExportRows($request, $startDate, $endDate);

        if ($rows->isEmpty()) {
            return ResponseService::error(
                message: __('No teacher hours records found for the selected filters.'),
                errors: [
                    'report' => [__('No teacher hours records found for the selected filters.')],
                ],
                statusCode: 422
            );
        }

        $fileName = $this->buildFileName($teacher->profile?->full_name ?? 'teacher', $startDate).'.xlsx';

        return Excel::download(new TeacherHoursExport($rows), $fileName);
    }

    public function exportPdf(TeacherHoursReportRequest $request)
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $teacher = $this->resolveTeacher((int) $request->input('teacher_id'));
        $rows = $this->buildExportRows($request, $startDate, $endDate);

        if ($rows->isEmpty()) {
            return ResponseService::error(
                message: __('No teacher hours records found for the selected filters.'),
                errors: [
                    'report' => [__('No teacher hours records found for the selected filters.')],
                ],
                statusCode: 422
            );
        }

        $logoBase64 = $this->resolveLogoBase64();
        $totalHours = $rows->sum(fn (array $row) => (float) $row['hours']);

        $pdf = Pdf::loadView('reports.teacher_hours_pdf', [
            'rows' => $rows,
            'teacherName' => $teacher->profile?->full_name ?? '-',
            'fromDate' => $this->formatDateForLocale($startDate),
            'toDate' => $this->formatDateForLocale($endDate),
            'generatedAt' => $this->formatDateTimeForLocale(now()->setTimezone(config('app.timezone'))),
            'totalHours' => $totalHours,
            'logoBase64' => $logoBase64,
        ])
            ->setOption('isPhpEnabled', true)
            ->setOption('defaultPaperSize', 'a4')
            ->setPaper('a4', 'portrait');

        $fileName = $this->buildFileName($teacher->profile?->full_name ?? 'teacher', $startDate).'.pdf';

        return $pdf->download($fileName);
    }

    private function resolveDateRange(TeacherHoursReportRequest $request): array
    {
        if ($request->filled('from_date') && $request->filled('to_date')) {
            return [
                $request->input('from_date'),
                $request->input('to_date'),
                'range',
            ];
        }

        return [
            $request->input('month_start_date'),
            $request->input('month_end_date'),
            'month',
        ];
    }

    private function resolveTeacher(int $teacherId): Teacher
    {
        return Teacher::query()
            ->with('profile')
            ->findOrFail($teacherId);
    }

    private function buildReportQuery(TeacherHoursReportRequest $request, string $startDate, string $endDate): Builder
    {
        $query = ClassRecord::query()
            ->with(['teacher.profile', 'course'])
            ->where('teacher_id', (int) $request->input('teacher_id'))
            ->where('mode', 'online')
            ->whereBetween('date', [$startDate, $endDate]);

        $selectedIds = collect($request->input('selected_row_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($selectedIds->isNotEmpty()) {
            $query->whereIn('id', $selectedIds->all());
        }

        return $query;
    }

    private function buildExportRows(TeacherHoursReportRequest $request, string $startDate, string $endDate): Collection
    {
        $query = $this->buildReportQuery($request, $startDate, $endDate)
            ->orderBy(
                Course::query()
                    ->select('name')
                    ->whereColumn('courses.id', 'class_records.course_id')
                    ->limit(1)
            )
            ->orderBy('date');

        return $query->get()->map(function (ClassRecord $record) {
            return [
                'id' => $record->id,
                'teacher' => $record->teacher?->profile?->full_name ?? '',
                'course' => $record->course?->name ?? '',
                'date' => $record->date ? $this->formatDateForLocale($record->date) : '',
                'hours' => round(((int) $record->duration_minutes) / 60, 2),
            ];
        })->values();
    }

    private function formatDateForLocale(string|Carbon $date): string
    {
        $dateValue = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $dateValue->format(match (app()->getLocale()) {
            'es', 'pt' => 'd/m/Y',
            'en' => 'm-d-Y',
            default => 'Y-m-d',
        });
    }

    private function formatDateTimeForLocale(Carbon $date): string
    {
        return $date->format(match (app()->getLocale()) {
            'es', 'pt' => 'd/m/Y H:i:s',
            'en' => 'm-d-Y h:i:s A',
            default => 'Y-m-d H:i:s',
        });
    }

    private function buildFileName(string $teacherName, string $startDate): string
    {
        $monthName = Carbon::parse($startDate)
            ->locale(app()->getLocale())
            ->translatedFormat('F');

        $safeTeacher = $this->safeFilePart($teacherName);
        $safeMonth = $this->safeFilePart($monthName);
        $timestamp = now()->format('YmdHi');

        return "{$safeTeacher}_{$safeMonth}_{$timestamp}";
    }

    private function safeFilePart(string $value): string
    {
        return (string) Str::of($value)
            ->replaceMatches('/[^\pL\pN]+/u', '_')
            ->trim('_');
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
