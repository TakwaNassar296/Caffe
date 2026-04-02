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
        Schema::create('material_request_approvals', function (Blueprint $table) {
            $table->id();
             $table->foreignId('request_material_id')
                ->constrained('request_materials')
                ->cascadeOnDelete();

            $table->foreignId('admin_id')
                ->constrained('admins')
                ->cascadeOnDelete();

            $table->enum('action', ['approved', 'rejected', 'updated']);

            $table->decimal('quantity', 10, 2)->nullable();

            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_request_approvals');
    }
};
