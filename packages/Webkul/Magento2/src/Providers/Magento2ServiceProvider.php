<?php

namespace Webkul\Magento2\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class Magento2ServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        // TODO(channel-syndication): Re-enable when admin UI is scaffolded for this package.
        // Routes/magento2-routes.php imports controllers that do not exist yet; Resources/views
        // and Resources/lang directories are also absent. The Magento2 package is currently
        // consumed only as an Adapter (Adapters/Magento2Adapter.php) via ChannelConnector.
        // $this->loadRoutesFrom(__DIR__.'/../Routes/magento2-routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migration');
        // $this->loadViewsFrom(__DIR__.'/../Resources/views', 'magento2');
        // $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'magento2');
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
