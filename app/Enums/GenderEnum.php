<?php

namespace App\Enums;

enum GenderEnum: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function label(string $value): string
    {
        return match ($value) {
            self::Male->value => __('Male'),
            self::Female->value => __('Female'),
            self::Other->value => __('Other'),
        };
    }
}