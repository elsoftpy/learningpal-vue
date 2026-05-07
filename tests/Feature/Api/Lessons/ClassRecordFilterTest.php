<?php

namespace Tests\Feature\Api\Lessons;

use App\Models\ClassRecord;
use App\Models\ClassRecordStudent;
use App\Models\ClassSchedule;
use App\Models\ClassScheduleDetail;
use App\Models\Course;
use App\Models\LanguageLevel;
use App\Models\Profile;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Tests\TestCase;

class ClassRecordFilterTest extends TestCase
{
    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function makeTeacherUser(): array
    {
        $profile = Profile::factory()->create();
        $teacher = Teacher::factory()->create(['profile_id' => $profile->id]);
        $user = User::factory()->create(['profile_id' => $profile->id]);
        $user->assignRole('teacher');

        return [$user, $teacher];
    }

    private function makeStudentUser(): array
    {
        $profile = Profile::factory()->create();
        $student = Student::factory()->create(['profile_id' => $profile->id]);
        $user = User::factory()->create(['profile_id' => $profile->id]);
        $user->assignRole('student');

        return [$user, $student];
    }

    private function makeClassRecord(Course $course, Teacher $teacher, User $user, ?int $languageLevelId = null): ClassRecord
    {
        return ClassRecord::query()->create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'class_schedule_detail_id' => ClassScheduleDetail::factory()->create([
                'class_schedule_id' => ClassSchedule::factory()->create(['course_id' => $course->id])->id,
            ])->id,
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'duration_minutes' => 60,
            'mode' => 'online',
            'language_level_id' => $languageLevelId ?? $course->language_level_id,
        ]);
    }

    // -------------------------------------------------------------------------
    // filterOptions endpoint
    // -------------------------------------------------------------------------

    public function test_admin_gets_all_language_levels_and_all_students_in_filter_options(): void
    {
        $admin = $this->makeUser('admin');
        LanguageLevel::factory()->count(2)->create();
        Student::factory()->count(2)->create();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/academics/lessons/class-records/filter-options');

        $response->assertOk();
        $this->assertNotEmpty($response->json('data.language_levels'));
        $this->assertNotEmpty($response->json('data.students'));
    }

    public function test_teacher_gets_scoped_students_in_filter_options(): void
    {
        [$teacherUser, $teacher] = $this->makeTeacherUser();

        $course = Course::factory()->create();
        $course->teachers()->attach($teacher->id);

        $enrolledStudent = Student::factory()->create();
        $course->students()->attach($enrolledStudent->id);

        $otherStudent = Student::factory()->create();

        $response = $this->actingAs($teacherUser, 'web')
            ->getJson('/academics/lessons/class-records/filter-options');

        $response->assertOk();
        $studentIds = collect($response->json('data.students'))->pluck('value')->all();
        $this->assertContains($enrolledStudent->id, $studentIds);
        $this->assertNotContains($otherStudent->id, $studentIds);
    }

    public function test_student_gets_no_students_in_filter_options(): void
    {
        [$studentUser] = $this->makeStudentUser();

        $response = $this->actingAs($studentUser, 'web')
            ->getJson('/academics/lessons/class-records/filter-options');

        $response->assertOk();
        $this->assertEmpty($response->json('data.students'));
    }

    public function test_unauthenticated_user_cannot_access_filter_options(): void
    {
        $this->getJson('/academics/lessons/class-records/filter-options')
            ->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // index: student scope
    // -------------------------------------------------------------------------

    public function test_student_only_sees_class_records_they_are_listed_in(): void
    {
        [$studentUser, $student] = $this->makeStudentUser();
        $adminUser = $this->makeUser('admin');
        $teacher = Teacher::factory()->create();

        $course = Course::factory()->create();
        $course->students()->attach($student->id);

        $ownRecord = $this->makeClassRecord($course, $teacher, $adminUser);
        ClassRecordStudent::query()->create([
            'class_record_id' => $ownRecord->id,
            'student_id' => $student->id,
        ]);

        $otherRecord = $this->makeClassRecord($course, $teacher, $adminUser);

        $response = $this->actingAs($studentUser, 'web')
            ->getJson('/academics/lessons/class-records');

        $response->assertOk();
        $ids = collect($response->json('data.class_records'))->pluck('id')->all();
        $this->assertContains($ownRecord->id, $ids);
        $this->assertNotContains($otherRecord->id, $ids);
    }

    // -------------------------------------------------------------------------
    // index: language_level_id filter
    // -------------------------------------------------------------------------

    public function test_admin_can_filter_by_language_level_id(): void
    {
        $admin = $this->makeUser('admin');
        $teacher = Teacher::factory()->create();

        $levelA = LanguageLevel::factory()->create();
        $levelB = LanguageLevel::factory()->create();
        $courseA = Course::factory()->create(['language_level_id' => $levelA->id]);
        $courseB = Course::factory()->create(['language_level_id' => $levelB->id]);

        $recordA = $this->makeClassRecord($courseA, $teacher, $admin, $levelA->id);
        $recordB = $this->makeClassRecord($courseB, $teacher, $admin, $levelB->id);

        $filters = json_encode(['language_level_id' => $levelA->id]);
        $response = $this->actingAs($admin, 'web')
            ->getJson('/academics/lessons/class-records?filters='.urlencode($filters));

        $response->assertOk();
        $ids = collect($response->json('data.class_records'))->pluck('id')->all();
        $this->assertContains($recordA->id, $ids);
        $this->assertNotContains($recordB->id, $ids);
    }

    // -------------------------------------------------------------------------
    // index: student_id filter
    // -------------------------------------------------------------------------

    public function test_admin_can_filter_by_student_id(): void
    {
        $admin = $this->makeUser('admin');
        $teacher = Teacher::factory()->create();
        $course = Course::factory()->create();

        $studentA = Student::factory()->create();
        $studentB = Student::factory()->create();

        $recordA = $this->makeClassRecord($course, $teacher, $admin);
        ClassRecordStudent::query()->create(['class_record_id' => $recordA->id, 'student_id' => $studentA->id]);

        $recordB = $this->makeClassRecord($course, $teacher, $admin);
        ClassRecordStudent::query()->create(['class_record_id' => $recordB->id, 'student_id' => $studentB->id]);

        $filters = json_encode(['student_id' => $studentA->id]);
        $response = $this->actingAs($admin, 'web')
            ->getJson('/academics/lessons/class-records?filters='.urlencode($filters));

        $response->assertOk();
        $ids = collect($response->json('data.class_records'))->pluck('id')->all();
        $this->assertContains($recordA->id, $ids);
        $this->assertNotContains($recordB->id, $ids);
    }

    public function test_student_user_cannot_use_student_id_filter(): void
    {
        [$studentUser, $student] = $this->makeStudentUser();
        $adminUser = $this->makeUser('admin');
        $teacher = Teacher::factory()->create();

        $course = Course::factory()->create();
        $course->students()->attach($student->id);

        $ownRecord = $this->makeClassRecord($course, $teacher, $adminUser);
        ClassRecordStudent::query()->create(['class_record_id' => $ownRecord->id, 'student_id' => $student->id]);

        $otherStudent = Student::factory()->create();
        $otherRecord = $this->makeClassRecord($course, $teacher, $adminUser);
        ClassRecordStudent::query()->create(['class_record_id' => $otherRecord->id, 'student_id' => $otherStudent->id]);

        // Student tries to filter by another student's id — filter is ignored, student scope still applies
        $filters = json_encode(['student_id' => $otherStudent->id]);
        $response = $this->actingAs($studentUser, 'web')
            ->getJson('/academics/lessons/class-records?filters='.urlencode($filters));

        $response->assertOk();
        $ids = collect($response->json('data.class_records'))->pluck('id')->all();
        $this->assertContains($ownRecord->id, $ids);
        $this->assertNotContains($otherRecord->id, $ids);
    }
}
