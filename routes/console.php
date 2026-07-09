<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Close out yesterday's un-arrived appointments shortly after midnight so
// reporting and history reflect reality without manual cleanup.
Schedule::command('appointments:mark-no-shows')->dailyAt('00:30');
