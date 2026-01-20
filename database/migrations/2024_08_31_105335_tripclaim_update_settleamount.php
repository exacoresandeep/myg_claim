<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('myg_08_trip_claim', function (Blueprint $table) {
            $table->decimal('SettleAmount', 8, 2)->nullable(); // Adjust the position with 'after' as needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('myg_08_trip_claim', function (Blueprint $table) {
            $table->dropColumn('SettleAmount');
        });
    }
};
