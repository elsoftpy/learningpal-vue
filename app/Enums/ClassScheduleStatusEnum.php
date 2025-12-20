<?php

namespace App\Enums;

enum ClassScheduleStatusEnum: string
{
    case SCHEDULED = 'scheduled';
    case COMPLETED = 'completed';
    case PENDING = 'pending';
    case ONGOING = 'ongoing';
    case REPROGRAMED = 'reprogramed';
    case CANCELED = 'canceled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function label(string $value): string
    {
        return match ($value) {
            self::SCHEDULED->value => __('Scheduled'),
            self::COMPLETED->value => __('Completed'),
            self::PENDING->value => __('Pending'),
            self::ONGOING->value => __('Ongoing'),
            self::REPROGRAMED->value => __('Reprogramed'),
            self::CANCELED->value => __('Canceled'),
        };
    }
}