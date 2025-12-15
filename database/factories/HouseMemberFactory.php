<?php

namespace Database\Factories;

use App\Models\House;
use App\Models\HouseMember;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HouseMember>
 */
class HouseMemberFactory extends Factory
{
    protected $model = HouseMember::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'house_id' => House::factory(),
            'resident_id' => Resident::factory(),
            'relationship' => fake()->randomElement(['owner', 'spouse', 'child', 'family', 'tenant']),
            'can_view_bills' => true,
            'can_pay' => true,
            'status' => 'active',
            'rejection_reason' => null,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Owner relationship
     */
    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship' => 'owner',
            'can_view_bills' => true,
            'can_pay' => true,
        ]);
    }

    /**
     * Spouse relationship
     */
    public function spouse(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship' => 'spouse',
            'can_view_bills' => true,
            'can_pay' => true,
        ]);
    }

    /**
     * Child relationship
     */
    public function child(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship' => 'child',
            'can_view_bills' => true,
            'can_pay' => false,
        ]);
    }

    /**
     * Tenant relationship
     */
    public function tenant(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship' => 'tenant',
            'can_view_bills' => true,
            'can_pay' => true,
        ]);
    }

    /**
     * Pending status
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Active status
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Rejected status
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => 'Not verified',
        ]);
    }

    /**
     * Inactive status
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * View only (cannot pay)
     */
    public function viewOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'can_view_bills' => true,
            'can_pay' => false,
        ]);
    }
}

