<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('myg_12_advancelist', function (Blueprint $table) {

            // Add the new TransactionID column
            $table->string('TransactionID', 255)->nullable()->after('Status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('myg_12_advancelist', function (Blueprint $table) {
            $table->dropColumn('TransactionID');
        });
    }
};
