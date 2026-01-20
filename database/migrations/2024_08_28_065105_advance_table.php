<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('myg_12_advancelist', function (Blueprint $table) {
            $table->string('id', 22)->primary(); // Primary key with varchar length of 22
            $table->unsignedInteger('user_id'); // Integer type user_id
            $table->float('Amount', 8, 2); // Float type with 2 decimal points
            $table->date('RequestDate'); // Date type
            $table->unsignedInteger('TripTypeID'); // Integer type
            $table->string('TripPurpose'); // String type
            $table->unsignedInteger('BranchID'); // Integer type
            $table->text('Remarks')->nullable(); // Text type for remarks, nullable
            $table->enum('Status', ['Paid', 'Settled', 'Pending']); // Enum type
            $table->unsignedInteger('ApproverID'); // Integer type for ApproverID
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('myg_12_advancelist');
    }
};
