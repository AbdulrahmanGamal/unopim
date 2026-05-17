<?php

namespace Webkul\Order\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;
use Webkul\Order\Models\OrderSyncLog;
use Webkul\Order\Models\OrderWebhook;
use Webkul\Order\Models\UnifiedOrder;
use Webkul\Order\Models\UnifiedOrderItem;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        UnifiedOrder::class,
        UnifiedOrderItem::class,
        OrderSyncLog::class,
        OrderWebhook::class,
    ];
}
