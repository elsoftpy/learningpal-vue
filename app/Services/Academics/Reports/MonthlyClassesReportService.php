<?php

namespace App\Services\Academics\Reports;

use App\Enums\AttendanceStatusEnum;
use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\Student;
use App\Services\Academics\Settings\CourseService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MonthlyClassesReportService
{
    public function monthOptions(int $courseId): Collection
    {
        return ClassSchedule::query()
            ->where('course_id', $courseId)
            ->orderByDesc('schedule_month')
            ->get()
            ->map(function (ClassSchedule $schedule) {
                $monthDate = $schedule->schedule_month instanceof Carbon
                    ? $schedule->schedule_month->copy()->startOfMonth()
                    : Carbon::parse($schedule->schedule_month)->startOfMonth();

                return [
                    'value' => $monthDate->format('Y-m'),
                    'label' => $monthDate->locale(app()->getLocale())->translatedFormat('F Y'),
                ];
            })
            ->unique('value')
            ->values();
    }

    public function studentOptions(int $courseId, ?string $month = null): Collection
    {
        $course = Course::query()
            ->with(['students.profile'])
            ->findOrFail($courseId);

        $monthStart = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : null;
        $monthEnd = $monthStart?->copy()->endOfMonth();

        if (! $monthStart || ! $monthEnd) {
            return $course->students
                ->sortBy(fn (Student $student) => $student->profile?->full_name ?? '')
                ->map(fn (Student $student) => [
                    'id' => $student->id,
                    'name' => $student->profile?->full_name ?? '',
                ])
                ->values();
        }

        $studentIdsWithAttendance = $this->attendanceStudentIdsForMonth($courseId, $monthStart, $monthEnd);

        return $course->students
            ->filter(fn (Student $student) => $studentIdsWithAttendance->contains($student->id))
            ->sortBy(fn (Student $student) => $student->profile?->full_name ?? '')
            ->map(fn (Student $student) => [
                'id' => $student->id,
                'name' => $student->profile?->full_name ?? '',
            ])
            ->values();
    }

    public function buildReportData(int $courseId, string $month, ?int $studentId = null): array
    {
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $schedule = $this->resolveSchedule($courseId, $monthStart);
        $course = $schedule->course;
        $courseDisplayName = (new CourseService())->getCourseDisplayName($course);

        $studentIds = $this->resolveReportStudentIds($course, $monthStart, $monthEnd, $studentId);
        $students = Student::query()
            ->with('profile')
            ->whereIn('id', $studentIds->all())
            ->get()
            ->keyBy('id');

        $carryoverDetails = $this->carryoverDetails($courseId, $monthStart);
        $reports = $studentIds
            ->map(function (int $resolvedStudentId) use ($schedule, $students, $carryoverDetails, $courseDisplayName, $monthStart) {
                $student = $students->get($resolvedStudentId);
                if (! $student) {
                    return null;
                }

                return $this->buildStudentReport(
                    schedule: $schedule,
                    student: $student,
                    carryoverDetails: $carryoverDetails,
                    courseDisplayName: $courseDisplayName,
                    monthStart: $monthStart,
                );
            })
            ->filter()
            ->values();

        return [
            'course' => [
                'id' => $course->id,
                'name' => $course->name,
                'display_name' => $courseDisplayName,
                'level' => $course->languageLevel?->level ?? '',
            ],
            'month' => [
                'value' => $monthStart->format('Y-m'),
                'label' => $monthStart->locale(app()->getLocale())->translatedFormat('F Y'),
            ],
            'reports' => $reports,
        ];
    }

    private function resolveSchedule(int $courseId, Carbon $monthStart): ClassSchedule
    {
        $schedule = ClassSchedule::query()
            ->with([
                'course.language',
                'course.languageLevel',
                'course.teachers.profile',
                'course.students.profile',
                'details.classRecord.teacher.profile',
                'details.classRecord.attendances.student.profile',
            ])
            ->where('course_id', $courseId)
            ->whereDate('schedule_month', $monthStart->toDateString())
            ->first();

        if (! $schedule) {
            throw new HttpException(422, __('No class schedule found for the selected course and month.'));
        }

        return $schedule;
    }

    private function resolveReportStudentIds(Course $course, Carbon $monthStart, Carbon $monthEnd, ?int $studentId = null): Collection
    {
        if ($studentId) {
            return collect([(int) $studentId]);
        }

        $courseStudentIds = $course->students->pluck('id')->map(fn ($id) => (int) $id);
        $attendanceStudentIds = $this->attendanceStudentIdsForMonth($course->id, $monthStart, $monthEnd);

        return $courseStudentIds
            ->filter(fn (int $resolvedStudentId) => $attendanceStudentIds->contains($resolvedStudentId))
            ->values();
    }

    private function attendanceStudentIdsForMonth(int $courseId, Carbon $monthStart, Carbon $monthEnd): Collection
    {
        return \App\Models\ClassRecordAttendance::query()
            ->select('student_id')
            ->whereHas('classRecord', function ($query) use ($courseId, $monthStart, $monthEnd) {
                $query
                    ->where('course_id', $courseId)
                    ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
            })
            ->distinct()
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    private function carryoverDetails(int $courseId, Carbon $monthStart): EloquentCollection
    {
        $carryoverStatuses = [
            ClassScheduleStatusEnum::PENDING->value,
            ClassScheduleStatusEnum::ONGOING->value,
            ClassScheduleStatusEnum::REPROGRAMED->value,
            'rescheduled',
        ];

        return ClassScheduleDetail::query()
            ->whereHas('classSchedule', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->whereDate('session_date', '<', $monthStart->toDateString())
            ->whereIn('status', $carryoverStatuses)
            ->get();
    }

    private function buildStudentReport(
        ClassSchedule $schedule,
        Student $student,
        EloquentCollection $carryoverDetails,
        string $courseDisplayName,
        Carbon $monthStart,
    ): array {
        $course = $schedule->course;
        $teacherNames = $course->teachers
            ->map(fn ($teacher) => $teacher->profile?->full_name)
            ->filter()
            ->values()
            ->implode(', ');

        $scheduleDetails = $schedule->details
            ->sortBy(fn (ClassScheduleDetail $detail) => sprintf(
                '%s|%s|%010d',
                $detail->session_date?->toDateString() ?? '',
                $detail->start_time?->format('H:i:s') ?? '',
                $detail->id,
            ))
            ->values();

        $hoursPerClass = $scheduleDetails->count() > 0
            ? round($scheduleDetails->avg(fn (ClassScheduleDetail $detail) => $this->effectiveDurationMinutes($detail)) / 60, 2)
            : 0.0;

        $sessions = $scheduleDetails->map(function (ClassScheduleDetail $detail) use ($student, $course, $teacherNames) {
            $record = $detail->classRecord;
            $attendance = $record?->attendances?->firstWhere('student_id', $student->id);
            $sessionHours = round($this->effectiveDurationMinutes($detail) / 60, 2);

            return [
                'teacher' => $record?->teacher?->profile?->full_name ?? $teacherNames,
                'course' => $course->name,
                'date' => ($record?->date ?? $detail->session_date)?->toDateString(),
                'display_date' => ($record?->date ?? $detail->session_date)?->format(match (app()->getLocale()) {
                    'es', 'pt' => 'd/m/Y',
                    'en' => 'm-d-Y',
                    default => 'Y-m-d',
                }),
                'hours' => $sessionHours,
                'attendance' => $attendance ? number_format((float) $attendance->attendance, 1, '.', '') : '',
                'attendance_label' => $attendance ? AttendanceStatusEnum::label((string) $attendance->attendance) : '',
                'progress' => $record?->comments ?? '',
            ];
        })->values();

        $totalHours = round($scheduleDetails->sum(fn (ClassScheduleDetail $detail) => $this->effectiveDurationMinutes($detail)) / 60, 2);
        $attendanceSum = round($sessions->sum(fn (array $session) => (float) ($session['attendance'] !== '' ? $session['attendance'] : 0)), 2);
        $attendancePercentage = $scheduleDetails->count() > 0
            ? round(($attendanceSum * 100) / $scheduleDetails->count(), 2)
            : 0.0;
        $hoursInFavor = round($carryoverDetails->sum(fn (ClassScheduleDetail $detail) => $this->effectiveDurationMinutes($detail)) / 60, 2);

        return [
            'student_id' => $student->id,
            'student_name' => $student->profile?->full_name ?? '',
            'course_name' => $course->name,
            'course_display_name' => $courseDisplayName,
            'level' => $course->languageLevel?->level ?? '',
            'month_name' => mb_strtoupper($monthStart->locale(app()->getLocale())->translatedFormat('F')),
            'month_label' => $monthStart->locale(app()->getLocale())->translatedFormat('F Y'),
            'hours_per_class' => $hoursPerClass,
            'classes_in_month' => $scheduleDetails->count(),
            'previous_carryover' => $carryoverDetails->count(),
            'total_hours_in_month' => $totalHours,
            'hours_in_favor' => $hoursInFavor,
            'feedback' => $schedule->feedback ?? '',
            'teacher_names' => $teacherNames,
            'sessions' => $sessions,
            'totals' => [
                'hours' => $totalHours,
                'attendance' => $attendanceSum,
                'attendance_percentage' => $attendancePercentage,
            ],
        ];
    }

    private function effectiveDurationMinutes(ClassScheduleDetail $detail): int
    {
        if ($detail->status === ClassScheduleStatusEnum::REPROGRAMED->value && $detail->rescheduled_estimated_duration_minutes) {
            return (int) $detail->rescheduled_estimated_duration_minutes;
        }

        return (int) ($detail->estimated_duration_minutes ?? 0);
    }
}