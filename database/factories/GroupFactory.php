<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Group;
use App\Models\Participant;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Group::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'group_name' => $this->faker->randomNumber(8),
            'user_id' => Profile::factory()->createOne(),
            'event_id' => Event::factory()->createOne(),
            'participant_id' => Participant::factory()->createOne(),
        ];
    }
}
