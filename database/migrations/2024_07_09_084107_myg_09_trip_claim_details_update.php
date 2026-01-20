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
        if (!Schema::hasColumn('myg_03_categories', 'StartMeter')) {
            DB::statement("ALTER TABLE `myg_03_categories` ADD `StartMeter` ENUM('0','1') NOT NULL AFTER `DocumentDate`");
       
        }
        
        if (!Schema::hasColumn('myg_03_categories', 'EndMeter')) {
            DB::statement("ALTER TABLE `myg_03_categories` ADD `EndMeter` ENUM('0','1') NOT NULL AFTER `StartMeter`");
       
        }
        if (!Schema::hasColumn('myg_09_trip_claim_details', 'StartMeter')) {
            DB::statement("ALTER TABLE `myg_09_trip_claim_details` ADD `StartMeter` INT(11) NOT NULL AFTER `DocumentDate`");
        }
        
        if (!Schema::hasColumn('myg_09_trip_claim_details', 'EndMeter')) {
            DB::statement("ALTER TABLE `myg_09_trip_claim_details`  ADD `EndMeter` INT(11) NOT NULL AFTER `StartMeter`");
        }
        
    }     

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `myg_03_categories` DROP `StartMeter`, DROP `EndMeter`");
        DB::statement("ALTER TABLE `myg_09_trip_claim_details` DROP `StartMeter`, DROP `EndMeter`;");
    }
};
