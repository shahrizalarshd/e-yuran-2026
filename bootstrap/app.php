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
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
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
        // Generate yearly bills on January 1st at 00:01 AM
        $schedule->command('bills:generate-yearly')
            ->yearlyOn(1, 1, '00:01')
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/yearly-bills.log'));
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
