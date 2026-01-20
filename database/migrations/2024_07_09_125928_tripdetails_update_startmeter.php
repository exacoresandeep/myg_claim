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
        DB::statement("ALTER TABLE `myg_09_trip_claim_details` CHANGE `StartMeter` `StartMeter` INT(11) NULL, CHANGE `EndMeter` `EndMeter` INT(11) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `myg_09_trip_claim_details` CHANGE `StartMeter` `StartMeter` INT(11) NOT NULL, CHANGE `EndMeter` `EndMeter` INT(11) NOT NULL");
    }
};
