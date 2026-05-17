<?php

namespace Webkul\Product\Observers;

use Illuminate\Support\Facades\Storage;
use Webkul\Product\Contracts\Product;
use Webkul\Tenant\Filesystem\TenantStorage;

class ProductObserver
{
    /**
     * Handle the Product "deleted" event.
     *
     * @param  Product  $product
     * @return void
     */
    public function deleted($product)
    {
        Storage::deleteDirectory(TenantStorage::path('product/'.$product->id));
    }
}
