<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_payments') && ! Schema::hasColumn('tenant_payments', 'gateway_transaction_uuid')) {
            Schema::table('tenant_payments', function (Blueprint $table): void {
                $table->uuid('gateway_transaction_uuid')->nullable()->unique()->after('transaction_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tenant_payments') && Schema::hasColumn('tenant_payments', 'gateway_transaction_uuid')) {
            Schema::table('tenant_payments', function (Blueprint $table): void {
                $table->dropColumn('gateway_transaction_uuid');
            });
        }
    }
};
