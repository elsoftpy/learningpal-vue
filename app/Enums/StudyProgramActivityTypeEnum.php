<?php

namespace App\Enums;

enum StudyProgramActivityTypeEnum: string
{
    case EXERCISE = 'exercise';
    case VIDEO = 'video';
    case ACTIVITY = 'activity';
    case PRODUCTION = 'production';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function label(string $value): string
    {
        return match ($value) {
            self::EXERCISE->value => __('Exercise'),
            self::VIDEO->value => __('Video'),
            self::ACTIVITY->value => __('Activity'),
            self::PRODUCTION->value => __('Production'),
        };
    }
}
