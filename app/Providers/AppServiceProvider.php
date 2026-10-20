<?php

namespace App\Providers;

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
        RateLimiter::for('ussd', function (Request $request): Limit {
            $msisdn = $request->input('MSISDN');
            $key = is_scalar($msisdn) && trim((string) $msisdn) !== ''
                ? trim((string) $msisdn)
                : $request->ip();

            return Limit::perMinute(10)->by('nalo-ussd:'.$key);
        });
    }
}
