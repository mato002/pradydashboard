<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->string('archive_path')->nullable()->after('storage_disk');
            $table->string('archive_filename')->nullable()->after('archive_path');
            $table->string('checksum', 128)->nullable()->after('archive_filename');
        });

        Schema::create('backup_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_id')->constrained('backups')->cascadeOnDelete();
            $table->string('action');
            $table->string('actor_label')->nullable();
            $table->unsignedBigInteger('actor_user_id')->nullable()->index();
            $table->string('result')->default('success');
            $table->json('meta')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->index(['backup_id', 'performed_at'], 'backup_actions_backup_performed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_actions');

        Schema::table('backups', function (Blueprint $table) {
            $table->dropColumn(['archive_path', 'archive_filename', 'checksum']);
        });
    }
};
