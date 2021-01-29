<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Participant::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'note' => $this->faker->sentence,
            'event_id' => 1,
            'transport_in' => 'test',
            'transport_out' => 'test',
            'user_id' => function () {
                return Profile::factory()->createOne()->user_id;
            },
        ];
    }
}
