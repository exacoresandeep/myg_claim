<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('myg_07_user_management', function (Blueprint $table) {
            $table->string('EmployeeName')->after('EmployeeID');

            // Modify the Role enum
            DB::statement("ALTER TABLE myg_07_user_management MODIFY COLUMN Role ENUM('SuperAdmin', 'Admin', 'Finance', 'CMDApprove', 'Others') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('myg_07_user_management', function (Blueprint $table) {
            $table->dropColumn('EmployeeName');

            // Revert the Role enum modification
            DB::statement("ALTER TABLE myg_07_user_management MODIFY COLUMN Role ENUM('SuperAdmin', 'Admin', 'Finance', 'CMDApprove') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
        });
    }
};
