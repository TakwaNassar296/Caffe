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
        Schema::create('branch_material_shipments', function (Blueprint $table) {
        $table->id();
        
            $table->date('shipment_date');
        $table->text('notes')->nullable();

        $table->foreignId('branch_id')
            ->nullable()
            ->constrained('branches')
            ->onDelete('cascade');

        $table->foreignId('branch_material_id')
            ->nullable()
            ->constrained('branch_materials')
            ->onDelete('cascade');

        $table->foreignId('material_id')
            ->nullable()
            ->constrained('materials')
            ->onDelete('cascade');

        $table->foreignId('order_id')
            ->nullable()
            ->constrained('orders')
            ->onDelete('set null');

        $table->decimal('quantity', 10, 2);
        $table->string('unit', 10);
        $table->enum('status', ['sent', 'consumed'])->default('consumed');
        $table->date('transaction_date'); 
        $table->date('sent_date')->nullable(); 
        $table->string('consumer_type')->default('branch'); 
        $table->string('consumer_name')->nullable();
            $table->timestamps();
 
        $table->index(['branch_id', 'transaction_date'], 'bm_ship_branch_trx_idx');
        $table->index(['material_id', 'transaction_date'], 'bm_ship_material_trx_idx');
        $table->index(['branch_material_id', 'transaction_date'], 'bm_ship_branch_material_trx_idx');
        $table->index('status', 'bm_ship_status_idx');
        $table->index('order_id', 'bm_ship_order_idx');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_material_shipments');
    }
};
