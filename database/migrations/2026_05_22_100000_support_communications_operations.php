<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('support_tickets', 'tenant_project_subscription_id')) {
                $table->unsignedBigInteger('tenant_project_subscription_id')->nullable()->after('tenant_id');
                $table->foreign('tenant_project_subscription_id', 'tst_subscription_fk')
                    ->references('id')->on('tenant_project_subscriptions')->nullOnDelete();
            }
            if (! Schema::hasColumn('support_tickets', 'assigned_staff_id')) {
                $table->foreignId('assigned_staff_id')->nullable()->after('hosted_project_id')
                    ->constrained('staff_profiles')->nullOnDelete();
            }
            if (! Schema::hasColumn('support_tickets', 'description')) {
                $table->text('description')->nullable()->after('subject');
            }
            if (! Schema::hasColumn('support_tickets', 'category')) {
                $table->string('category', 40)->default('other')->after('description');
            }
            if (! Schema::hasColumn('support_tickets', 'source')) {
                $table->string('source', 40)->default('internal')->after('priority');
            }
            if (! Schema::hasColumn('support_tickets', 'due_at')) {
                $table->timestamp('due_at')->nullable()->after('opened_at');
            }
            if (! Schema::hasColumn('support_tickets', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('due_at');
            }
            if (! Schema::hasColumn('support_tickets', 'resolution_notes')) {
                $table->text('resolution_notes')->nullable()->after('resolved_at');
            }
        });

        if (! Schema::hasTable('support_ticket_comments')) {
            Schema::create('support_ticket_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
                $table->foreignId('staff_profile_id')->nullable()->constrained('staff_profiles')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('comment_type', 40)->default('internal_note');
                $table->text('message');
                $table->string('visibility', 20)->default('internal');
                $table->string('attachment_path')->nullable();
                $table->timestamps();

                $table->index(['support_ticket_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('tenant_communications')) {
            Schema::create('tenant_communications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('tenant_project_subscription_id')->nullable();
                $table->foreign('tenant_project_subscription_id', 'tcomm_subscription_fk')
                    ->references('id')->on('tenant_project_subscriptions')->nullOnDelete();
                $table->foreignId('staff_profile_id')->nullable()->constrained('staff_profiles')->nullOnDelete();
                $table->string('channel', 30);
                $table->string('direction', 20)->default('inbound');
                $table->string('subject')->nullable();
                $table->text('message');
                $table->dateTime('communication_date');
                $table->boolean('follow_up_required')->default(false);
                $table->date('follow_up_date')->nullable();
                $table->string('status', 30)->default('logged');
                $table->foreignId('related_support_ticket_id')->nullable()
                    ->constrained('support_tickets')->nullOnDelete();
                $table->timestamps();

                $table->index(['tenant_id', 'communication_date']);
                $table->index(['follow_up_required', 'follow_up_date', 'status'], 'tcomm_followup_idx');
            });
        }

        if (! Schema::hasTable('tenant_notices')) {
            Schema::create('tenant_notices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedBigInteger('tenant_project_subscription_id')->nullable();
                $table->foreign('tenant_project_subscription_id', 'tnotice_subscription_fk')
                    ->references('id')->on('tenant_project_subscriptions')->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('notice_type', 40);
                $table->string('title');
                $table->text('message');
                $table->string('severity', 20)->default('info');
                $table->string('status', 30)->default('draft');
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->index(['tenant_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_notices');
        Schema::dropIfExists('tenant_communications');
        Schema::dropIfExists('support_ticket_comments');

        Schema::table('support_tickets', function (Blueprint $table) {
            foreach (['assigned_staff_id', 'description', 'category', 'source', 'due_at', 'resolved_at', 'resolution_notes'] as $col) {
                if (Schema::hasColumn('support_tickets', $col)) {
                    if ($col === 'assigned_staff_id') {
                        $table->dropConstrainedForeignId('assigned_staff_id');
                    } else {
                        $table->dropColumn($col);
                    }
                }
            }
        });
    }
};
