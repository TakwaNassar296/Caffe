<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        $types = [
            ['name' => 'Car', 'icon' => 'car.png'],
            ['name' => 'Motorcycle', 'icon' => 'motorcycle.png'],
            ['name' => 'Van', 'icon' => 'van.png'],
            ['name' => 'Small Truck', 'icon' => 'small_truck.png'],
            ['name' => 'Other', 'icon' => 'other.png'],
        ];

       VehicleType::insert($types);
    }
}
