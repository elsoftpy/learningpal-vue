<?php

namespace Tests\Feature\Api;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\ClassScheduleDetailStatusHistory;
use App\Models\Course;
use App\Models\Profile;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentSessionActionTest extends TestCase
{
    /**
     * @return array{0: User, 1: Student}
     */
    private function makeStudentUser(): array
    {
        $profile = Profile::factory()->create();
        $student = Student::factory()->create(['profile_id' => $profile->id]);
        $user = User::factory()->create(['profile_id' => $profile->id]);
        $user->assignRole('student');

        return [$user, $student];
    }

    /**
     * @return array{0: Course, 1: ClassSchedule, 2: ClassScheduleDetail}
     */
    private function makeEnrolledDetail(Student $student, string $status = 'scheduled'): array
    {
        $teacher = Teacher::factory()->create([
            'profile_id' => Profile::factory()->create([
                'email' => 'undefined',
                'email_alt' => 'undefined',
            ])->id,
        ]);
        $course = Course::factory()->create();
        $course->teachers()->sync([$teacher->id]);
        $course->students()->sync([$student->id]);

        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'status' => $status,
            'session_date' => Carbon::parse('2026-06-15'),
            'start_time' => Carbon::parse('2026-06-15 09:00:00'),
            'end_time' => Carbon::parse('2026-06-15 10:00:00'),
            'rescheduled_date' => null,
            'rescheduled_start_time' => null,
            'rescheduled_end_time' => null,
        ]);

        return [$course, $schedule, $detail];
    }

    public function test_student_can_perform_pending_action_on_enrolled_session(): void
    {
        Notification::fake();

        config()->set('mail.from.address', null);
        config()->set('services.class_notification.cc', null);

        [$user, $student] = $this->makeStudentUser();
        [, , $detail] = $this->makeEnrolledDetail($student);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-schedules/details/{$detail->id}/student-action", [
                'action_type' => 'pending',
            ]);

        $response->assertOk();
        $this->assertSame(ClassScheduleStatusEnum::PENDING->value, $detail->fresh()->status);
        $this->assertDatabaseHas('class_reminder_actions', [
            'class_schedule_detail_id' => $detail->id,
            'student_id' => $student->id,
            'action_type' => 'pending',
        ]);
        $this->assertDatabaseHas('class_schedule_detail_status_histories', [
            'class_schedule_detail_id' => $detail->id,
            'changed_by_type' => 'student',
            'new_status' => ClassScheduleStatusEnum::PENDING->value,
            'action_type' => 'pending',
        ]);
        Notification::assertNothingSent();
    }

    public function test_student_can_perform_upload_task_action_on_enrolled_session(): void
    {
        Notification::fake();

        config()->set('mail.from.address', null);
        config()->set('services.class_notification.cc', null);

        [$user, $student] = $this->makeStudentUser();
        [, , $detail] = $this->makeEnrolledDetail($student);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-schedules/details/{$detail->id}/student-action", [
                'action_type' => 'upload_task',
            ]);

        $response->assertOk();
        $this->assertSame(ClassScheduleStatusEnum::CANCELED->value, $detail->fresh()->status);
        $this->assertDatabaseHas('class_reminder_actions', [
            'class_schedule_detail_id' => $detail->id,
            'student_id' => $student->id,
            'action_type' => 'upload_task',
        ]);
        $this->assertDatabaseHas('class_schedule_detail_status_histories', [
            'class_schedule_detail_id' => $detail->id,
            'changed_by_type' => 'student',
            'new_status' => ClassScheduleStatusEnum::CANCELED->value,
            'action_type' => 'upload_task',
        ]);
    }

    public function test_student_action_logs_email_even_when_url_is_null(): void
    {
        Notification::fake();

        config()->set('mail.from.address', 'noreply@example.com');
        config()->set('services.class_notification.cc', null);

        [$user, $student] = $this->makeStudentUser();
        [, , $detail] = $this->makeEnrolledDetail($student);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-schedules/details/{$detail->id}/student-action", [
                'action_type' => 'upload_task',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('email_logs', [
            'class_schedule_detail_id' => $detail->id,
            'email_destino' => 'noreply@example.com',
            'action_type' => 'upload_task',
            'url' => null,
            'estado' => 'Enviado',
        ]);
    }

    public function test_student_cannot_act_on_session_from_unenrolled_course(): void
    {
        Notification::fake();

        [$user] = $this->makeStudentUser();

        $otherStudent = Student::factory()->create();
        [, , $detail] = $this->makeEnrolledDetail($otherStudent);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-schedules/details/{$detail->id}/student-action", [
                'action_type' => 'pending',
            ]);

        $response->assertForbidden();
        Notification::assertNothingSent();
    }

    public function test_student_cannot_act_on_non_actionable_session(): void
    {
        Notification::fake();

        [$user, $student] = $this->makeStudentUser();
        [, , $detail] = $this->makeEnrolledDetail($student, ClassScheduleStatusEnum::PENDING->value);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-schedules/details/{$detail->id}/student-action", [
                'action_type' => 'pending',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonFragment(['message' => __('This session cannot be modified at this time.')]);
        Notification::assertNothingSent();
    }

    public function test_student_cannot_set_pending_with_less_than_one_hour_before_start(): void
    {
        Notification::fake();

        [$user, $student] = $this->makeStudentUser();
        [, , $detail] = $this->makeEnrolledDetail($student);

        ClassScheduleDetail::query()->whereKey($detail->id)->update([
            'session_date' => Carbon::parse('2026-06-15'),
            'start_time' => Carbon::parse('2026-06-15 10:00:00'),
            'end_time' => Carbon::parse('2026-06-15 11:00:00'),
            'rescheduled_date' => null,
            'rescheduled_start_time' => null,
            'rescheduled_end_time' => null,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-06-15 09:05:00'));

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-schedules/details/{$detail->id}/student-action", [
                'action_type' => 'pending',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'message' => __('This session can only be set to pending up to 1 hour before the start time.'),
        ]);
        $this->assertSame(ClassScheduleStatusEnum::SCHEDULED->value, $detail->fresh()->status);
        Notification::assertNothingSent();

        Carbon::setTestNow();
    }

    public function test_student_cannot_request_upload_task_with_less_than_ten_minutes_before_end(): void
    {
        Notification::fake();

        [$user, $student] = $this->makeStudentUser();
        [, , $detail] = $this->makeEnrolledDetail($student);

        ClassScheduleDetail::query()->whereKey($detail->id)->update([
            'session_date' => Carbon::parse('2026-06-15'),
            'start_time' => Carbon::parse('2026-06-15 13:30:00'),
            'end_time' => Carbon::parse('2026-06-15 14:30:00'),
            'rescheduled_date' => null,
            'rescheduled_start_time' => null,
            'rescheduled_end_time' => null,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-06-15 14:21:00'));

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-schedules/details/{$detail->id}/student-action", [
                'action_type' => 'upload_task',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonFragment([
            'message' => __('You can only request class record upload up to 10 minutes before the session end time.'),
        ]);
        $this->assertSame(ClassScheduleStatusEnum::SCHEDULED->value, $detail->fresh()->status);
        Notification::assertNothingSent();

        Carbon::setTestNow();
    }

    public function test_duplicate_action_is_rejected(): void
    {
        Notification::fake();

        config()->set('mail.from.address', null);
        config()->set('services.class_notification.cc', null);

        [$user, $student] = $this->makeStudentUser();
        [, , $detail] = $this->makeEnrolledDetail($student);

        $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-schedules/details/{$detail->id}/student-action", [
                'action_type' => 'pending',
            ]);

        ClassScheduleDetail::query()->where('id', $detail->id)->update(['status' => ClassScheduleStatusEnum::SCHEDULED->value]);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-schedules/details/{$detail->id}/student-action", [
                'action_type' => 'pending',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonFragment(['message' => __('An action has already been registered for this session.')]);
    }

    public function test_unauthenticated_user_cannot_perform_action(): void
    {
        [, , $detail] = $this->makeEnrolledDetail(Student::factory()->create());

        $response = $this->postJson("/academics/lessons/class-schedules/details/{$detail->id}/student-action", [
            'action_type' => 'pending',
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_perform_action(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('teacher');

        [, , $detail] = $this->makeEnrolledDetail(Student::factory()->create());

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-schedules/details/{$detail->id}/student-action", [
                'action_type' => 'pending',
            ]);

        $response->assertForbidden();
    }

    public function test_status_history_returns_entries_for_enrolled_student(): void
    {
        [$user, $student] = $this->makeStudentUser();
        [, , $detail] = $this->makeEnrolledDetail($student);

        ClassScheduleDetailStatusHistory::query()->create([
            'class_schedule_detail_id' => $detail->id,
            'changed_by_user_id' => $user->id,
            'changed_by_type' => 'student',
            'old_status' => ClassScheduleStatusEnum::SCHEDULED->value,
            'new_status' => ClassScheduleStatusEnum::PENDING->value,
            'action_type' => 'pending',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-schedules/details/{$detail->id}/status-history");

        $response->assertOk();
        $response->assertJsonCount(1, 'history');
        $response->assertJsonFragment([
            'changed_by_type' => 'student',
            'new_status' => ClassScheduleStatusEnum::PENDING->value,
        ]);
    }

    public function test_status_history_is_forbidden_for_unenrolled_student(): void
    {
        [$user] = $this->makeStudentUser();
        $otherStudent = Student::factory()->create();
        [, , $detail] = $this->makeEnrolledDetail($otherStudent);

        $response = $this->actingAs($user, 'web')
            ->postJson("/academics/lessons/class-schedules/details/{$detail->id}/status-history");

        $response->assertForbidden();
    }

    public function test_student_session_list_only_returns_enrolled_course_sessions(): void
    {
        [$user, $student] = $this->makeStudentUser();
        [, , $enrolledDetail] = $this->makeEnrolledDetail($student);

        $otherStudent = Student::factory()->create();
        [, , $unenrolledDetail] = $this->makeEnrolledDetail($otherStudent);

        $response = $this->actingAs($user, 'web')
            ->postJson('/academics/lessons/class-schedules/my-sessions/list');

        $response->assertOk();
        $ids = collect($response->json('sessions'))->pluck('id');
        $this->assertTrue($ids->contains($enrolledDetail->id));
        $this->assertFalse($ids->contains($unenrolledDetail->id));
    }

    public function test_admin_can_see_all_sessions_in_list(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [, , $detail1] = $this->makeEnrolledDetail(Student::factory()->create());
        [, , $detail2] = $this->makeEnrolledDetail(Student::factory()->create());

        $response = $this->actingAs($admin, 'web')
            ->postJson('/academics/lessons/class-schedules/my-sessions/list');

        $response->assertOk();
        $ids = collect($response->json('sessions'))->pluck('id');
        $this->assertTrue($ids->contains($detail1->id));
        $this->assertTrue($ids->contains($detail2->id));
    }
}
