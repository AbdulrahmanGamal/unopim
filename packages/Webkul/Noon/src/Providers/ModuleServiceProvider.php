<?php

namespace Webkul\Noon\Providers;

use Webkul\ChannelConnector\Services\AdapterResolver;
use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Noon\Adapters\NoonAdapter;
use Webkul\Noon\Models\NoonCredentialsConfig;
use Webkul\Noon\Models\NoonExportMappingConfig;
use Webkul\Noon\Models\NoonMappingConfig;
use Webkul\Noon\Models\NoonProductMapping;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        NoonCredentialsConfig::class,
        NoonExportMappingConfig::class,
        NoonMappingConfig::class,
        NoonProductMapping::class,
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
            $resolver->register('noon', NoonAdapter::class);
        });
    }
}
