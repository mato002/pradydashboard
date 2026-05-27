<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->uuid('payments_gateway_tenant_uuid')->nullable()->unique()->after('external_key');
            $table->timestamp('payments_gateway_linked_at')->nullable()->after('payments_gateway_tenant_uuid');
            $table->string('payments_gateway_status', 32)->nullable()->after('payments_gateway_linked_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'payments_gateway_tenant_uuid',
                'payments_gateway_linked_at',
                'payments_gateway_status',
            ]);
        });
    }
};
