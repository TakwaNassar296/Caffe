<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update employee_id foreign key from employees to admins table
     */
    public function up(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
             $table->dropForeign(['employee_id']);
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
             $table->foreign('employee_id')
                ->references('id')
                ->on('admins')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
             $table->dropForeign(['employee_id']);
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
             $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');
        });
    }
};
