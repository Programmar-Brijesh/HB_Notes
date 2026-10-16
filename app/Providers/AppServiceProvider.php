<?php

namespace App\Providers;

use App\Models\User;
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
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return Limit::perMinute(5)->by($email . '|' . $request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = (string) $request->input('email');

            return Limit::perMinute(5)->by($email . '|' . $request->ip());
        });

        // Configure password reset URL for Flutter deep link or web fallback
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $frontendUrl = env('FRONTEND_URL', env('APP_URL', 'http://localhost'));

            return rtrim($frontendUrl, '/') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });
    }
}
