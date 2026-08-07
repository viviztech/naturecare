<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\ProductVariantMigrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data-only migration: copies each product's legacy `variants` JSON blob
     * into the new normalized `product_variants` table. Guarded so it is
     * safe to re-run (e.g. after `migrate:fresh` in a different environment)
     * — if any product_variants rows already exist, this is a no-op.
     */
    public function up(): void
    {
        if (ProductVariant::query()->exists()) {
            return;
        }

        // Deliberately reads via the raw DB query builder rather than the
        // Product Eloquent model: by the time this migration runs in a
        // fresh `php artisan migrate`, the application's Product model no
        // longer casts/fills `variants` (that's a Phase 2 code change,
        // shipped in the same deploy as this migration) — going through
        // Eloquent here would silently see the column as an unparsed raw
        // string. Reading the column directly keeps this migration correct
        // regardless of the current state of the model layer.
        Product::query()->orderBy('id')->chunkById(50, function ($products) {
            $ids = $products->pluck('id');

            $rawRows = DB::table('products')->whereIn('id', $ids)->get(['id', 'variants']);

            foreach ($products as $product) {
                $raw = $rawRows->firstWhere('id', $product->id);
                $jsonVariants = $raw && $raw->variants ? json_decode($raw->variants, true) : [];

                if (! is_array($jsonVariants) || $jsonVariants === []) {
                    continue;
                }

                $rows = ProductVariantMigrator::rowsFromJson($product, $jsonVariants);

                if ($rows !== []) {
                    DB::table('product_variants')->insert($rows);
                }
            }
        });
    }

    /**
     * Irreversible data migration by design — reversing it would mean
     * guessing which product_variants rows came from the original JSON
     * vs. rows created afterwards through the admin panel. Documented no-op.
     */
    public function down(): void
    {
        // Intentionally left blank — see class docblock.
    }
};
