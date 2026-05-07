<?php

namespace Tests\Feature\Api\Settings;

use App\Enums\StatusEnum;
use App\Models\Course;
use App\Models\DistanceActivity;
use App\Models\DistanceActivityStudent;
use App\Models\Language;
use App\Models\LanguageLevel;
use App\Models\Profile;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\StudyProgramWeek;
use App\Models\StudyProgramWeekActivity;
use App\Models\User;
use App\Services\Academics\Settings\StudyProgramReplicationService;
use Tests\TestCase;

class CourseSpaTest extends TestCase
{
    public function test_updating_course_language_level_replicates_new_level_activities_and_syncs_enrolled_students(): void
    {
        $user = User::factory()->create(['profile_id' => Profile::factory()->create()->id]);
        $user->assignRole('admin');

        $language = Language::factory()->create(['name' => fake()->unique()->word()]);
        $oldLevel = LanguageLevel::factory()->create(['language_id' => $language->id]);
        $oldProgram = StudyProgram::factory()->create(['language_level_id' => $oldLevel->id]);
        $oldWeek = StudyProgramWeek::factory()->create(['study_program_id' => $oldProgram->id]);
        StudyProgramWeekActivity::factory()->create(['study_program_week_id' => $oldWeek->id]);

        // New level with its own study program and weeks
        $newLevel = LanguageLevel::factory()->create(['language_id' => $language->id]);
        $newProgram = StudyProgram::factory()->create(['language_level_id' => $newLevel->id]);
        $newWeek = StudyProgramWeek::factory()->create(['study_program_id' => $newProgram->id]);
        StudyProgramWeekActivity::factory()->create(['study_program_week_id' => $newWeek->id]);

        // Create course at old level and replicate its activities
        $course = Course::factory()->create([
            'language_id' => $language->id,
            'language_level_id' => $oldLevel->id,
        ]);
        (new StudyProgramReplicationService)->replicateToCourse($course, $user);

        // Enroll a student and sync their enrollments
        $student = Student::factory()->create();
        $course->students()->attach($student->id);
        $oldActivity = DistanceActivity::query()->where('course_id', $course->id)->first();
        DistanceActivityStudent::query()->firstOrCreate([
            'distance_activity_id' => $oldActivity->id,
            'student_id' => $student->id,
        ], ['completed' => false, 'completed_at' => null]);

        $this->assertDatabaseHas('distance_activities', [
            'course_id' => $course->id,
            'language_level_id' => $oldLevel->id,
        ]);

        // Update the course to the new language level
        /** @var User $user */
        $this->actingAs($user, 'web')
            ->postJson(route('academics.settings.courses.edit', $course), [
                'name' => $course->name,
                'language_id' => $language->id,
                'language_level_id' => $newLevel->id,
                'chat_room_link' => null,
                'status' => StatusEnum::ACTIVE->value,
            ])
            ->assertOk();

        // New level's activities are created for the course
        $this->assertDatabaseHas('distance_activities', [
            'course_id' => $course->id,
            'language_level_id' => $newLevel->id,
            'study_program_week_id' => $newWeek->id,
        ]);

        // Old level's activities are still there (students accumulate over their progress)
        $this->assertDatabaseHas('distance_activities', [
            'course_id' => $course->id,
            'language_level_id' => $oldLevel->id,
        ]);

        // Enrolled student has a distance_activity_students record for the new activity
        $newActivity = DistanceActivity::query()
            ->where('course_id', $course->id)
            ->where('language_level_id', $newLevel->id)
            ->first();

        $this->assertNotNull($newActivity);
        $this->assertDatabaseHas('distance_activity_students', [
            'distance_activity_id' => $newActivity->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_updating_course_without_language_level_change_does_not_replicate_activities(): void
    {
        $user = User::factory()->create(['profile_id' => Profile::factory()->create()->id]);
        $user->assignRole('admin');

        $language = Language::factory()->create(['name' => fake()->unique()->word()]);
        $level = LanguageLevel::factory()->create(['language_id' => $language->id]);

        $course = Course::factory()->create([
            'language_id' => $language->id,
            'language_level_id' => $level->id,
        ]);

        $activitiesBefore = DistanceActivity::query()->where('course_id', $course->id)->count();

        /** @var User $user */
        $this->actingAs($user, 'web')
            ->postJson(route('academics.settings.courses.edit', $course), [
                'name' => 'Updated Name',
                'language_id' => $language->id,
                'language_level_id' => $level->id,
                'chat_room_link' => null,
                'status' => StatusEnum::ACTIVE->value,
            ])
            ->assertOk();

        $activitiesAfter = DistanceActivity::query()->where('course_id', $course->id)->count();

        $this->assertEquals($activitiesBefore, $activitiesAfter);
    }
}
