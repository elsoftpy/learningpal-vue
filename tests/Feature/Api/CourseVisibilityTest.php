<?php

namespace Tests\Feature\Api;

use App\Models\ClassRecord;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\Profile;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class CourseVisibilityTest extends TestCase
{
    public function test_teacher_only_sees_class_schedules_for_owned_courses(): void
    {
        [$user, $ownedCourse, $hiddenCourse] = $this->createTeacherUserWithVisibleAndHiddenCourses();

        ClassSchedule::factory()->create([
            'course_id' => $ownedCourse->id,
            'name' => 'Visible Visibility Schedule',
            'schedule_month' => '2026-04-01',
        ]);

        ClassSchedule::factory()->create([
            'course_id' => $hiddenCourse->id,
            'name' => 'Hidden Visibility Schedule',
            'schedule_month' => '2026-04-01',
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/academics/lessons/class-schedules?search=Visibility Schedule&per_page=10');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Visible Visibility Schedule'])
            ->assertJsonMissing(['name' => 'Hidden Visibility Schedule']);
    }

    public function test_teacher_only_sees_owned_courses_in_course_selector(): void
    {
        [$user, $ownedCourse, $hiddenCourse] = $this->createTeacherUserWithVisibleAndHiddenCourses();

        $ownedCourse->update(['name' => 'Visible Selector Course']);
        $hiddenCourse->update(['name' => 'Hidden Selector Course']);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('lists.courses'), [
                'params' => [
                    'search' => 'Selector Course',
                ],
            ]);

        $response->assertOk()
            ->assertJsonFragment(['id' => $ownedCourse->id])
            ->assertJsonMissing(['id' => $hiddenCourse->id]);
    }

    public function test_teacher_cannot_access_class_record_for_hidden_course(): void
    {
        [$user, $ownedCourse, $hiddenCourse] = $this->createTeacherUserWithVisibleAndHiddenCourses();

        $hiddenTeacher = Teacher::factory()->create();
        $hiddenTeacher->courses()->sync([$hiddenCourse->id]);

        $hiddenSchedule = ClassSchedule::factory()->create([
            'course_id' => $hiddenCourse->id,
            'name' => 'Hidden Record Schedule',
            'schedule_month' => '2026-04-01',
        ]);

        $hiddenDetail = ClassScheduleDetail::factory()->create([
            'class_schedule_id' => $hiddenSchedule->id,
            'session_date' => '2026-04-10',
            'start_time' => Carbon::parse('2026-04-10 09:00:00'),
            'end_time' => Carbon::parse('2026-04-10 10:00:00'),
            'estimated_duration_minutes' => 60,
            'status' => 'completed',
        ]);

        $record = ClassRecord::query()->create([
            'course_id' => $hiddenCourse->id,
            'teacher_id' => $hiddenTeacher->id,
            'class_schedule_detail_id' => $hiddenDetail->id,
            'user_id' => $user->id,
            'date' => '2026-04-10',
            'start_time' => Carbon::parse('2026-04-10 09:00:00'),
            'end_time' => Carbon::parse('2026-04-10 10:00:00'),
            'duration_minutes' => 60,
            'comments' => 'Hidden record',
            'mode' => 'online',
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('academics.lessons.class-records.data', ['classRecord' => $record->id]));

        $response->assertForbidden();
    }

    public function test_teacher_cannot_load_monthly_report_options_for_hidden_course(): void
    {
        [$user, $ownedCourse, $hiddenCourse] = $this->createTeacherUserWithVisibleAndHiddenCourses();

        ClassSchedule::factory()->create([
            'course_id' => $ownedCourse->id,
            'schedule_month' => '2026-04-01',
        ]);

        ClassSchedule::factory()->create([
            'course_id' => $hiddenCourse->id,
            'schedule_month' => '2026-04-01',
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('academics.reports.monthly-classes.options.months'), [
                'course_id' => $hiddenCourse->id,
            ]);

        $response->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Course, 2: Course}
     */
    private function createTeacherUserWithVisibleAndHiddenCourses(): array
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $user->assignRole('teacher');

        $teacher = Teacher::factory()->create([
            'profile_id' => $user->profile_id,
            'status' => 'active',
        ]);

        $ownedCourse = Course::factory()->create([
            'name' => 'Owned Visibility Course',
            'status' => 'active',
        ]);

        $hiddenCourse = Course::factory()->create([
            'name' => 'Hidden Visibility Course',
            'status' => 'active',
        ]);

        $teacher->courses()->sync([$ownedCourse->id]);

        return [$user, $ownedCourse, $hiddenCourse];
    }
}
