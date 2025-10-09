<?php

use App\Jobs\CheckUpcomingRenewals;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule subscription renewal notifications
Schedule::job(new CheckUpcomingRenewals(0))->dailyAt('08:00');
Schedule::job(new CheckUpcomingRenewals(1))->dailyAt('09:30');
Schedule::job(new CheckUpcomingRenewals(3))->dailyAt('09:00');
