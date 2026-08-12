<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    use InteractsWithMedia;

    public const MEDIA_COLLECTION = 'images';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'usage_instructions',
        'hsn_code',
        'is_commercial',
        'is_featured',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'is_commercial' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION)
            ->useFallbackUrl('/images/product-placeholder.svg');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->format('webp')
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(1200)
            ->format('webp')
            ->nonQueued();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true)->orderBy('sort_order');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('short_description', 'like', "%{$term}%");
        });
    }

    public function relatedProducts(int $limit = 4)
    {
        return static::query()
            ->active()
            ->where('category_id', $this->category_id)
            ->where('id', '!=', $this->id)
            ->with(['category', 'media'])
            ->ordered()
            ->limit($limit)
            ->get();
    }

    public function whatsappEnquiryUrl(): string
    {
        $number = Setting::whatsappNumber();
        $message = "Hi Nature Care, I'm interested in {$this->name}. Please share more details.";

        return "https://wa.me/{$number}?text=".rawurlencode($message);
    }

    public function lowestVariantPrice(): ?float
    {
        // If a variants relation is already eager-loaded (either `variants`
        // or `activeVariants`), avoid an extra query.
        $loaded = $this->relationLoaded('activeVariants')
            ? $this->activeVariants
            : ($this->relationLoaded('variants') ? $this->variants : null);

        if ($loaded !== null) {
            $lowest = $loaded
                ->filter(fn (ProductVariant $variant) => $variant->is_active)
                ->min(fn (ProductVariant $variant) => $variant->selling_price->paise());

            return $lowest !== null ? $lowest / 100 : null;
        }

        $lowestPaise = $this->activeVariants()->min('selling_price');

        return $lowestPaise !== null ? ((int) $lowestPaise) / 100 : null;
    }

    public function metaTitle(): string
    {
        return $this->meta_title ?: "{$this->name} | Nature Care Products";
    }

    public function metaDescription(): string
    {
        return $this->meta_description ?: $this->short_description;
    }
}
