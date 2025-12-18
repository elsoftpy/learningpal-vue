<?php

namespace App\Services\Utilities;

use Carbon\Carbon;

class DateTimeService
{
    public static function formatDate(Carbon $date): string|null
    {
        return $date->format(match(app()->getLocale()) {
                'es', 'pt' => 'd/m/Y',
                'en' => 'm-d-Y',
                default => 'Y-m-d',
            }) ?? null;
    }

    public static function formatTime(Carbon $time): string|null
    {
        return $time->format(match(app()->getLocale()) {
                'es', 'pt' => 'H:i',
                'en' => 'h:i A',
                default => 'H:i',
            }) ?? null;
    }

    public static function formatDateMonthYear(Carbon $date): string|null
    {
        return $date->format(match(app()->getLocale()) {
                'es', 'pt' => 'm/Y',
                'en' => 'm/Y',
                default => 'Y-m',
            }) ?? null;
    }

    public static function dateFromLocalizedString(string $dateString): Carbon|null
    {
        return match(app()->getLocale()) {
            'es', 'pt' => Carbon::createFromFormat('d/m/Y', $dateString)->format('Y-m-d'),
            'en' => Carbon::createFromFormat('m-d-Y', $dateString)->format('Y-m-d'),
            default => null,
        };
    }
}