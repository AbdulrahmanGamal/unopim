<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a debounce timestamp so the outbound sync engine can detect when a
 * mapping has just been updated from an inbound channel webhook, and skip
 * pushing the same change back to the channel (bidirectional sync loop
 * prevention — see ChannelConnector audit CRIT-7).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_channel_mappings', function (Blueprint $table) {
            $table->timestamp('last_inbound_at')->nullable()->after('last_synced_at');
            $table->index('last_inbound_at', 'idx_product_mapping_last_inbound');
        });
    }

    public function down(): void
    {
        Schema::table('product_channel_mappings', function (Blueprint $table) {
            $table->dropIndex('idx_product_mapping_last_inbound');
            $table->dropColumn('last_inbound_at');
        });
    }
};
