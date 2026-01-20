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
            // Adding 'Approved' and changing 'Settled' to 'Rejected'
            $table->enum('Status', ['Paid', 'Rejected', 'Pending', 'Approved'])->change();
        });
    }
    
    public function down(): void
    {
        Schema::table('myg_12_advancelist', function (Blueprint $table) {
            // Reverting back to original values 'Settled', 'Paid', and 'Pending'
            $table->enum('Status', ['Paid', 'Settled', 'Pending'])->change();
        });
    }
    
};
