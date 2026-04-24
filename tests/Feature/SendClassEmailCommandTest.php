<?php

namespace Tests\Feature;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\EmailLog;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendClassEmailCommandTest extends TestCase
{
    public function test_pending_status_session_does_not_send_class_reminder_notification(): void
    {
        Notification::fake();

        $student = Student::factory()->create();
        $course = Course::factory()->create(['name' => 'Ingles B1']);
        $course->students()->sync([$student->id]);

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::PENDING->value,
            'start_time' => Carbon::parse('2026-04-24 10:00:00'),
            'rescheduled_start_time' => null,
        ]);

        $this->artisan('elsoft:send-class-email', ['--detail' => $detail->id])
            ->expectsOutputToContain('status: pending')
            ->expectsOutputToContain('Sent: 0, Failed: 0, Skipped: 1.')
            ->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertSame(0, EmailLog::query()->count());
    }
}
