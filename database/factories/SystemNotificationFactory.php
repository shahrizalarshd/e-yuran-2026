<?php

namespace Database\Factories;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SystemNotification>
 */
class SystemNotificationFactory extends Factory
{
    protected $model = SystemNotification::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'type' => fake()->randomElement(['info', 'success', 'warning', 'error']),
            'action_url' => null,
            'is_read' => false,
            'read_at' => null,
        ];
    }

    /**
     * Read notification
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Unread notification
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Info type
     */
    public function info(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'info',
        ]);
    }

    /**
     * Success type
     */
    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'success',
        ]);
    }

    /**
     * Warning type
     */
    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'warning',
        ]);
    }

    /**
     * Error type
     */
    public function error(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'error',
        ]);
    }

    /**
     * With action URL
     */
    public function withActionUrl(string $url): static
    {
        return $this->state(fn (array $attributes) => [
            'action_url' => $url,
        ]);
    }
}

