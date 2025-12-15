<?php

namespace Database\Factories;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SystemSetting>
 */
class SystemSettingFactory extends Factory
{
    protected $model = SystemSetting::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'value' => fake()->word(),
            'type' => 'string',
            'group' => 'general',
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Boolean setting
     */
    public function boolean(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'boolean',
            'value' => fake()->boolean() ? '1' : '0',
        ]);
    }

    /**
     * Integer setting
     */
    public function integer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'integer',
            'value' => (string) fake()->numberBetween(1, 100),
        ]);
    }

    /**
     * ToyyibPay setting
     */
    public function toyyibpay(): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => 'toyyibpay',
        ]);
    }

    /**
     * Telegram setting
     */
    public function telegram(): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => 'telegram',
        ]);
    }
}

