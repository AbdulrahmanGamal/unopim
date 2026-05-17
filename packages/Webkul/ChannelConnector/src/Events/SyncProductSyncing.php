<?php

namespace Webkul\ChannelConnector\Events;

use Webkul\ChannelConnector\Models\ChannelConnector;
use Webkul\Product\Contracts\Product;

class SyncProductSyncing
{
    public function __construct(
        public readonly Product $product,
        public readonly ChannelConnector $connector
    ) {}
}
