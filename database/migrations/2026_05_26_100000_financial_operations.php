<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_invoices', function (Blueprint $table): void {
            $table->string('document_type', 32)->default('invoice')->after('invoice_number');
            $table->string('approval_status', 32)->nullable()->after('status');
            $table->timestamp('converted_at')->nullable()->after('approval_status');
            $table->foreignId('converted_invoice_id')->nullable()->after('converted_at')
                ->constrained('tenant_invoices')->nullOnDelete();
            $table->string('delivery_status', 32)->default('pending')->after('email_delivered_at');
            $table->timestamp('finalized_at')->nullable()->after('delivery_status');
            $table->unsignedSmallInteger('revision_number')->default(1)->after('finalized_at');
            $table->foreignId('source_quotation_id')->nullable()->after('revision_number')
                ->constrained('tenant_invoices')->nullOnDelete();
            $table->timestamp('last_reminder_at')->nullable()->after('collection_failed');
            $table->unsignedSmallInteger('reminder_count')->default(0)->after('last_reminder_at');
        });

        Schema::create('document_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type', 32);
            $table->string('style', 32)->default('modern_saas');
            $table->string('blade_view');
            $table->text('css')->nullable();
            $table->json('branding')->nullable();
            $table->string('paper_size', 16)->default('A4');
            $table->string('orientation', 16)->default('portrait');
            $table->boolean('active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['type', 'active']);
        });

        Schema::create('generated_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32);
            $table->foreignId('document_template_id')->nullable()->constrained()->nullOnDelete();
            $table->longText('html_snapshot');
            $table->json('data_snapshot')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('rendered_at');
            $table->string('rendered_by')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('whatsapp_sent_at')->nullable();
            $table->string('delivery_status', 32)->default('pending');
            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_invoice_id', 'type']);
        });

        Schema::create('billing_automation_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('Platform defaults');
            $table->unsignedSmallInteger('reminder_after_days')->default(3);
            $table->unsignedSmallInteger('penalty_after_days')->default(14);
            $table->unsignedSmallInteger('suspension_after_days')->default(30);
            $table->unsignedSmallInteger('grace_period_days')->default(7);
            $table->decimal('penalty_percent', 5, 2)->default(2.00);
            $table->decimal('vat_percent', 5, 2)->nullable();
            $table->boolean('recurring_enabled')->default(true);
            $table->boolean('auto_send_invoices')->default(false);
            $table->boolean('auto_send_receipts')->default(true);
            $table->boolean('auto_generate_pdf')->default(true);
            $table->timestamps();
        });

        Schema::create('collection_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note_type', 32);
            $table->text('body');
            $table->date('promised_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_invoice_id', 'note_type']);
        });

        Schema::table('invoice_recurring_schedules', function (Blueprint $table): void {
            $table->string('cycle', 32)->nullable()->after('frequency');
            $table->unsignedSmallInteger('custom_interval_days')->nullable()->after('cycle');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_recurring_schedules', function (Blueprint $table): void {
            $table->dropColumn(['cycle', 'custom_interval_days']);
        });

        Schema::dropIfExists('collection_notes');
        Schema::dropIfExists('billing_automation_rules');
        Schema::dropIfExists('generated_documents');
        Schema::dropIfExists('document_templates');

        Schema::table('tenant_invoices', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('converted_invoice_id');
            $table->dropConstrainedForeignId('source_quotation_id');
            $table->dropColumn([
                'document_type',
                'approval_status',
                'converted_at',
                'delivery_status',
                'finalized_at',
                'revision_number',
                'last_reminder_at',
                'reminder_count',
            ]);
        });
    }
};
