<?php

namespace Database\Factories;

use App\Models\House;
use App\Models\HouseOccupancy;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HouseOccupancy>
 */
class HouseOccupancyFactory extends Factory
{
    protected $model = HouseOccupancy::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'house_id' => House::factory(),
            'resident_id' => Resident::factory(),
            'role' => 'owner',
            'start_date' => now()->subMonths(fake()->numberBetween(1, 24)),
            'end_date' => null,
            'is_payer' => true,
        ];
    }

    /**
     * Owner occupancy
     */
    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'owner',
            'is_payer' => true,
        ]);
    }

    /**
     * Tenant occupancy
     */
    public function tenant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'tenant',
            'is_payer' => false,
        ]);
    }

    /**
     * Tenant as payer
     */
    public function tenantPayer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'tenant',
            'is_payer' => true,
        ]);
    }

    /**
     * Ended occupancy
     */
    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_date' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    /**
     * Active occupancy
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_date' => null,
        ]);
    }
}

