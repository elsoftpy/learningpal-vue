<?php

namespace Tests\Feature\Api;

use App\Models\ClassRecord;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\Language;
use App\Models\LanguageLevel;
use App\Models\Profile;
use App\Models\StudyProgram;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class CourseVisibilityProgramsAndReportsTest extends TestCase
{
    public function test_teacher_cannot_load_teacher_hours_report_for_other_teachers_courses(): void
    {
        [$user, $ownedCourse, $hiddenCourse, $teacher] = $this->createTeacherUserWithVisibleAndHiddenCourses();

        $hiddenTeacher = Teacher::factory()->create([
            'status' => 'active',
        ]);
        $hiddenTeacher->courses()->sync([$hiddenCourse->id]);

        $hiddenSchedule = ClassSchedule::factory()->create([
            'course_id' => $hiddenCourse->id,
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

        ClassRecord::query()->create([
            'course_id' => $hiddenCourse->id,
            'teacher_id' => $hiddenTeacher->id,
            'class_schedule_detail_id' => $hiddenDetail->id,
            'user_id' => $user->id,
            'date' => '2026-04-10',
            'start_time' => Carbon::parse('2026-04-10 09:00:00'),
            'end_time' => Carbon::parse('2026-04-10 10:00:00'),
            'duration_minutes' => 60,
            'comments' => 'Hidden teacher hours record',
            'mode' => 'online',
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('academics.reports.teacher-hours'), [
                'teacher_id' => $hiddenTeacher->id,
                'from_date' => '2026-04-01',
                'to_date' => '2026-04-30',
            ]);

        $response->assertForbidden();
    }

    public function test_teacher_only_sees_languages_for_owned_course_levels(): void
    {
        [$user, $ownedCourse, $hiddenCourse] = $this->createTeacherUserWithVisibleAndHiddenCourses();

        $response = $this->actingAs($user, 'web')
            ->postJson(route('lists.languages'));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $ownedCourse->language?->id,
                'name' => $ownedCourse->language?->name,
            ])
            ->assertJsonMissing([
                'id' => $hiddenCourse->language?->id,
                'name' => $hiddenCourse->language?->name,
            ]);
    }

    public function test_teacher_only_sees_language_levels_for_owned_courses(): void
    {
        [$user, $ownedCourse, $hiddenCourse] = $this->createTeacherUserWithVisibleAndHiddenCourses();

        $response = $this->actingAs($user, 'web')
            ->postJson(route('lists.language-levels'), [
                'language_id' => $ownedCourse->language_id,
            ]);

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $ownedCourse->language_level_id,
            ])
            ->assertJsonMissing([
                'id' => $hiddenCourse->language_level_id,
            ]);
    }

    public function test_teacher_cannot_access_study_program_for_hidden_course_level(): void
    {
        [$user, $ownedCourse, $hiddenCourse] = $this->createTeacherUserWithVisibleAndHiddenCourses();

        $hiddenStudyProgram = StudyProgram::factory()->create([
            'language_level_id' => $hiddenCourse->language_level_id,
        ]);

        $response = $this->actingAs($user, 'web')
            ->postJson(route('academics.settings.study-programs.data', [
                'studyProgram' => $hiddenStudyProgram->id,
            ]));

        $response->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Course, 2: Course, 3: Teacher}
     */
    private function createTeacherUserWithVisibleAndHiddenCourses(): array
    {
        $visibleLanguage = Language::factory()->create([
            'name' => 'Visible Language',
        ]);

        $hiddenLanguage = Language::factory()->create([
            'name' => 'Hidden Language',
        ]);

        $visibleLevel = LanguageLevel::factory()->create([
            'language_id' => $visibleLanguage->id,
            'description' => 'Visible Level',
            'level' => 'A1',
            'status' => 'active',
        ]);

        $hiddenLevel = LanguageLevel::factory()->create([
            'language_id' => $hiddenLanguage->id,
            'description' => 'Hidden Level',
            'level' => 'B2',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $user->assignRole('teacher');

        $teacher = Teacher::factory()->create([
            'profile_id' => $user->profile_id,
            'status' => 'active',
        ]);

        $ownedCourse = Course::factory()->create([
            'language_id' => $visibleLanguage->id,
            'language_level_id' => $visibleLevel->id,
            'name' => 'Owned Program Visibility Course',
            'status' => 'active',
        ]);

        $hiddenCourse = Course::factory()->create([
            'language_id' => $hiddenLanguage->id,
            'language_level_id' => $hiddenLevel->id,
            'name' => 'Hidden Program Visibility Course',
            'status' => 'active',
        ]);

        $teacher->courses()->sync([$ownedCourse->id]);

        return [$user, $ownedCourse, $hiddenCourse, $teacher];
    }
}
