<?php

namespace Database\Factories;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resident>
 */
class ResidentFactory extends Factory
{
    protected $model = Resident::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '01' . fake()->numerify('#########'),
            'ic_number' => fake()->numerify('######-##-####'),
            'language_preference' => fake()->randomElement(['bm', 'en']),
        ];
    }

    /**
     * Resident with BM language preference
     */
    public function bm(): static
    {
        return $this->state(fn (array $attributes) => [
            'language_preference' => 'bm',
        ]);
    }

    /**
     * Resident with EN language preference
     */
    public function en(): static
    {
        return $this->state(fn (array $attributes) => [
            'language_preference' => 'en',
        ]);
    }

    /**
     * Resident without user account
     */
    public function withoutUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}

