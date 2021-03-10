<?php

namespace Database\Factories;

use App\Models\TransportType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransportTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TransportType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}
