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
        Schema::create('myg_10_persons_details', function (Blueprint $table) {
            $table->string('PersonDetailsID',16)->primary();
            $table->string('TripClaimDetailID',16)->unique();
            $table->string('Grade',22)->nullable();
            $table->string('EmployeeID',16)->unique();
            $table->enum('ClaimOwner', [0, 1]);
            $table->string('user_id',22)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('myg_10_persons_details');
    }
};
