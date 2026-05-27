<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'system_code')) {
            Schema::table('products', function (Blueprint $table) {
            $table->string('system_code', 80)->nullable()->after('slug');
            $table->string('owner_department')->nullable()->after('description');
            $table->text('internal_notes')->nullable()->after('owner_department');
            $table->string('min_supported_version', 50)->nullable()->after('default_license_mode');
            $table->date('latest_release_date')->nullable()->after('min_supported_version');
            $table->string('business_model', 60)->nullable()->after('latest_release_date');
            $table->string('deployment_type', 60)->nullable()->after('business_model');
            $table->decimal('default_setup_fee', 14, 2)->nullable()->after('deployment_type');
            $table->decimal('default_monthly_fee', 14, 2)->nullable()->after('default_setup_fee');
            $table->string('billing_model', 40)->nullable()->after('default_monthly_fee');
            $table->string('currency', 3)->default('KES')->after('billing_model');
            $table->unsignedSmallInteger('trial_days')->nullable()->after('currency');
            $table->unsignedSmallInteger('minimum_contract_term')->nullable()->after('trial_days');
            $table->string('license_validation_mode', 20)->default('api')->after('minimum_contract_term');
            $table->unsignedSmallInteger('grace_period_days')->default(7)->after('license_validation_mode');
            $table->boolean('kill_switch_allowed')->default(true)->after('grace_period_days');
            $table->boolean('offline_mode_allowed')->default(false)->after('kill_switch_allowed');
            $table->boolean('contract_document_required')->default(false)->after('offline_mode_allowed');
            $table->boolean('requires_server')->default(true)->after('contract_document_required');
            $table->boolean('requires_domain')->default(true)->after('requires_server');
            $table->boolean('requires_ssl')->default(true)->after('requires_domain');
            $table->boolean('requires_whm')->default(false)->after('requires_ssl');
            $table->unsignedInteger('default_disk_quota_mb')->nullable()->after('requires_whm');
            $table->boolean('default_database_required')->default(true)->after('default_disk_quota_mb');
            $table->boolean('backup_required')->default(true)->after('default_database_required');

            $table->index('business_model');
            $table->index('deployment_type');
            $table->index('status');
            });
        }

        if (! Schema::hasColumn('tenants', 'tenant_code')) {
            Schema::table('tenants', function (Blueprint $table) {
            $table->string('tenant_code', 80)->nullable()->unique()->after('tenant_key');
            $table->string('industry')->nullable()->after('business_type');
            $table->string('registration_number', 80)->nullable()->after('kra_pin');
            $table->string('county_city')->nullable()->after('country');
            $table->string('website')->nullable()->after('county_city');
            $table->string('primary_contact_name')->nullable()->after('website');
            $table->string('primary_contact_email')->nullable()->after('primary_contact_name');
            $table->string('primary_contact_phone', 50)->nullable()->after('primary_contact_email');

            $table->index('status');
            $table->index('tenant_code');
            });
        }

        if (! Schema::hasTable('project_modules')) {
        Schema::create('project_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 80);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->boolean('is_billable')->default(false);
            $table->boolean('default_enabled')->default(false);
            $table->decimal('monthly_price', 14, 2)->nullable();
            $table->decimal('setup_price', 14, 2)->nullable();
            $table->text('dependency_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'code']);
            $table->index(['product_id', 'status']);
        });
        }

        if (! Schema::hasTable('project_versions')) {
        Schema::create('project_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('version', 50);
            $table->date('release_date')->nullable();
            $table->string('release_type', 20)->default('minor');
            $table->string('minimum_supported_version', 50)->nullable();
            $table->text('changelog')->nullable();
            $table->text('migration_notes')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'version']);
            $table->index(['product_id', 'is_current']);
        });
        }

        if (! Schema::hasTable('tenant_project_subscriptions')) {
        Schema::create('tenant_project_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('package_name')->nullable();
            $table->string('billing_cycle', 20)->default('monthly');
            $table->date('start_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->date('trial_expires_at')->nullable();
            $table->string('contract_status', 30)->default('draft');
            $table->string('license_status', 30)->default('active');
            $table->string('product_status', 30)->default('active');
            $table->decimal('monthly_fee', 14, 2)->nullable();
            $table->decimal('setup_fee', 14, 2)->nullable();
            $table->string('currency', 3)->default('KES');
            $table->decimal('discount', 14, 2)->nullable();
            $table->string('payment_terms')->nullable();
            $table->unsignedSmallInteger('grace_period_days')->nullable();
            $table->boolean('kill_switch_enabled')->default(false);
            $table->boolean('offline_mode_allowed')->default(false);
            $table->string('contract_document_path')->nullable();
            $table->date('signed_contract_date')->nullable();
            $table->timestamp('last_license_check_at')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->string('disabled_reason')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'product_id']);
            $table->index(['tenant_id', 'license_status']);
            $table->index(['tenant_id', 'product_status']);
            $table->index(['product_id', 'product_status']);
            $table->index('renewal_date');
        });
        }

        if (! Schema::hasTable('tenant_project_infrastructure')) {
        Schema::create('tenant_project_infrastructure', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_project_subscription_id');
            $table->foreign('tenant_project_subscription_id', 'tpi_subscription_fk')
                ->references('id')->on('tenant_project_subscriptions')->cascadeOnDelete();
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->string('cpanel_account')->nullable();
            $table->string('whm_account_reference')->nullable();
            $table->string('domain')->nullable();
            $table->string('subdomain')->nullable();
            $table->string('database_name')->nullable();
            $table->string('database_user')->nullable();
            $table->unsignedInteger('disk_quota_mb')->nullable();
            $table->unsignedInteger('disk_used_mb')->nullable();
            $table->unsignedInteger('bandwidth_quota_mb')->nullable();
            $table->unsignedInteger('bandwidth_used_mb')->nullable();
            $table->string('ssl_status')->nullable();
            $table->date('ssl_expiry_date')->nullable();
            $table->string('backup_policy')->nullable();
            $table->string('backup_status')->nullable();
            $table->timestamp('last_backup_at')->nullable();
            $table->string('deployment_path')->nullable();
            $table->string('public_url')->nullable();
            $table->string('admin_url')->nullable();
            $table->string('health_check_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('server_id');
            $table->index('ssl_expiry_date');
        });
        }

        if (! Schema::hasTable('tenant_project_versions')) {
        Schema::create('tenant_project_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_project_subscription_id');
            $table->foreign('tenant_project_subscription_id', 'tpv_subscription_fk')
                ->references('id')->on('tenant_project_subscriptions')->cascadeOnDelete();
            $table->string('current_version', 50)->nullable();
            $table->string('latest_version', 50)->nullable();
            $table->string('update_status', 30)->default('unknown');
            $table->string('commit_hash', 80)->nullable();
            $table->string('build_number', 50)->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->text('update_notes')->nullable();
            $table->timestamps();

            $table->unique('tenant_project_subscription_id');
            $table->index('update_status');
        });
        }

        if (! Schema::hasTable('tenant_project_service_integrations')) {
        Schema::create('tenant_project_service_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_project_subscription_id');
            $table->foreign('tenant_project_subscription_id', 'tpsi_subscription_fk')
                ->references('id')->on('tenant_project_subscriptions')->cascadeOnDelete();
            $table->string('service_type', 60);
            $table->string('provider_name')->nullable();
            $table->string('display_name');
            $table->string('status', 30)->default('not_configured');
            $table->text('api_secret')->nullable();
            $table->string('endpoint_url')->nullable();
            $table->string('account_reference')->nullable();
            $table->decimal('balance_credits', 14, 2)->nullable();
            $table->unsignedInteger('monthly_quota')->nullable();
            $table->unsignedInteger('used_quota')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status')->nullable();
            $table->text('last_error')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_project_subscription_id', 'service_type'], 'tpsi_sub_service_idx');
            $table->index('status', 'tpsi_status_idx');
        });
        }

        if (! Schema::hasTable('tenant_project_module_subscriptions')) {
        Schema::create('tenant_project_module_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_project_subscription_id');
            $table->unsignedBigInteger('project_module_id');
            $table->foreign('tenant_project_subscription_id', 'tpms_subscription_fk')
                ->references('id')->on('tenant_project_subscriptions')->cascadeOnDelete();
            $table->foreign('project_module_id', 'tpms_module_fk')
                ->references('id')->on('project_modules')->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->boolean('subscribed')->default(true);
            $table->string('billing_status', 30)->default('active');
            $table->decimal('monthly_price_override', 14, 2)->nullable();
            $table->decimal('setup_price_override', 14, 2)->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_project_subscription_id', 'project_module_id'], 'tpms_subscription_module_unique');
        });
        }

        if (! Schema::hasTable('operational_documents')) {
        Schema::create('operational_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('tenant_project_subscription_id')->nullable();
            $table->foreign('tenant_project_subscription_id', 'odoc_subscription_fk')
                ->references('id')->on('tenant_project_subscriptions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('document_type', 60);
            $table->string('title');
            $table->string('file_path');
            $table->string('status', 30)->default('draft');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('signed_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'document_type']);
            $table->index('expiry_date');
        });
        }

        if (Schema::hasTable('tenant_invoices')) {
            Schema::table('tenant_invoices', function (Blueprint $table) {
                if (! Schema::hasColumn('tenant_invoices', 'tenant_project_subscription_id')) {
                    $table->unsignedBigInteger('tenant_project_subscription_id')->nullable()->after('tenant_id');
                    $table->foreign('tenant_project_subscription_id', 'tinv_subscription_fk')
                        ->references('id')->on('tenant_project_subscriptions')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('support_tickets')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                if (! Schema::hasColumn('support_tickets', 'tenant_project_subscription_id')) {
                    $table->unsignedBigInteger('tenant_project_subscription_id')->nullable()->after('tenant_id');
                    $table->foreign('tenant_project_subscription_id', 'tst_subscription_fk')
                        ->references('id')->on('tenant_project_subscriptions')->nullOnDelete();
                }
            });
        }

        $this->backfillTenantProjectSubscriptions();
    }

    public function down(): void
    {
        if (Schema::hasTable('support_tickets') && Schema::hasColumn('support_tickets', 'tenant_project_subscription_id')) {
            Schema::table('support_tickets', fn (Blueprint $table) => $table->dropConstrainedForeignId('tenant_project_subscription_id'));
        }
        if (Schema::hasTable('tenant_invoices') && Schema::hasColumn('tenant_invoices', 'tenant_project_subscription_id')) {
            Schema::table('tenant_invoices', fn (Blueprint $table) => $table->dropConstrainedForeignId('tenant_project_subscription_id'));
        }

        Schema::dropIfExists('operational_documents');
        Schema::dropIfExists('tenant_project_module_subscriptions');
        Schema::dropIfExists('tenant_project_service_integrations');
        Schema::dropIfExists('tenant_project_versions');
        Schema::dropIfExists('tenant_project_infrastructure');
        Schema::dropIfExists('tenant_project_subscriptions');
        Schema::dropIfExists('project_versions');
        Schema::dropIfExists('project_modules');

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'tenant_code', 'industry', 'registration_number', 'county_city', 'website',
                'primary_contact_name', 'primary_contact_email', 'primary_contact_phone',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'system_code', 'owner_department', 'internal_notes', 'min_supported_version', 'latest_release_date',
                'business_model', 'deployment_type', 'default_setup_fee', 'default_monthly_fee', 'billing_model',
                'currency', 'trial_days', 'minimum_contract_term', 'license_validation_mode', 'grace_period_days',
                'kill_switch_allowed', 'offline_mode_allowed', 'contract_document_required',
                'requires_server', 'requires_domain', 'requires_ssl', 'requires_whm',
                'default_disk_quota_mb', 'default_database_required', 'backup_required',
            ]);
        });
    }

    private function backfillTenantProjectSubscriptions(): void
    {
        if (! Schema::hasTable('tenants') || ! Schema::hasTable('tenant_project_subscriptions')) {
            return;
        }

        $tenants = DB::table('tenants')->whereNotNull('product_id')->get(['id', 'product_id', 'subscription_plan', 'billing_cycle', 'start_date', 'renewal_date', 'subscription_amount', 'tenant_currency', 'grace_days', 'status', 'deployment_version', 'tenant_domain', 'cpanel_account_ref', 'database_ref', 'login_url', 'server_id']);

        foreach ($tenants as $row) {
            $exists = DB::table('tenant_project_subscriptions')
                ->where('tenant_id', $row->id)
                ->where('product_id', $row->product_id)
                ->exists();

            if ($exists) {
                continue;
            }

            $licenseStatus = match ($row->status) {
                'trial' => 'grace',
                'overdue' => 'grace',
                'suspended', 'restricted', 'terminated', 'cancelled' => 'suspended',
                default => 'active',
            };

            $subscriptionId = DB::table('tenant_project_subscriptions')->insertGetId([
                'tenant_id' => $row->id,
                'product_id' => $row->product_id,
                'package_name' => $row->subscription_plan,
                'billing_cycle' => $row->billing_cycle ?? 'monthly',
                'start_date' => $row->start_date,
                'renewal_date' => $row->renewal_date,
                'contract_status' => 'active',
                'license_status' => $licenseStatus,
                'product_status' => in_array($row->status, ['suspended', 'terminated', 'cancelled'], true) ? 'disabled' : 'active',
                'monthly_fee' => $row->subscription_amount,
                'currency' => $row->tenant_currency ?? 'KES',
                'grace_period_days' => $row->grace_days,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('tenant_project_infrastructure')->insert([
                'tenant_project_subscription_id' => $subscriptionId,
                'server_id' => $row->server_id,
                'cpanel_account' => $row->cpanel_account_ref,
                'domain' => $row->tenant_domain,
                'database_name' => $row->database_ref,
                'public_url' => $row->login_url,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($row->deployment_version) {
                DB::table('tenant_project_versions')->insert([
                    'tenant_project_subscription_id' => $subscriptionId,
                    'current_version' => $row->deployment_version,
                    'update_status' => 'unknown',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
