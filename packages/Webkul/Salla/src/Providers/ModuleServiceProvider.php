<?php

namespace Webkul\Salla\Providers;

use Webkul\ChannelConnector\Services\AdapterResolver;
use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Salla\Adapters\SallaAdapter;
use Webkul\Salla\Models\SallaCredentialsConfig;
use Webkul\Salla\Models\SallaExportMappingConfig;
use Webkul\Salla\Models\SallaMappingConfig;
use Webkul\Salla\Models\SallaProductMapping;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        SallaCredentialsConfig::class,
        SallaExportMappingConfig::class,
        SallaMappingConfig::class,
        SallaProductMapping::class,
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
            $resolver->register('salla', SallaAdapter::class);
        });
    }
}
