<?php

namespace Tests\Feature;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\EmailLog;
use App\Models\Profile;
use App\Models\Student;
use App\Models\Teacher;
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

    public function test_class_reminder_email_log_includes_class_context(): void
    {
        Notification::fake();

        $studentProfile = Profile::factory()->create([
            'full_name' => 'Silvia Murdoch',
            'email' => 'silvia@example.com',
            'email_alt' => null,
        ]);
        $student = Student::factory()->create(['profile_id' => $studentProfile->id]);
        $teacherProfile = Profile::factory()->create(['full_name' => 'Ana Sanabria']);
        $teacher = Teacher::factory()->create(['profile_id' => $teacherProfile->id]);
        $course = Course::factory()->create([
            'name' => 'Ingles B1',
            'chat_room_link' => 'https://meet.google.com/paf-jfsv-qvf',
        ]);
        $course->students()->sync([$student->id]);
        $course->teachers()->sync([$teacher->id]);

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
            'session_date' => Carbon::parse('2026-05-30'),
            'start_time' => Carbon::parse('2026-05-30 08:00:00'),
            'rescheduled_date' => null,
            'rescheduled_start_time' => null,
        ]);

        $this->artisan('elsoft:send-class-email', ['--detail' => $detail->id])
            ->expectsOutputToContain('Sent: 1, Failed: 0, Skipped: 0.')
            ->assertSuccessful();

        $this->assertDatabaseHas('email_logs', [
            'class_schedule_detail_id' => $detail->id,
            'course_id' => $course->id,
            'course_name' => 'Ingles B1',
            'teacher_name' => 'Ana Sanabria',
            'student_name' => 'Silvia Murdoch',
            'email_destino' => 'silvia@example.com',
            'url' => 'https://meet.google.com/paf-jfsv-qvf',
            'estado' => 'Enviado',
        ]);

        $emailLog = EmailLog::query()->where('class_schedule_detail_id', $detail->id)->firstOrFail();

        $this->assertSame('2026-05-30', $emailLog->session_date->toDateString());
        $this->assertSame('08:00', $emailLog->start_time);
    }
}
