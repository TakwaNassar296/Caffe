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
            $table->integer('max_limit')->default(0)->after('current_quantity');
            $table->integer('min_limit')->default(0)->after('max_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_materials', function (Blueprint $table) {
            $table->dropColumn('max_limit');
            $table->dropColumn('min_limit');
        });
    }
};
