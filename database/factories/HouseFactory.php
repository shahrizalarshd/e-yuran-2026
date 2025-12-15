<?php

namespace Database\Factories;

use App\Models\House;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\House>
 */
class HouseFactory extends Factory
{
    protected $model = House::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $streetNames = [
            'Jalan Tropika 1',
            'Jalan Tropika 2', 
            'Jalan Tropika 3',
            'Jalan Tropika 4',
            'Jalan Tropika Utama',
        ];

        return [
            'house_no' => fake()->unique()->numberBetween(1, 500),
            'street_name' => fake()->randomElement($streetNames),
            'is_registered' => true,
            'is_active' => true,
            'status' => 'occupied',
        ];
    }

    /**
     * House that is registered and active (billable)
     */
    public function billable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_registered' => true,
            'is_active' => true,
        ]);
    }

    /**
     * House that is not registered
     */
    public function unregistered(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_registered' => false,
            'is_active' => true,
        ]);
    }

    /**
     * House that is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_registered' => true,
            'is_active' => false,
        ]);
    }

    /**
     * Vacant house
     */
    public function vacant(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'vacant',
        ]);
    }
}

