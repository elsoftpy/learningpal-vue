<?php

namespace Tests\Feature\Lists;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleListTest extends TestCase
{
    public function test_role_list_endpoint_is_accessible()
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
            ]
        );

        /** @var \App\Models\User $user */
        $this->actingAs($user);

        $response = $this->postJson(route('lists.roles', ['search' => 'Est']));

        $response->assertStatus(200);
    }

    public function test_role_list_endpoint_forbbids_unauthenticated_users()
    {
        $response = $this->postJson(route('lists.roles', ['search' => 'admin']));

        $response->assertStatus(401);
    }

    public function test_role_list_endpoint_searches_english_role_names()
    {
        app()->setLocale('en');

        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
            ]
        );

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->postJson(route('lists.roles', ['search' => 'Stu']));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'label' => 'Student',
            ]);
    }

    public function test_role_list_endpoint_searches_spanish_translated_role_names()
    {
        app()->setLocale('es');

        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
            ]
        );

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->postJson(route('lists.roles', ['search' => 'Est']));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'label' => 'Estudiante',
            ]);
    }

    public function test_role_list_searches_portuguese_translated_role_names()
    {
        app()->setLocale('pt');

        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
            ]
        );

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');


        $response = $this->postJson(route('lists.roles', ['search' => 'Est']));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'label' => 'Estudante',
            ]);
    }

    public function test_role_list_endpoint_returns_all_roles_if_no_search_parameter_is_provided()
    {
        app()->setLocale('en');

        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
            ]
        );

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->postJson(route('lists.roles'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'label' => 'Admin',
            ])
            ->assertJsonFragment([
                'label' => 'Student',
            ])
            ->assertJsonFragment([
                'label' => 'Teacher',
            ]);
    }
        
}
