<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->currencyCode(),
            'name' => $this->faker->currencyCode() . ' Currency',
            'symbol' => '$',
            'exchange_rate' => $this->faker->randomFloat(6, 0.1, 10.0),
            'is_base' => false,
            'is_active' => true,
            'last_updated_at' => $this->faker->dateTimeThisMonth(),
        ];
    }

    /**
     * Indicate that this is the base currency.
     */
    public function base(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'exchange_rate' => 1.000000,
            'is_base' => true,
        ]);
    }

    /**
     * Indicate that this currency is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create specific currency.
     */
    public function currency(string $code, string $name, string $symbol, float $rate = 1.0): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
            'name' => $name,
            'symbol' => $symbol,
            'exchange_rate' => $rate,
        ]);
    }
}
