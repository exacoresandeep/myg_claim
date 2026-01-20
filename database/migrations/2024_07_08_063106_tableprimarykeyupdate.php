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
        DB::statement('ALTER TABLE `myg_02_uom` CHANGE `UomID` `UomID` INT(11) NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE `myg_03_categories` CHANGE `CategoryID` `CategoryID` INT(11) NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE `myg_04_subcategories` CHANGE `SubCategoryID` `SubCategoryID` INT(11) NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE `myg_06_policies` CHANGE `PolicyID` `PolicyID` INT(11) NOT NULL AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `myg_02_uom` CHANGE `UomID` `UomID` INT(11) NOT NULL');
        DB::statement('ALTER TABLE `myg_03_categories` CHANGE `CategoryID` `CategoryID` INT(11) NOT NULL');
        DB::statement('ALTER TABLE `myg_04_subcategories` CHANGE `SubCategoryID` `SubCategoryID` INT(11) NOT NULL');
        DB::statement('ALTER TABLE `myg_06_policies` CHANGE `PolicyID` `PolicyID` INT(11) NOT NULL');
    }
};
