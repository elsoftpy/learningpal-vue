<?php

namespace Tests\Feature\Lists;

use App\Models\Course;
use App\Models\Language;
use App\Models\LanguageLevel;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseListTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_list_accepts_nested_params_search_payload()
    {
        $user = User::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
        ]);

        $this->actingAs($user);

        $language = Language::factory()->create([
            'name' => 'Ingles',
        ]);

        $languageLevel = LanguageLevel::factory()->create([
            'language_id' => $language->id,
            'level' => 'A1.3',
        ]);

        $matchingCourse = Course::factory()->create([
            'language_id' => $language->id,
            'language_level_id' => $languageLevel->id,
            'name' => 'Silvia Murdoch',
        ]);

        Course::factory()->create([
            'language_id' => $language->id,
            'language_level_id' => $languageLevel->id,
            'name' => 'Gabriela P',
        ]);

        $response = $this->postJson(route('lists.courses'), [
            'params' => [
                'search' => 'silvi',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $matchingCourse->id,
                'name' => 'Silvia Murdoch - A1.3 - Ingles',
            ])
            ->assertJsonMissing([
                'name' => 'Gabriela P - A1.3 - Ingles',
            ]);
    }
}
