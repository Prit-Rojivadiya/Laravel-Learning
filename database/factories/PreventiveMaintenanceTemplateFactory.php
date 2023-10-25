<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PreventiveMaintenanceTemplateFactory extends Factory
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
            'desc' => $this->faker->text(),
        ];
    }
}
