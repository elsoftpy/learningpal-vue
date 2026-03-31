<?php

namespace App\Console\Commands;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassScheduleDetail;
use App\Models\EmailLog;
use App\Notifications\ClassReminderActionableNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use function filter_var;

class SendClassEmailCommand extends Command
{
    protected $signature = 'elsoft:send-class-email {--dry-run : Preview notifications without sending emails} {--detail= : Send notification for a specific class_schedule_detail ID (for testing)}';

    protected $description = 'Send class reminder notifications using the configured lead time';

    public function __construct()
    {
        parent::__construct();

        $minutes = (int) config('services.class_notification.reminder_lead_minutes', 70);
        $this->description = "Send class reminder notifications {$minutes} minutes before class start";
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $detailId = $this->option('detail');
        $leadMinutes = (int) config('services.class_notification.reminder_lead_minutes', 70);
        $actionExpirationMinutes = (int) config('services.class_notification.action_expiration_minutes', 10);

        if ($detailId) {
            $detail = ClassScheduleDetail::with([
                'classSchedule.course.students.profile.user',
            ])->find($detailId);

            if (! $detail) {
                $this->error("ClassScheduleDetail #{$detailId} not found.");
                return self::FAILURE;
            }

            $classes = collect([$detail]);
            $this->info("Testing with ClassScheduleDetail #{$detailId} (status: {$detail->status})");
        } else {
            $targetStart = now()->startOfMinute()->addMinutes($leadMinutes);

            $classes = ClassScheduleDetail::query()
                ->with([
                    'classSchedule.course.students.profile.user',
                ])
                ->whereNotIn('status', [
                    ClassScheduleStatusEnum::COMPLETED->value,
                    ClassScheduleStatusEnum::CANCELED->value,
                ])
                ->where(function ($query) use ($targetStart): void {
                    $query
                        ->where(function ($subQuery) use ($targetStart): void {
                            $subQuery
                                ->whereNotNull('rescheduled_start_time')
                                ->where('rescheduled_start_time', $targetStart);
                        })
                        ->orWhere(function ($subQuery) use ($targetStart): void {
                            $subQuery
                                ->whereNull('rescheduled_start_time')
                                ->where('start_time', $targetStart);
                        });
                })
                ->get();
        }

        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($classes as $classDetail) {
            $course = $classDetail->classSchedule?->course;

            if (! $course) {
                $skipped++;
                continue;
            }

            $students = $course->students;
            $classTime = ($classDetail->rescheduled_start_time ?? $classDetail->start_time)?->format('H:i');
            $url = $course->chat_room_link ?: 'https://academy.ipl.com.py';

            foreach ($students as $student) {
                $profile = $student->profile;
                $user = $profile?->user;
                $email = $this->sanitizeEmail($profile?->email_alt)
                    ?: $this->sanitizeEmail($user?->email)
                    ?: $this->sanitizeEmail($profile?->email);

                if (! $profile || ! $email || ! $classTime) {
                    $skipped++;
                    Log::error('Class email reminder skipped due to missing profile, email, or class time.', [
                        'class_schedule_detail_id' => $classDetail->id,
                        'student_id' => $student->id,
                    ]);
                    continue;
                }

                $greeting = '¡Hola '.$profile->full_name.'!';
                $notifyUrl = URL::temporarySignedRoute(
                    'email.class-reminder.notify',
                    now()->addMinutes($actionExpirationMinutes),
                    [
                        'detail' => $classDetail->id,
                        'student' => $student->id,
                        'locale' => app()->getLocale(),
                    ]
                );

                if ($dryRun) {
                    $this->line(sprintf('DRY RUN -> %s (%s) at %s', $profile->full_name, $email, $classTime));
                    continue;
                }

                try {
                    if ($user) {
                        $user->notify(new ClassReminderActionableNotification(
                            hora: $classTime,
                            classUrl: $url,
                            greeting: $greeting,
                            notifyUrl: $notifyUrl,
                        ));
                    } else {
                        Notification::route('mail', $email)
                            ->notify(new ClassReminderActionableNotification(
                                hora: $classTime,
                                classUrl: $url,
                                greeting: $greeting,
                                notifyUrl: $notifyUrl,
                            ));
                    }

                    EmailLog::create([
                        'email_destino' => $email,
                        'greeting' => $greeting,
                        'hora' => $classTime,
                        'url' => $url,
                        'estado' => 'Enviado',
                    ]);

                    $sent++;
                } catch (\Throwable $exception) {
                    $failed++;

                    Log::error('Class email reminder failed.', [
                        'class_schedule_detail_id' => $classDetail->id,
                        'student_id' => $student->id,
                        'error' => $exception->getMessage(),
                    ]);

                    EmailLog::create([
                        'email_destino' => $email,
                        'greeting' => $greeting,
                        'hora' => $classTime,
                        'url' => $url,
                        'estado' => 'Error',
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        $summary = sprintf('Processed class reminders. Sent: %d, Failed: %d, Skipped: %d.', $sent, $failed, $skipped);
        $this->info($summary);

        return self::SUCCESS;
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
