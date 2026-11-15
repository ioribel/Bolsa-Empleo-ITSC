<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

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
        RateLimiter::for('cv-analysis', fn (Request $request) => Limit::perHour(5)
            ->by((string) ($request->user()?->id ?? $request->ip())));

        RateLimiter::for('login', function (Request $request) {
            $limits = [
                Limit::perMinute(30)->by('login-ip:'.$request->ip()),
            ];
            $email = $request->input('email');

            if (is_string($email) && $email !== '') {
                $limits[] = Limit::perMinute(10)
                    ->by('login-email:'.hash('sha256', Str::lower($email)));
            }

            return $limits;
        });

        RateLimiter::for('registration', fn (Request $request) => Limit::perMinute(10)
            ->by('registration-ip:'.$request->ip()));

        RateLimiter::for('email-change', fn (Request $request) => Limit::perHour(5)
            ->by('email-change-user:'.($request->user()?->getAuthIdentifier() ?? $request->ip())));

        Password::defaults(fn () => Password::min(12)->max(255));
    }
}
