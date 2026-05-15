<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('product_key', 80)->nullable()->after('product_slug');
            $table->text('description')->nullable()->after('name');
            $table->string('base_url')->nullable()->after('domain');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('tenant_key', 120)->nullable()->unique()->after('external_key');
            $table->string('license_secret', 128)->nullable()->after('tenant_key');
            $table->string('access_level')->default('full')->after('status');
        });

        Schema::create('license_check_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tenant_key')->nullable();
            $table->string('product_key')->nullable();
            $table->string('domain')->nullable();
            $table->string('decision')->nullable();
            $table->boolean('allowed')->default(false);
            $table->string('tenant_status')->nullable();
            $table->string('access_level')->nullable();
            $table->unsignedSmallInteger('http_status')->default(200);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('auth_method')->nullable();
            $table->json('request_meta')->nullable();
            $table->timestamp('checked_at')->useCurrent();
            $table->timestamps();

            $table->index(['tenant_id', 'checked_at']);
            $table->index(['product_key', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_check_logs');

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['tenant_key', 'license_secret', 'access_level']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['product_key', 'description', 'base_url']);
        });
    }
};
