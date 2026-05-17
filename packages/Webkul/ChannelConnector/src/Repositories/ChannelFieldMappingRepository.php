<?php

namespace Webkul\ChannelConnector\Repositories;

use Webkul\ChannelConnector\Contracts\ChannelFieldMapping;
use Webkul\Core\Eloquent\Repository;

class ChannelFieldMappingRepository extends Repository
{
    public function model(): string
    {
        return ChannelFieldMapping::class;
    }
}
