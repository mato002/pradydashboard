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
            if (! Schema::hasColumn('tenant_invoices', 'tenant_payment_id')) {
                $table->foreignId('tenant_payment_id')
                    ->nullable()
                    ->after('linked_invoice_id')
                    ->constrained('tenant_payments')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tenant_invoices') || ! Schema::hasColumn('tenant_invoices', 'tenant_payment_id')) {
            return;
        }

        Schema::table('tenant_invoices', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tenant_payment_id');
        });
    }
};
