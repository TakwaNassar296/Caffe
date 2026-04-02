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
        Schema::create('employee_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->decimal('point_amount', 8, 2)->nullable();
            $table->json('notes')->nullable();
            
            $table->enum('type_reason', [
                'punctuality_opening_on_time',
                'extra_shift',
                'customer_compliment',
                'exceptional_performance',
                'cleanliness_hygiene',
                'other'
                ])->default('other');
        
            $table->text('other_reason')->nullable();
                
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_points');
    }
};
