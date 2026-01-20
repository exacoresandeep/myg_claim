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
        // Modify the column type using raw SQL
        DB::statement("ALTER TABLE `users` MODIFY `reporting_person_empid` TEXT");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the column type back to int
        DB::statement("ALTER TABLE `users` MODIFY `reporting_person_empid` INT(11)");
    }
};
