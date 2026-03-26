<?php

namespace Tests\Feature\Api\Settings;

use App\Enums\ProfileTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserProfileSpaTest extends TestCase
{
    public function test_user_can_edit_its_own_profile()
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $user->assignRole('student');

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->postJson(route('settings.users.profile.update', ['user' => $user->id]), [
            'type' => $user->profile->type,
            'personal_id' => $user->profile->personal_id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'name' => $user->name,
            'roles' => ['student'],
            'status' => $user->status,
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
            'status' => $user->status,
        ]);
    }

    public function test_unauthenticated_user_cannot_list_users()
    {
        $response = $this->getJson(route('settings.users.index'));

        $response->assertStatus(401);
    }

    public function test_allowed_user_can_list_users()
    {
        $user = User::factory()->create();

        $user->assignRole('admin');

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->getJson(route('settings.users.index'));

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'users' => [
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
                        'full_name',
                        'status',
                        'roles',
                        'avatar_url',
                        'payment_receipt'
                    ],
                ],
            ],
        ]);
    }

    public function test_forbidden_user_cannot_list_users()
    {
        $user = User::factory()->create();

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->getJson(route('settings.users.index'));

        $response->assertStatus(403);
    }  

    public function test_unauthenticated_user_cannot_create_user()
    {
        $response = $this->getJson(route('settings.users.store'));

        $response->assertStatus(401);
    }

    public function test_admin_user_can_create_user()
    {
        $adminUser = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $adminUser->assignRole('admin');

        /** @var \App\Models\User $adminUser */
        $this->actingAs($adminUser, 'web');

        $response = $this->postJson(route('settings.users.store'), [
            'type' => ProfileTypeEnum::PERSON->value,
            'personal_id' => '987654321',
            'first_name' => 'Michael',
            'last_name' => 'Brown',
            'name' => 'michael.brown',
            'password' => 'SecurePass123!',
            'roles' => ['student'],
            'status' => StatusEnum::ACTIVE->value,
            'email' => 'michael.brown@example.com',
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
                    'roles',
                    'avatar_url',
                    'payment_receipt'
                ],
            ],
        ]);

        $this->assertDatabaseHas('profiles', [
            'first_name' => 'Michael',
            'last_name' => 'Brown',
            'email' => 'michael.brown@example.com',
        ]);
    }

    public function test_admin_user_cannot_create_user_with_non_image_avatar()
    {
        $adminUser = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $adminUser->assignRole('admin');

        $this->actingAs($adminUser, 'web');

        $response = $this->post(route('settings.users.store'), [
            'type' => ProfileTypeEnum::PERSON->value,
            'personal_id' => '987654322',
            'first_name' => 'Michael',
            'last_name' => 'Brown',
            'name' => 'michael.brown',
            'password' => 'SecurePass123!',
            'roles' => ['student'],
            'status' => StatusEnum::ACTIVE->value,
            'email' => 'michael.avatar@example.com',
            'avatar' => UploadedFile::fake()->create('avatar.pdf', 10, 'application/pdf'),
        ]);

        $response->assertInvalid(['avatar']);
    }

    public function test_admin_user_cannot_create_user_with_invalid_payment_receipt(): void
    {
        $adminUser = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $adminUser->assignRole('admin');

        $this->actingAs($adminUser, 'web');

        $response = $this->post(route('settings.users.store'), [
            'type' => ProfileTypeEnum::PERSON->value,
            'personal_id' => '987654323',
            'first_name' => 'Michael',
            'last_name' => 'Brown',
            'name' => 'michael.brown',
            'password' => 'SecurePass123!',
            'roles' => ['student'],
            'status' => StatusEnum::ACTIVE->value,
            'email' => 'michael.receipt@example.com',
            'payment_receipt' => UploadedFile::fake()->create('receipt.zip', 10, 'application/zip'),
        ]);

        $response->assertInvalid(['payment_receipt']);
    }

    public function test_forbidden_user_cannot_create_user()
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->postJson(route('settings.users.store'), [
            'type' => ProfileTypeEnum::PERSON->value,
            'personal_id' => '123456789',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'john.doe',
            'password' => 'SecurePass123!',
            'roles' => ['student'],
            'status' => StatusEnum::ACTIVE->value,
            'email' => 'john.doe@example.com',
        ]);
    
        $response->assertStatus(403);

    }

    public function test_unauthenticated_user_cannot_edit_users()
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('settings.users.profile.update', ['user' => $user->id]), [
            'type' => ProfileTypeEnum::PERSON->value,
            'personal_id' => '123456789',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_user_can_edit_another_users_profile()
    {
        $adminUser = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $adminUser->assignRole('admin');

        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        /** @var \App\Models\User $adminUser */
        $this->actingAs($adminUser, 'web');

        $response = $this->postJson(route('settings.users.profile.update', ['user' => $user->id]), [
            'type' => $user->profile->type,
            'personal_id' => $user->profile->personal_id,
            'first_name' => 'Bob',
            'last_name' => 'Williams',
            'email' => 'bob.williams@example.com',
            'name' => $user->name,
            'roles' => ['teacher'],
            'status' => 'active',
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
                    'roles',
                    'avatar_url',
                    'payment_receipt'
                ],
            ],
        ]);

        $this->assertDatabaseHas('profiles', [
            'id' => $user->profile->id,
            'first_name' => 'Bob',
            'last_name' => 'Williams',
            'email' => 'bob.williams@example.com',
        ]);
    }

    public function test_user_profile_update_rejects_non_image_avatar_uploads()
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $user->assignRole('admin');

        $this->actingAs($user, 'web');

        $response = $this->post(route('settings.users.profile.update', ['user' => $user->id]), [
            'type' => $user->profile->type,
            'personal_id' => $user->profile->personal_id,
            'first_name' => $user->profile->first_name,
            'last_name' => $user->profile->last_name,
            'email' => $user->email,
            'name' => $user->name,
            'roles' => ['admin'],
            'status' => $user->status,
            'avatar' => UploadedFile::fake()->create('avatar.pdf', 10, 'application/pdf'),
        ]);

        $response->assertInvalid(['avatar']);
    }

    public function test_user_profile_update_rejects_invalid_payment_receipt_uploads(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $user->assignRole('admin');

        $this->actingAs($user, 'web');

        $response = $this->post(route('settings.users.profile.update', ['user' => $user->id]), [
            'type' => $user->profile->type,
            'personal_id' => $user->profile->personal_id,
            'first_name' => $user->profile->first_name,
            'last_name' => $user->profile->last_name,
            'email' => $user->email,
            'name' => $user->name,
            'roles' => ['admin'],
            'status' => $user->status,
            'payment_receipt' => UploadedFile::fake()->create('receipt.zip', 10, 'application/zip'),
        ]);

        $response->assertInvalid(['payment_receipt']);
    }

    public function test_user_cannot_edit_another_users_profile()
    {
        $user1 = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $user2 = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        /** @var \App\Models\User $user1 */
        $this->actingAs($user1, 'web');

        $response = $this->postJson(route('settings.users.profile.update', ['user' => $user2->id]), [
            'type' => $user2->profile->type,
            'personal_id' => $user2->profile->personal_id,
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'email' => 'alice.johnson@example.com',
            'name' => $user2->name,
            'roles' => ['student'],
            'status' => 'pending',
        ]);

        $response->assertStatus(403);
    } 
    
    public function test_unauthenticated_user_cannot_delete_users()
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('settings.users.profile.destroy', ['user' => $user->id]));

        $response->assertStatus(401);
    }

    public function test_admin_user_can_delete_user()
    {
        $adminUser = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $adminUser->assignRole('admin');

        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $userId = $user->id;
        $profileId = $user->profile->id;

        /** @var \App\Models\User $adminUser */
        $this->actingAs($adminUser, 'web');

        $response = $this->postJson(route('settings.users.profile.destroy', ['user' => $user->id]));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);

        $this->assertDatabaseMissing('profiles', [
            'id' => $profileId,
        ]);
    }   

    public function test_forbidden_user_cannot_delete_user()
    {
        $student = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $user2 = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        /** @var \App\Models\User $student */
        $this->actingAs($student, 'web');

        $response = $this->postJson(route('settings.users.profile.destroy', ['user' => $user2->id]));

        $response->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
        ]);
    }
    
}
