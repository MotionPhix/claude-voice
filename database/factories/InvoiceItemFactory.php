<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->randomFloat(2, 10, 500);
        $total = $quantity * $unitPrice;

        return [
            'invoice_id' => Invoice::factory(),
            'description' => $this->faker->sentence(4),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $total,
        ];
    }

    /**
     * Indicate that the item is for web development services.
     */
    public function webDevelopment(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => 'Web Development - ' . $this->faker->randomElement([
                'Frontend Development',
                'Backend Development',
                'Database Design',
                'API Integration',
                'Performance Optimization'
            ]),
        ]);
    }

    /**
     * Indicate that the item is for consulting services.
     */
    public function consulting(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => 'Consulting - ' . $this->faker->randomElement([
                'Technical Consultation',
                'Strategy Planning',
                'Code Review',
                'Architecture Review',
                'Security Audit'
            ]),
        ]);
    }

    /**
     * Indicate that the item has hourly billing.
     */
    public function hourlyRate(): static
    {
        return $this->state(function (array $attributes) {
            $hours = $this->faker->numberBetween(1, 40);
            $rate = $this->faker->randomFloat(2, 50, 200);
            
            return [
                'description' => $attributes['description'] . ' (' . $hours . ' hours @ $' . $rate . '/hr)',
                'quantity' => $hours,
                'unit_price' => $rate,
                'total' => $hours * $rate,
            ];
        });
    }

    /**
     * Indicate that the item is a fixed price item.
     */
    public function fixedPrice(): static
    {
        return $this->state(function (array $attributes) {
            $price = $this->faker->randomFloat(2, 500, 5000);
            
            return [
                'quantity' => 1,
                'unit_price' => $price,
                'total' => $price,
            ];
        });
    }
}
