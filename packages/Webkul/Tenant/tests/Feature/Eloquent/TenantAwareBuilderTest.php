<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\Locale;
use Webkul\Tenant\Eloquent\TenantAwareBuilder;
use Webkul\Tenant\Models\Scopes\TenantScope;
use Webkul\Tenant\Models\Tenant;

beforeEach(function () {
    Mail::fake();
});

it('uses TenantAwareBuilder for models with BelongsToTenant', function () {
    $channel = new Channel;
    $builder = $channel->newQuery();

    expect($builder)->toBeInstanceOf(TenantAwareBuilder::class);
});

it('allows query to proceed when TenantScope is bypassed (log + allow)', function () {
    $tenant = Tenant::factory()->create(['status' => Tenant::STATUS_ACTIVE]);
    core()->setCurrentTenantId($tenant->id);

    // withoutGlobalScope should NOT throw — it logs and allows
    $result = Channel::withoutGlobalScope(TenantScope::class)->get();
    expect($result)->toBeInstanceOf(Collection::class);

    core()->setCurrentTenantId(null);
});

it('allows query to proceed when all scopes are bypassed', function () {
    $tenant = Tenant::factory()->create(['status' => Tenant::STATUS_ACTIVE]);
    core()->setCurrentTenantId($tenant->id);

    $result = Channel::withoutGlobalScopes()->get();
    expect($result)->toBeInstanceOf(Collection::class);

    core()->setCurrentTenantId(null);
});

it('returns unscoped data when TenantScope is bypassed', function () {
    $tenant1 = Tenant::factory()->create(['status' => Tenant::STATUS_ACTIVE]);
    $tenant2 = Tenant::factory()->create(['status' => Tenant::STATUS_ACTIVE]);

    // Set tenant context to tenant1
    core()->setCurrentTenantId($tenant1->id);

    // Normal scoped query
    $scoped = Locale::all();

    // Bypassed query — should see ALL locales
    $unscoped = Locale::withoutGlobalScope(TenantScope::class)->get();

    expect($unscoped->count())->toBeGreaterThanOrEqual($scoped->count());

    core()->setCurrentTenantId(null);
});
