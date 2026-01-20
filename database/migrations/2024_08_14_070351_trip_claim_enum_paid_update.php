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
        DB::statement("ALTER TABLE `myg_08_trip_claim` CHANGE `Status` `Status` ENUM('Approved', 'Pending', 'Rejected', 'Paid')");
        DB::statement("ALTER TABLE `myg_09_trip_claim_details` CHANGE `Status` `Status` ENUM('Approved', 'Pending', 'Rejected', 'Paid')");
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `myg_08_trip_claim` CHANGE `Status` `Status` ENUM('Recieved', 'Pending', 'Rejected', 'Settled')");
        DB::statement("ALTER TABLE `myg_09_trip_claim_details` CHANGE `Status` `Status` ENUM('Recieved', 'Pending', 'Rejected', 'Settled')");
     
    }
};
