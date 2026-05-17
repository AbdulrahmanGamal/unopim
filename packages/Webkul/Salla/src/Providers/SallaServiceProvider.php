<?php

namespace Webkul\Salla\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class SallaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        // TODO(channel-syndication): Re-enable when admin UI is scaffolded for this package.
        // Routes/salla-routes.php imports controllers that do not exist yet; Resources/views
        // and Resources/lang directories are also absent. The Salla package is currently
        // consumed only as an Adapter (Adapters/SallaAdapter.php) via ChannelConnector, and
        // OAuth flow is handled by ChannelConnector\Http\Controllers\Admin\SallaOAuthController.
        // $this->loadRoutesFrom(__DIR__.'/../Routes/salla-routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migration');
        // $this->loadViewsFrom(__DIR__.'/../Resources/views', 'salla');
        // $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'salla');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        // TODO(channel-syndication): Re-enable when admin routes for this package exist.
        // Menu entries would point to routes whose controllers don't exist, producing
        // broken links in the admin sidebar.
        // $this->mergeConfigFrom(
        //     dirname(__DIR__).'/Config/menu.php',
        //     'menu.admin'
        // );
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/acl.php',
            'acl'
        );
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/exporters.php',
            'exporters'
        );
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/importers.php',
            'importers'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../Config/unopim-vite.php',
            'unopim-vite.viters'
        );
    }
}
