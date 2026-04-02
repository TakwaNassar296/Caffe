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
        if (Schema::hasTable('cities')) {
            Schema::table('cities', function (Blueprint $table) {
                if (!Schema::hasColumn('cities', 'code')) {
                    $table->string('code')->nullable()->after('name');
                }
                if (!Schema::hasColumn('cities', 'governorate_id')) {
                    $table->foreignId('governorate_id')->nullable()->constrained('governorates')->onDelete('cascade')->after('code');
                }
            });
        } else {
            Schema::create('cities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->foreignId('governorate_id')->constrained('governorates')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cities')) {
            Schema::table('cities', function (Blueprint $table) {
                if (Schema::hasColumn('cities', 'governorate_id')) {
                    $table->dropForeign(['governorate_id']);
                    $table->dropColumn('governorate_id');
                }
                if (Schema::hasColumn('cities', 'code')) {
                    $table->dropColumn('code');
                }
            });
        } else {
            Schema::dropIfExists('cities');
        }
    }
};
