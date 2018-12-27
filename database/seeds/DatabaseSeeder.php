<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\User::class, 5)->create();
        factory(App\Models\NewsItem::class, 5)->create();
        factory(App\Models\Page::class, 5)->create();
        factory(App\Models\SliderImage::class, 5)->create();
    }
}
