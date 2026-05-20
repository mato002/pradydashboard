<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->string('telemetry_mode', 16)->default('basic')->after('telemetry_source');
            $table->string('billing_status')->nullable()->after('renewal_expires_at');
            $table->decimal('load_average', 8, 2)->nullable()->after('disk_usage_percent');
            $table->decimal('ram_usage_percent', 5, 2)->nullable()->after('load_average');
            $table->unsignedSmallInteger('ssl_days_remaining')->nullable()->after('ssl_status');
            $table->unsignedInteger('account_count')->nullable()->after('backup_status');
        });

        Schema::create('server_provider_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->string('source')->nullable();
            $table->string('notice_type', 32);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('severity', 16)->default('info');
            $table->date('notice_date');
            $table->date('due_date')->nullable();
            $table->string('status', 16)->default('open');
            $table->string('source_reference')->nullable();
            $table->string('attachment_reference')->nullable();
            $table->timestamps();

            $table->index(['server_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_provider_notices');

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn([
                'telemetry_mode',
                'billing_status',
                'load_average',
                'ram_usage_percent',
                'ssl_days_remaining',
                'account_count',
            ]);
        });
    }
};
