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
        DB::statement("ALTER TABLE `myg_10_persons_details` CHANGE `EmployeeID` `EmployeeID` INT(11) NOT NULL");
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `myg_10_persons_details` CHANGE `EmployeeID` `EmployeeID` VARCHAR(26) DEFAULT NULL");
       
    }
};
