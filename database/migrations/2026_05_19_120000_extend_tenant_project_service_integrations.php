<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_project_service_integrations')) {
            return;
        }

        Schema::table('tenant_project_service_integrations', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_project_service_integrations', 'integration_category')) {
                $table->string('integration_category', 30)->default('provider')->after('tenant_project_subscription_id');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'api_name')) {
                $table->string('api_name')->nullable()->after('display_name');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'authentication_type')) {
                $table->string('authentication_type', 30)->default('none')->after('endpoint_url');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'purpose')) {
                $table->string('purpose', 40)->nullable()->after('authentication_type');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'last_checked_at')) {
                $table->timestamp('last_checked_at')->nullable()->after('last_tested_at');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'last_success_at')) {
                $table->timestamp('last_success_at')->nullable()->after('last_checked_at');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'last_failure_at')) {
                $table->timestamp('last_failure_at')->nullable()->after('last_success_at');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'last_response_code')) {
                $table->unsignedSmallInteger('last_response_code')->nullable()->after('last_failure_at');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'last_response_time_ms')) {
                $table->unsignedInteger('last_response_time_ms')->nullable()->after('last_response_code');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'success_count')) {
                $table->unsignedInteger('success_count')->default(0)->after('last_response_time_ms');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'failure_count')) {
                $table->unsignedInteger('failure_count')->default(0)->after('success_count');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'uptime_percentage')) {
                $table->decimal('uptime_percentage', 5, 2)->nullable()->after('failure_count');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'average_response_time_ms')) {
                $table->unsignedInteger('average_response_time_ms')->nullable()->after('uptime_percentage');
            }
            if (! Schema::hasColumn('tenant_project_service_integrations', 'last_payload_summary')) {
                $table->json('last_payload_summary')->nullable()->after('average_response_time_ms');
            }
        });

        Schema::table('tenant_project_service_integrations', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_project_service_integrations', 'integration_category')) {
                $table->index(['integration_category', 'status'], 'tpsi_category_status_idx');
            }
            if (Schema::hasColumn('tenant_project_service_integrations', 'purpose')) {
                $table->index(['tenant_project_subscription_id', 'purpose'], 'tpsi_sub_purpose_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tenant_project_service_integrations')) {
            return;
        }

        Schema::table('tenant_project_service_integrations', function (Blueprint $table) {
            $columns = [
                'integration_category',
                'api_name',
                'authentication_type',
                'purpose',
                'last_checked_at',
                'last_success_at',
                'last_failure_at',
                'last_response_code',
                'last_response_time_ms',
                'success_count',
                'failure_count',
                'uptime_percentage',
                'average_response_time_ms',
                'last_payload_summary',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tenant_project_service_integrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
