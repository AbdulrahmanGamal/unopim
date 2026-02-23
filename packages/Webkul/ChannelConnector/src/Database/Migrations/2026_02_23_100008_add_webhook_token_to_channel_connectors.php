<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_connectors', function (Blueprint $table) {
            $table->string('webhook_token', 64)->nullable()->after('settings');
            $table->index('webhook_token', 'idx_connector_webhook_token');
        });
    }

    public function down(): void
    {
        Schema::table('channel_connectors', function (Blueprint $table) {
            $table->dropIndex('idx_connector_webhook_token');
            $table->dropColumn('webhook_token');
        });
    }
};
