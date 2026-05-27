<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('system_activity_logs')) {
            return;
        }

        Schema::create('system_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_profile_id')->nullable()->constrained('staff_profiles')->nullOnDelete();
            $table->string('actor_name')->nullable();
            $table->string('action', 120);
            $table->string('category', 40);
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('hosted_project_id')->nullable()->constrained('hosted_projects')->nullOnDelete();
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('tenant_invoices')->nullOnDelete();
            $table->foreignId('support_ticket_id')->nullable()->constrained('support_tickets')->nullOnDelete();
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['category', 'created_at']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['hosted_project_id', 'created_at']);
            $table->index(['server_id', 'created_at']);
            $table->index(['invoice_id', 'created_at']);
            $table->index(['support_ticket_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_activity_logs');
    }
};
