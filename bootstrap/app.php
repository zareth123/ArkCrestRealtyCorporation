<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        try {
            $time = \DB::table('app_settings')->where('key', 'notification_time')->value('value') ?? '08:00';
        } catch (\Exception $e) {
            $time = '08:00';
        }
        $schedule->command('commissions:send-reminders')->dailyAt($time)->timezone('Asia/Manila')->appendOutputTo(storage_path('logs/scheduler.log'));
        $schedule->command('notes:send-reminders')->everyMinute();
        // Day-before reminders at 8AM and 5PM Manila time
        $schedule->command('events:send-reminders --trigger=day_before')->dailyAt('08:00')->timezone('Asia/Manila')->appendOutputTo(storage_path('logs/scheduler.log'))->evenInMaintenanceMode();
        $schedule->command('events:send-reminders --trigger=day_before')->dailyAt('17:00')->timezone('Asia/Manila')->appendOutputTo(storage_path('logs/scheduler.log'))->evenInMaintenanceMode();
        // Same-day reminders at 6AM Manila time
        $schedule->command('events:send-reminders --trigger=same_day')->dailyAt('06:00')->timezone('Asia/Manila')->appendOutputTo(storage_path('logs/scheduler.log'))->evenInMaintenanceMode();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->alias([
            'page.visible' => \App\Http\Middleware\CheckPageVisibility::class,
            'sales.only'   => \App\Http\Middleware\RestrictSalesPersons::class,
            'no.cache'     => \App\Http\Middleware\PreventBackHistoryCache::class,
            'admin'        => \App\Http\Middleware\EnsureIsAdmin::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\RestrictSalesPersons::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Redirect back to login on CSRF token expiry
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->routeIs('logout') || $request->is('logout')) {
                return redirect()->route('login');
            }
            return redirect()->route('login')->with('error', 'Your session expired. Please try again.');
        });
        // Handle login rate limiting
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->routeIs('login.post')) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Too many login attempts. Please wait 2 minutes before trying again.'])
                    ->withInput($request->only('email'));
            }
        });
    })->create();
