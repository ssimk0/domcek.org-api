<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Profile;
use App\Models\Volunteer;
use App\Models\VolunteerType;
use Illuminate\Database\Eloquent\Factories\Factory;

class VolunteerFactory extends Factory {

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Volunteer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'event_id' => function () {
                return Event::factory()->createOne()->id;
            },
            'user_id' => function () {
                return Profile::factory()->createOne()->user->id;
            },
            'volunteer_type_id' => function () {
                return VolunteerType::factory()->createOne()->id;
            },
            'is_leader' => 0,
        ];
    }
}
