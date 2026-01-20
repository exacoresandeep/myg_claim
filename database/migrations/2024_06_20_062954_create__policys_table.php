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
        Schema::create('myg_06_policies', function (Blueprint $table) {
            $table->integer('PolicyID')->primary();
            $table->integer('SubCategoryID')->unique();
            $table->integer('GradeID')->unique();
            $table->string('GradeClass',255)->nullable();
            $table->enum('GradeType', [0, 1, 2]);
            $table->integer('GradeAmount')->nullable();
            $table->enum('Approver', ['NA', 'SuperAdmin', 'Admin','Finance','CMDApprover']);
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
        Schema::dropIfExists('myg_06_policies');
    }
};
