<?php

namespace Tests\Feature\Api\Settings;

use App\Models\Language;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LanguageSpaTest extends TestCase
{
    public function test_unauthenticated_user_cannot_list_languages(): void
    {
        $response = $this->getJson(route('settings.languages.index'));

        $response->assertStatus(401);
    }

    public function test_authenticated_user_cannot_list_languages(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $user->assignRole('admin');

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->getJson(route('settings.languages.index'));

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'languages' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
            ],
        ]);
    }

    public function test_authenticated_user_can_list_languages(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $user->assignRole('admin');

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->getJson(route('settings.languages.index'));

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'languages' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
            ],
        ]);
    }

    public function test_forbidden_user_cannot_list_languages(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->getJson(route('settings.languages.index'));

        $response->assertStatus(403);
    }


    public function test_unauthenticated_user_cannot_delete_language(): void
    {
        $language = Language::first() ?? Language::factory()->create();
        $response = $this->postJson(route('settings.languages.destroy', ['language' => $language->id]));
        

        $response->assertStatus(401);
    }

    public function test_admin_user_can_delete_language(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $language = Language::first() ?? Language::factory()->create();

        $user->assignRole('admin');

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->postJson(route('settings.languages.destroy', ['language' => $language->id]));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('languages', [
            'id' => $language->id,
        ]);
    }

    public function test_authenticated_user_cannot_delete_language(): void
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $language = Language::first() ?? Language::factory()->create();

        /** @var \App\Models\User $user */
        $this->actingAs($user, 'web');

        $response = $this->postJson(route('settings.languages.destroy', ['language' => $language->id]));

        $response->assertStatus(403);

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
        ]);
    }
}