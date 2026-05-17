<?php

namespace Webkul\ChannelConnector\Repositories;

use Webkul\ChannelConnector\Contracts\ChannelSyncConflict;
use Webkul\Core\Eloquent\Repository;

class ChannelSyncConflictRepository extends Repository
{
    public function model(): string
    {
        return ChannelSyncConflict::class;
    }
}
