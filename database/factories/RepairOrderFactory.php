<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RepairOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'start_date' => now(),
            'completed_date' => now(),
            'meter_reading' => $this->faker->numberBetween(1, 100000),
            'invoice_number' => strtoupper(Str::random(8)),
            'total_price' => $this->faker->randomFloat(2, 1, 80000),
            'desc' => $this->faker->text(),
            'notes' => $this->faker->text(),
            'needs_approval' => $this->faker->boolean(),
            'approval_received_date' => now(),
            'ro_number' => $this->faker->numberBetween(1, 100000),
        ];
    }
}
