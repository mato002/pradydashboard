<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenant_invoices')) {
            return;
        }

        Schema::table('tenant_invoices', function (Blueprint $table): void {
            $table->index(['tenant_id', 'document_type', 'status'], 'tenant_invoices_tenant_type_status_idx');
            $table->index(['issue_date'], 'tenant_invoices_issue_date_idx');
            $table->index(['finalized_at'], 'tenant_invoices_finalized_at_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tenant_invoices')) {
            return;
        }

        Schema::table('tenant_invoices', function (Blueprint $table): void {
            $table->dropIndex('tenant_invoices_tenant_type_status_idx');
            $table->dropIndex('tenant_invoices_issue_date_idx');
            $table->dropIndex('tenant_invoices_finalized_at_idx');
        });
    }
};
