<?php
// database/migrations/2024_01_01_000001_create_companies_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->text('company_address')->nullable();
            $table->string('logo')->nullable();
            $table->string('currency')->default('AED');
            $table->string('currency_symbol')->default('د.إ');
            $table->integer('working_days_per_month')->default(26);
            $table->decimal('overtime_rate', 10, 2)->nullable(); // Simple amount field
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
};