<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->timestamp('last_synced_at')->nullable()->after('provisioning_meta');
            $table->string('sync_status')->nullable()->after('last_synced_at');
            $table->text('sync_message')->nullable()->after('sync_status');
            $table->string('telemetry_source')->nullable()->after('sync_message');
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['last_synced_at', 'sync_status', 'sync_message', 'telemetry_source']);
        });
    }
};
