<?php

namespace Tests\Feature\Api\Settings;

use App\Models\Profile;
use App\Models\Teacher;
use App\Models\User;
use Tests\TestCase;

class TeacherSpaTest extends TestCase
{
    public function test_index_returns_total_teacher_count_for_pagination(): void
    {
        $user = User::factory()->create(['profile_id' => Profile::factory()->create()->id]);
        $user->assignRole('admin');

        Teacher::factory()->count(3)->create();
        $expectedTotal = Teacher::query()->count();

        /** @var User $user */
        $this->actingAs($user, 'web')
            ->getJson(route('academics.settings.teachers.index', ['page' => 1, 'per_page' => 2]))
            ->assertOk()
            ->assertJsonCount(2, 'data.teachers')
            ->assertJsonPath('data.total', $expectedTotal)
            ->assertJsonMissingPath('data.total ');
    }
}
