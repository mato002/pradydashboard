<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tenant_invoices', 'manual_client_name')) {
            Schema::table('tenant_invoices', function (Blueprint $table): void {
                $table->string('manual_client_name')->nullable()->after('tenant_id');
                $table->string('manual_client_email')->nullable()->after('manual_client_name');
                $table->string('manual_client_phone')->nullable()->after('manual_client_email');
                $table->text('manual_client_address')->nullable()->after('manual_client_phone');
                $table->string('created_source', 32)->default('automatic')->after('generated_by');
                $table->foreignId('document_template_id')->nullable()->after('created_source')
                    ->constrained('document_templates')->nullOnDelete();
                $table->foreignId('linked_invoice_id')->nullable()->after('source_quotation_id')
                    ->constrained('tenant_invoices')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('tenant_invoices', 'tenant_id')) {
            try {
                Schema::table('tenant_invoices', function (Blueprint $table): void {
                    $table->dropForeign(['tenant_id']);
                });
            } catch (\Throwable) {
                // FK may already be dropped.
            }
            Schema::table('tenant_invoices', function (Blueprint $table): void {
                $table->unsignedBigInteger('tenant_id')->nullable()->change();
                $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            });
        }

        if (Schema::hasTable('tenant_payments') && Schema::hasColumn('tenant_payments', 'tenant_id')) {
            try {
                Schema::table('tenant_payments', function (Blueprint $table): void {
                    $table->dropForeign(['tenant_id']);
                });
            } catch (\Throwable) {
            }
            Schema::table('tenant_payments', function (Blueprint $table): void {
                $table->unsignedBigInteger('tenant_id')->nullable()->change();
                $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('tenant_invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('tenant_invoices', 'linked_invoice_id')) {
                $table->dropConstrainedForeignId('linked_invoice_id');
            }
            if (Schema::hasColumn('tenant_invoices', 'document_template_id')) {
                $table->dropConstrainedForeignId('document_template_id');
            }
            $table->dropColumn([
                'manual_client_name',
                'manual_client_email',
                'manual_client_phone',
                'manual_client_address',
                'created_source',
            ]);
        });
    }
};
