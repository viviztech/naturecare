<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Support\ProductVariantMigrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantMigratorTest extends TestCase
{
    use RefreshDatabase;

    protected function makeProduct(string $slug = 'floor-cleaner'): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->for($category)->create(['slug' => $slug]);
    }

    public function test_converts_rupee_mrp_to_paise(): void
    {
        $product = $this->makeProduct();

        $rows = ProductVariantMigrator::rowsFromJson($product, [
            ['size' => '500ml', 'mrp' => 95],
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame(9500, $rows[0]['mrp']);
        $this->assertSame(9500, $rows[0]['selling_price']);
    }

    public function test_generates_deterministic_uppercase_sku_per_entry(): void
    {
        $product = $this->makeProduct('floor-cleaner');

        $rows = ProductVariantMigrator::rowsFromJson($product, [
            ['size' => '500ml', 'mrp' => 95],
            ['size' => '1L', 'mrp' => 175],
        ]);

        $this->assertSame('FLOOR-CLEANER-1', $rows[0]['sku']);
        $this->assertSame('FLOOR-CLEANER-2', $rows[1]['sku']);
    }

    public function test_defaults_stock_to_zero_and_is_active_to_true(): void
    {
        $product = $this->makeProduct();

        $rows = ProductVariantMigrator::rowsFromJson($product, [
            ['size' => '500ml', 'mrp' => 95],
        ]);

        $this->assertSame(0, $rows[0]['stock_qty']);
        $this->assertTrue($rows[0]['is_active']);
    }

    public function test_skips_malformed_entries_without_throwing(): void
    {
        $product = $this->makeProduct();

        $rows = ProductVariantMigrator::rowsFromJson($product, [
            ['size' => '500ml', 'mrp' => 95],
            ['size' => '', 'mrp' => 100], // missing size
            ['size' => '1L', 'mrp' => 'not-a-number'], // malformed mrp
            'not-an-array',
            ['size' => '5L', 'mrp' => 799],
        ]);

        // Only the two well-formed entries should survive.
        $this->assertCount(2, $rows);
        $this->assertSame('500ml', $rows[0]['size_label']);
        $this->assertSame('5L', $rows[1]['size_label']);
    }

    public function test_returns_empty_array_for_empty_input(): void
    {
        $product = $this->makeProduct();

        $rows = ProductVariantMigrator::rowsFromJson($product, []);

        $this->assertSame([], $rows);
    }

    public function test_uses_explicit_selling_price_when_present(): void
    {
        $product = $this->makeProduct();

        $rows = ProductVariantMigrator::rowsFromJson($product, [
            ['size' => '500ml', 'mrp' => 100, 'selling_price' => 89],
        ]);

        $this->assertSame(10000, $rows[0]['mrp']);
        $this->assertSame(8900, $rows[0]['selling_price']);
    }
}
