<?php

namespace Tests\Feature;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassReminderAction;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\Profile;
use App\Models\Student;
use App\Models\Teacher;
use App\Notifications\ClassStudentActionToTeacherNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ClassReminderActionControllerTest extends TestCase
{
    public function test_notify_page_shows_choice_page_without_side_effects(): void
    {
        Notification::fake();

        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create();

        $course = Course::factory()->create(['name' => 'Ingles B1']);
        $course->teachers()->sync([$teacher->id]);
        $course->students()->sync([$student->id]);

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
            'start_time' => Carbon::parse('2026-03-20 09:00:00'),
            'rescheduled_start_time' => null,
        ]);

        $notifyUrl = URL::temporarySignedRoute('email.class-reminder.notify', now()->addHour(), [
            'detail' => $detail->id,
            'student' => $student->id,
        ]);

        $response = $this->get($notifyUrl);

        $response->assertOk();
        $response->assertSee(__('Leave Pending'));
        $response->assertSee(__('Upload Task'));
        $this->assertSame(ClassScheduleStatusEnum::SCHEDULED->value, $detail->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_pending_execute_marks_single_student_course_as_pending_and_notifies(): void
    {
        Notification::fake();

        config()->set('mail.from.address', 'sender@example.com');
        config()->set('services.class_notification.cc', 'cc@example.com');

        $teacherProfile = Profile::factory()->create([
            'full_name' => 'Docente Uno',
            'email' => 'teacher@example.com',
        ]);
        $teacher = Teacher::factory()->create(['profile_id' => $teacherProfile->id]);

        $studentProfile = Profile::factory()->create();
        $student = Student::factory()->create(['profile_id' => $studentProfile->id]);

        $course = Course::factory()->create(['name' => 'Ingles B1']);
        $course->teachers()->sync([$teacher->id]);
        $course->students()->sync([$student->id]);

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
            'start_time' => Carbon::parse('2026-03-20 09:00:00'),
            'rescheduled_start_time' => null,
        ]);

        $executeUrl = URL::temporarySignedRoute('email.class-reminder.execute', now()->addHour(), [
            'action' => 'pending',
            'detail' => $detail->id,
            'student' => $student->id,
        ]);

        $response = $this->followingRedirects()->post($executeUrl);

        $response->assertOk();
        $response->assertSee(__('Request Received'));
        $this->assertSame(ClassScheduleStatusEnum::PENDING->value, $detail->fresh()->status);
        Notification::assertSentOnDemand(ClassStudentActionToTeacherNotification::class);
        Notification::assertCount(3);
    }

    public function test_upload_task_does_not_change_status_for_multi_student_course(): void
    {
        Notification::fake();

        config()->set('mail.from.address', 'sender@example.com');
        config()->set('services.class_notification.cc', 'cc@example.com');

        $teacher = Teacher::factory()->create();
        $studentA = Student::factory()->create();
        $studentB = Student::factory()->create();

        $course = Course::factory()->create();
        $course->teachers()->sync([$teacher->id]);
        $course->students()->sync([$studentA->id, $studentB->id]);

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $executeUrl = URL::temporarySignedRoute('email.class-reminder.execute', now()->addHour(), [
            'action' => 'upload_task',
            'detail' => $detail->id,
            'student' => $studentA->id,
        ]);

        $response = $this->followingRedirects()->post($executeUrl);

        $response->assertOk();
        $response->assertSee(__('Request Received'));
        $this->assertSame(ClassScheduleStatusEnum::SCHEDULED->value, $detail->fresh()->status);
        Notification::assertSentOnDemand(ClassStudentActionToTeacherNotification::class);
        Notification::assertCount(3);
    }

    public function test_each_student_can_submit_an_action_independently_for_the_same_multi_student_course(): void
    {
        Notification::fake();

        config()->set('mail.from.address', 'sender@example.com');
        config()->set('services.class_notification.cc', 'cc@example.com');

        $teacher = Teacher::factory()->create();
        $studentA = Student::factory()->create();
        $studentB = Student::factory()->create();

        $course = Course::factory()->create();
        $course->teachers()->sync([$teacher->id]);
        $course->students()->sync([$studentA->id, $studentB->id]);

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $studentAUrl = URL::temporarySignedRoute('email.class-reminder.execute', now()->addHour(), [
            'action' => 'pending',
            'detail' => $detail->id,
            'student' => $studentA->id,
        ]);

        $studentBUrl = URL::temporarySignedRoute('email.class-reminder.execute', now()->addHour(), [
            'action' => 'upload_task',
            'detail' => $detail->id,
            'student' => $studentB->id,
        ]);

        $first = $this->followingRedirects()->post($studentAUrl);
        $second = $this->followingRedirects()->post($studentBUrl);

        $first->assertOk();
        $first->assertSee(__('Request Received'));

        $second->assertOk();
        $second->assertSee(__('Request Received'));
        $second->assertDontSee(__('Already Processed'));

        $this->assertSame(ClassScheduleStatusEnum::SCHEDULED->value, $detail->fresh()->status);
        $this->assertDatabaseHas('class_reminder_actions', [
            'class_schedule_detail_id' => $detail->id,
            'student_id' => $studentA->id,
            'action_type' => 'pending',
        ]);
        $this->assertDatabaseHas('class_reminder_actions', [
            'class_schedule_detail_id' => $detail->id,
            'student_id' => $studentB->id,
            'action_type' => 'upload_task',
        ]);
        $this->assertSame(2, ClassReminderAction::query()->count());
    }

    public function test_upload_task_marks_single_student_course_as_canceled_and_notifies(): void
    {
        Notification::fake();

        config()->set('mail.from.address', 'sender@example.com');
        config()->set('services.class_notification.cc', 'cc@example.com');

        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create();

        $course = Course::factory()->create();
        $course->teachers()->sync([$teacher->id]);
        $course->students()->sync([$student->id]);

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $executeUrl = URL::temporarySignedRoute('email.class-reminder.execute', now()->addHour(), [
            'action' => 'upload_task',
            'detail' => $detail->id,
            'student' => $student->id,
        ]);

        $response = $this->followingRedirects()->post($executeUrl);

        $response->assertOk();
        $response->assertSee(__('Request Received'));
        $this->assertSame(ClassScheduleStatusEnum::CANCELED->value, $detail->fresh()->status);
        Notification::assertSentOnDemand(ClassStudentActionToTeacherNotification::class);
        Notification::assertCount(3);
    }

    public function test_duplicate_execute_request_is_idempotent(): void
    {
        Notification::fake();

        config()->set('mail.from.address', 'sender@example.com');
        config()->set('services.class_notification.cc', 'cc@example.com');

        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create();

        $course = Course::factory()->create();
        $course->teachers()->sync([$teacher->id]);
        $course->students()->sync([$student->id]);

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $executeUrl = URL::temporarySignedRoute('email.class-reminder.execute', now()->addHour(), [
            'action' => 'pending',
            'detail' => $detail->id,
            'student' => $student->id,
        ]);

        $first = $this->followingRedirects()->post($executeUrl);
        $second = $this->followingRedirects()->post($executeUrl);

        $first->assertOk();
        $first->assertSee(__('Request Received'));

        $second->assertOk();
        $second->assertSee(__('Already Processed'));

        $this->assertSame(ClassScheduleStatusEnum::PENDING->value, $detail->fresh()->status);
        Notification::assertSentOnDemand(ClassStudentActionToTeacherNotification::class);
        Notification::assertCount(3);
    }

    public function test_execute_skips_invalid_email_recipients_without_crashing(): void
    {
        Notification::fake();

        config()->set('mail.from.address', 'undefined');
        config()->set('services.class_notification.cc', 'not-an-email');

        $teacherProfile = Profile::factory()->create([
            'full_name' => 'Docente Uno',
            'email' => 'undefined',
            'email_alt' => 'teacher@example.com',
        ]);
        $teacher = Teacher::factory()->create(['profile_id' => $teacherProfile->id]);

        $student = Student::factory()->create();

        $course = Course::factory()->create();
        $course->teachers()->sync([$teacher->id]);
        $course->students()->sync([$student->id]);

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $executeUrl = URL::temporarySignedRoute('email.class-reminder.execute', now()->addHour(), [
            'action' => 'pending',
            'detail' => $detail->id,
            'student' => $student->id,
        ]);

        $response = $this->followingRedirects()->post($executeUrl);

        $response->assertOk();
        $response->assertSee(__('Request Received'));
        $this->assertSame(ClassScheduleStatusEnum::PENDING->value, $detail->fresh()->status);
        Notification::assertSentOnDemand(ClassStudentActionToTeacherNotification::class, 1);
    }

    public function test_expired_signed_route_renders_friendly_page(): void
    {
        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create();

        $course = Course::factory()->create();
        $course->teachers()->sync([$teacher->id]);
        $course->students()->sync([$student->id]);

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $expiredUrl = URL::temporarySignedRoute('email.class-reminder.notify', now()->subMinute(), [
            'detail' => $detail->id,
            'student' => $student->id,
        ]);

        $response = $this->get($expiredUrl);

        $response->assertStatus(403);
        $response->assertSee(__('Link Expired'));
        $response->assertSee(__('This link has expired. Please request a new one if you still need to take action.'));
    }
}
