<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'student_id' => strtoupper($this->faker->unique()->bothify('STU###')),
            'class' => $this->faker->randomElement(['9', '10', '11', '12']),
            'section' => $this->faker->randomElement(['A', 'B', 'C']),
            'photo_path' => null,
        ];
    }
}
