<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return route('admin.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });

        // 5 login attempts per minute per IP
        RateLimiter::for('admin-login', fn (Request $request) =>
            Limit::perMinute(5)->by($request->ip())
        );

        // 3 password reset requests per minute per IP (covers forgot + reset submit)
        RateLimiter::for('password-reset', fn (Request $request) =>
            Limit::perMinute(3)->by($request->ip())
        );

        // 5 contact form submissions per 10 minutes per IP
        RateLimiter::for('contact-form', fn (Request $request) =>
            Limit::perMinutes(10, 5)->by($request->ip())
        );

        // 5 written consultation submissions per 10 minutes per IP
        RateLimiter::for('written-consultation', fn (Request $request) =>
            Limit::perMinutes(10, 5)->by($request->ip())
        );

        // 5 phone consultation booking submissions per 10 minutes per IP
        RateLimiter::for('phone-consultation', fn (Request $request) =>
            Limit::perMinutes(10, 5)->by($request->ip())
        );
    }
}
