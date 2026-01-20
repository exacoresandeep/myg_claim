<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('myg_08_trip_claim', function (Blueprint $table) {
            $table->string('TransactionID')->nullable(); // Adjust the position with 'after' as needed$table->string('TransactionID', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('myg_08_trip_claim', function (Blueprint $table) {
            $table->dropColumn('TransactionID');
        });
    }
};
