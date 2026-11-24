<?php

namespace Omegakontur\SiteAnalytics;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class SiteAnalyticsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'site-analytics');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'site-analytics');
        Livewire::component('site-analytics-chart', AnalyticsChart::class);
        RateLimiter::for('site-analytics', fn (Request $request) => Limit::perMinute(120)
            ->by(hash_hmac('sha256', (string) $request->ip(), (string) config('app.key'))));

        if (! $this->app->routesAreCached()) {
            Route::post('/site-analytics/event', RecordEvent::class)
                ->middleware('throttle:site-analytics')->name('site-analytics.event');
        }
    }
}
