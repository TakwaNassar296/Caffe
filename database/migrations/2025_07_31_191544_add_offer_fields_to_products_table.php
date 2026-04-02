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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_offer')->default(false);
            $table->boolean('is_offer_percentage')->default(true);
            $table->decimal('discount_rate', 8, 2)->nullable();
            $table->decimal('price_after_discount', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_offer', 'is_offer_percentage', 'discount_rate', 'price_after_discount']);
        });
    }
};
