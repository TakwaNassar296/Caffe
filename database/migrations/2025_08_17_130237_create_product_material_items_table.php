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
        Schema::create('product_material_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('products_material_id')->constrained('products_materials')->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete('cascade'); 
            $table->decimal('quantity_used', 10, 2)->nullable(); 
            $table->enum('unit', ['ml', 'l', 'g', 'kg', 'pcs']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_material_items');
    }
};
