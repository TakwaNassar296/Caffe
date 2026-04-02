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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_id')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending','under_receipt', 'under_review', 'in_preparation', 'prepared','shipped', 'arrived' , 'canceled' , 'completed' , 'scheduled' , 'paid'])->default('pending');
            // تم التجهيز و تم الشحن وتم الوصول وتم الغاء, قيد الاستلام و قيد المراجعه و قيد التجهيز
            $table->text('cancelled_reason')->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->decimal('discount', 10, 2)->default(0);
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_location_id')->nullable()->constrained('user_locations')->cascadeOnDelete();
            $table->foreignId('user_payment_id')->nullable()->constrained('user_payments');
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->enum('type',['delivery','pick_up']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
