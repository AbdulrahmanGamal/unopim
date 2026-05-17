<?php

namespace Webkul\Ebay\Providers;

use Webkul\ChannelConnector\Services\AdapterResolver;
use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Ebay\Adapters\EbayAdapter;
use Webkul\Ebay\Models\EbayCredentialsConfig;
use Webkul\Ebay\Models\EbayExportMappingConfig;
use Webkul\Ebay\Models\EbayMappingConfig;
use Webkul\Ebay\Models\EbayProductMapping;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        EbayCredentialsConfig::class,
        EbayExportMappingConfig::class,
        EbayMappingConfig::class,
        EbayProductMapping::class,
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
            $resolver->register('ebay', EbayAdapter::class);
        });
    }
}
