<?php

namespace App\Enums;

enum ProfileTypeEnum: string
{
    case PERSON = 'person';
    case COMPANY = 'company';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function label(): string
    {
        return match (self::class) {
            self::PERSON => __('Person'),
            self::COMPANY => __('Company'),
        };
    }
}
