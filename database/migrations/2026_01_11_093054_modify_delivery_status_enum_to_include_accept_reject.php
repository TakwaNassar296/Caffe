<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify enum to include 'accept' and 'reject'
        DB::statement("ALTER TABLE request_materials MODIFY COLUMN delivery_status ENUM('pending', 'delivered', 'not_delivered', 'accept', 'reject') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE request_materials MODIFY COLUMN delivery_status ENUM('pending', 'delivered', 'not_delivered') DEFAULT 'pending'");
    }
};
