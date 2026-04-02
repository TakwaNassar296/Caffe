<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
       Coupon::firstOrCreate([
        'code' => '2323', 
        ],
         [
            'name' => 'Discount 23',
            'type' => 'fixed',
            'value' => 20,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 100,
            'used' => 0,
            'is_active' => true,
        ]);
    }
}
