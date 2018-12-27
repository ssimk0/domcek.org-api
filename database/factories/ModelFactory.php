<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'emails' => $faker->email,
    ];
});


$factory->define(App\Models\Page::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence,
        'body' => $faker->text,
        'image' => $faker->imageUrl(),
        'order' => $faker->randomDigit,
        'active' => 1,
    ];
});


$factory->define(App\Models\NewsItem::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence,
        'body' => $faker->text,
        'short' => $faker->sentence,
        'image' => $faker->imageUrl(),
        'status' => \App\Constants\NewsConstant::PUBLISHED,
        'is_featured' => 0,
    ];
});

$factory->define(App\Models\SliderImage::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence,
        'text' => $faker->text,
        'image' => $faker->imageUrl(),
        'order' => $faker->randomDigit,
    ];
});
