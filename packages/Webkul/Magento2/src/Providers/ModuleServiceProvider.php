<?php

namespace Webkul\Magento2\Providers;

use Webkul\ChannelConnector\Services\AdapterResolver;
use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Magento2\Adapters\Magento2Adapter;
use Webkul\Magento2\Models\Magento2CredentialsConfig;
use Webkul\Magento2\Models\Magento2ExportMappingConfig;
use Webkul\Magento2\Models\Magento2MappingConfig;
use Webkul\Magento2\Models\Magento2ProductMapping;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        Magento2CredentialsConfig::class,
        Magento2ExportMappingConfig::class,
        Magento2MappingConfig::class,
        Magento2ProductMapping::class,
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
            $resolver->register('magento2', Magento2Adapter::class);
        });
    }
}
