<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserProfileSpaTest extends TestCase
{
    public function test_user_can_edit_its_own_profile()
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $role = Role::first();

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->postJson(route('settings.users.profile.update', ['user' => $user->id]), [
            'type' => $user->profile->type,
            'personal_id' => $user->profile->personal_id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'name' => $user->name,
            'roles' => [$role->id],
            'status' => 'disabled',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
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
                    'full_name',
                    'status',
                ],
            ],
        ]);

        $this->assertDatabaseHas('profiles', [
            'id' => $user->profile->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'jane.smith@example.com',
            'status' => 'disabled',
        ]);
    }
}
