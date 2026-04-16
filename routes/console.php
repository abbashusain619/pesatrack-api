<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule exchange rate update daily
Schedule::command('exchange:update')->daily();

// Schedule currency list sync weekly (to pick up new currencies)
Schedule::command('currencies:sync')->weekly();