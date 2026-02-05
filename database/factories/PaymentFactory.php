<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'payment_number' => $this->faker->randomNumber(8),
            'user_id' => function () {
                return Profile::factory()->createOne()->user->id;
            },
            'on_registration' => $this->faker->randomNumber(1),
            'need_pay' => $this->faker->randomNumber(2),
            'paid' => 0,
        ];
    }
}
