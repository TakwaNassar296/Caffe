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
        Schema::table('product_material_items', function (Blueprint $table) {
            // First drop the foreign key constraint (if it exists)
            $table->dropForeign(['product_option_id']);
            // Then drop the column
            $table->dropColumn('product_option_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_material_items', function (Blueprint $table) {
            $table->foreignId('product_option_id')->nullable()->constrained('product_options')->nullOnDelete();
        });
    }
};
