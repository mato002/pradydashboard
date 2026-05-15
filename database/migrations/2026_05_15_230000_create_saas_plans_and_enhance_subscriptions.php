<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('tier'); // starter, professional, enterprise, custom
            $table->decimal('monthly_price', 14, 2)->default(0);
            $table->decimal('annual_price', 14, 2)->nullable();
            $table->string('currency', 3)->default('KES');
            $table->json('features')->nullable();
            $table->unsignedInteger('api_quota')->nullable();
            $table->unsignedInteger('storage_gb')->nullable();
            $table->unsignedInteger('max_tenants')->nullable();
            $table->unsignedInteger('max_seats')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('tenant_subscriptions', function (Blueprint $table) {
            $table->foreignId('saas_plan_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
            $table->string('product_name')->nullable()->after('plan_name');
            $table->boolean('auto_renew')->default(true)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('saas_plan_id');
            $table->dropColumn(['product_name', 'auto_renew']);
        });

        Schema::dropIfExists('saas_plans');
    }
};
