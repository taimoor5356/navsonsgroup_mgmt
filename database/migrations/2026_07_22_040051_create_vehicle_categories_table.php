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
        Schema::create('vehicle_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('deleted_at')->nullable();
            $table->timestamps();
        });

        DB::table('vehicle_categories')->insert([
            ['name' => 'Mini', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hatchback', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sedan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SUV', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Van/Pickup', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_categories');
    }
};
