<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'super_admin',
            'guard_name' => 'admin',
        ]);

         Role::create([
            'name' => 'branch_manger',
            'guard_name' => 'admin',
        ]);

         Role::create([
            'name' => 'employee',
            'guard_name' => 'admin',
        ]);
    }
}
