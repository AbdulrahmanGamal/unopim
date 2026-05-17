<?php

namespace Webkul\WooCommerce\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class WooCommerceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        // TODO(channel-syndication): Re-enable when admin UI is scaffolded for this package.
        // Routes/woocommerce-routes.php imports controllers that do not exist yet; Resources/views
        // and Resources/lang directories are also absent. The WooCommerce package is currently
        // consumed only as an Adapter (Adapters/WooCommerceAdapter.php) via ChannelConnector.
        // $this->loadRoutesFrom(__DIR__.'/../Routes/woocommerce-routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        // $this->loadViewsFrom(__DIR__.'/../Resources/views', 'woocommerce');
        // $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'woocommerce');
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
