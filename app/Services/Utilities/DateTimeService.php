<?php

namespace App\Services\Utilities;

use Carbon\Carbon;

class DateTimeService
{
    public static function formatDate(?Carbon $date): ?string
    {
        if (! $date) {
            return null;
        }

        return $date->format(match (app()->getLocale()) {
            'es', 'pt' => 'd/m/Y',
            'en' => 'm-d-Y',
            default => 'Y-m-d',
        }) ?? null;
    }

    public static function formatTime(?Carbon $time): ?string
    {
        if (! $time) {
            return null;
        }

        return $time->format(match (app()->getLocale()) {
            'es', 'pt' => 'H:i',
            'en' => 'h:i A',
            default => 'H:i',
        }) ?? null;
    }

    public static function formatDateMonthYear(?Carbon $date): ?string
    {
        if (! $date) {
            return null;
        }

        return $date->format(match (app()->getLocale()) {
            'es', 'pt' => 'm/Y',
            'en' => 'm/Y',
            default => 'Y-m',
        }) ?? null;
    }

    public static function dateFromLocalizedString(?string $dateString): ?Carbon
    {
        if (! $dateString) {
            return null;
        }

        return match (app()->getLocale()) {
            'es', 'pt' => Carbon::createFromFormat('d/m/Y', $dateString),
            'en' => Carbon::createFromFormat('m-d-Y', $dateString),
            default => null,
        };
    }

    public static function dateTimeFromLocalizedString(?string $timeString): ?Carbon
    {
        if (! $timeString) {
            return null;
        }

        return match (app()->getLocale()) {
            'es', 'pt' => Carbon::createFromFormat('d/m/Y H:i:s', $timeString),
            'en' => Carbon::createFromFormat('m-d-Y h:i:s A', $timeString),
            default => null,
        };
    }

    public static function monthYearFromLocalizedString(?string $monthYearString): ?Carbon
    {
        if (! $monthYearString) {
            return null;
        }

        $monthYear = match (app()->getLocale()) {
            'es', 'pt' => Carbon::createFromFormat('m/Y', $monthYearString),
            'en' => Carbon::createFromFormat('m/Y', $monthYearString),
            default => null,
        };

        return $monthYear?->startOfMonth();
    }
}
