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
        Schema::create('ranks', function (Blueprint $table) {
             $table->id();
            $table->string('name');
            $table->decimal('min_stars');
            $table->decimal('max_stars');
            $table->decimal('points_increment');
            $table->decimal('stars_increment');
            $table->longText('description' );
            $table->string('badge_color');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
