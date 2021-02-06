<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventPriceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventPrice::class;

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
            'need_pay' => $this->faker->randomDigit,
            'deposit' => $this->faker->randomDigit,
            'description' => $this->faker->sentence,
        ];
    }
}
