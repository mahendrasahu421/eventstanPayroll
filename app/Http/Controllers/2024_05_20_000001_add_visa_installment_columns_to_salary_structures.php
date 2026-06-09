<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            if (!Schema::hasColumn('salary_structures', 'visa_total_installments')) {
                $table->integer('visa_total_installments')->default(1)->after('visa_deduction');
            }
            if (!Schema::hasColumn('salary_structures', 'visa_total_amount')) {
                $table->decimal('visa_total_amount', 15, 2)->default(0)->after('visa_deduction');
            }
        });
    }

    public function down()
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            $table->dropColumn(['visa_total_installments', 'visa_total_amount']);
        });
    }
};