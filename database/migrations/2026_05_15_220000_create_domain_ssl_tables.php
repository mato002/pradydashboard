<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('managed_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->boolean('is_subdomain')->default(false);
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('registrar')->nullable();
            $table->string('status')->default('active');
            $table->string('ssl_status')->default('active');
            $table->string('dns_status')->default('healthy');
            $table->timestamp('ssl_expires_at')->nullable();
            $table->date('domain_expires_at')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->boolean('is_wildcard')->default(false);
            $table->boolean('is_tenant_custom')->default(false);
            $table->string('ssl_issuer')->nullable();
            $table->string('routing_target')->nullable();
            $table->json('certificate_chain')->nullable();
            $table->json('renewal_history')->nullable();
            $table->timestamp('last_dns_check_at')->nullable();
            $table->timestamps();
        });

        Schema::create('dns_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('managed_domain_id')->constrained()->cascadeOnDelete();
            $table->string('record_type');
            $table->string('host');
            $table->text('value');
            $table->unsignedInteger('ttl')->default(3600);
            $table->string('propagation_status')->default('propagated');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dns_records');
        Schema::dropIfExists('managed_domains');
    }
};
