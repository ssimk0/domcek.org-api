<?php

namespace Database\Factories;

use App\Models\NewsItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = NewsItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'body' => $this->faker->text,
            'short' => $this->faker->sentence,
            'image' => $this->faker->imageUrl(),
            'status' => \App\Constants\NewsConstant::PUBLISHED,
            'is_featured' => 0,
        ];
    }
}
