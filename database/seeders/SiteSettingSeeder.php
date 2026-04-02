<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SiteSetting::create([
            // 'title' => 'Site Title',
            // 'description' => 'Site Description',
            // 'image' => 'site-settings/image.jpg',
            'app_link_google_play' => 'https://play.google.com/store/',
            'app_link_app_store' => 'https://www.apple.com/eg/app-store/',
        ]);
    }
}
