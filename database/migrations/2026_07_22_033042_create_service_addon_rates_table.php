<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_addon_rates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });

        DB::table('service_addon_rates')->insert([
            ['key' => 'diesel', 'label' => 'Diesel', 'price' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'polish', 'label' => 'Polish', 'price' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_addon_rates');
    }
};
