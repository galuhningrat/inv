<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntangibleAssetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'asset_code' => 'NF/2026/'.str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'name' => 'Test Asset '.$this->faker->word(),
            'category' => 'Software',
            'price' => 1000000,
            'activation_date' => now(),
            'license_type' => 'Perpetual',
            'unit_id' => Unit::factory(),
            'status' => 'Aktif',
            'category_data' => [],
        ];
    }
}
