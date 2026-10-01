<?php

namespace Tests\Feature;

use App\Enums\ProfileTypeEnum;
use App\Models\Course;
use App\Models\Profile;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Tests\TestCase;

class StudentSpaTest extends TestCase
{
    public function test_teacher_user_cant_list_own_students_only(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $user->assignRole('teacher');

        $teacher = Teacher::factory()->create([
            'profile_id' => $user->profile_id,
        ]);

        $course1 = Course::factory()->create();
        $course2 = Course::factory()->create();

        $teacher->courses()->sync([$course1->id]);

        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();

        $student1->courses()->sync([$course1->id]);
        $student2->courses()->sync([$course2->id]);

        /** @var User $user */
        $this->actingAs($user, 'web');

        $response = $this->getJson(route('academics.settings.students.index', [
            'per_page' => 5,
            'page' => 1,
        ]));

        $response->assertStatus(200);

        $response->assertJsonCount(1, 'data.students');

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'students' => [
                    '*' => [
                        'id',
                        'type',
                        'personal_id',
                        'first_name',
                        'last_name',
                        'company_name',
                        'ruc',
                        'email',
                        'phone',
                        'address',
                        'gender',
                        'birth_date',
                        'email',
                        'status',
                        'display_status',
                    ],
                ],
                'total',
            ],
        ]);
    }

    public function test_unautheticated_user_cannot_list_students(): void
    {
        $response = $this->getJson(route('academics.settings.students.index'));

        $response->assertStatus(401);
    }

    public function test_admin_user_can_list_students(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $user->assignRole('admin');

        Student::factory()->count(5)->create();

        /** @var User $user */
        $this->actingAs($user, 'web');

        $response = $this->getJson(route('academics.settings.students.index', [
            'per_page' => 5,
            'page' => 1,
        ]));

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'students' => [
                    '*' => [
                        'id',
                        'type',
                        'personal_id',
                        'first_name',
                        'last_name',
                        'company_name',
                        'ruc',
                        'email',
                        'phone',
                        'address',
                        'gender',
                        'birth_date',
                        'email',
                        'status',
                        'display_status',
                    ],
                ],
                'total',
            ],
        ]);
    }

    public function test_student_user_cannot_list_students(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $user->assignRole('student');

        /** @var User $user */
        $this->actingAs($user, 'web');

        $response = $this->getJson(route('academics.settings.students.index'));

        $response->assertStatus(403);
    }

    public function test_admin_cannot_create_student_with_personal_id_of_another_profile(): void
    {
        $this->actingAsAdmin();

        $existingProfile = Profile::factory()->create(['personal_id' => '1234567']);

        $response = $this->postJson(route('academics.settings.students.store'), [
            'type' => ProfileTypeEnum::PERSON->value,
            'personal_id' => $existingProfile->personal_id,
            'first_name' => 'Diara',
            'last_name' => 'Tandi',
            'email' => 'new.student@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['personal_id']);
        $this->assertDatabaseMissing('profiles', ['email' => 'new.student@example.com']);
    }

    public function test_admin_can_create_student_reusing_profile_with_same_personal_id_and_email(): void
    {
        $this->actingAsAdmin();

        $existingProfile = Profile::factory()->create();
        $profileCount = Profile::query()->count();

        $response = $this->postJson(route('academics.settings.students.store'), [
            'type' => ProfileTypeEnum::PERSON->value,
            'personal_id' => $existingProfile->personal_id,
            'first_name' => $existingProfile->first_name,
            'last_name' => $existingProfile->last_name,
            'email' => $existingProfile->email,
        ]);

        $response->assertStatus(200);
        $this->assertSame($profileCount, Profile::query()->count());
        $this->assertDatabaseHas('students', ['profile_id' => $existingProfile->id]);
    }

    public function test_admin_can_update_student_keeping_its_own_personal_id(): void
    {
        $this->actingAsAdmin();

        $student = Student::factory()->create();

        $response = $this->postJson(route('academics.settings.students.edit', ['student' => $student->id]), [
            'type' => ProfileTypeEnum::PERSON->value,
            'personal_id' => $student->profile->personal_id,
            'first_name' => 'Updated',
            'last_name' => 'Student',
            'email' => $student->profile->email,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('profiles', [
            'id' => $student->profile_id,
            'first_name' => 'Updated',
        ]);
    }

    public function test_admin_cannot_update_student_with_personal_id_of_another_profile(): void
    {
        $this->actingAsAdmin();

        $student = Student::factory()->create();
        $otherProfile = Profile::factory()->create();

        $response = $this->postJson(route('academics.settings.students.edit', ['student' => $student->id]), [
            'type' => ProfileTypeEnum::PERSON->value,
            'personal_id' => $otherProfile->personal_id,
            'first_name' => 'Updated',
            'last_name' => 'Student',
            'email' => $student->profile->email,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['personal_id']);
    }

    protected function actingAsAdmin(): User
    {
        $admin = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $admin->assignRole('admin');

        /** @var User $admin */
        $this->actingAs($admin, 'web');

        return $admin;
    }
}
