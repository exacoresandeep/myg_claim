<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('myg_09_trip_claim_details', function (Blueprint $table) {
            $table->decimal('DeductAmount', 8, 2)->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('myg_09_trip_claim_details', function (Blueprint $table) {
            $table->dropColumn('DeductAmount');
        });
    }
};
