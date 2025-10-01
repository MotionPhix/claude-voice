<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueDate = $this->faker->dateTimeBetween('-6 months', 'now');
        $dueDate = $this->faker->dateTimeBetween($issueDate, '+1 month');

        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $taxRate = $this->faker->randomFloat(2, 0, 25);
        $taxAmount = $subtotal * ($taxRate / 100);
        $discount = $this->faker->randomFloat(2, 0, 100);
        $total = $subtotal + $taxAmount - $discount;

        return [
            'organization_id' => Organization::factory(),
            'client_id' => Client::factory(),
            'currency' => 'USD',
            'exchange_rate' => 1.000000,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid', 'overdue']),
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount' => $discount,
            'total' => $total,
            'amount_paid' => 0,
            'notes' => $this->faker->optional()->paragraph(),
            'terms' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the invoice is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'sent_at' => null,
            'paid_at' => null,
            'amount_paid' => 0,
        ]);
    }

    /**
     * Indicate that the invoice has been sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'paid_at' => null,
            'amount_paid' => 0,
        ]);
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
                'sent_at' => $this->faker->dateTimeBetween('-2 months', '-1 month'),
                'paid_at' => $this->faker->dateTimeBetween($attributes['sent_at'] ?? '-1 month', 'now'),
                'amount_paid' => $attributes['total'],
            ];
        });
    }

    /**
     * Indicate that the invoice is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => $this->faker->dateTimeBetween('-2 months', '-1 day'),
            'sent_at' => $this->faker->dateTimeBetween('-3 months', '-1 month'),
            'paid_at' => null,
            'amount_paid' => 0,
        ]);
    }

    /**
     * Indicate that the invoice is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'amount_paid' => 0,
        ]);
    }

    /**
     * Indicate that the invoice has a specific currency.
     */
    public function withCurrency(string $currency): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => $currency,
            'exchange_rate' => $this->faker->randomFloat(6, 0.5, 2.0),
        ]);
    }

    /**
     * Indicate that the invoice has no tax.
     */
    public function withoutTax(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total' => $attributes['subtotal'] - $attributes['discount'],
            ];
        });
    }

    /**
     * Indicate that the invoice has no discount.
     */
    public function withoutDiscount(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'discount' => 0,
                'total' => $attributes['subtotal'] + $attributes['tax_amount'],
            ];
        });
    }
}
