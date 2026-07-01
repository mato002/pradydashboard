<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_invoices', function (Blueprint $table): void {
            $table->dropUnique(['invoice_number']);
            $table->unique(['document_type', 'invoice_number'], 'tenant_invoices_type_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_invoices', function (Blueprint $table): void {
            $table->dropUnique('tenant_invoices_type_number_unique');
            $table->unique('invoice_number');
        });
    }
};
