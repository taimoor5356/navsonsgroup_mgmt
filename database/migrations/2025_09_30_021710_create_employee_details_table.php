<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('joining_date')->nullable();
            $table->string('contract_length')->nullable();
            $table->tinyInteger('permanent')->default(0);
            $table->double('basic_salary', 8,2)->default(0.00);
            $table->double('allowance', 8,2)->default(0.00);
            $table->double('bonus', 8,2)->default(0.00);
            $table->string('bonus_reason')->nullable();
            $table->integer('increment_percent')->default(0);
            $table->string('increment_period')->nullable();
            $table->tinyInteger('performance')->default(0);
            $table->tinyInteger('skill_level')->default(0);
            $table->string('resignation_date')->nullable();
            $table->string('resignation_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_details');
    }
};
