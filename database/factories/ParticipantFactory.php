<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Profile;
use App\Models\User;
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
            'event_id' => Event::factory()->createOne(),
            'transport_in' => 'test',
            'transport_out' => 'test',
            'user_id' => Profile::factory()->createOne()->user,
        ];
    }
}
