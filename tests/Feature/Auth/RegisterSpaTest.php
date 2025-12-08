<?php

namespace Tests\Feature\Auth;

use App\Enums\ProfileTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class RegisterSpaTest extends TestCase
{
    public function test_register_person_and_gets_login_after()
    {

        $response = $this->postJson(route('auth.register'), [
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

        $this->assertAuthenticated();

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

    public function test_register_company_and_gets_login_after()
    {

        $response = $this->postJson(route('auth.register'), [
            'type' => ProfileTypeEnum::COMPANY->value,
            'company_name' => 'Acme Corp',
            'ruc' => '80012345-2',
            'email' => 'contact@acmecorp.com',
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
            'phone' => '0987654321',
            'address' => '456 Corporate Blvd',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
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

        $this->assertAuthenticated();

        $this->assertDatabaseHas('profiles', [
            'type' => ProfileTypeEnum::COMPANY->value,
            'company_name' => 'Acme Corp',
            'email' => 'contact@acmecorp.com',
            'phone' => '0987654321',
            'address' => '456 Corporate Blvd',
            'ruc' => '80012345-2',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'AcmeCorp',
            'email' => 'contact@acmecorp.com',
            'status' => StatusEnum::PENDING->value,
        ]);
    }

    public function test_registration_fails_with_existing_email()
    {

        User::factory()->create([
            'profile_id' => Profile::factory()->create([
                'email' => 'jane@example.com',
            ])->id,
        ]);
        
        $response = $this->postJson(route('auth.register'), [
            'type' => ProfileTypeEnum::PERSON->value,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password123!',
            'phone' => '1112223333',
            'personal_id' => '654321',
            'gender' => 'female',
            'address' => '456 Elm St',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['email']);

        $this->assertGuest();
    }

    public function test_registration_fails_with_password_mismatch()
    {

        $response = $this->postJson(route('auth.register'), [
            'type' => ProfileTypeEnum::PERSON->value,
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'email' => 'alice.johnson@example.com',
            'password' => 'password123!',
            'password_confirmation' => 'password1234!',
            'phone' => '2223334444',
            'personal_id' => '789012',
            'gender' => 'female',
            'address' => '789 Oak St',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['password']);

        $this->assertGuest();
    }

    public function test_person_registration_fails_with_missing_fields()
    {

        $response = $this->postJson(route('auth.register'), [
            'type' => ProfileTypeEnum::PERSON->value,
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'email',
            'password',
            'personal_id',
        ]);

        $this->assertGuest();
    }

    public function test_company_registration_fails_with_missing_fields()
    {

        $response = $this->postJson(route('auth.register'), [
            'type' => ProfileTypeEnum::COMPANY->value,
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'company_name',
            'email',
            'password',
            'ruc',
        ]);

        $this->assertGuest();
    }

    public function test_type_field_is_required()
    {

        $response = $this->postJson(route('auth.register'), [
            'first_name' => 'Bob',
            'last_name' => 'Brown',
            'email' => 'bob.brown@example.com',
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
            'phone' => '5556667777',
            'personal_id' => '123456',
            'gender' => 'male',
            'address' => '123 Main St',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['type']);

        $this->assertGuest();
    }
}