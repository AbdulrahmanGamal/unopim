<?php

namespace Webkul\Shopify\Providers;

use Webkul\ChannelConnector\Services\AdapterResolver;
use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Shopify\Adapters\ShopifyAdapter;
use Webkul\Shopify\Models\ShopifyCredentialsConfig;
use Webkul\Shopify\Models\ShopifyExportMappingConfig;
use Webkul\Shopify\Models\ShopifyMappingConfig;
use Webkul\Shopify\Models\ShopifyMetaFieldsConfig;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        ShopifyCredentialsConfig::class,
        ShopifyExportMappingConfig::class,
        ShopifyMappingConfig::class,
        ShopifyMetaFieldsConfig::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->registerAdapter();
    }

    protected function registerAdapter(): void
    {
        $this->app->resolving(AdapterResolver::class, function (AdapterResolver $resolver) {
            $resolver->register('shopify', ShopifyAdapter::class);
        });
    }
}
