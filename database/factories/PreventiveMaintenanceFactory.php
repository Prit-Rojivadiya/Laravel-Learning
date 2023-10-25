<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PreventiveMaintenanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'recurring' => $this->faker->boolean(),
            'length_meters' => $this->faker->numberBetween(100000, 300000),
            'length_days' => $this->faker->numberBetween(0, 364),
            'start_date' => now(),
            'due_date' => now()->addMonth(),
            'completed_date' => now()->addYear(),
            'start_at_meter' => $this->faker->numberBetween(1000, 300000),
            'due_at_meter' => $this->faker->numberBetween(1000, 300000),
            'completed_at_meter' => $this->faker->numberBetween(1000, 300000),
            'desc' => $this->faker->text(),
        ];
    }
}
