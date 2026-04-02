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
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->string('phone_number')->unique()->after('last_name');
            $table->string('image')->nullable()->after('phone_number');
            $table->timestamp('account_verified_at')->nullable();
            $table->integer('total_points')->default(0)->after('image');
            $table->integer('total_stars')->default(0)->after('total_points');
            $table->string('fcm_token')->nullable();
            $table->dropColumn(['email', 'name']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
