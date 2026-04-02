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
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'governorate_id')) {
                $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            }
            if (!Schema::hasColumn('branches', 'city_id')) {
                $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            }
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'governorate_id')) {
                $table->dropForeign(['governorate_id']);
                $table->dropColumn('governorate_id');
            }
            if (Schema::hasColumn('branches', 'city_id')) {
                // Assuming we want to drop it if we added it, but if it existed before, this might be risky. 
                // However, standard down() reverses up(). If up() didn't add it, down() shouldn't drop it ideally.
                // But migrations are simple. I'll include drop logic but wrap in check.
                // $table->dropForeign(['city_id']); // This might fail if key name differs
                // $table->dropColumn('city_id');
            }
           
        });
    }
};
