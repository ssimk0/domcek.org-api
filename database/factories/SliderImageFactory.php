<?php

namespace Database\Factories;

use App\Models\SliderImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class SliderImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SliderImage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'text' => $this->faker->text,
            'image' => $this->faker->imageUrl(),
            'order' => $this->faker->randomDigit,
            'active' => 1
        ];
    }
}
