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
        Schema::create('service_category_rates', function (Blueprint $table) {
            $table->id();
            $table->integer('service_type_id');
            $table->integer('vehicle_category_id');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['service_type_id', 'vehicle_category_id'], 'service_category_rates_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_category_rates');
    }
};
