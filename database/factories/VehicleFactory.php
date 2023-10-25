<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'vehicle_number' => strtoupper(Str::random(3)),
            'purchase_price' => $this->faker->randomFloat(2, 1, 9999999),
            'in_service_date' => now(),
            'year' => $this->faker->numberBetween(1980, 2022),
            'make' => $this->faker->company(),
            'model' => $this->faker->company(),
            'vin' => strtoupper(Str::random(14)),
            'tire_size' => strtoupper(Str::random(14)),
            'license_plate_number' => strtoupper(Str::random(6)),
            'license_state' => strtolower($this->faker->state),
            'engine_serial_number' => strtoupper(Str::random(8)),
        ];
    }
}
