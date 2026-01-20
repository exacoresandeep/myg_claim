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
        Schema::create('myg_08_trip_claim', function (Blueprint $table) {
            $table->integer('TripClaimID')->primary();
            $table->integer('TripTypeID')->unique();
            $table->string('ApproverID',16)->nullable();
            $table->string('TripPurpose',500)->nullable();
            $table->string('VisitBranchID',16)->nullable();
            $table->float('AdvanceAmount')->nullable();
            $table->dateTime('ApprovalDate')->nullable();
            $table->integer('RejectionCount')->nullable();
            $table->enum('NotificationFlg', [0, 1]);
            $table->enum('Status', ['Pending','Recieved','InProcess','Completed','Rejected']);
            $table->string('user_id',22)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('myg_08_trip_claim');
    }
};
