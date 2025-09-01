<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 2000),
            'currency' => 'USD',
            'exchange_rate' => 1.000000,
            'amount_in_base_currency' => fn (array $attributes) => $attributes['amount'],
            'payment_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'method' => $this->faker->randomElement(['cash', 'check', 'bank_transfer', 'credit_card', 'paypal', 'other']),
            'reference' => $this->faker->optional()->bothify('REF-####-????'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the payment was made by cash.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'cash',
            'reference' => null,
        ]);
    }

    /**
     * Indicate that the payment was made by check.
     */
    public function check(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'check',
            'reference' => 'Check #' . $this->faker->numerify('####'),
        ]);
    }

    /**
     * Indicate that the payment was made by bank transfer.
     */
    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'bank_transfer',
            'reference' => 'Transfer #' . $this->faker->bothify('TXN-####-????'),
        ]);
    }

    /**
     * Indicate that the payment was made by credit card.
     */
    public function creditCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'credit_card',
            'reference' => 'Card ending in ' . $this->faker->numerify('####'),
        ]);
    }

    /**
     * Indicate that the payment was made via PayPal.
     */
    public function paypal(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'paypal',
            'reference' => 'PayPal TXN: ' . $this->faker->bothify('??????????'),
        ]);
    }

    /**
     * Indicate that this payment was made recently.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that this is a partial payment.
     */
    public function partial(): static
    {
        return $this->state(function (array $attributes) {
            // Ensure the amount is less than a typical invoice total
            return [
                'amount' => $this->faker->randomFloat(2, 50, 500),
            ];
        });
    }

    /**
     * Indicate that this is a full payment.
     */
    public function full(): static
    {
        return $this->state(function (array $attributes) {
            // This will need to be set based on the invoice total when used
            return [
                'notes' => 'Full payment received',
            ];
        });
    }
}
