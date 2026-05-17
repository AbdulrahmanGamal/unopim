<?php

namespace Webkul\Tenant\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Tenant\Models\Tenant;
use Webkul\Tenant\Models\TenantOAuthClient;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        Tenant::class,
        TenantOAuthClient::class,
    ];
}
