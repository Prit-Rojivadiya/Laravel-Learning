<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MeterReadingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'meter_reading' => $this->faker->numberBetween(1, 900000),
            'meter_reading_date' => now(),
            'source' => 'Fueling',
            'notes' => $this->faker->text(),
        ];
    }
}
