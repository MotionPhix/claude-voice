<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Organization;
use App\Models\RecurringInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecurringInvoice>
 */
class RecurringInvoiceFactory extends Factory
{
    protected $model = RecurringInvoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $taxRate = fake()->randomFloat(2, 0, 25);
        $taxAmount = $subtotal * ($taxRate / 100);
        $discount = fake()->randomFloat(2, 0, 100);
        $total = $subtotal + $taxAmount - $discount;

        $startDate = fake()->dateTimeBetween('now', '+1 month');
        $frequency = fake()->randomElement(['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
        $interval = fake()->numberBetween(1, 3);

        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true).' - Recurring',
            'client_id' => Client::factory(),
            'currency' => 'USD',
            'frequency' => $frequency,
            'interval' => $interval,
            'start_date' => $startDate,
            'end_date' => fake()->optional()->dateTimeBetween($startDate, '+2 years'),
            'next_invoice_date' => $startDate,
            'max_cycles' => fake()->optional()->numberBetween(6, 36),
            'cycles_completed' => 0,
            'is_active' => true,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount' => $discount,
            'total' => $total,
            'notes' => fake()->optional()->paragraph(),
            'terms' => fake()->optional()->paragraph(),
            'payment_terms_days' => fake()->randomElement([7, 14, 30, 45, 60]),
        ];
    }

    /**
     * Indicate that the recurring invoice is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the recurring invoice runs monthly.
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'monthly',
            'interval' => 1,
        ]);
    }

    /**
     * Indicate that the recurring invoice runs weekly.
     */
    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'weekly',
            'interval' => 1,
        ]);
    }

    /**
     * Indicate that the recurring invoice has completed several cycles.
     */
    public function withCompletedCycles(int $cycles = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'cycles_completed' => $cycles,
        ]);
    }
}
