<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['create', 'update', 'delete', 'login', 'logout']),
            'model_type' => null,
            'model_id' => null,
            'old_values' => null,
            'new_values' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Create action
     */
    public function createAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'create',
        ]);
    }

    /**
     * Update action
     */
    public function updateAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'update',
            'old_values' => ['name' => 'Old Name'],
            'new_values' => ['name' => 'New Name'],
        ]);
    }

    /**
     * Delete action
     */
    public function deleteAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'delete',
            'old_values' => ['id' => 1, 'name' => 'Deleted Item'],
        ]);
    }

    /**
     * Login action
     */
    public function loginAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'login',
            'description' => 'User logged in',
        ]);
    }

    /**
     * For specific model
     */
    public function forModel(string $modelType, int $modelId): static
    {
        return $this->state(fn (array $attributes) => [
            'model_type' => $modelType,
            'model_id' => $modelId,
        ]);
    }
}

