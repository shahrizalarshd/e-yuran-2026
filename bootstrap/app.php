<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckAdmin;
use App\Services\TelegramService;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->alias([
            'role' => CheckRole::class,
            'admin' => CheckAdmin::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Generate yearly bills on January 15th at 00:01 AM
        $schedule->command('bills:generate-yearly')
            ->yearlyOn(1, 15, '00:01')
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/yearly-bills.log'));

        // Send bill payment reminders on 1st of every month at 9:00 AM
        // Reminder for unpaid bills (not yet overdue)
        $schedule->command('bills:send-reminders --type=unpaid')
            ->monthlyOn(1, '09:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/bill-reminders.log'));

        // Send overdue bill reminders on 15th of every month at 9:00 AM
        // For bills that are past due date
        $schedule->command('bills:send-reminders --type=overdue')
            ->monthlyOn(15, '09:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/bill-reminders.log'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e) {
            // Send error to Telegram if enabled
            if (app()->bound(TelegramService::class)) {
                try {
                    app(TelegramService::class)->sendErrorNotification($e);
                } catch (\Exception $ex) {
                    // Silently fail - don't interrupt error reporting
                }
            }
        });
    })->create();