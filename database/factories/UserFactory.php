<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'resident',
            'language_preference' => fake()->randomElement(['bm', 'en']),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Super Admin role
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
        ]);
    }

    /**
     * Treasurer role
     */
    public function treasurer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'treasurer',
        ]);
    }

    /**
     * Auditor role
     */
    public function auditor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'auditor',
        ]);
    }

    /**
     * Resident role
     */
    public function resident(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'resident',
        ]);
    }

    /**
     * Inactive user
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * BM language preference
     */
    public function bm(): static
    {
        return $this->state(fn (array $attributes) => [
            'language_preference' => 'bm',
        ]);
    }

    /**
     * EN language preference
     */
    public function en(): static
    {
        return $this->state(fn (array $attributes) => [
            'language_preference' => 'en',
        ]);
    }
}
