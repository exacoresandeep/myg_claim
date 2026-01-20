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
        Schema::table('myg_01_triptypes', function (Blueprint $table) {
            // Drop the existing primary key constraint
            // $table->dropPrimary('PRIMARY');

            // Change the TripTypeID column to be auto-incrementing
            //$table->increments('TripTypeID')->first();

            // Add the primary key constraint back
            // $table->primary('TripTypeID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('myg_01_triptypes', function (Blueprint $table) {
            // Drop the primary key constraint
            $table->dropPrimary(['TripTypeID']);

            // Revert the TripTypeID column change
            $table->unsignedBigInteger('TripTypeID')->change();

            // Optionally add back the original primary key constraint
            // Note: You may need to define the original primary key constraint accurately
        });
    }
};
