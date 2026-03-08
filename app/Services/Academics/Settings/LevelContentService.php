<?php

namespace App\Services\Academics\Settings;

use App\Models\LevelContent;

class LevelContentService
{
    public function levelContentData(LevelContent $levelContent): array
    {
        return [
            'id' => $levelContent->id,
            'language_level_id' => $levelContent->language_level_id,
            'content' => $levelContent->content,
            'language_level' => [
                'id' => $levelContent->languageLevel->id,
                'description' => $levelContent->languageLevel->description,
                'level' => $levelContent->languageLevel->level,
                'language' => [
                    'id' => $levelContent->languageLevel->language->id,
                    'name' => $levelContent->languageLevel->language->name,
                ],
            ],
        ];
    }

    public function createLevelContent(array $data): LevelContent
    {
        $levelContent = LevelContent::create([
            'language_level_id' => $data['language_level_id'],
            'content' => $data['content'],
        ]);

        return $levelContent;
    }   

    public function updateLevelContent(LevelContent $levelContent, array $data): LevelContent
    {
        $levelContent->update([
            'language_level_id' => $data['language_level_id'],
            'content' => $data['content'],
        ]);

        return $levelContent;
    }
}