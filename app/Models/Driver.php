<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Driver extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [

        'profile_image',
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'id_number',
        'date_of_birth',
        'nationality',
        'vehicle_registration_document',
        'vehicle_insurance_document',
        'type_of_vehicle',
        'vehicle_model',
        'year_of_manufacture',
        'license_plate_number',
        'driving_license_photo',
        'license_issue_date',
        'license_expiry_date',
        'previous_experience',
        'experience',
        'city',
        'district_area',
        'have_gps',
        'notes',
        'reject_reason',
        'status',
        'fcm_token',
        'password',
        'account_verified_at'
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_driver');
    }

    // public function orders()
    // {
    //     return $this->belongsToMany(Order::class, 'driver_orders')
    //         ->withPivot('branch_id');
    // }

    public function orders()
{
    return $this->hasMany(Order::class, 'driver_id');
}

    public function vehicleType()
{
    return $this->belongsTo(VehicleType::class);
}


    public function driverOrders()
    {
        return $this->hasMany(DriverOrder::class);
    }

    public function getGenerateCodeOtpAttribute(): string
    {
        return rand(1111, 9999);
    }
}


