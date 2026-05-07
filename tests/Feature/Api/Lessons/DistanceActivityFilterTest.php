<?php

namespace Tests\Feature\Api\Lessons;

use App\Models\Course;
use App\Models\DistanceActivity;
use App\Models\DistanceActivityStudent;
use App\Models\LanguageLevel;
use App\Models\Profile;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Tests\TestCase;

class DistanceActivityFilterTest extends TestCase
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

    private function makeActivity(User $user, ?int $languageLevelId = null): DistanceActivity
    {
        $activity = DistanceActivity::factory()->create([
            'user_id' => $user->id,
            'language_level_id' => $languageLevelId,
        ]);

        return $activity;
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
            ->getJson('/academics/lessons/distance-activities/filter-options');

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
            ->getJson('/academics/lessons/distance-activities/filter-options');

        $response->assertOk();
        $studentIds = collect($response->json('data.students'))->pluck('value')->all();
        $this->assertContains($enrolledStudent->id, $studentIds);
        $this->assertNotContains($otherStudent->id, $studentIds);
    }

    public function test_student_gets_no_students_in_filter_options(): void
    {
        [$studentUser] = $this->makeStudentUser();

        $response = $this->actingAs($studentUser, 'web')
            ->getJson('/academics/lessons/distance-activities/filter-options');

        $response->assertOk();
        $this->assertEmpty($response->json('data.students'));
    }

    public function test_unauthenticated_user_cannot_access_filter_options(): void
    {
        $this->getJson('/academics/lessons/distance-activities/filter-options')
            ->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // index: language_level_id filter
    // -------------------------------------------------------------------------

    public function test_admin_can_filter_by_language_level_id(): void
    {
        $admin = $this->makeUser('admin');

        $levelA = LanguageLevel::factory()->create();
        $levelB = LanguageLevel::factory()->create();

        $activityA = $this->makeActivity($admin, $levelA->id);
        $activityB = $this->makeActivity($admin, $levelB->id);

        $filters = json_encode(['language_level_id' => $levelA->id]);
        $response = $this->actingAs($admin, 'web')
            ->getJson('/academics/lessons/distance-activities?filters='.urlencode($filters));

        $response->assertOk();
        $ids = collect($response->json('data.distance_activities'))->pluck('id')->all();
        $this->assertContains($activityA->id, $ids);
        $this->assertNotContains($activityB->id, $ids);
    }

    // -------------------------------------------------------------------------
    // index: student_id filter
    // -------------------------------------------------------------------------

    public function test_admin_can_filter_by_student_id(): void
    {
        $admin = $this->makeUser('admin');

        $studentA = Student::factory()->create();
        $studentB = Student::factory()->create();

        $activityA = $this->makeActivity($admin);
        DistanceActivityStudent::query()->create(['distance_activity_id' => $activityA->id, 'student_id' => $studentA->id, 'completed' => false]);

        $activityB = $this->makeActivity($admin);
        DistanceActivityStudent::query()->create(['distance_activity_id' => $activityB->id, 'student_id' => $studentB->id, 'completed' => false]);

        $filters = json_encode(['student_id' => $studentA->id]);
        $response = $this->actingAs($admin, 'web')
            ->getJson('/academics/lessons/distance-activities?filters='.urlencode($filters));

        $response->assertOk();
        $ids = collect($response->json('data.distance_activities'))->pluck('id')->all();
        $this->assertContains($activityA->id, $ids);
        $this->assertNotContains($activityB->id, $ids);
    }

    public function test_student_user_cannot_use_student_id_filter(): void
    {
        [$studentUser, $student] = $this->makeStudentUser();
        $adminUser = $this->makeUser('admin');

        // Student must be enrolled in the course for the activity to be visible to them
        $course = Course::factory()->create();
        $course->students()->attach($student->id);

        $ownActivity = DistanceActivity::factory()->create([
            'user_id' => $adminUser->id,
            'course_id' => $course->id,
        ]);
        DistanceActivityStudent::query()->create(['distance_activity_id' => $ownActivity->id, 'student_id' => $student->id, 'completed' => false]);

        $otherStudent = Student::factory()->create();
        $otherActivity = DistanceActivity::factory()->create([
            'user_id' => $adminUser->id,
            'course_id' => $course->id,
        ]);
        DistanceActivityStudent::query()->create(['distance_activity_id' => $otherActivity->id, 'student_id' => $otherStudent->id, 'completed' => false]);

        // Student tries to filter by another student's id — filter is ignored, visibility scope still applies
        $filters = json_encode(['student_id' => $otherStudent->id]);
        $response = $this->actingAs($studentUser, 'web')
            ->getJson('/academics/lessons/distance-activities?filters='.urlencode($filters));

        $response->assertOk();
        $ids = collect($response->json('data.distance_activities'))->pluck('id')->all();
        $this->assertContains($ownActivity->id, $ids);
        $this->assertNotContains($otherActivity->id, $ids);
    }
}
