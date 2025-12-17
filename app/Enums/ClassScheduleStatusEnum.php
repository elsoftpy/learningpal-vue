<?php

namespace App\Enums;

enum ClassScheduleStatusEnum: string
{
    case SCHEDULED = 'scheduled';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
    case RESCHEDULED = 'rescheduled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function label(string $value): string
    {
        return match ($value) {
            self::SCHEDULED->value => __('Scheduled'),
            self::COMPLETED->value => __('Completed'),
            self::CANCELED->value => __('Canceled'),
            self::RESCHEDULED->value => __('Rescheduled'),
        };
    }
}