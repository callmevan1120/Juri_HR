<?php

namespace Database\Factories;

use App\Models\Barcode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Barcode>
 */
class BarcodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()
                ->randomElement(['Barcode 1', 'Barcode 2', 'Barcode 3', 'Barcode 4', 'Barcode 5']),
            'value' => fake()->ean13(),
            'radius' => 50,
            'latitude' => fake()->latitude(-90, 90),
            'longitude' => fake()->longitude(-90, 90),
        ];
    }
}
