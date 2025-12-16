<?php

namespace Database\Factories;

use App\Models\House;
use App\Models\HouseOccupancy;
use App\Models\LegacyPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LegacyPayment>
 */
class LegacyPaymentFactory extends Factory
{
    protected $model = LegacyPayment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'house_no' => fake()->numberBetween(1, 500),
            'payment_type' => fake()->randomElement(['membership', 'annual']),
            'year' => fake()->numberBetween(2017, 2024),
            'amount' => fake()->randomFloat(2, 20, 150),
            'payment_date' => fake()->dateTimeBetween('2017-01-01', '2024-12-31'),
            'owner_name' => fake()->name(),
            'notes' => fake()->optional()->sentence(),
            'imported_at' => now(),
            'linked_to_house_id' => null,
            'linked_to_occupancy_id' => null,
        ];
    }

    /**
     * Membership payment
     */
    public function membership(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => 'membership',
            'year' => null,
            'amount' => 20.00,
        ]);
    }

    /**
     * Annual payment
     */
    public function annual(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => 'annual',
            'year' => fake()->numberBetween(2017, 2024),
            'amount' => 120.00,
        ]);
    }

    /**
     * Linked to house (for annual payments)
     */
    public function linkedToHouse(House $house = null): static
    {
        return $this->state(fn (array $attributes) => [
            'linked_to_house_id' => $house?->id ?? House::factory(),
            'house_no' => $house?->house_no ?? $attributes['house_no'],
        ]);
    }

    /**
     * Linked to occupancy (for membership payments)
     */
    public function linkedToOccupancy(HouseOccupancy $occupancy = null): static
    {
        return $this->state(fn (array $attributes) => [
            'linked_to_occupancy_id' => $occupancy?->id ?? HouseOccupancy::factory(),
        ]);
    }

    /**
     * Unlinked payment
     */
    public function unlinked(): static
    {
        return $this->state(fn (array $attributes) => [
            'linked_to_house_id' => null,
            'linked_to_occupancy_id' => null,
        ]);
    }

    /**
     * For a specific year
     */
    public function forYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => $year,
            'payment_date' => fake()->dateTimeBetween("{$year}-01-01", "{$year}-12-31"),
        ]);
    }
}

