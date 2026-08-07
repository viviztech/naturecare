<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductVariantMigrator
{
    /**
     * Build normalized product_variants row data from a product's legacy
     * `variants` JSON array (Phase 1 shape: [['size' => '500ml', 'mrp' => 95], ...]).
     *
     * Pure and unit-testable: takes data in, returns row arrays out. Does not
     * touch the database. Skips (and logs a warning for) malformed entries
     * instead of throwing, so one bad row in one product never aborts the
     * whole migration.
     *
     * @param  array<int, mixed>  $jsonVariants
     * @return array<int, array<string, mixed>>
     */
    public static function rowsFromJson(Product $product, array $jsonVariants): array
    {
        $rows = [];
        $index = 0;

        foreach ($jsonVariants as $variant) {
            $index++;

            if (! is_array($variant) || blank($variant['size'] ?? null) || ! is_numeric($variant['mrp'] ?? null)) {
                Log::warning('ProductVariantMigrator: skipped malformed variant entry', [
                    'product_id' => $product->id,
                    'product_slug' => $product->slug,
                    'entry_index' => $index,
                    'entry' => $variant,
                ]);

                continue;
            }

            $mrpPaise = (int) round(((float) $variant['mrp']) * 100);

            $sellingPricePaise = isset($variant['selling_price']) && is_numeric($variant['selling_price'])
                ? (int) round(((float) $variant['selling_price']) * 100)
                : $mrpPaise;

            $rows[] = [
                'product_id' => $product->id,
                'size_label' => (string) $variant['size'],
                'sku' => strtoupper($product->slug.'-'.$index),
                'mrp' => $mrpPaise,
                'selling_price' => $sellingPricePaise,
                'weight_grams' => null,
                'stock_qty' => 0,
                'is_active' => true,
                'sort_order' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $rows;
    }

    /**
     * Deterministic SKU builder shared with the migrator so callers (e.g.
     * tests) can predict a SKU without duplicating the slug-N-uppercase rule.
     */
    public static function skuFor(string $productSlug, int $index): string
    {
        return strtoupper(Str::slug($productSlug).'-'.$index);
    }
}
