<?php

namespace App\Services\Academics\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassReminderAction;
use App\Models\ClassScheduleDetail;
use App\Models\ClassScheduleDetailStatusHistory;
use App\Models\Profile;
use App\Models\Student;
use App\Notifications\ClassStudentActionToTeacherNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use App\Models\EmailLog;

class ClassSessionActionService
{
    /**
     * Perform a student-initiated session action (pending or upload_task).
     * Returns true if action was performed, false if already processed.
     */
    public function performAction(ClassScheduleDetail $detail, Student $student, string $actionType): bool
    {
        $detail->loadMissing([
            'classSchedule.course.students.profile.user',
            'classSchedule.course.teachers.profile.user',
        ]);

        $course = $detail->classSchedule?->course;
        $isMultiStudentCourse = $course?->students->count() > 1;
        $existingAction = null;
        $createdAction = null;

        DB::transaction(function () use ($detail, $student, $actionType, $isMultiStudentCourse, &$existingAction, &$createdAction): void {
            $existingAction = $isMultiStudentCourse
                ? $this->existingActionForDetail($detail->id, true)
                : null;

            if ($existingAction) {
                return;
            }

            try {
                $createdAction = ClassReminderAction::query()->create([
                    'class_schedule_detail_id' => $detail->id,
                    'student_id' => $student->id,
                    'action_type' => $actionType,
                    'processed_at' => now(),
                ]);
            } catch (QueryException $exception) {
                $alreadyProcessed = in_array($exception->getCode(), ['23000', '23505'], true);

                if (! $alreadyProcessed) {
                    throw $exception;
                }

                $existingAction = $this->existingActionForDetail($detail->id, true);
            }
        });

        if ($existingAction) {
            return false;
        }

        $oldStatus = $detail->status;
        $newStatus = $actionType === 'pending'
            ? ClassScheduleStatusEnum::PENDING->value
            : ClassScheduleStatusEnum::CANCELED->value;

        $detail->status = $newStatus;
        $detail->save();

        $studentUserId = $student->profile?->user?->id;

        ClassScheduleDetailStatusHistory::query()->create([
            'class_schedule_detail_id' => $detail->id,
            'changed_by_user_id' => $studentUserId,
            'changed_by_type' => 'student',
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'action_type' => $actionType,
            'created_at' => now(),
        ]);

        $this->sendNotifications($detail, $student->id, $actionType);

        return true;
    }

    private function sendNotifications(ClassScheduleDetail $detail, int $studentId, string $actionType): void
    {
        $course = $detail->classSchedule?->course;

        $teacherNames = $course->teachers
            ->map(fn ($teacher): string => $teacher->profile?->full_name ?? '')
            ->filter()
            ->values();

        $teacherName = $teacherNames->isNotEmpty() ? $teacherNames->join(', ') : 'Docente';
        $classDate = ($detail->rescheduled_date ?? $detail->session_date)?->format('d/m/Y') ?? '--/--/----';
        $classTime = ($detail->rescheduled_start_time ?? $detail->start_time)?->format('H:i') ?? '--:--';

        $teacherRecipients = collect(
            $course->teachers
                ->map(fn ($teacher): ?string => $teacher->profile ? $this->resolveProfileEmail($teacher->profile) : null)
                ->all()
        )
            ->filter()
            ->values();

        $recipients = $teacherRecipients
            ->push($this->sanitizeEmail(config('mail.from.address')))
            ->push($this->sanitizeEmail(config('services.class_notification.cc')))
            ->filter()
            ->unique()
            ->values();

        $studentName = $course->students->find($studentId)?->profile?->full_name ?? '';
        $courseName = $course->name;
        $detailId = $detail->id;
        $courseId = $course?->id;

        foreach ($recipients as $email) {
            $logData = [
                'class_schedule_detail_id' => $detailId,
                'course_id' => $courseId,
                'course_name' => $courseName,
                'session_date' => $detail->session_date?->toDateString(),
                'start_time' => $detail->start_time?->format('H:i'),
                'rescheduled_date' => $detail->rescheduled_date?->toDateString(),
                'rescheduled_start_time' => $detail->rescheduled_start_time?->format('H:i'),
                'teacher_name' => $teacherName,
                'student_name' => $studentName,
                'action_type' => $actionType,
                'email_destino' => $email,
            ];
            try {
                Notification::route('mail', $email)
                    ->notify(new ClassStudentActionToTeacherNotification(
                        teacherName: $teacherName,
                        studentName: $studentName,
                        sessionDate: $classDate,
                        startTime: $classTime,
                        courseName: $courseName,
                        actionType: $actionType,
                    ));

                EmailLog::create([
                    'email_destino' => $email,
                    'greeting' => $teacherName,
                    'hora' => $classTime,
                    'url' => null,
                    'estado' => 'Enviado',
                    'error' => null,
                ] + $logData);

                Log::info('ClassStudentActionToTeacherNotification sent', $logData + ['estado' => 'Enviado']);
            } catch (\Throwable $exception) {
                EmailLog::create([
                    'email_destino' => $email,
                    'greeting' => $teacherName,
                    'hora' => $classTime,
                    'url' => null,
                    'estado' => 'Error',
                    'error' => $exception->getMessage(),
                ] + $logData);

                Log::error('ClassStudentActionToTeacherNotification failed', $logData + [
                    'estado' => 'Error',
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function existingActionForDetail(int $detailId, bool $lockForUpdate = false): ?ClassReminderAction
    {
        $query = ClassReminderAction::query()
            ->where('class_schedule_detail_id', $detailId)
            ->orderBy('processed_at')
            ->orderBy('id');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function resolveProfileEmail(Profile $profile): ?string
    {
        return $this->sanitizeEmail($profile->user?->email)
            ?: $this->sanitizeEmail($profile->email)
            ?: $this->sanitizeEmail($profile->email_alt);
    }

    private function sanitizeEmail(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $email = trim($value);

        if ($email === '' || in_array(strtolower($email), ['undefined', 'null'], true)) {
            return null;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
}
