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
        Schema::create('myg_00_security_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('EmployeeID',22)->nullable();
            $table->string('UserToken',256)->nullable();
            $table->string('user_id',22)->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('myg_00_security_tokens');
    }
};
