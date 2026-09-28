<?php

namespace App\Providers;

use App\Auth\FirebaseUserProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FirebaseUserProvider::class, fn ($app) => new FirebaseUserProvider(
            $app,
            $app['cache']->store(),
            (int) config('larafire.user_cache_ttl', 60),
        ));
    }

    public function boot(): void
    {
        Auth::provider('firebase', fn ($app) => $app->make(FirebaseUserProvider::class));

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->getAuthIdentifier() ?: $request->ip()));

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        View::share('firebaseWebConfig', array_filter((array) config('larafire.web')));
    }
}
