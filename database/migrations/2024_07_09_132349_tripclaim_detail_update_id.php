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
        DB::statement("ALTER TABLE `myg_09_trip_claim_details` CHANGE `NotificationFlg` `NotificationFlg` ENUM('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
        DB::statement("ALTER TABLE `myg_09_trip_claim_details` CHANGE `TripClaimDetailID` `TripClaimDetailID` VARCHAR(22) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `myg_09_trip_claim_details` CHANGE `NotificationFlg` `NotificationFlg` ENUM('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '0'");
        DB::statement("ALTER TABLE `myg_09_trip_claim_details` CHANGE `TripClaimDetailID` `TripClaimDetailID` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    
    }
};
