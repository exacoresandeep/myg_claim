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
        DB::statement("ALTER TABLE `myg_08_trip_claim` CHANGE `TripClaimID` `TripClaimID` VARCHAR(22) NOT NULL;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `myg_08_trip_claim` CHANGE `TripClaimID` `TripClaimID` INT(11) NOT NULL;");
    }
};
