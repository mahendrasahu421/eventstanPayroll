<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('nationality')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('designation_id')->nullable()->constrained()->onDelete('set null');
            $table->date('joining_date');
            $table->date('confirmation_date')->nullable();
            $table->date('resignation_date')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'probation'])->default('full_time');
            $table->enum('status', ['active', 'inactive', 'terminated', 'on_leave'])->default('active');
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('wps_personal_number')->nullable();
            $table->json('custom_fields')->nullable();

            // Insurance metadata saved as real employee columns (as requested)
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->string('insurance_card_number')->nullable();
            $table->date('insurance_start_date')->nullable();
            $table->date('insurance_end_date')->nullable();

            $table->text('address')->nullable();
            $table->string('photo')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('housing_allowance', 12, 2)->default(0);
            $table->decimal('transport_allowance', 12, 2)->default(0);
            $table->decimal('medical_allowance', 12, 2)->default(0);
            $table->decimal('other_allowance', 12, 2)->default(0);
            $table->decimal('overtime_rate_per_hour', 10, 2)->default(0);
            $table->decimal('wps_first_transfer_amount', 12, 2)->default(0);
            $table->decimal('food_deduction', 10, 2)->default(0);
            $table->decimal('visa_deduction', 10, 2)->default(0);
            $table->decimal('insurance_deduction', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('document_type', [
                'passport', 'emirates_id', 'labour_card', 'driving_license',
                'insurance', 'visa', 'contract', 'other'
            ]);
            $table->string('document_number')->nullable();
            $table->string('file_path')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('alert_sent')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('salary_structures');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('designations');
        Schema::dropIfExists('departments');
    }
};
