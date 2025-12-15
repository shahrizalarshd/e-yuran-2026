<?php

namespace Database\Factories;

use App\Models\FeeConfiguration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeeConfiguration>
 */
class FeeConfigurationFactory extends Factory
{
    protected $model = FeeConfiguration::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => 'Yuran Bulanan ' . fake()->year(),
            'amount' => fake()->randomElement([10.00, 15.00, 20.00, 25.00, 30.00]),
            'effective_from' => now()->startOfYear(),
            'effective_until' => null,
            'description' => 'Yuran penyelenggaraan bulanan',
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Active fee configuration
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'effective_from' => now()->subMonths(6),
            'effective_until' => null,
        ]);
    }

    /**
     * Inactive fee configuration
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Expired fee configuration
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_from' => now()->subYear(),
            'effective_until' => now()->subMonth(),
        ]);
    }

    /**
     * Future fee configuration
     */
    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_from' => now()->addMonth(),
            'effective_until' => null,
        ]);
    }

    /**
     * Fixed amount
     */
    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }
}

