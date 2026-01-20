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
        Schema::create('myg_09_trip_claim_details', function (Blueprint $table) {
            $table->string('TripClaimDetailID',16)->primary();
            $table->string('TripClaimID',16)->unique();
            $table->integer('PolicyID')->unique();
            $table->dateTime('FromDate')->nullable();
            $table->dateTime('ToDate')->nullable();
            $table->string('TripFrom',256)->nullable(); 
            $table->string('TripTo',256)->nullable();
            $table->dateTime('DocumentDate')->nullable();
            $table->integer('Qty')->nullable();
            $table->float('UnitAmount')->nullable();
            $table->integer('NoOfPersons')->nullable();
            $table->string('FileUrl',256)->nullable(); 
            $table->string('Remarks',500)->nullable(); 
            $table->enum('NotificationFlg', [0,1]);
            $table->tinyInteger('RejectionCount')->nullable();
            $table->string('ApproverID',22)->nullable(); 
            $table->enum('Status', ['Pending','Recieved','InProcess','Completed','Rejected','SpecialApprove']);
            $table->string('user_id',22)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('myg_09_trip_claim_details');
    }
};
