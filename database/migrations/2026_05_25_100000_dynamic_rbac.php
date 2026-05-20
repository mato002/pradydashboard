<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_system')->default(false);
            $table->boolean('requires_elevation')->default(false);
            $table->json('elevation_methods')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('role_inheritance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('child_role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['parent_role_id', 'child_role_id']);
        });

        Schema::create('user_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('scope_type')->default('global');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('server_id')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('assignment_reason')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoke_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['role_id', 'scope_type']);
        });

        Schema::create('user_active_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_role_assignment_id')->constrained()->cascadeOnDelete();
            $table->timestamp('activated_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('elevation_verified_at')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();
        });

        Schema::create('role_switch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_assignment_id')->nullable()->constrained('user_role_assignments')->nullOnDelete();
            $table->foreignId('to_assignment_id')->constrained('user_role_assignments')->cascadeOnDelete();
            $table->string('from_role_name')->nullable();
            $table->string('to_role_name');
            $table->text('reason')->nullable();
            $table->string('elevation_method')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_switch_logs');
        Schema::dropIfExists('user_active_roles');
        Schema::dropIfExists('user_role_assignments');
        Schema::dropIfExists('role_inheritance');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
