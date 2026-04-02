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
        Schema::table('request_materials', function (Blueprint $table) {
            $table->enum('delivery_status', ['pending', 'delivered', 'not_delivered','accept','reject'])->default('pending')->after('status');
            $table->text('delivery_feedback')->nullable()->after('delivery_status');
            $table->timestamp('delivery_confirmed_at')->nullable()->after('delivery_feedback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_materials', function (Blueprint $table) {
            $table->dropColumn(['delivery_status', 'delivery_feedback', 'delivery_confirmed_at']);
        });
    }
};
