<?php

namespace App\Services\Academics\Settings;

class LanguageLevelService
{
    public function languageLevelData($languageLevel): array
    {
        return [
            'id' => $languageLevel->id,
            'language_id' => $languageLevel->language_id,
            'status' => $languageLevel->status,
            'description' => $languageLevel->description,
            'level' => $languageLevel->level,
            'status' => $languageLevel->status,
            'display_status' => ucfirst(__($languageLevel->status)),
            'language_name' => $languageLevel->language->name ?? null,
        ];
    }
}