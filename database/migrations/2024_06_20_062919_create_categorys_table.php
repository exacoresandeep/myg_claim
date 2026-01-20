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
        Schema::create('myg_03_categories', function (Blueprint $table) {
            $table->integer('CategoryID')->primary();
            $table->string('CategoryName',255)->nullable();
            $table->enum('TripFrom', [0, 1]);
            $table->enum('TripTo', [0, 1]);
            $table->enum('FromDate', [0, 1]);
            $table->enum('ToDate', [0, 1]);
            $table->enum('DocumentDate', [0, 1]);
            $table->enum('Status', [0, 1, 2]);
            $table->string('user_id',22)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('myg_03_categories');
    }
};
