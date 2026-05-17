<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidates the 7 per-channel product-mapping tables into the unified
 * `product_channel_mappings` table.
 *
 * Each per-channel table (e.g. `amazon_product_mappings`, `ebay_product_mappings`,
 * etc.) tracked the same conceptual data (product ↔ external id per connector)
 * but with slightly different schemas. Wave 3d unifies them so:
 *
 *   - Conflict detection (ConflictResolver) works for every channel
 *   - The inbound debounce timer (wave 3c `last_inbound_at`) works for every channel
 *   - There is one source of truth per (connector, product) pair
 *
 * Extra per-channel columns (external_sku, external_parent_id, variant_data,
 * error_message) are preserved in the unified table's `meta` JSON column.
 *
 * SAFETY:
 *   - This migration COPIES data; it does NOT drop the source tables.
 *     A separate cleanup migration can drop them once production staging
 *     confirms the unified table is being used correctly by all adapters.
 *   - The INSERT is idempotent: existing (channel_connector_id, product_id,
 *     entity_type='product') rows in product_channel_mappings are preserved.
 *   - Cross-DB safe: uses Eloquent + PHP rather than DB-specific JSON helpers.
 */
return new class extends Migration
{
    /**
     * Source tables to consolidate, keyed by table name with the source-of-
     * truth column mapping. Each source table has columns:
     *   tenant_id, connector_id, product_id, external_id, external_sku,
     *   external_parent_id, variant_data (JSON), sync_status, last_synced_at,
     *   error_message, created_at, updated_at
     *
     * The Salla adapter already uses the unified table — no source for it here.
     */
    protected array $sourceTables = [
        'amazon_product_mappings',
        'ebay_product_mappings',
        'magento2_product_mappings',
        'noon_product_mappings',
        'woocommerce_product_mappings',
        'easyorders_product_mappings',
    ];

    public function up(): void
    {
        $totalMigrated = 0;
        $totalSkipped = 0;

        foreach ($this->sourceTables as $sourceTable) {
            if (! Schema::hasTable($sourceTable)) {
                Log::info("[UnifiedMapping] Skipping {$sourceTable} (table does not exist)");

                continue;
            }

            $rows = DB::table($sourceTable)->get();

            foreach ($rows as $row) {
                $exists = DB::table('product_channel_mappings')
                    ->where('channel_connector_id', $row->connector_id)
                    ->where('product_id', $row->product_id)
                    ->where('entity_type', 'product')
                    ->exists();

                if ($exists) {
                    $totalSkipped++;

                    continue;
                }

                // Pack the per-channel extras into the unified `meta` JSON column.
                $meta = array_filter([
                    'external_sku'        => $row->external_sku ?? null,
                    'external_parent_id'  => $row->external_parent_id ?? null,
                    'variant_data'        => $this->decodeJsonField($row->variant_data ?? null),
                    'error_message'       => $row->error_message ?? null,
                    '_migrated_from'      => $sourceTable,
                    '_migrated_at'        => now()->toIso8601String(),
                ], fn ($v) => $v !== null && $v !== '');

                DB::table('product_channel_mappings')->insert([
                    'tenant_id'            => $row->tenant_id ?? null,
                    'channel_connector_id' => $row->connector_id,
                    'product_id'           => $row->product_id,
                    'entity_type'          => 'product',
                    'external_id'          => $row->external_id ?? '',
                    'sync_status'          => $row->sync_status ?? 'pending',
                    'last_synced_at'       => $row->last_synced_at ?? null,
                    'meta'                 => json_encode($meta),
                    'created_at'           => $row->created_at ?? now(),
                    'updated_at'           => $row->updated_at ?? now(),
                ]);

                $totalMigrated++;
            }

            Log::info("[UnifiedMapping] Migrated rows from {$sourceTable}");
        }

        Log::info('[UnifiedMapping] Consolidation complete', [
            'rows_migrated'                   => $totalMigrated,
            'rows_skipped_duplicate'          => $totalSkipped,
            'source_tables_kept_for_rollback' => $this->sourceTables,
        ]);
    }

    /**
     * Rollback: delete only the rows that this migration inserted. Identified
     * by the `_migrated_from` marker in the meta JSON. The per-channel source
     * tables are untouched (this migration never dropped them), so adapters
     * that get reverted will find their data exactly where they left it.
     */
    public function down(): void
    {
        // Cross-DB-safe: pull all rows and filter in PHP rather than using
        // database-specific JSON-search SQL.
        $rows = DB::table('product_channel_mappings')->get(['id', 'meta']);

        $deleteIds = [];
        foreach ($rows as $row) {
            $meta = is_string($row->meta) ? json_decode($row->meta, true) : ($row->meta ?? null);
            if (is_array($meta) && in_array($meta['_migrated_from'] ?? null, $this->sourceTables, true)) {
                $deleteIds[] = $row->id;
            }
        }

        if (! empty($deleteIds)) {
            DB::table('product_channel_mappings')
                ->whereIn('id', $deleteIds)
                ->delete();
        }

        Log::info('[UnifiedMapping] Rolled back consolidation', [
            'rows_deleted' => count($deleteIds),
        ]);
    }

    /**
     * Source tables stored variant_data as a JSON column. The DB driver may
     * return it as a string (MySQL) or an array (PostgreSQL JSON column).
     * Normalise to array, or null when not parseable.
     */
    protected function decodeJsonField($value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : null;
    }
};
