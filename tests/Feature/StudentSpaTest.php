<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Profile;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

        /** @var \App\Models\User $user */
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

        /** @var \App\Models\User $user */
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

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->getJson(route('academics.settings.students.index'));

        $response->assertStatus(403);
    }

    
}
