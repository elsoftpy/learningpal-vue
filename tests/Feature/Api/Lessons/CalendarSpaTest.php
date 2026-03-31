<?php

namespace Tests\Feature\Api\Lessons;

use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\Profile;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

class CalendarSpaTest extends TestCase
{
    public function test_student_only_sees_sessions_for_enrolled_courses(): void
    {
        $profile = Profile::factory()->create();
        $student = Student::factory()->create(['profile_id' => $profile->id]);
        $user = User::factory()->create(['profile_id' => $profile->id]);
        $user->assignRole('student');

        $visibleCourse = Course::factory()->create();
        $hiddenCourse = Course::factory()->create();

        $student->courses()->sync([$visibleCourse->id]);

        $visibleSchedule = ClassSchedule::factory()->create([
            'course_id' => $visibleCourse->id,
            'name' => 'Visible schedule',
        ]);

        $hiddenSchedule = ClassSchedule::factory()->create([
            'course_id' => $hiddenCourse->id,
            'name' => 'Hidden schedule',
        ]);

        ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $visibleSchedule->id,
            'session_date' => '2026-04-10',
            'start_time' => '2026-04-10 09:00:00',
            'end_time' => '2026-04-10 10:00:00',
        ]);

        ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $hiddenSchedule->id,
            'session_date' => '2026-04-11',
            'start_time' => '2026-04-11 09:00:00',
            'end_time' => '2026-04-11 10:00:00',
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/lists/sessions', [
                'start_date' => '2026-04-01',
                'end_date' => '2026-04-30',
                'view' => 'month',
            ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'sessions');
    }

    public function test_student_only_sees_calendars_for_enrolled_courses(): void
    {
        $profile = Profile::factory()->create();
        $student = Student::factory()->create(['profile_id' => $profile->id]);
        $user = User::factory()->create(['profile_id' => $profile->id]);
        $user->assignRole('student');

        $visibleCourse = Course::factory()->create();
        $hiddenCourse = Course::factory()->create();

        $student->courses()->sync([$visibleCourse->id]);

        $visibleSchedule = ClassSchedule::factory()->create([
            'course_id' => $visibleCourse->id,
            'name' => 'Visible schedule',
        ]);

        $hiddenSchedule = ClassSchedule::factory()->create([
            'course_id' => $hiddenCourse->id,
            'name' => 'Hidden schedule',
        ]);

        ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $visibleSchedule->id,
            'session_date' => '2026-04-10',
            'start_time' => '2026-04-10 09:00:00',
            'end_time' => '2026-04-10 10:00:00',
        ]);

        ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $hiddenSchedule->id,
            'session_date' => '2026-04-11',
            'start_time' => '2026-04-11 09:00:00',
            'end_time' => '2026-04-11 10:00:00',
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson('/lists/calendars', [
                'start_date' => '2026-04-01',
                'end_date' => '2026-04-30',
                'view' => 'month',
            ]);

        $response->assertOk();

        $calendarCourseIds = collect($response->json('calendars'))
            ->flatten(1)
            ->pluck('course_id')
            ->filter()
            ->values()
            ->all();

        $this->assertSame([$visibleCourse->id], $calendarCourseIds);
    }
}
