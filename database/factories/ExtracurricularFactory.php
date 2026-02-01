<?php

namespace Database\Factories;

use App\Models\ExtracurricularCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Extracurricular>
 */
class ExtracurricularFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'award' => fake()->sentence(),
            'category_id' => ExtracurricularCategory::factory(),
            'is_active' => fake()->boolean(),
            'description' => fake()->paragraph(),
        ];
    }
}
