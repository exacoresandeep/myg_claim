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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->text('emp_id')->nullable();
            $table->string('emp_name')->nullable();
            $table->string('user_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->text('emp_phonenumber')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('emp_department')->nullable();
            $table->text('emp_branch')->nullable();
            $table->text('emp_baselocation')->nullable();
            $table->text('emp_designation')->nullable();
            $table->text('emp_grade')->nullable();
            $table->text('reporting_person')->nullable();
            $table->integer('reporting_person_empid')->nullable();
            $table->text('emp_role')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
