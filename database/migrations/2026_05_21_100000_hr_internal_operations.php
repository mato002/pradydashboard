<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 40)->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('manager_staff_id')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('hr_department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $table->string('staff_number', 40)->unique();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('job_title')->nullable();
            $table->string('employment_type', 30)->default('full_time');
            $table->string('status', 20)->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('monthly_salary', 14, 2)->nullable();
            $table->string('currency', 3)->default('KES');
            $table->string('emergency_contact')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'hr_department_id']);
        });

        Schema::table('hr_departments', function (Blueprint $table) {
            $table->foreign('manager_staff_id')
                ->references('id')
                ->on('staff_profiles')
                ->nullOnDelete();
        });

        Schema::create('internal_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('open');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('staff_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained('staff_profiles')->cascadeOnDelete();
            $table->string('assignable_type');
            $table->unsignedBigInteger('assignable_id');
            $table->string('role_on_assignment')->nullable();
            $table->text('responsibility_notes')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['assignable_type', 'assignable_id', 'status'], 'staff_assign_assignable_idx');
            $table->index(['staff_profile_id', 'status']);
        });

        Schema::create('staff_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained('staff_profiles')->cascadeOnDelete();
            $table->string('title');
            $table->string('document_type', 40);
            $table->string('file_path');
            $table->date('signed_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['staff_profile_id', 'document_type']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_documents');
        Schema::dropIfExists('staff_assignments');
        Schema::dropIfExists('internal_tasks');

        Schema::table('hr_departments', function (Blueprint $table) {
            if (Schema::hasColumn('hr_departments', 'manager_staff_id')) {
                $table->dropForeign(['manager_staff_id']);
            }
        });

        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('hr_departments');
    }
};
