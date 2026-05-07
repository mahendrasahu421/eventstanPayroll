<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('payroll_month'); // format: YYYY-MM
            $table->integer('working_days')->default(0);
            $table->integer('present_days')->default(0);
            $table->integer('leave_days')->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);

            // Earnings
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('housing_allowance', 12, 2)->default(0);
            $table->decimal('transport_allowance', 12, 2)->default(0);
            $table->decimal('medical_allowance', 12, 2)->default(0);
            $table->decimal('other_allowance', 12, 2)->default(0);
            $table->decimal('overtime_amount', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0);

            // Deductions
            $table->decimal('food_deduction', 10, 2)->default(0);
            $table->decimal('visa_deduction', 10, 2)->default(0);
            $table->decimal('insurance_deduction', 10, 2)->default(0);
            $table->decimal('advance_deduction', 10, 2)->default(0);
            $table->decimal('other_deduction', 10, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);

            // Net & WPS
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->decimal('wps_first_transfer', 12, 2)->default(0);
            $table->decimal('wps_second_transfer', 12, 2)->default(0);

            $table->enum('status', ['draft', 'processed', 'approved', 'paid'])->default('draft');
            $table->text('remarks')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'payroll_month']);
        });

        Schema::create('advance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->date('advance_date');
            $table->text('reason')->nullable();
            $table->decimal('installment_amount', 10, 2)->default(0);
            $table->integer('total_installments')->default(1);
            $table->integer('paid_installments')->default(0);
            $table->decimal('recovered_amount', 12, 2)->default(0);
            $table->decimal('pending_amount', 12, 2)->default(0);
            $table->enum('status', ['active', 'fully_recovered', 'cancelled'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('advance_recoveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advance_payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('payroll_record_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->string('recovery_month'); // YYYY-MM
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_recoveries');
        Schema::dropIfExists('advance_payments');
        Schema::dropIfExists('payroll_records');
    }
};
