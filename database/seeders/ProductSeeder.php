<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Coupon;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
      
        $category = Category::first() ?? Category::factory()->create();
        $coupon = Coupon::first() ?? Coupon::factory()->create(['name' => 'Default Coupon', 'value' => 10]);

        Product::create([
            'name' => 'Sample Product',
            'points' => 50,
            'stars' => 4,
            'total_sales' => 100,
            'title' => 'Best Seller Product',
            'description' => 'This is a demo product created by seeder.',
            'price' => 99.99,
            'coupon_id' => $coupon->id,
            'category_id' => $category->id,
            'rating' => 4.5,
            'total_rating' => 200,
            'amount' => 10,
            'remaining_quantity' => 8,
            'delivery_time' => '2 days',
            'image' => 'products/sample.jpg',
            'is_active' => true,
            // 'options' => [
            //     [
            //         'name' => 'Color',
            //         'values' => [
            //             ['type' => 'Red', 'price' => 0],
            //             ['type' => 'Blue', 'price' => 5],
            //         ],
            //     ],
            //     [
            //         'name' => 'Size',
            //         'values' => [
            //             ['type' => 'S', 'price' => 0],
            //             ['type' => 'L', 'price' => 10],
            //         ],
            //     ],
            // ],
        ]);
    }
}
