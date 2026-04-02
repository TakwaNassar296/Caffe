<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        $ranks = [
            [
                'name' => 'Beginner',
                'min_stars' => 0,
                'max_stars' => 999,
                'points_increment' => 100,
                'stars_increment' => 1,
                'description' => 'New to the platform.',
                'badge_color' => '#A0AEC0',
            ],
            [
                'name' => 'Intermediate',
                'min_stars' => 1000,
                'max_stars' => 4999,
                'points_increment' => 500,
                'stars_increment' => 2,
                'description' => 'Gaining experience.',
                'badge_color' => '#63B3ED',
            ],
            [
                'name' => 'Advanced',
                'min_stars' => 5000,
                'max_stars' => 9999,
                'points_increment' => 1000,
                'stars_increment' => 3,
                'description' => 'Highly active member.',
                'badge_color' => '#48BB78',
            ],
            [
                'name' => 'Elite',
                'min_stars' => 10000,
                'max_stars' => 19999,
                'points_increment' => 2000,
                'stars_increment' => 4,
                'description' => 'Top of the game.',
                'badge_color' => '#D69E2E',
            ],
            [
                'name' => 'Legend',
                'min_stars' => 20000,
                'max_stars' => 999999,
                'points_increment' => 5000,
                'stars_increment' => 5,
                'description' => 'Legendary performance.',
                'badge_color' => '#ED8936',
            ],
        ];

        foreach ($ranks as $rank) {
            Rank::create($rank);
        }
    }
}
