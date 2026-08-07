<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        $mrpPaise = fake()->numberBetween(6000, 30000);

        return [
            'product_id' => Product::factory(),
            'size_label' => fake()->randomElement(['250ml', '500ml', '1L', '5L']),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-??')),
            'mrp' => $mrpPaise,
            'selling_price' => $mrpPaise,
            'weight_grams' => fake()->numberBetween(100, 5000),
            'stock_qty' => fake()->numberBetween(10, 200),
            'is_active' => true,
            'sort_order' => 1,
        ];
    }

    public function outOfStock(): self
    {
        return $this->state(fn () => ['stock_qty' => 0]);
    }

    public function inactive(): self
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
