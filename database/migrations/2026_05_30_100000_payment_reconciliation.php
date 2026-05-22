<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        Schema::table('tenant_payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('tenant_payments', 'source')) {
                $table->string('source', 32)->default('manual')->after('tenant_invoice_id');
            }
            if (! Schema::hasColumn('tenant_payments', 'payer_name')) {
                $table->string('payer_name')->nullable()->after('source');
            }
            if (! Schema::hasColumn('tenant_payments', 'payer_phone')) {
                $table->string('payer_phone', 40)->nullable()->after('payer_name');
            }
            if (! Schema::hasColumn('tenant_payments', 'payer_email')) {
                $table->string('payer_email')->nullable()->after('payer_phone');
            }
            if (! Schema::hasColumn('tenant_payments', 'bank_source')) {
                $table->string('bank_source', 120)->nullable()->after('reference');
            }
            if (! Schema::hasColumn('tenant_payments', 'narration')) {
                $table->text('narration')->nullable()->after('bank_source');
            }
            if (! Schema::hasColumn('tenant_payments', 'reconciliation_status')) {
                $table->string('reconciliation_status', 32)->default('unreconciled')->after('status');
            }
            if (! Schema::hasColumn('tenant_payments', 'unapplied_amount')) {
                $table->decimal('unapplied_amount', 14, 2)->default(0)->after('amount');
            }
            if (! Schema::hasColumn('tenant_payments', 'matched_at')) {
                $table->timestamp('matched_at')->nullable()->after('reconciliation_status');
            }
            if (! Schema::hasColumn('tenant_payments', 'matched_by')) {
                $table->foreignId('matched_by')->nullable()->after('matched_at')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('tenant_payments', 'recorded_by')) {
                $table->foreignId('recorded_by')->nullable()->after('matched_by')
                    ->constrained('users')->nullOnDelete();
            }
        });

        if (! Schema::hasTable('payment_allocations')) {
            Schema::create('payment_allocations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_payment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tenant_invoice_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 14, 2);
                $table->timestamps();

                $table->index(['tenant_payment_id', 'tenant_invoice_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');

        Schema::table('tenant_payments', function (Blueprint $table): void {
            foreach ([
                'source', 'payer_name', 'payer_phone', 'payer_email', 'bank_source', 'narration',
                'reconciliation_status', 'unapplied_amount', 'matched_at',
            ] as $col) {
                if (Schema::hasColumn('tenant_payments', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('tenant_payments', 'matched_by')) {
                $table->dropConstrainedForeignId('matched_by');
            }
            if (Schema::hasColumn('tenant_payments', 'recorded_by')) {
                $table->dropConstrainedForeignId('recorded_by');
            }
        });
    }
};
