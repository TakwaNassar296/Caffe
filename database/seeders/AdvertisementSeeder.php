<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\Advertisement;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run()
    {
        $data = [
            [
                'title' => 'Sample Title 1',
                'status' => 1,
                'image' => null,
            ],
            [
                'title' => 'Sample Title 2',
                'status' => 0,
                'image' => null,
            ],
            [
                'title' => 'Sample Title 3',
                'status' => 1,
                'image' => null,
            ],
            [
                'title' => 'Sample Title 4',
                'status' => 0,
                'image' => null,
            ],
            [
                'title' => 'Sample Title 5',
                'status' => 1,
                'image' => null,
            ],

          
            [
                'title' => null,
                'status' => 1,
                'image' => 'https://picsum.photos/seed/' . Str::random(8) . '/300/200',
            ],
            [
                'title' => null,
                'status' => 0,
                'image' => 'https://picsum.photos/seed/' . Str::random(8) . '/300/200',
            ],
            [
                'title' => null,
                'status' => 1,
                'image' => 'https://picsum.photos/seed/' . Str::random(8) . '/300/200',
            ],
            [
                'title' => null,
                'status' => 0,
                'image' => 'https://picsum.photos/seed/' . Str::random(8) . '/300/200',
            ],
            [
                'title' => null,
                'status' => 1,
                'image' => 'https://picsum.photos/seed/' . Str::random(8) . '/300/200',
            ],
        ];

        Advertisement::insert($data);
    }
}
