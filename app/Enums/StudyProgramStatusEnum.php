<?php

namespace App\Enums;

enum StudyProgramStatusEnum: string
{
    case ACTIVE = 'active';
    case DISABLED = 'disabled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function label(string $value): string
    {
        return match ($value) {
            self::ACTIVE->value => __('Active'),
            self::DISABLED->value => __('Disabled'),
        };
    }
}
