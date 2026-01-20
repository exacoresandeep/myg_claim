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
        DB::statement("ALTER TABLE `myg_09_trip_claim_details` ADD `ApproverRemarks` VARCHAR(500) AFTER `Remarks`");
        DB::statement("ALTER TABLE `myg_08_trip_claim` ADD `FinanceRemarks` VARCHAR(500) AFTER `Status`");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `myg_09_trip_claim_details` DROP COLUMN `ApproverRemarks`");
        DB::statement("ALTER TABLE `myg_08_trip_claim` DROP COLUMN `FinanceRemarks`");
    }
};
