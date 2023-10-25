<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WarrantyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'desc' => $this->faker->text(),
            'ending_date' => now()->addYear(),
            'ending_mileage' => $this->faker->numberBetween(100000, 300000),
            'mileage_total' => $this->faker->numberBetween(100000, 300000),
        ];
    }
}
