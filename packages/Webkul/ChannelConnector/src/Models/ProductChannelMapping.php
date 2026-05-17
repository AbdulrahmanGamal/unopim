<?php

namespace Webkul\ChannelConnector\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\ChannelConnector\Contracts\ProductChannelMapping as ProductChannelMappingContract;
use Webkul\Product\Models\ProductProxy;
use Webkul\Tenant\Models\Concerns\BelongsToTenant;

class ProductChannelMapping extends Model implements ProductChannelMappingContract
{
    use BelongsToTenant;

    /**
     * Default debounce window for the wasRecentlyInbound() check. Outbound
     * syncs running within this many seconds after an inbound webhook update
     * will be skipped to prevent webhook → sync → webhook loops.
     *
     * Tunable via the second argument to wasRecentlyInbound().
     */
    public const DEFAULT_INBOUND_DEBOUNCE_SECONDS = 60;

    protected $table = 'product_channel_mappings';

    protected $fillable = [
        'channel_connector_id',
        'product_id',
        'external_id',
        'external_variant_id',
        'entity_type',
        'sync_status',
        'last_synced_at',
        'last_inbound_at',
        'data_hash',
        'meta',
    ];

    protected $casts = [
        'meta'            => 'array',
        'last_synced_at'  => 'datetime',
        'last_inbound_at' => 'datetime',
    ];

    public function connector(): BelongsTo
    {
        return $this->belongsTo(ChannelConnector::class, 'channel_connector_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductProxy::class, 'product_id');
    }

    /**
     * True when this mapping was updated from an inbound channel webhook
     * within the debounce window. The outbound sync engine should skip
     * pushing the same change back when this returns true (loop prevention).
     */
    public function wasRecentlyInbound(?int $debounceSeconds = null): bool
    {
        if ($this->last_inbound_at === null) {
            return false;
        }

        $window = $debounceSeconds ?? self::DEFAULT_INBOUND_DEBOUNCE_SECONDS;

        return $this->last_inbound_at->greaterThan(now()->subSeconds($window));
    }
}
