<?php

namespace Database\Factories;

use App\Models\House;
use App\Models\Payment;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'house_id' => House::factory(),
            'resident_id' => Resident::factory(),
            'payment_no' => Payment::generatePaymentNo(),
            'amount' => fake()->randomFloat(2, 10, 300),
            'status' => 'pending',
            'payment_type' => fake()->randomElement(['current_month', 'selected_months', 'yearly']),
            'toyyibpay_billcode' => null,
            'toyyibpay_ref' => null,
            'toyyibpay_transaction_id' => null,
            'toyyibpay_response' => null,
            'paid_at' => null,
        ];
    }

    /**
     * Pending payment
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }

    /**
     * Successful payment
     */
    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'success',
            'toyyibpay_transaction_id' => 'TXN' . fake()->numerify('########'),
            'toyyibpay_ref' => 'REF' . fake()->numerify('########'),
            'paid_at' => now(),
        ]);
    }

    /**
     * Failed payment
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'toyyibpay_response' => json_encode(['reason' => 'Payment declined']),
            'paid_at' => null,
        ]);
    }

    /**
     * Cancelled payment
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'paid_at' => null,
        ]);
    }

    /**
     * Current month payment
     */
    public function currentMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => 'current_month',
        ]);
    }

    /**
     * Yearly payment
     */
    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => 'yearly',
            'amount' => 240.00, // 12 months x RM20
        ]);
    }

    /**
     * With ToyyibPay billcode
     */
    public function withBillcode(): static
    {
        return $this->state(fn (array $attributes) => [
            'toyyibpay_billcode' => fake()->regexify('[a-z0-9]{8}'),
        ]);
    }
}

