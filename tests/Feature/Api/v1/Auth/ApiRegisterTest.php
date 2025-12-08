<?php

namespace Tests\Feature\Api\v1\Auth;

use App\Enums\ProfileTypeEnum;
use App\Enums\StatusEnum;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class ApiRegisterTest extends TestCase
{
    public function test_register_person()
    {   
        $response = $this->postJson(route('api.v1.auth.register'), [
            'type' => ProfileTypeEnum::PERSON->value,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '1234567890',
            'personal_id' => '123456',
            'gender' => 'male',
            'address' => '123 Main St', 
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'user' => [
                             'id',
                             'first_name',
                             'last_name',
                             'email',
                             'token',
                         ],
                     ],
                 ]);

        $this->assertDatabaseHas('profiles', [
            'type' => ProfileTypeEnum::PERSON->value,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'full_name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'personal_id' => '123456',
            'gender' => 'male',
            'address' => '123 Main St',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'JohnDoe',
            'email' => 'john.doe@example.com',
            'status' => StatusEnum::PENDING->value,
        ]);
    }
}
