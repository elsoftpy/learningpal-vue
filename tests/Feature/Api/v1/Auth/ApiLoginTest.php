<?php

namespace Tests\Feature\Api\v1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiLoginTest extends TestCase
{
    public function test_user_can_login_and_receive_token()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'type',
                            'first_name',
                            'last_name',
                            'company_name',
                            'email',
                            'token',
                        ],
                    ],
                 ]);
        
        $this->assertAuthenticated();
    }

    public function test_invalid_credentials_return_error()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                    'success' => false,
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
}
