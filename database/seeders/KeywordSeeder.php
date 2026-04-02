<?php

namespace Database\Seeders;

use App\Models\Keyword;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KeywordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        $keywords = [
            ['name' => 'Good service', 'status' => 1],
            ['name' => 'Very fast delivery', 'status' => 1],
            ['name' => 'High quality product', 'status' => 1],
            ['name' => 'Excellent packaging', 'status' => 1],
            ['name' => 'Friendly driver', 'status' => 1],
            ['name' => 'Late delivery', 'status' => 0],
            ['name' => 'Bad packaging', 'status' => 0],
            ['name' => 'Poor quality', 'status' => 0],
            ['name' => 'Unhelpful support', 'status' => 0],
            ['name' => 'Not as described', 'status' => 0],
        ];

        foreach ($keywords as $keyword) {
            Keyword::create($keyword);
        }
    }
}
