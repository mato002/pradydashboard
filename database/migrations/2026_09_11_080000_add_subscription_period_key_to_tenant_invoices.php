<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_invoices', function (Blueprint $table): void {
            $table->string('subscription_period_key', 96)->nullable()->after('statement_period_end');
            $table->unique('subscription_period_key', 'tenant_invoices_subscription_period_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_invoices', function (Blueprint $table): void {
            $table->dropUnique('tenant_invoices_subscription_period_key_unique');
            $table->dropColumn('subscription_period_key');
        });
    }
};
