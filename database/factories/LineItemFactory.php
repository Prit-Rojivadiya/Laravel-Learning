<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LineItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'quantity' => $this->faker->numberBetween(1, 10),
            'price' => $this->faker->randomFloat(2, 1, 80000),
            'total_price' => $this->faker->randomFloat(2, 1, 80000),
        ];
    }
}
