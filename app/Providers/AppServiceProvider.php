<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use App\Models\Scholarship;
use App\Models\User;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\Review;
use App\Services\MatchCalculator;
use App\Observers\ActivityLogObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MatchCalculator::class, function ($app) {
            return new MatchCalculator();
        });
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Scholarship::observe(ActivityLogObserver::class);
        User::observe(ActivityLogObserver::class);
        Faq::observe(ActivityLogObserver::class);
        Testimonial::observe(ActivityLogObserver::class);
        Review::observe(ActivityLogObserver::class);

        $this->registerCacheInvalidation();
    }

    private function registerCacheInvalidation(): void
    {
        $invalidateLanding = function () {
            try {
                Cache::forget('landing_data');
                Cache::forget('api_landing_stats');
            } catch (\Throwable $e) {
                Log::warning('Cache invalidation failed: ' . $e->getMessage());
            }
        };

        Faq::created($invalidateLanding);
        Faq::updated($invalidateLanding);
        Faq::deleted($invalidateLanding);

        Testimonial::created($invalidateLanding);
        Testimonial::updated($invalidateLanding);
        Testimonial::deleted($invalidateLanding);

        Review::created($invalidateLanding);
        Review::updated($invalidateLanding);
        Review::deleted($invalidateLanding);

        User::created(function () use ($invalidateLanding) { $invalidateLanding(); });

        Scholarship::created(function () use ($invalidateLanding) {
            $invalidateLanding();
            try { Cache::forget('scholarships_countries'); } catch (\Throwable $e) {}
            try { Cache::forget('scholarships_categories'); } catch (\Throwable $e) {}
            try { Cache::tags(['scholarships_list'])->flush(); } catch (\Throwable $e) {}
        });
        Scholarship::updated(function () use ($invalidateLanding) {
            $invalidateLanding();
            try { Cache::tags(['scholarships_list'])->flush(); } catch (\Throwable $e) {}
        });
        Scholarship::deleted(function () use ($invalidateLanding) {
            $invalidateLanding();
            try { Cache::tags(['scholarships_list'])->flush(); } catch (\Throwable $e) {}
        });
    }
}