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
        Schema::create('branch_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->time('peak_start_time')->nullable();
            $table->time('peak_end_time')->nullable();
            $table->boolean('enable_peak_pricing')->default(false);
            $table->string('order_prefix')->nullable()->default('');
            $table->integer('starting_number')->default(1);
            $table->integer('max_orders_per_hour')->nullable();
            $table->boolean('auto_print_orders')->default(false);
            $table->string('printer_name')->nullable();
            $table->string('receipt_format')->nullable()->default('Standard (80mm)');
            $table->boolean('print_kitchen_copy')->default(false);
            $table->boolean('print_customer_copy')->default(false);
            $table->boolean('order_sound_alert')->default(false);
            $table->boolean('mobile_notifications')->default(false);
            $table->boolean('email_notifications')->default(false);
            $table->boolean('low_stock_alerts')->default(false);
            $table->boolean('auto_deduction')->default(false);
            $table->integer('minimum_stock_alert_level')->default(10);
            $table->boolean('auto_ordering')->default(false);
            $table->boolean('enable_online_orders')->default(false);
            $table->integer('preparation_time')->default(15); // in minutes
            $table->boolean('auto_accept_orders')->default(false);
            $table->string('delivery_integration')->nullable()->default('None'); 
            $table->timestamps();
            $table->unique('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_settings');
    }
};
