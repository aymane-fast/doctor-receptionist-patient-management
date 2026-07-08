<?php

use App\Models\Setting;
use Carbon\Carbon;

beforeEach(function () {
    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
        Setting::set("{$day}_working", true, 'boolean');
        Setting::set("{$day}_start", '09:00', 'time');
        Setting::set("{$day}_end", '12:00', 'time');
    }
});

afterEach(function () {
    Carbon::setTestNow();
});

it('returns the same day opening time before the clinic opens', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-09 08:00:00'));

    expect(Setting::isWithinWorkingHours())->toBeFalse()
        ->and(Setting::getNextWorkingTime()?->format('Y-m-d H:i'))->toBe('2026-06-09 09:00');
});

it('returns the next day opening time after the clinic closes', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-09 20:00:00'));

    expect(Setting::isWithinWorkingHours())->toBeFalse()
        ->and(Setting::getNextWorkingTime()?->format('Y-m-d H:i'))->toBe('2026-06-10 09:00');
});
