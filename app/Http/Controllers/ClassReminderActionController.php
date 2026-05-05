<?php

namespace App\Http\Controllers;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassReminderAction;
use App\Models\ClassScheduleDetail;
use App\Models\Profile;
use App\Notifications\ClassStudentActionToTeacherNotification;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

use function filter_var;

class ClassReminderActionController extends Controller
{
    /**
     * Single entry point from email "Avisar" button.
     */
    public function showNotifyPage(Request $request, int $detail, int $student): View
    {
        $this->applyLocaleFromRequest($request);

        return $this->showChoicePage($request, $detail, $student);
    }

    /**
     * Legacy routes (old emails in inboxes) — delegate to choice page.
     */
    public function confirmPending(Request $request, int $detail, int $student): View
    {
        $this->applyLocaleFromRequest($request);

        return $this->showChoicePage($request, $detail, $student);
    }

    public function confirmUploadTask(Request $request, int $detail, int $student): View
    {
        $this->applyLocaleFromRequest($request);

        return $this->showChoicePage($request, $detail, $student);
    }

    public function execute(Request $request, string $action, int $detail, int $student): RedirectResponse
    {
        $this->applyLocaleFromRequest($request);

        if (! in_array($action, ['pending', 'upload_task'], true)) {
            abort(404);
        }

        return $this->processAction($detail, $student, $action);
    }

    public function showDonePage(): View
    {
        $this->applyLocaleFromRequest(request());

        return view('email-action.done');
    }

    private function showChoicePage(Request $request, int $detailId, int $studentId): View
    {
        $detail = $this->findAuthorizedDetail($detailId, $studentId);
        $course = $detail->classSchedule?->course;
        $existingAction = $course && $course->students->count() > 1
            ? $this->existingActionForDetail($detail->id)
            : null;

        if ($existingAction) {
            return view('email-action.done', [
                'doneStatus' => 'already',
                'actionContext' => $this->buildActionContext($detail, $studentId, $existingAction),
            ]);
        }

        $expiresAt = $request->query('expires')
            ? Carbon::createFromTimestamp((int) $request->query('expires'))
            : now()->addDays(2);

        $pendingExecuteUrl = URL::temporarySignedRoute(
            'email.class-reminder.execute',
            $expiresAt,
            ['action' => 'pending', 'detail' => $detailId, 'student' => $studentId, 'locale' => app()->getLocale()]
        );

        $uploadTaskExecuteUrl = URL::temporarySignedRoute(
            'email.class-reminder.execute',
            $expiresAt,
            ['action' => 'upload_task', 'detail' => $detailId, 'student' => $studentId, 'locale' => app()->getLocale()]
        );

        return view('email-action.notify', [
            'pendingExecuteUrl' => $pendingExecuteUrl,
            'uploadTaskExecuteUrl' => $uploadTaskExecuteUrl,
            'actionContext' => $this->buildActionContext($detail, $studentId),
        ]);
    }

    private function processAction(int $detailId, int $studentId, string $actionType): RedirectResponse
    {
        $detail = $this->findAuthorizedDetail($detailId, $studentId);
        $course = $detail->classSchedule?->course;
        $existingAction = null;
        $createdAction = null;
        $isMultiStudentCourse = $course?->students->count() > 1;

        DB::transaction(function () use ($detail, $studentId, $actionType, $isMultiStudentCourse, &$existingAction, &$createdAction): void {
            $existingAction = $isMultiStudentCourse
                ? $this->existingActionForDetail($detail->id, true)
                : null;

            if ($existingAction) {
                return;
            }

            try {
                $createdAction = ClassReminderAction::query()->create([
                    'class_schedule_detail_id' => $detail->id,
                    'student_id' => $studentId,
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
            return redirect()->route('email.class-reminder.done', ['locale' => app()->getLocale()])
                ->with('done_status', 'already')
                ->with('action_context', $this->buildActionContext($detail, $studentId, $existingAction));
        }

        $detail->status = $actionType === 'pending'
            ? ClassScheduleStatusEnum::PENDING->value
            : ClassScheduleStatusEnum::CANCELED->value;
        $detail->save();

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

        foreach ($recipients as $email) {
            Notification::route('mail', $email)
                ->notify(new ClassStudentActionToTeacherNotification(
                    teacherName: $teacherName,
                    studentName: $detail->classSchedule->course->students->find($studentId)?->profile?->full_name ?? '',
                    sessionDate: $classDate,
                    startTime: $classTime,
                    courseName: $course->name,
                    actionType: $actionType,
                ));
        }

        return redirect()->route('email.class-reminder.done', ['locale' => app()->getLocale()])
            ->with('done_status', 'success')
            ->with('action_context', $this->buildActionContext($detail, $studentId, $createdAction));
    }

    private function applyLocaleFromRequest(Request $request): void
    {
        $locale = $request->query('locale');
        $allowedLocales = config('app.available_locales', ['en', 'es', 'pt']);

        if (is_string($locale) && in_array($locale, $allowedLocales, true)) {
            App::setLocale($locale);
        }
    }

    private function assertStudentBelongsToCourse(int $detailId, int $studentId): void
    {
        $this->findAuthorizedDetail($detailId, $studentId);
    }

    private function findAuthorizedDetail(int $detailId, int $studentId): ClassScheduleDetail
    {
        $detail = ClassScheduleDetail::query()
            ->with([
                'classSchedule.course.students.profile.user',
                'classSchedule.course.teachers.profile.user',
            ])
            ->findOrFail($detailId);

        $course = $detail->classSchedule?->course;

        if (! $course || ! $course->students->contains('id', $studentId)) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        return $detail;
    }

    private function buildActionContext(ClassScheduleDetail $detail, int $studentId, ?ClassReminderAction $action = null): array
    {
        $course = $detail->classSchedule?->course;
        $student = $course?->students->find($studentId);
        $teacherNames = $course?->teachers
            ?->map(fn ($teacher): string => $teacher->profile?->full_name ?? '')
            ->filter()
            ->values() ?? collect();
        $actionStudent = $action?->student_id ? $course?->students->find($action->student_id) : null;

        return [
            'student_name' => $student?->profile?->full_name ?: __('Student'),
            'course_name' => $course?->name ?: __('Course'),
            'teacher_name' => $teacherNames->isNotEmpty() ? $teacherNames->join(', ') : __('Teacher'),
            'class_time' => ($detail->rescheduled_start_time ?? $detail->start_time)?->format('d/m/Y H:i') ?: '--',
            'processed_by_student_name' => $actionStudent?->profile?->full_name,
            'action_type' => $action?->action_type,
            'action_label' => $this->actionLabel($action?->action_type),
        ];
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

    private function actionLabel(?string $actionType): ?string
    {
        return match ($actionType) {
            'pending' => __('Leave Session Pending For Reschedule'),
            'upload_task' => __('Request Class Record Upload'),
            default => null,
        };
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
