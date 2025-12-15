<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\FeeConfiguration;
use App\Models\House;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
{
    protected $model = Bill::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $year = now()->year;
        $month = fake()->numberBetween(1, 12);
        // Use unique suffix for bill_no to avoid collisions
        $uniqueSuffix = fake()->unique()->numberBetween(100000, 999999);

        return [
            'house_id' => House::factory(),
            'fee_configuration_id' => FeeConfiguration::factory(),
            'bill_no' => sprintf('BIL-%04d%02d-%06d', $year, $month, $uniqueSuffix),
            'bill_year' => $year,
            'bill_month' => $month,
            'amount' => fake()->randomElement([10.00, 15.00, 20.00, 25.00]),
            'status' => 'unpaid',
            'paid_amount' => 0,
            'due_date' => now()->setYear($year)->setMonth($month)->endOfMonth(),
            'paid_at' => null,
        ];
    }

    /**
     * Unpaid bill
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unpaid',
            'paid_amount' => 0,
            'paid_at' => null,
        ]);
    }

    /**
     * Paid bill
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_amount' => $attributes['amount'] ?? 20.00,
            'paid_at' => now(),
        ]);
    }

    /**
     * Processing bill
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'paid_amount' => 0,
            'paid_at' => null,
        ]);
    }

    /**
     * Partial payment bill
     */
    public function partial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'partial',
            'paid_amount' => ($attributes['amount'] ?? 20.00) / 2,
            'paid_at' => null,
        ]);
    }

    /**
     * Overdue bill
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unpaid',
            'due_date' => now()->subMonth(),
            'paid_amount' => 0,
            'paid_at' => null,
        ]);
    }

    /**
     * Bill for specific month and year
     */
    public function forPeriod(int $year, int $month): static
    {
        return $this->state(fn (array $attributes) => [
            'bill_year' => $year,
            'bill_month' => $month,
            'due_date' => now()->setYear($year)->setMonth($month)->endOfMonth(),
        ]);
    }

    /**
     * Current month bill
     */
    public function currentMonth(): static
    {
        return $this->forPeriod(now()->year, now()->month);
    }
}

