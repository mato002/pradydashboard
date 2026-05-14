<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('product_slug', 80)->nullable()->unique()->after('domain');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('business_type')->nullable()->after('company_name');
            $table->string('kra_pin', 64)->nullable();
            $table->text('physical_address')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('logo_path')->nullable();
            $table->unsignedSmallInteger('grace_days')->default(7)->after('renewal_date');
            $table->decimal('subscription_amount', 14, 2)->nullable()->after('subscription_plan');
            $table->string('tenant_currency', 3)->default('KES')->after('subscription_amount');
            $table->string('tenant_domain')->nullable()->after('login_url');
            $table->string('deployment_version')->nullable();
            $table->decimal('penalties_total', 14, 2)->default(0);
        });

        Schema::table('tenant_invoices', function (Blueprint $table) {
            $table->decimal('penalty_amount', 14, 2)->default(0)->after('amount_paid');
        });

        Schema::create('license_module_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('license_module_id')->constrained('license_module_catalog')->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'license_module_id']);
        });

        Schema::create('tenant_usage_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('active_users')->nullable();
            $table->decimal('database_size_mb', 14, 2)->nullable();
            $table->decimal('storage_usage_mb', 14, 2)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->decimal('server_cpu_percent', 5, 2)->nullable();
            $table->string('reported_app_version', 64)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();

            $table->unique('tenant_id');
        });

        Schema::create('tenant_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 128);
            $table->string('summary')->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('tenant_reported_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'email']);
        });

        Schema::create('tenant_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('level')->default('info');
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_alerts');
        Schema::dropIfExists('tenant_reported_users');
        Schema::dropIfExists('tenant_activity_logs');
        Schema::dropIfExists('tenant_usage_metrics');
        Schema::dropIfExists('tenant_modules');
        Schema::dropIfExists('license_module_catalog');

        Schema::table('tenant_invoices', function (Blueprint $table) {
            $table->dropColumn('penalty_amount');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'business_type',
                'kra_pin',
                'physical_address',
                'country',
                'logo_path',
                'grace_days',
                'subscription_amount',
                'tenant_currency',
                'tenant_domain',
                'deployment_version',
                'penalties_total',
            ]);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('product_slug');
        });
    }
};
