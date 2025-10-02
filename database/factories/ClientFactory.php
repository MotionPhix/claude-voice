<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Organization;
use App\Models\Membership;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => function (array $attributes) {
                // If no organization_id provided, create new one
                return \App\Models\Organization::factory();
            },
            'name' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'notes' => fake()->optional()->text(),
            'currency' => 'USD',
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the client is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the client has minimal information.
     */
    public function minimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => null,
            'address' => null,
            'city' => null,
            'state' => null,
            'postal_code' => null,
            'country' => null,
            'tax_number' => null,
            'website' => null,
            'notes' => null,
        ]);
    }
}
