<?php

namespace Webkul\EasyOrders\Providers;

use Webkul\ChannelConnector\Services\AdapterResolver;
use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\EasyOrders\Adapters\EasyOrdersAdapter;
use Webkul\EasyOrders\Models\EasyOrdersCredentialsConfig;
use Webkul\EasyOrders\Models\EasyOrdersExportMappingConfig;
use Webkul\EasyOrders\Models\EasyOrdersMappingConfig;
use Webkul\EasyOrders\Models\EasyOrdersProductMapping;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        EasyOrdersCredentialsConfig::class,
        EasyOrdersExportMappingConfig::class,
        EasyOrdersMappingConfig::class,
        EasyOrdersProductMapping::class,
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
            $resolver->register('easyorders', EasyOrdersAdapter::class);
        });
    }
}
