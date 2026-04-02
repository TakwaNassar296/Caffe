<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Page::create([
            'title'   => 'First Demo Post',
            'content' => 'This is the content of the first demo post.',
            'image'   => 'images/post1.jpg',
        ]);
    }
}
