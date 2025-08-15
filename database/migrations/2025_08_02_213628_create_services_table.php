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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id')->nullable();
            $table->integer('service_type_id')->nullable();
            $table->tinyInteger('diesel')->default(0);
            $table->tinyInteger('polish')->default(0);
            $table->integer('charges')->default(0);
            $table->integer('discount')->default(0);
            $table->string('discount_reason')->nullable();
            $table->integer('collected_amount')->default(0);
            $table->string('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
