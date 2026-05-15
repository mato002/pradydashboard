<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_payments', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->unique()->after('id');
            $table->string('status')->default('successful')->after('amount');
            $table->string('gateway')->nullable()->after('method');
            $table->string('currency', 3)->default('KES')->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_payments', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'status', 'gateway', 'currency']);
        });
    }
};
