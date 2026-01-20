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
        Schema::create('myg_04_subcategories', function (Blueprint $table) {
            $table->integer('SubCategoryID')->primary();
            $table->integer('UomID')->unique();
            $table->integer('CategoryID')->unique();
            $table->string('SubCategoryName',255)->nullable();
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
        Schema::dropIfExists('myg_04_subcategories');
    }
};
