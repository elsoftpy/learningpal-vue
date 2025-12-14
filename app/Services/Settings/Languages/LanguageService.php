<?php

namespace App\Services\Settings\Languages;

class LanguageService
{
    public function languageData($language): array
    {
        return [
            'id' => $language->id,
            'name' => $language->name,
        ];
    }
}