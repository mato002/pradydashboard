<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployment_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('name');
            $table->string('status')->default('connected');
            $table->unsignedInteger('repositories_count')->default(0);
            $table->unsignedInteger('webhooks_count')->default(0);
            $table->json('settings')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('deployment_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deployment_integration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->string('status')->default('delivered');
            $table->string('summary');
            $table->json('payload')->nullable();
            $table->timestamp('received_at');
            $table->timestamps();
        });

        Schema::create('deployment_ops_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_deployment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('summary');
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_ops_events');
        Schema::dropIfExists('deployment_webhook_events');
        Schema::dropIfExists('deployment_integrations');
    }
};
