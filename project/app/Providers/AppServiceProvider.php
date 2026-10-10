<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Ensure generated URLs never contain '/public'
        if (!app()->runningInConsole() && request()->hasHeader('Host')) {
            $root = request()->root();
            if (str_ends_with($root, '/public')) {
                URL::forceRootUrl(substr($root, 0, -7));
            }
            if (request()->isSecure() || str_contains($root, 'shreeshyamwelfare.com')) {
                URL::forceScheme('https');
            }
        }

        // Use Bootstrap 5 Pagination across entire application
        \Illuminate\Pagination\Paginator::useBootstrapFive();
    }
}
