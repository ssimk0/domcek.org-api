<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'theme' => $this->faker->sentence,
            'start_date' => \Carbon\Carbon::now()->addYear()->format('Y-m-d'),
            'end_date' => $this->faker->date(),
            'start_registration' => \Carbon\Carbon::now()->addYear()->format('Y-m-d'),
            'end_registration' => \Carbon\Carbon::now()->addYear()->format('Y-m-d'),
            'end_volunteer_registration' => \Carbon\Carbon::now()->addYear()->format('Y-m-d'),
        ];
    }
}
