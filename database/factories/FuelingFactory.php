<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FuelingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'price_per_unit' => $this->faker->randomFloat(2, 1, 10),
            'total_price' => $this->faker->randomFloat(2, 1, 999),
            'total_units' => $this->faker->randomFloat(2, 1, 50),
            'meter_reading' => $this->faker->numberBetween(1, 900000),
            'fueling_date' => now(),
            'location_country' => strtolower($this->faker->country),
            'location_state' => strtolower($this->faker->state),
            'notes' => $this->faker->text(),
        ];
    }
}
