<?php

namespace App\Enums;

enum AttendanceStatusEnum: string
{
    case PRESENT = "1.0";
    case ABSENT = "0.0";
    case LATE = "0.5";

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function label(string|float $value): string
    {
        $normalized = number_format((float) $value, 1, '.', '');

        return match ($normalized) {
            self::PRESENT->value => __('Present'),
            self::ABSENT->value => __('Absent'),
            self::LATE->value => __('Late')
        };
    }
}