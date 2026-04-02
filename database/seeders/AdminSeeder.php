<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        // Create the admin user
        Admin::updateOrCreate([
            'email' => 'admin@coffee.com' 
        ], [
            'name' => 'admin',
            'email' => 'admin@coffee.com',
            'password' => Hash::make('123123'), 
            'role' => 'super_admin', 
            'super_admin' => true,
        ]);
    }
}
