<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:refresh-holidays', function () {
    // Truncate the holidays table
    DB::table('holidays')->truncate();
    $this->info('Holidays table truncated.');

    // Run the HolidaySeeder
    Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\HolidaySeeder',
    ]);
    $this->info('HolidaySeeder executed successfully.');
})->purpose('Truncate the holidays table and seed the HolidaySeeder');

// Schedule the command to run on Dec 31 at midnight
app(Schedule::class)->command('app:refresh-holidays')->yearlyOn(12, 31, '00:00');

