<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => 'Unit '.$this->faker->unique()->word(),
            'type' => $this->faker->randomElement(['fakultas', 'biro', 'lembaga', 'upt']),
            'category' => $this->faker->randomElement(['Akademik', 'Sarpras', 'Keuangan', 'Umum']),
        ];
    }
}
