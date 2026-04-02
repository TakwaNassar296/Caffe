<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::create([
            'name' => 'Electronics',
            'is_active' => true,
            'image' => 'categories/electronics.jpg',
        ]);

        Category::create([
            'name' => 'Books',
            'is_active' => true,
             'image' => 'categories/electronics.jpg',
        ]);

        Category::create([
            'name' => 'Clothing',
            'is_active' => false,
             'image' => 'categories/electronics.jpg',
        ]);
    }
}
