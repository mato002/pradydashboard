<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('operational_risk_acknowledgements')) {
            return;
        }

        Schema::create('operational_risk_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->string('risk_key', 191);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('acknowledged_at');
            $table->timestamps();

            $table->unique('risk_key');
            $table->index('acknowledged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_risk_acknowledgements');
    }
};
