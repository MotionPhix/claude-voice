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
        return [
            'client_id' => function () {
                if (auth()->check() && ! session('current_organization_id')) {
                    // Create organization and membership for auth user if none exists
                    $org = Organization::factory()->create();
                    \Database\Factories\MembershipFactory::new()->owner()->create([
                        'user_id' => auth()->id(),
                        'organization_id' => $org->id,
                    ]);
                    session(['current_organization_id' => $org->id]);

                    // Create client in this org
                    return Client::factory()->create([
                        'organization_id' => $org->id,
                    ])->id;
                }

                return Client::factory();
            },
            'organization_id' => function (array $attributes) {
                if (isset($attributes['client_id'])) {
                    if ($attributes['client_id'] instanceof Client) {
                        return $attributes['client_id']->organization_id;
                    }
                    $client = Client::withoutGlobalScopes()->find($attributes['client_id']);
                    if ($client) {
                        return $client->organization_id;
                    }
                }

                return Organization::factory();
            },
            'currency' => 'USD',
            'exchange_rate' => 1.000000,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'draft',
            'subtotal' => 0,
            'tax_rate' => $this->faker->randomFloat(2, 0, 25),
            'tax_amount' => 0,
            'discount' => $this->faker->randomFloat(2, 0, 100),
            'total' => 0,
            'amount_paid' => 0,
            'notes' => $this->faker->optional()->paragraph(),
            'terms' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Invoice $invoice) {
            if (auth()->check()) {
                // Check if auth user already has membership in this organization
                $membership = \App\Models\Membership::where([
                    'user_id' => auth()->id(),
                    'organization_id' => $invoice->organization_id,
                ])->first();

                if (! $membership) {
                    // Create owner membership if none exists
                    \Database\Factories\MembershipFactory::new()->owner()->create([
                        'user_id' => auth()->id(),
                        'organization_id' => $invoice->organization_id,
                    ]);
                }

                session(['current_organization_id' => $invoice->organization_id]);
            }
        });
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
