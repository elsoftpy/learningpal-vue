<?php

namespace App\Http\Controllers\Academics\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailLogReportRequest;
use App\Models\EmailLog;
use App\Services\Traits\FilterResolverTrait;
use App\Services\Utilities\ResponseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class EmailLogReportController extends Controller
{
    use FilterResolverTrait;

    public function index(EmailLogReportRequest $request)
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 15);
        $search = trim((string) $request->input('search', ''));
        $filters = $this->resolveFilters($request->input('filters'));
        $sortField = (string) $request->input('sort_field', 'created_at');
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc'));

        if (! in_array($sortField, ['id', 'email_destino', 'course_name', 'teacher_name', 'student_name', 'action_type', 'estado', 'created_at'], true)) {
            $sortField = 'created_at';
        }

        if (! in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }

        $query = EmailLog::query();

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('email_destino', 'like', '%'.$search.'%')
                    ->orWhere('greeting', 'like', '%'.$search.'%')
                    ->orWhere('course_name', 'like', '%'.$search.'%')
                    ->orWhere('teacher_name', 'like', '%'.$search.'%')
                    ->orWhere('student_name', 'like', '%'.$search.'%')
                    ->orWhere('action_type', 'like', '%'.$search.'%')
                    ->orWhere('estado', 'like', '%'.$search.'%')
                    ->orWhere('url', 'like', '%'.$search.'%')
                    ->orWhere('error', 'like', '%'.$search.'%');
            });
        }

        $this->applyFilters($query, $filters);

        $query->orderBy($sortField, $sortOrder);

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $logs = $paginated->getCollection()->map(function (EmailLog $emailLog): array {
            return [
                'id' => $emailLog->id,
                'class_schedule_detail_id' => $emailLog->class_schedule_detail_id,
                'course_id' => $emailLog->course_id,
                'course_name' => $emailLog->course_name,
                'session_date' => $this->formatDate($emailLog->session_date),
                'start_time' => $emailLog->start_time,
                'rescheduled_date' => $this->formatDate($emailLog->rescheduled_date),
                'rescheduled_start_time' => $emailLog->rescheduled_start_time,
                'teacher_name' => $emailLog->teacher_name,
                'student_name' => $emailLog->student_name,
                'action_type' => $emailLog->action_type,
                'email_destino' => $emailLog->email_destino,
                'greeting' => $emailLog->greeting,
                'hora' => $emailLog->hora,
                'url' => $emailLog->url,
                'estado' => $emailLog->estado,
                'error' => $emailLog->error,
                'created_at' => $this->formatDateTime($emailLog->created_at),
                'updated_at' => $this->formatDateTime($emailLog->updated_at),
            ];
        })->values();

        return ResponseService::success(
            message: __('Email logs retrieved successfully.'),
            data: [
                'logs' => $logs,
                'total' => $paginated->total(),
            ]
        );
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $estado = trim((string) ($filters['estado'] ?? ''));
        $actionType = trim((string) ($filters['action_type'] ?? ''));
        $emailDestino = trim((string) ($filters['email_destino'] ?? ''));
        $courseName = trim((string) ($filters['course_name'] ?? ''));
        $createdFrom = trim((string) ($filters['created_from'] ?? ''));
        $createdTo = trim((string) ($filters['created_to'] ?? ''));

        if ($estado !== '') {
            $query->where('estado', $estado);
        }

        if ($actionType !== '') {
            $query->where('action_type', $actionType);
        }

        if ($emailDestino !== '') {
            $query->where('email_destino', 'like', '%'.$emailDestino.'%');
        }

        if ($courseName !== '') {
            $query->where('course_name', 'like', '%'.$courseName.'%');
        }

        if ($createdFrom !== '') {
            $query->whereDate('created_at', '>=', $createdFrom);
        }

        if ($createdTo !== '') {
            $query->whereDate('created_at', '<=', $createdTo);
        }
    }

    private function formatDate(?Carbon $date): ?string
    {
        return $date?->toDateString();
    }

    private function formatDateTime(?Carbon $date): ?string
    {
        return $date?->format('Y-m-d H:i:s');
    }
}
