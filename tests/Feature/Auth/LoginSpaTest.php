<?php

namespace Tests\Feature\Auth;

use App\Enums\StatusEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoginSpaTest extends TestCase
{
    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'name' => 'jane',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'name' => 'jane',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
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

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_incorrect_credentials()
    {
        $user = User::factory()->create([
            'name' => 'jane',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->post(route('auth.login'), [
            'name' => 'jane',
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(401);

        $response->assertJson([
            'message' => __('Unauthenticated.'),
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'errors' => [
                'authentication',
            ],
        ]);

        $this->assertGuest();
    }

    public function test_missing_fields_return_validation_errors()
    {
        $response = $this->postJson(route('auth.login'), []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['name', 'password'])
            ->assertJsonStructure([
            'success',
            'message',
            'data',
            'errors' => [
                'name',
                'password',
            ],
        ]);
    }

    public function test_inactive_user_cannot_login()
    {
        $user = User::factory()->create([
            'name' => 'jane',
            'password' => bcrypt('Password123!'),
            'status' => StatusEnum::DISABLED->value,
        ]);

        $response = $this->postJson(route('auth.login'), [
            'name' => 'jane',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(401);

        $response->assertJson([
            'message' => __('Unauthenticated.'),
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'errors' => [
                'authentication',
            ],
        ]);

        $this->assertGuest();
    }

    public function test_pending_user_can_login()
    {
        $user = User::factory()->create([
            'name' => 'jane',
            'password' => bcrypt('Password123!'),
            'status' => StatusEnum::PENDING->value,
        ]);

        $response = $this->postJson(route('auth.login'), [
            'name' => 'jane',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
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

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create([
            'name' => 'jane',
            'password' => bcrypt('Password123!'),
        ]);

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->postJson(route('auth.logout'));

        $response->assertStatus(200);

        $response->assertJson([
            'success' => true,
            'message' => __('Logged out successfully.'),
        ]);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_get_me()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->getJson(route('auth.me'));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
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
    }

    public function test_guest_cannot_access_me_endpoint()
    {
        $response = $this->getJson(route('auth.me'));

        $response->assertStatus(401);
    }
}