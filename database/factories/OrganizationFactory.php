<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => fake()->country(),
            'postal_code' => fake()->postcode(),
            'tax_id' => fake()->numerify('##-#######'),
            'website' => fake()->optional()->domainName(),
            'logo' => null,
            'billing_email' => fake()->companyEmail(),
            'stripe_customer_id' => null,
            'is_active' => true,
            'settings' => [
                'currency' => 'USD',
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'invoice_prefix' => 'INV',
            ],
        ];
    }

    /**
     * Indicate that the organization is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the organization has a Stripe customer ID.
     */
    public function withStripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_customer_id' => 'cus_'.Str::random(24),
        ]);
    }

    /**
     * Indicate that the organization has a logo.
     */
    public function withLogo(): static
    {
        return $this->state(fn (array $attributes) => [
            'logo' => 'logos/'.Str::random(40).'.png',
        ]);
    }
}
