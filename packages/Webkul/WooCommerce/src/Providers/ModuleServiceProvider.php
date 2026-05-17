<?php

namespace Webkul\WooCommerce\Providers;

use Webkul\ChannelConnector\Services\AdapterResolver;
use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\WooCommerce\Adapters\WooCommerceAdapter;
use Webkul\WooCommerce\Models\WooCommerceCredentialsConfig;
use Webkul\WooCommerce\Models\WooCommerceExportMappingConfig;
use Webkul\WooCommerce\Models\WooCommerceMappingConfig;
use Webkul\WooCommerce\Models\WooCommerceProductMapping;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        WooCommerceCredentialsConfig::class,
        WooCommerceExportMappingConfig::class,
        WooCommerceMappingConfig::class,
        WooCommerceProductMapping::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migration');
        $this->registerAdapter();
    }

    protected function registerAdapter(): void
    {
        $this->app->resolving(AdapterResolver::class, function (AdapterResolver $resolver) {
            $resolver->register('woocommerce', WooCommerceAdapter::class);
        });
    }
}
