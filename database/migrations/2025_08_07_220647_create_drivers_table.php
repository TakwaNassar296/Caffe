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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('profile_image')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number');
            $table->string('email');
             $table->string('password');
            $table->string('id_number');
            $table->date('date_of_birth');
            $table->string('nationality');


            $table->string('vehicle_registration_document')->nullable();
            $table->string('vehicle_insurance_document')->nullable();
             $table->foreignId('vehicle_type_id')->nullable()->constrained('vehicle_types');
            $table->string('vehicle_model')->nullable();
            $table->string('year_of_manufacture')->nullable();
            $table->string('license_plate_number')->nullable();

            $table->string('driving_license_photo')->nullable();
            $table->date('license_issue_date')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->boolean('previous_experience')->nullable();
            $table->text('experience')->nullable();

            $table->string('city')->nullable();
            $table->string('district_area')->nullable();
            $table->boolean('have_gps')->nullable();
            $table->text('notes')->nullable();

            $table->text('reject_reason')->nullable();
            $table->enum('status', ['accepted', 'rejected', 'pending','in_complete'])->nullable()->default('in_complete');
            $table->string('fcm_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
