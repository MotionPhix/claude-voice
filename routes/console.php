<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

$schedule = app(Illuminate\Console\Scheduling\Schedule::class);

// Update overdue invoices daily at 1:00 AM
$schedule->command('invoices:update-overdue')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground();

// Generate recurring invoices daily at 2:00 AM
$schedule->command('invoices:generate-recurring')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();

// Update currency exchange rates daily at 3:00 AM
$schedule->call(function () {
    app(\App\Http\Controllers\CurrencyController::class)->updateRates(request());
})->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground();

// Send payment reminders (if auto-reminders are enabled)
$schedule->call(function () {
    // This would check settings and send reminders accordingly
    // Implementation would go in a separate service class
})->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground();

// Clean up temporary files weekly
$schedule->call(function () {
    Storage::deleteDirectory('temp');
})->weekly()
    ->sundays()
    ->at('04:00');

