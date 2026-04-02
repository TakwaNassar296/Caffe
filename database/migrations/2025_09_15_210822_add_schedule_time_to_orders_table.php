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
        Schema::table('orders', function (Blueprint $table) {
               $table->timestamp('schedule_time')->nullable()->after('status');
               $table->decimal('finance', 10, 2)->nullable()->after('schedule_time');
               $table->boolean('booked_by_driver')->default(false)->after('finance');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('schedule_time');
            $table->dropColumn('finance');
            $table->dropColumn('booked_by_driver');
        });
    }
};
