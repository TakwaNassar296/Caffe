<?php

namespace Database\Seeders;

use App\Models\Rank;
use App\Models\User;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $this->call([
            // RoleSeeder::class,
            // AdminSeeder::class,
            // RankSeeder::class,
            // CouponSeeder::class,
            // CategorySeeder::class,
            // VehicleTypeSeeder::class,
            // ProductSeeder::class,
            //  KeywordSeeder::class,
            // AdvertisementSeeder::class,
            // PageSeeder::class,
            SiteSettingSeeder::class,
       ]);
   
       
    }
}
