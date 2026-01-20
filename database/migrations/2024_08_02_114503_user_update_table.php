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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_name');
            $table->dropColumn('email_verified_at');
            $table->integer('emp_branch')->change();
            $table->integer('emp_grade')->change();
            $table->integer('emp_baselocation')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_name', 26)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->text('emp_branch')->change();
            $table->text('emp_grade')->change();
            $table->text('emp_baselocation')->change();
        });
    }
};
