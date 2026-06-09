<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_code', 50)->nullable()->unique();
            $table->string('plate_number', 50)->unique();
            $table->string('vehicle_name', 150);
            $table->string('vehicle_type', 100)->nullable();
            $table->date('registration_expiry_date')->nullable();
            $table->date('insurance_expiry_date')->nullable();
            $table->date('permit_expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
