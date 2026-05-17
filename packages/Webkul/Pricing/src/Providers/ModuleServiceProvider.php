<?php

namespace Webkul\Pricing\Providers;

use Konekt\Concord\BaseModuleServiceProvider;
use Webkul\Pricing\Models\ChannelCost;
use Webkul\Pricing\Models\MarginProtectionEvent;
use Webkul\Pricing\Models\PricingStrategy;
use Webkul\Pricing\Models\ProductCost;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        ProductCost::class,
        ChannelCost::class,
        MarginProtectionEvent::class,
        PricingStrategy::class,
    ];
}
