<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('whm_cpanel_reference')->nullable();
            $table->unsignedSmallInteger('cpu_cores')->nullable();
            $table->decimal('ram_gb', 8, 2)->nullable();
            $table->decimal('storage_gb', 12, 2)->nullable();
            $table->decimal('disk_usage_percent', 5, 2)->nullable();
            $table->string('status')->default('unknown'); // online, offline, unknown
            $table->string('ssl_status')->nullable();
            $table->string('backup_status')->nullable();
            $table->json('hosted_domains')->nullable();
            $table->date('renewal_expires_at')->nullable();
            $table->decimal('monthly_cost', 14, 2)->default(0);
            $table->string('currency', 3)->default('KES');
            $table->decimal('monthly_revenue', 14, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('domain');
            $table->text('technology_stack')->nullable();
            $table->string('git_repository')->nullable();
            $table->string('database_name')->nullable();
            $table->string('status')->default('active'); // active, maintenance, suspended
            $table->string('version')->nullable();
            $table->decimal('monthly_revenue', 14, 2)->nullable();
            $table->decimal('monthly_cost', 14, 2)->nullable();
            $table->string('api_token', 64)->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_key')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('subscription_plan')->nullable();
            $table->string('billing_cycle')->default('monthly'); // monthly, annual
            $table->date('start_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->string('status')->default('active'); // active, trial, suspended, cancelled, overdue
            $table->string('cpanel_account_ref')->nullable();
            $table->string('database_ref')->nullable();
            $table->string('login_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('plan_name');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('billing_cycle')->default('monthly');
            $table->date('current_period_start')->nullable();
            $table->date('current_period_end')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('tenant_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('amount_due', 14, 2)->default(0);
            $table->decimal('amount_paid', 14, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status')->default('draft'); // draft, sent, paid, overdue, void
            $table->timestamps();
        });

        Schema::create('tenant_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_invoice_id')->nullable()->constrained('tenant_invoices')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->timestamp('paid_at')->nullable();
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_access_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('level')->default('soft_reminder');
            $table->boolean('restrict_login')->default(false);
            $table->json('disabled_modules')->nullable();
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('server_health_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->decimal('cpu_percent', 5, 2)->nullable();
            $table->decimal('ram_percent', 5, 2)->nullable();
            $table->decimal('disk_percent', 5, 2)->nullable();
            $table->unsignedInteger('uptime_seconds')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
        });

        Schema::create('project_deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('version');
            $table->timestamp('deployed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->string('status')->default('open');
            $table->string('priority')->default('normal');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('project_deployments');
        Schema::dropIfExists('server_health_logs');
        Schema::dropIfExists('tenant_access_controls');
        Schema::dropIfExists('tenant_payments');
        Schema::dropIfExists('tenant_invoices');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('servers');
    }
};
