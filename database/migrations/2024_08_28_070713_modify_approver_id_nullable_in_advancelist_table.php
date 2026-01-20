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
        Schema::table('myg_12_advancelist', function (Blueprint $table) {
            $table->unsignedInteger('ApproverID')->nullable()->change(); // Make ApproverID nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('myg_12_advancelist', function (Blueprint $table) {
            $table->unsignedInteger('ApproverID')->nullable(false)->change(); // Revert ApproverID to not nullable
        });
    }
};