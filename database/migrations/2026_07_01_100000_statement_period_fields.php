<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_invoices', function (Blueprint $table): void {
            $table->date('statement_period_start')->nullable()->after('linked_invoice_id');
            $table->date('statement_period_end')->nullable()->after('statement_period_start');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_invoices', function (Blueprint $table): void {
            $table->dropColumn(['statement_period_start', 'statement_period_end']);
        });
    }
};
