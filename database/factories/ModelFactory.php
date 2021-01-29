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

$factory->define(\App\Models\Page::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence,
        'body' => $faker->text,
        'image' => $faker->imageUrl(),
        'order' => $faker->randomDigit,
        'active' => 1,
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

$factory->define(App\Models\TransportType::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
    ];
});

$factory->define(App\Models\EventPrice::class, function (Faker\Generator $faker) {
    return [
       'event_id' => 1,
       'need_pay' => $faker->randomDigit,
       'deposit' => $faker->randomDigit,
       'description' => $faker->sentence,
   ];
});


$factory->define(App\Models\Volunteer::class, function (Faker\Generator $faker) {
    return [

    ];
});

$factory->define(App\Models\Payment::class, function (Faker\Generator $faker) {
    return [
        'payment_number' => $faker->randomNumber(8),
        'user_id' => function () {
            return factory(App\Models\Profile::class)->create()->user_id;
        },
        'bus' => $faker->randomNumber(1),
        'deposit' => $faker->randomNumber(1),
        'on_registration' => $faker->randomNumber(1),
        'need_pay' => $faker->randomNumber(2),
    ];
});

$factory->define(App\Models\Group::class, function (Faker\Generator $faker) {
    return [
        'group_name' => $faker->randomNumber(8),
        'user_id' => function () {
            return factory(App\Models\Profile::class)->create()->user_id;
        },
        'event_id' => function () {
            return factory(App\Models\Event::class)->create()->id;
        },
        'participant_id' => function () {
            return factory(App\Models\Participant::class)->create()->id;
        },
    ];
});
