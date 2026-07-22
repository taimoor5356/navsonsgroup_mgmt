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
        Schema::table('user_vehicles', function (Blueprint $table) {
            $table->string('user_pic')->nullable()->after('model_year');
            $table->string('cnic_pic')->nullable()->after('user_pic');
            $table->string('cnic_number')->nullable()->after('cnic_pic');
            $table->string('vehicle_pic')->nullable()->after('cnic_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_vehicles', function (Blueprint $table) {
            $table->dropColumn(['user_pic', 'cnic_pic', 'cnic_number', 'vehicle_pic']);
        });
    }
};
