<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GlobalApiExceptionTest extends TestCase
{
   /* public function test_invalid_route_returns_standardized_error_response()
   {
       $response = $this->getJson('/api/non-existent-route');

       $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => __('The requested resource was not found.'),
                    'data' => null,
                    'errors' => [
                        'route' => [__('The requested route does not exist.')],
                    ],
                ]);
   } */

   public function test_forbidden_access_returns_standardized_error_response()
   {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        Sanctum::actingAs($user, ['*']);

        $permission = 'access-restricted-resource';
        $otherPermission = 'some-other-permission';

        Permission::create(['guard_name' => 'web', 'name' => $permission]);
        Permission::create(['guard_name' => 'web', 'name' => $otherPermission]);

        $role = Role::create(['guard_name' => 'web', 'name' => 'test-user-role']);
        
        $user->assignRole('test-user-role');

        $role->givePermissionTo($otherPermission);

       // Assuming there's a route that requires authentication
       $response = $this->getJson('/api/authorization-test');

       $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => __('You do not have permission to access this resource.'),
                    'data' => null,
                    'errors' => [],
                ]);
   }

   public function test_unauthorized_access_returns_standardized_error_response()
   {
       $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'name' => $user->name,
            'password' => 'wrongpassword',
        ]);

       $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => __('Unauthenticated.'),
                    'data' => null,
                    'errors' => [
                        'authentication' => [__('Invalid credentials. Please try again.')]
                    ],
                ]);
   }
}
