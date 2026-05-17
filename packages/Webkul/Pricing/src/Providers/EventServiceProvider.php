<?php

namespace Webkul\Pricing\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Log;
use Webkul\Category\Models\Category;
use Webkul\Core\Models\Channel;
use Webkul\Pricing\Events\CostUpdated;
use Webkul\Pricing\Events\MarginApproved;
use Webkul\Pricing\Events\MarginBlocked;
use Webkul\Pricing\Events\RecommendationApplied;
use Webkul\Pricing\Listeners\InvalidatePricingCache;
use Webkul\Pricing\Listeners\NotifyMarginViolation;
use Webkul\Pricing\Models\PricingStrategy;
use Webkul\Product\Models\Product;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CostUpdated::class => [
            [InvalidatePricingCache::class, 'handleCostUpdated'],
        ],
        MarginBlocked::class => [
            NotifyMarginViolation::class,
        ],
        MarginApproved::class => [
            [InvalidatePricingCache::class, 'handleMarginApproved'],
        ],
        RecommendationApplied::class => [
            [InvalidatePricingCache::class, 'handleRecommendationApplied'],
        ],
    ];

    /**
     * Register any events for your application (F-008: orphan strategy cleanup).
     */
    public function boot(): void
    {
        parent::boot();

        // F-008: Clean up orphan pricing strategies when a product/channel/category is deleted
        $this->app['events']->listen('eloquent.deleted:*', function (string $event, array $models) {
            foreach ($models as $model) {
                $this->cleanupOrphanStrategies($model);
            }
        });
    }

    /**
     * Delete orphaned pricing strategies when their scope entity is deleted.
     */
    protected function cleanupOrphanStrategies($model): void
    {
        $scopeType = match (true) {
            $model instanceof Product                          => 'product',
            $model instanceof Channel                          => 'channel',
            $model instanceof Category                         => 'category',
            default                                            => null,
        };

        if ($scopeType === null) {
            return;
        }

        try {
            PricingStrategy::query()
                ->where('scope_type', $scopeType)
                ->where('scope_id', $model->id)
                ->delete();

            Log::debug('Orphan strategies cleaned up', [
                'scope_type' => $scopeType,
                'scope_id'   => $model->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to cleanup orphan strategies', [
                'scope_type' => $scopeType,
                'scope_id'   => $model->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
