<?php

namespace Tests\Feature\Api\Lessons;

use App\Models\ClassRecord;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\DistanceActivity;
use App\Models\LanguageLevel;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\StudyProgramWeek;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Academics\Lessons\ClassRecordService;
use App\Services\Academics\Settings\StudyProgramReplicationService;
use Tests\TestCase;

class LanguageLevelSnapshotTest extends TestCase
{
    public function test_class_record_snapshots_language_level_id_from_course_at_creation(): void
    {
        $user = User::factory()->create();
        $languageLevel = LanguageLevel::factory()->create();
        $course = Course::factory()->create(['language_level_id' => $languageLevel->id]);
        $student = Student::factory()->create();
        $course->students()->attach($student->id);
        $teacher = Teacher::factory()->create();
        $schedule = ClassSchedule::factory()->create(['course_id' => $course->id]);
        $detail = ClassScheduleDetail::factory()->create(['class_schedule_id' => $schedule->id]);

        (new ClassRecordService)->createClassRecord([
            'course_id' => $course->id,
            'language_level_id' => $course->language_level_id,
            'teacher_id' => $teacher->id,
            'class_schedule_detail_id' => $detail->id,
            'user_id' => $user->id,
            'date' => now(),
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'duration_minutes' => 60,
            'comments' => 'Test class',
            'mode' => 'online',
            'student_attendances' => [
                ['student_id' => $student->id, 'attendance' => '1.0'],
            ],
            'details' => [],
        ]);

        $classRecord = ClassRecord::query()->where('course_id', $course->id)->latest()->first();
        $this->assertNotNull($classRecord, 'Class record should be created');
        $this->assertEquals($languageLevel->id, $classRecord->language_level_id);
    }

    public function test_distance_activity_snapshots_language_level_id_when_replicated_to_course(): void
    {
        $languageLevel = LanguageLevel::factory()->create();
        $studyProgram = StudyProgram::factory()->create(['language_level_id' => $languageLevel->id]);
        StudyProgramWeek::factory()->create(['study_program_id' => $studyProgram->id]);

        $user = User::factory()->create();
        $course = Course::factory()->create(['language_level_id' => $languageLevel->id]);

        (new StudyProgramReplicationService)->replicateToCourse($course, $user);

        $distanceActivity = DistanceActivity::query()
            ->where('course_id', $course->id)
            ->first();

        $this->assertNotNull($distanceActivity, 'Distance activity should be created');
        $this->assertEquals($languageLevel->id, $distanceActivity->language_level_id);
    }
}
