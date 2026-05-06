<?php

namespace Tests\Feature\Api\Lessons;

use App\Enums\ClassScheduleStatusEnum;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\Profile;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClassScheduleDetailSpaTest extends TestCase
{
    public function test_authorized_user_can_update_a_session_status_manually(): void
    {
        $user = User::factory()->create();

        /** @var User $user */
        $user->assignRole('admin');

        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'schedule_month' => '2026-03-01',
        ]);

        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'session_date' => Carbon::parse('2026-03-10'),
            'start_time' => Carbon::parse('2026-03-10 09:00:00'),
            'end_time' => Carbon::parse('2026-03-10 10:00:00'),
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $response = $this->actingAs($user, 'web')->postJson(
            "/academics/lessons/class-schedules/details/{$detail->id}/edit",
            [
                'class_schedule_id' => $schedule->id,
                'session_date' => '10/03/2026',
                'start_time' => '09:00',
                'end_time' => '10:00',
                'status' => ClassScheduleStatusEnum::CANCELED->value,
            ]
        );

        $response->assertOk();

        $this->assertSame(ClassScheduleStatusEnum::CANCELED->value, $detail->fresh()->status);
    }

    public function test_user_without_status_permission_cannot_submit_status_changes(): void
    {
        $user = User::factory()->create();

        /** @var User $user */
        $user->assignRole('teacher');

        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'schedule_month' => '2026-03-01',
        ]);

        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'session_date' => Carbon::parse('2026-03-10'),
            'start_time' => Carbon::parse('2026-03-10 09:00:00'),
            'end_time' => Carbon::parse('2026-03-10 10:00:00'),
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $response = $this->actingAs($user, 'web')->postJson(
            "/academics/lessons/class-schedules/details/{$detail->id}/edit",
            [
                'class_schedule_id' => $schedule->id,
                'session_date' => '10/03/2026',
                'start_time' => '09:00',
                'end_time' => '10:00',
                'status' => ClassScheduleStatusEnum::CANCELED->value,
            ]
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['status']);

        $this->assertSame(ClassScheduleStatusEnum::SCHEDULED->value, $detail->fresh()->status);
    }

    public function test_rescheduling_a_session_marks_it_reprogramed(): void
    {
        $user = User::factory()->create();

        /** @var User $user */
        $user->assignRole('admin');

        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'schedule_month' => '2026-03-01',
        ]);

        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'session_date' => Carbon::parse('2026-03-10'),
            'start_time' => Carbon::parse('2026-03-10 09:00:00'),
            'end_time' => Carbon::parse('2026-03-10 10:00:00'),
            'rescheduled_date' => null,
            'rescheduled_start_time' => null,
            'rescheduled_end_time' => null,
            'rescheduled_estimated_duration_minutes' => 0,
            'reschedule_count' => 0,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $response = $this->actingAs($user, 'web')->postJson(
            "/academics/lessons/class-schedules/details/{$detail->id}/edit",
            [
                'class_schedule_id' => $schedule->id,
                'session_date' => '10/03/2026',
                'start_time' => '09:00',
                'end_time' => '10:00',
                'rescheduled_date' => '12/03/2026',
                'rescheduled_start_time' => '11:00',
                'rescheduled_end_time' => '12:00',
            ]
        );

        $response->assertOk();

        $detail->refresh();

        $this->assertSame(ClassScheduleStatusEnum::REPROGRAMED->value, $detail->status);
        $this->assertSame(1, $detail->reschedule_count);
        $this->assertSame(60, $detail->rescheduled_estimated_duration_minutes);
    }

    public function test_canceled_session_cannot_be_rescheduled_without_status_change(): void
    {
        $user = User::factory()->create();

        /** @var User $user */
        $user->assignRole('admin');

        $course = Course::factory()->create();
        $schedule = ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'schedule_month' => '2026-03-01',
        ]);

        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'session_date' => Carbon::parse('2026-03-10'),
            'start_time' => Carbon::parse('2026-03-10 09:00:00'),
            'end_time' => Carbon::parse('2026-03-10 10:00:00'),
            'rescheduled_date' => null,
            'rescheduled_start_time' => null,
            'rescheduled_end_time' => null,
            'rescheduled_estimated_duration_minutes' => 0,
            'reschedule_count' => 0,
            'status' => ClassScheduleStatusEnum::CANCELED->value,
        ]);

        $response = $this->actingAs($user, 'web')->postJson(
            "/academics/lessons/class-schedules/details/{$detail->id}/edit",
            [
                'class_schedule_id' => $schedule->id,
                'session_date' => '10/03/2026',
                'start_time' => '09:00',
                'end_time' => '10:00',
                'status' => ClassScheduleStatusEnum::CANCELED->value,
                'rescheduled_date' => '12/03/2026',
                'rescheduled_start_time' => '11:00',
                'rescheduled_end_time' => '12:00',
            ]
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['rescheduled_date']);

        $detail->refresh();

        $this->assertSame(ClassScheduleStatusEnum::CANCELED->value, $detail->status);
        $this->assertSame(0, (int) $detail->reschedule_count);
        $this->assertSame(0, (int) $detail->rescheduled_estimated_duration_minutes);
    }

    public function test_teacher_can_reschedule_assigned_course_session(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);
        $user->assignRole('teacher');

        $teacher = Teacher::factory()->create([
            'profile_id' => $user->profile_id,
            'status' => 'active',
        ]);

        $course = Course::factory()->create();
        $teacher->courses()->sync([$course->id]);

        $schedule = ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'schedule_month' => '2026-03-01',
        ]);

        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'session_date' => Carbon::parse('2026-03-10'),
            'start_time' => Carbon::parse('2026-03-10 09:00:00'),
            'end_time' => Carbon::parse('2026-03-10 10:00:00'),
            'rescheduled_date' => null,
            'rescheduled_start_time' => null,
            'rescheduled_end_time' => null,
            'reschedule_count' => 0,
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $response = $this->actingAs($user, 'web')->postJson(
            "/academics/lessons/class-schedules/details/{$detail->id}/edit",
            [
                'rescheduled_date' => '12/03/2026',
                'rescheduled_start_time' => '11:00',
                'rescheduled_end_time' => '12:00',
            ]
        );

        $response->assertOk();

        $detail->refresh();

        $this->assertSame(ClassScheduleStatusEnum::REPROGRAMED->value, $detail->status);
        $this->assertSame(1, $detail->reschedule_count);
        $this->assertSame(60, $detail->rescheduled_estimated_duration_minutes);
    }

    public function test_teacher_cannot_edit_original_session_fields(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);
        $user->assignRole('teacher');

        $teacher = Teacher::factory()->create([
            'profile_id' => $user->profile_id,
            'status' => 'active',
        ]);

        $course = Course::factory()->create();
        $teacher->courses()->sync([$course->id]);

        $schedule = ClassSchedule::factory()->create([
            'course_id' => $course->id,
            'schedule_month' => '2026-03-01',
        ]);

        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'session_date' => Carbon::parse('2026-03-10'),
            'start_time' => Carbon::parse('2026-03-10 09:00:00'),
            'end_time' => Carbon::parse('2026-03-10 10:00:00'),
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $response = $this->actingAs($user, 'web')->postJson(
            "/academics/lessons/class-schedules/details/{$detail->id}/edit",
            [
                'session_date' => '15/03/2026',
                'start_time' => '10:00',
                'end_time' => '11:00',
                'topic' => 'New topic',
                'activity' => 'New activity',
            ]
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['session_date', 'start_time', 'end_time', 'topic', 'activity']);

        $detail->refresh();

        $this->assertSame('2026-03-10', $detail->session_date->toDateString());
    }

    public function test_teacher_cannot_reschedule_session_for_unassigned_course(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);
        $user->assignRole('teacher');

        $teacher = Teacher::factory()->create([
            'profile_id' => $user->profile_id,
            'status' => 'active',
        ]);

        $otherCourse = Course::factory()->create();
        // teacher is NOT assigned to $otherCourse

        $schedule = ClassSchedule::factory()->create([
            'course_id' => $otherCourse->id,
            'schedule_month' => '2026-03-01',
        ]);

        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'session_date' => Carbon::parse('2026-03-10'),
            'start_time' => Carbon::parse('2026-03-10 09:00:00'),
            'end_time' => Carbon::parse('2026-03-10 10:00:00'),
            'status' => ClassScheduleStatusEnum::SCHEDULED->value,
        ]);

        $response = $this->actingAs($user, 'web')->postJson(
            "/academics/lessons/class-schedules/details/{$detail->id}/edit",
            [
                'rescheduled_date' => '12/03/2026',
                'rescheduled_start_time' => '11:00',
                'rescheduled_end_time' => '12:00',
            ]
        );

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_edit_session(): void
    {
        $schedule = ClassSchedule::factory()->create(['schedule_month' => '2026-03-01']);
        $detail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $schedule->id,
            'session_date' => Carbon::parse('2026-03-10'),
            'start_time' => Carbon::parse('2026-03-10 09:00:00'),
            'end_time' => Carbon::parse('2026-03-10 10:00:00'),
        ]);

        $response = $this->postJson(
            "/academics/lessons/class-schedules/details/{$detail->id}/edit",
            [
                'rescheduled_date' => '12/03/2026',
                'rescheduled_start_time' => '11:00',
                'rescheduled_end_time' => '12:00',
            ]
        );

        $response->assertUnauthorized();
    }
}
