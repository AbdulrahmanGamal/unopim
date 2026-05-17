<?php

namespace Webkul\Amazon\Providers;

use Webkul\Amazon\Adapters\AmazonAdapter;
use Webkul\Amazon\Models\AmazonCredentialsConfig;
use Webkul\Amazon\Models\AmazonExportMappingConfig;
use Webkul\Amazon\Models\AmazonMappingConfig;
use Webkul\Amazon\Models\AmazonProductMapping;
use Webkul\ChannelConnector\Services\AdapterResolver;
use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        AmazonCredentialsConfig::class,
        AmazonExportMappingConfig::class,
        AmazonMappingConfig::class,
        AmazonProductMapping::class,
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
            $resolver->register('amazon', AmazonAdapter::class);
        });
    }
}
