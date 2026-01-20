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
        DB::statement("ALTER TABLE `myg_08_trip_claim` CHANGE `NotificationFlg` `NotificationFlg` ENUM('0', '1', '2', '3','4','5','6','7')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `myg_08_trip_claim` CHANGE `NotificationFlg` `NotificationFlg` ENUM('0', '1')");
    }
};
