<?php

namespace Webkul\ChannelConnector\Repositories;

use Webkul\ChannelConnector\Contracts\ProductChannelMapping;
use Webkul\Core\Eloquent\Repository;

class ProductChannelMappingRepository extends Repository
{
    public function model(): string
    {
        return ProductChannelMapping::class;
    }
}
