<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unique_code' => (string) fake()->unique()->randomNumber(6, true),
            'text' => fake()->paragraph(),
            'type' => 'multiple_choice',
            'user_id' => \App\Models\User::factory(),
            'correct_option' => fake()->numberBetween(1, 4),
            'current_status' => 'draft',
        ];
    }
}
