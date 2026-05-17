<?php

namespace Webkul\Pricing\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\Pricing\Services\BreakEvenCalculator;
use Webkul\Pricing\Services\MarginProtector;
use Webkul\Pricing\Services\RecommendedPriceEngine;

class PricingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/admin-routes.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api-routes.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'pricing');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'pricing');

        $this->app->register(EventServiceProvider::class);
        $this->app->register(ModuleServiceProvider::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/menu.php', 'menu.admin');
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/acl.php', 'acl');

        $this->app->singleton(
            BreakEvenCalculator::class,
            BreakEvenCalculator::class
        );

        $this->app->singleton(
            MarginProtector::class,
            MarginProtector::class
        );

        $this->app->singleton(
            RecommendedPriceEngine::class,
            RecommendedPriceEngine::class
        );
    }
}
