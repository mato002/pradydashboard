<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'billing_contact_name')) {
                $table->string('billing_contact_name')->nullable()->after('email');
                $table->string('billing_email')->nullable()->after('billing_contact_name');
                $table->string('billing_phone')->nullable()->after('billing_email');
                $table->text('billing_address')->nullable()->after('billing_phone');
                $table->string('billing_tax_pin', 80)->nullable()->after('billing_address');
                $table->string('billing_preferred_currency', 3)->nullable()->after('billing_tax_pin');
                $table->string('billing_payment_terms', 80)->nullable()->after('billing_preferred_currency');
                $table->boolean('billing_tax_exempt')->default(false)->after('billing_payment_terms');
                $table->text('billing_notes')->nullable()->after('billing_tax_exempt');
            }
        });

        Schema::table('tenant_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_invoices', 'tenant_project_subscription_id')) {
                $table->unsignedBigInteger('tenant_project_subscription_id')->nullable()->after('tenant_id');
                $table->foreign('tenant_project_subscription_id', 'tinv_subscription_fk')
                    ->references('id')->on('tenant_project_subscriptions')->nullOnDelete();
            }
            if (! Schema::hasColumn('tenant_invoices', 'currency')) {
                $table->string('currency', 3)->default('KES')->after('invoice_number');
            }
            if (! Schema::hasColumn('tenant_invoices', 'subtotal')) {
                $table->decimal('subtotal', 14, 2)->default(0)->after('currency');
            }
            if (! Schema::hasColumn('tenant_invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 14, 2)->default(0)->after('subtotal');
            }
            if (! Schema::hasColumn('tenant_invoices', 'total')) {
                $table->decimal('total', 14, 2)->default(0)->after('tax_amount');
            }
            if (! Schema::hasColumn('tenant_invoices', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            if (! Schema::hasColumn('tenant_invoices', 'issue_date')) {
                $table->date('issue_date')->nullable()->after('notes');
            }
        });

        if (! Schema::hasTable('tenant_invoice_line_items')) {
            Schema::create('tenant_invoice_line_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_invoice_id')->constrained('tenant_invoices')->cascadeOnDelete();
                $table->string('item_type', 40);
                $table->string('description');
                $table->decimal('quantity', 12, 4)->default(1);
                $table->decimal('unit_price', 14, 2)->default(0);
                $table->decimal('discount', 14, 2)->default(0);
                $table->decimal('tax_rate', 8, 4)->default(0);
                $table->decimal('tax_amount', 14, 2)->default(0);
                $table->decimal('line_total', 14, 2)->default(0);
                $table->string('related_model_type')->nullable();
                $table->unsignedBigInteger('related_model_id')->nullable();
                $table->timestamps();

                $table->index(['item_type', 'related_model_type', 'related_model_id'], 'tinv_line_billable_idx');
            });
        }

        if (! Schema::hasColumn('tenant_payments', 'notes')) {
            Schema::table('tenant_payments', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('reference');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_invoice_line_items');

        Schema::table('tenant_payments', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_payments', 'notes')) {
                $table->dropColumn('notes');
            }
        });

        Schema::table('tenant_invoices', function (Blueprint $table) {
            $cols = ['currency', 'subtotal', 'discount_amount', 'total', 'notes', 'issue_date'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('tenant_invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('tenant_invoices', 'tenant_project_subscription_id')) {
                $table->dropConstrainedForeignId('tenant_project_subscription_id');
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'billing_contact_name')) {
                $table->dropColumn([
                    'billing_contact_name', 'billing_email', 'billing_phone', 'billing_address',
                    'billing_tax_pin', 'billing_preferred_currency', 'billing_payment_terms',
                    'billing_tax_exempt', 'billing_notes',
                ]);
            }
        });
    }
};
