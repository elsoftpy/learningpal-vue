<?php

namespace Tests\Feature\Api\Settings;

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

    public function test_authenticated_user_with_permission_can_list_languages(): void
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
}
