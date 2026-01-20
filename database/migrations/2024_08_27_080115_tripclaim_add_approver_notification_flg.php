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
        Schema::table('myg_08_trip_claim', function (Blueprint $table) {
            // Add an ENUM column for Status
           $table->enum('AppNotificationFlg', ['0', '1','2','3','4'])->default('0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('myg_08_trip_claim', function (Blueprint $table) {
            // Remove the ENUM column
            $table->dropColumn('AppNotificationFlg');
        });
    }
};
