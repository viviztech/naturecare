<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'size_label',
        'sku',
        'mrp',
        'selling_price',
        'weight_grams',
        'stock_qty',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'mrp' => MoneyCast::class,
            'selling_price' => MoneyCast::class,
            'weight_grams' => 'integer',
            'stock_qty' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_qty', '>', 0);
    }

    public function isInStock(): bool
    {
        return $this->stock_qty > 0;
    }

    public function label(): string
    {
        return "{$this->product?->name} - {$this->size_label}";
    }
}
