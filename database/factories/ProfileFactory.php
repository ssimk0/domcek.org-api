<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->createOne(),
            'first_name' => $this->faker->name,
            'last_name' => $this->faker->name,
            'city' => $this->faker->word,
            'birth_date' => \Carbon\Carbon::now()->subYears(18)->format('Y-m-d'),
            'phone' => $this->faker->phoneNumber,
        ];
    }
}
