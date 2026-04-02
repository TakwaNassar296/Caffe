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
        Schema::table('branch_materials', function (Blueprint $table) {
            $table->decimal('current_quantity', 10, 2)->nullable();
        });

    

        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('current_quantity_material', 10, 2)->nullable();
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_materials', function (Blueprint $table) {
            $table->dropColumn('current_quantity');
        });

    
    }
};
