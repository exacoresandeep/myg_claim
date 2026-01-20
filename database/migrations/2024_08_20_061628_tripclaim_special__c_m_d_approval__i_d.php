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
            // $table->string('CMDApproverID', 16)->nullable(); 
            // $table->string('SpecialApproverID', 16)->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('myg_08_trip_claim', function (Blueprint $table) {
            // Add an ENUM column for Status
            $table->dropColumn('CMDApproverID');
            $table->dropColumn('SpecialApproverID');
        });
    }
};
