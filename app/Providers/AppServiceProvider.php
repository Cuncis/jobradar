<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AdzunaService;
use App\Services\TheMuseService;
use App\Services\RemotiveService;
use App\Services\ZipRecruiterService;
use App\Services\JobAggregatorService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register each individual API service as a singleton.
        // Singleton means Laravel creates the object ONCE and reuses
        // the same instance every time it's requested in this request cycle.
        // This avoids rebuilding the same object multiple times.

        $this->app->singleton(AdzunaService::class, function () {
            return new AdzunaService();
        });

        $this->app->singleton(TheMuseService::class, function () {
            return new TheMuseService();
        });

        $this->app->singleton(RemotiveService::class, function () {
            return new RemotiveService();
        });

        $this->app->singleton(ZipRecruiterService::class, function () {
            return new ZipRecruiterService();
        });

        // Register the aggregator as a singleton too.
        // The container automatically injects the 4 services above
        // because we already registered them.
        $this->app->singleton(JobAggregatorService::class, function ($app) {
            return new JobAggregatorService(
                $app->make(AdzunaService::class),
                $app->make(TheMuseService::class),
                $app->make(RemotiveService::class),
                $app->make(ZipRecruiterService::class),
            );
        });
    }

    public function boot(): void
    {
        // Nothing needed here for now
    }
}