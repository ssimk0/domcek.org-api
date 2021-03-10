<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory {

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
        $start_date = $this->faker->dateTimeBetween('now', '+2years');

        return [
            'name' => $this->faker->sentence,
            'theme' => $this->faker->sentence,
            'start_date' => function () use ($start_date) {
                // we need unique start_date
                if (Event::query()->whereDate('start_date', $start_date)) $start_date = $this->faker->dateTimeBetween('now', '+2years');
                return $start_date->format('Y-m-d');
            },
            'end_date' => $this->faker->dateTimeBetween($start_date, $start_date->add(new \DateInterval('P2D')))->format('Y-m-d'),
            'start_registration' => now()->subDay()->format('Y-m-d'),
            'end_registration' => now()->addYear()->format('Y-m-d'),
            'end_volunteer_registration' => now()->addYear()->format('Y-m-d'),
        ];
    }
}
