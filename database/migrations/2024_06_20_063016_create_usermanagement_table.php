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
        Schema::create('myg_07_user_management', function (Blueprint $table) {
            $table->integer('EmployeeID')->unique();
            $table->string('ApproverID',255)->nullable();
            $table->enum('Role', ['SuperAdmin', 'Admin','Finance','CMDApprover']);
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
        Schema::dropIfExists('myg_07_user_management');
    }
};
