<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hosted_project_id')->nullable()->constrained('hosted_projects')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('agent_key')->unique();
            $table->string('hostname')->nullable();
            $table->string('environment')->nullable();
            $table->string('agent_version')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->json('capabilities')->nullable();
            $table->string('status')->default('registered');
            $table->unsignedBigInteger('disk_space_bytes')->nullable();
            $table->unsignedBigInteger('free_space_bytes')->nullable();
            $table->string('health')->nullable();
            $table->string('php_version')->nullable();
            $table->string('laravel_version')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_agents');
    }
};
