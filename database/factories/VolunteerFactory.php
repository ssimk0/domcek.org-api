<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Volunteer;
use App\Models\VolunteerType;
use Illuminate\Database\Eloquent\Factories\Factory;

class VolunteerFactory extends Factory
{
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
                return 1;
            },
            'user_id' => Profile::factory()->createOne(),
            'volunteer_type_id' => VolunteerType::factory()->createOne(),
            'is_leader' => 0,
        ];
    }
}
