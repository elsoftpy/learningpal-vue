<?php

namespace Tests\Unit;

use App\Services\Utilities\DateTimeService;
use Carbon\Carbon;
use Tests\TestCase;

class DateTimeServiceTest extends TestCase
{
    public function test_month_year_from_localized_string_normalizes_to_the_first_day_of_the_month(): void
    {
        Carbon::setTestNow('2026-03-30 09:00:00');
        app()->setLocale('en');

        $parsed = DateTimeService::monthYearFromLocalizedString('03/2026');

        $this->assertNotNull($parsed);
        $this->assertSame('2026-03-01', $parsed->toDateString());

        Carbon::setTestNow();
    }
}
