<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_invoices', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('invoice_number');
            $table->string('payment_method')->nullable()->after('status');
            $table->string('generated_by')->nullable()->after('payment_method');
            $table->decimal('tax_amount', 14, 2)->default(0)->after('penalty_amount');
            $table->timestamp('issued_at')->nullable()->after('due_date');
            $table->boolean('is_recurring')->default(false)->after('issued_at');
            $table->boolean('pdf_generated')->default(false)->after('is_recurring');
            $table->timestamp('email_delivered_at')->nullable()->after('pdf_generated');
            $table->boolean('collection_failed')->default(false)->after('email_delivered_at');
        });

        Schema::create('invoice_recurring_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('product_name')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(16);
            $table->string('frequency')->default('monthly');
            $table->timestamp('next_run_at')->nullable();
            $table->boolean('auto_email')->default(true);
            $table->boolean('auto_pdf')->default(true);
            $table->boolean('enabled')->default(true);
            $table->string('generated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_recurring_schedules');

        Schema::table('tenant_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'product_name',
                'payment_method',
                'generated_by',
                'tax_amount',
                'issued_at',
                'is_recurring',
                'pdf_generated',
                'email_delivered_at',
                'collection_failed',
            ]);
        });
    }
};
