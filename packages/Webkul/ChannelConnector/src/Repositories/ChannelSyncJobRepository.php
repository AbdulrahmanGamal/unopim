<?php

namespace Webkul\ChannelConnector\Repositories;

use Webkul\ChannelConnector\Contracts\ChannelSyncJob;
use Webkul\Core\Eloquent\Repository;

class ChannelSyncJobRepository extends Repository
{
    public function model(): string
    {
        return ChannelSyncJob::class;
    }
}
