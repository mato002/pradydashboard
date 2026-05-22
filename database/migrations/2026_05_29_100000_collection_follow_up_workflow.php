<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_notes', function (Blueprint $table): void {
            if (! Schema::hasColumn('collection_notes', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('tenant_invoice_id')
                    ->constrained('tenants')->nullOnDelete();
            }
            if (! Schema::hasColumn('collection_notes', 'note')) {
                $table->text('note')->nullable()->after('body');
            }
            if (! Schema::hasColumn('collection_notes', 'follow_up_date')) {
                $table->date('follow_up_date')->nullable()->after('promised_at');
            }
            if (! Schema::hasColumn('collection_notes', 'promise_to_pay_date')) {
                $table->date('promise_to_pay_date')->nullable()->after('follow_up_date');
            }
            if (! Schema::hasColumn('collection_notes', 'promised_amount')) {
                $table->decimal('promised_amount', 14, 2)->nullable()->after('promise_to_pay_date');
            }
            if (! Schema::hasColumn('collection_notes', 'status')) {
                $table->string('status', 32)->default('open')->after('promised_amount');
            }
            if (! Schema::hasColumn('collection_notes', 'outcome')) {
                $table->string('outcome', 48)->nullable()->after('status');
            }
        });

        if (Schema::hasColumn('collection_notes', 'note') && Schema::hasColumn('collection_notes', 'body')) {
            DB::table('collection_notes')
                ->whereNull('note')
                ->whereNotNull('body')
                ->update(['note' => DB::raw('body')]);
        }

        if (Schema::hasColumn('tenant_invoices', 'last_reminder_at') && ! Schema::hasColumn('tenant_invoices', 'last_reminded_at')) {
            // Keep last_reminder_at as canonical field (requirement alias).
        }
    }

    public function down(): void
    {
        Schema::table('collection_notes', function (Blueprint $table): void {
            $cols = ['tenant_id', 'note', 'follow_up_date', 'promise_to_pay_date', 'promised_amount', 'status', 'outcome'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('collection_notes', $col)) {
                    if ($col === 'tenant_id') {
                        $table->dropConstrainedForeignId('tenant_id');
                    } else {
                        $table->dropColumn($col);
                    }
                }
            }
        });
    }
};
