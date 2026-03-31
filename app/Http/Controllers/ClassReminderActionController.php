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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;
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
        $this->assertStudentBelongsToCourse($detailId, $studentId);

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
        ]);
    }

    private function processAction(int $detailId, int $studentId, string $actionType): RedirectResponse
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

        $alreadyProcessed = false;

        DB::transaction(function () use ($detail, $studentId, $actionType, &$alreadyProcessed): void {
            try {
                ClassReminderAction::query()->create([
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
            }
        });

        if ($alreadyProcessed) {
            return redirect()->route('email.class-reminder.done', ['locale' => app()->getLocale()])
                ->with('done_status', 'already');
        }

        if ($course->students->count() === 1) {
            $detail->status = $actionType === 'pending'
                ? ClassScheduleStatusEnum::PENDING->value
                : ClassScheduleStatusEnum::CANCELED->value;
            $detail->save();
        }

        $teacherNames = $course->teachers
            ->map(fn ($teacher): string => $teacher->profile?->full_name ?? '')
            ->filter()
            ->values();

        $teacherName = $teacherNames->isNotEmpty() ? $teacherNames->join(', ') : 'Docente';
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
                    startTime: $classTime,
                    courseName: $course->name,
                    actionType: $actionType,
                ));
        }

        return redirect()->route('email.class-reminder.done', ['locale' => app()->getLocale()])
            ->with('done_status', 'success');
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
        $detail = ClassScheduleDetail::query()
            ->with(['classSchedule.course.students'])
            ->findOrFail($detailId);

        $course = $detail->classSchedule?->course;

        if (! $course || ! $course->students->contains('id', $studentId)) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }
    }

    private function resolveProfileEmail(Profile $profile): ?string
    {
        return $this->sanitizeEmail($profile->email_alt)
            ?: $this->sanitizeEmail($profile->user?->email)
            ?: $this->sanitizeEmail($profile->email);
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
