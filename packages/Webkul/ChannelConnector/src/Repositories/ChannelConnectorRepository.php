<?php

namespace Webkul\ChannelConnector\Repositories;

use Webkul\ChannelConnector\Contracts\ChannelConnector;
use Webkul\Core\Eloquent\Repository;

class ChannelConnectorRepository extends Repository
{
    public function model(): string
    {
        return ChannelConnector::class;
    }
}
