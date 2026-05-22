<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_invoices', function (Blueprint $table): void {
            if (! Schema::hasColumn('tenant_invoices', 'email_sent_at')) {
                $table->timestamp('email_sent_at')->nullable()->after('email_delivered_at');
            }
            if (! Schema::hasColumn('tenant_invoices', 'last_delivery_error')) {
                $table->text('last_delivery_error')->nullable()->after('email_sent_at');
            }
        });

        Schema::table('generated_documents', function (Blueprint $table): void {
            if (! Schema::hasColumn('generated_documents', 'last_delivery_error')) {
                $table->text('last_delivery_error')->nullable()->after('delivery_status');
            }
        });

        if (Schema::hasColumn('generated_documents', 'tenant_id')) {
            try {
                Schema::table('generated_documents', function (Blueprint $table): void {
                    $table->dropForeign(['tenant_id']);
                });
            } catch (\Throwable) {
            }
            Schema::table('generated_documents', function (Blueprint $table): void {
                $table->unsignedBigInteger('tenant_id')->nullable()->change();
                $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('tenant_invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('tenant_invoices', 'last_delivery_error')) {
                $table->dropColumn('last_delivery_error');
            }
            if (Schema::hasColumn('tenant_invoices', 'email_sent_at')) {
                $table->dropColumn('email_sent_at');
            }
        });

        Schema::table('generated_documents', function (Blueprint $table): void {
            if (Schema::hasColumn('generated_documents', 'last_delivery_error')) {
                $table->dropColumn('last_delivery_error');
            }
        });
    }
};
