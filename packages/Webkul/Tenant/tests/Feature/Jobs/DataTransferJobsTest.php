<?php

use Illuminate\Support\Facades\Mail;
use Webkul\DataTransfer\Jobs\Export\ExportBatch;
use Webkul\DataTransfer\Jobs\Export\ExportTrackBatch;
use Webkul\DataTransfer\Jobs\Export\UploadFile;
use Webkul\DataTransfer\Jobs\Import\Completed;
use Webkul\DataTransfer\Jobs\Import\ImportBatch;
use Webkul\DataTransfer\Jobs\Import\ImportTrackBatch;
use Webkul\DataTransfer\Jobs\Import\IndexBatch;
use Webkul\DataTransfer\Jobs\Import\Indexing;
use Webkul\DataTransfer\Jobs\Import\JobTrackBatch;
use Webkul\DataTransfer\Jobs\Import\LinkBatch;
use Webkul\DataTransfer\Jobs\Import\Linking;
use Webkul\DataTransfer\Jobs\System\BulkProductUpdate;
use Webkul\MagicAI\Jobs\SaveTranslatedAllAttributesJob;
use Webkul\MagicAI\Jobs\SaveTranslatedDataJob;
use Webkul\Product\Models\Product;
use Webkul\Tenant\Jobs\TenantAwareJob;
use Webkul\Tenant\Models\Scopes\TenantScope;
use Webkul\Tenant\Models\Tenant;

beforeEach(function () {
    Mail::fake();
});

// --- Story 4.2: DataTransfer jobs use TenantAwareJob ---

it('ImportBatch has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(ImportBatch::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('ImportTrackBatch has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(ImportTrackBatch::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('IndexBatch has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(IndexBatch::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('Indexing has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(Indexing::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('LinkBatch has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(LinkBatch::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('Linking has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(Linking::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('JobTrackBatch has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(JobTrackBatch::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('Import Completed has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(Completed::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('ExportBatch has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(ExportBatch::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('ExportTrackBatch has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(ExportTrackBatch::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('Export Completed has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(Webkul\DataTransfer\Jobs\Export\Completed::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('UploadFile has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(UploadFile::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('BulkProductUpdate has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(BulkProductUpdate::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

// --- Story 4.3: MagicAI jobs use TenantAwareJob ---

it('SaveTranslatedDataJob has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(SaveTranslatedDataJob::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

it('SaveTranslatedAllAttributesJob has TenantAwareJob trait', function () {
    $traits = class_uses_recursive(SaveTranslatedAllAttributesJob::class);
    expect($traits)->toHaveKey(TenantAwareJob::class);
});

// --- Story 4.6/4.7: Import/Export tenant isolation via Eloquent scope ---

it('Product model has TenantScope global scope for query-level filtering (FR37)', function () {
    $product = new Product;
    $scopes = $product->getGlobalScopes();

    expect($scopes)->toHaveKey(TenantScope::class);
});

it('Product model auto-sets tenant_id on creation (FR34)', function () {
    $tenant = Tenant::factory()->create(['status' => Tenant::STATUS_ACTIVE]);
    core()->setCurrentTenantId($tenant->id);

    // Create a product directly (simulating what import does)
    $product = Product::create([
        'sku'  => 'AUTO-TENANT-SKU-'.uniqid(),
        'type' => 'simple',
    ]);

    expect($product->tenant_id)->toBe($tenant->id);

    core()->setCurrentTenantId(null);
});
