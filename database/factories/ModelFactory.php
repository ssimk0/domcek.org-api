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
        'password' => $faker->password,
        'avatar' => $faker->imageUrl(),
        'email' => $faker->email,
    ];
});

$factory->define(App\Models\Profile::class, function (Faker\Generator $faker) {
    return [
        'user_id' => function () {
            return factory(App\User::class)->create()->id;
        },
        'first_name' => $faker->name,
        'last_name' => $faker->name,
        'city' => $faker->word,
        'birth_date' => \Carbon\Carbon::now()->subYear(18)->format('Y-m-d'),
        'phone' => $faker->phoneNumber,
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

$factory->define(App\Models\TransportType::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
    ];
});


$factory->define(App\Models\Event::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->sentence,
        'theme' => $faker->sentence,
        'need_pay' => $faker->randomDigit,
        'deposit' => $faker->randomDigit,
        'start_date' => \Carbon\Carbon::now()->addYear(1)->format('Y-m-d'),
        'end_date' => $faker->date(),
        'start_registration' => $faker->date(),
        'end_registration' => $faker->date(),
        'end_volunteer_registration' => $faker->date(),
    ];
});


$factory->define(App\Models\VolunteerType::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->sentence,
        'active' => true
    ];
});

$factory->define(App\Models\Participant::class, function (Faker\Generator $faker) {
    return [
        'note' => $faker->sentence,
        'event_id' => 1,
        'transport_in' => 'test',
        'transport_out' => 'test',
        'user_id' => function () {
            return factory(App\Models\Profile::class)->create()->user_id;
        }
    ];
});

$factory->define(App\Models\Volunteer::class, function (Faker\Generator $faker) {
    return [
        'event_id' => function () {
            return 1;
        },
        'user_id' => function () {
            return factory(App\Models\Profile::class)->create()->user_id;
        },
        'volunteer_type_id' => function () {
            return factory(App\Models\VolunteerType::class)->create()->id;
        },
        'is_leader' => 0
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
        'need_pay' => $faker->randomNumber(2)
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

