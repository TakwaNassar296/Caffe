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
        Schema::create('products_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->nullable()->constrained('product_options')->nullOnDelete('cascade'); 

            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_materials');
    }
};
