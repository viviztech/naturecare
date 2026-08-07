<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Support\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Seeds the real NatureCare catalog from public/NatureCare_Product_Catalog.xlsx
 * and attaches the matching photos/renders from public/images/{commercials,cleanings,detergents}.
 *
 * Pricing: only pack sizes with an MRP actually printed on the source packaging
 * are seeded active (purchasable). Everything else is seeded inactive with a
 * zero price placeholder — visible on the product page with a WhatsApp enquiry
 * CTA, but hidden from price/cart until a real MRP is entered in the admin.
 */
class ProductSeeder extends Seeder
{
    /** @var array<string, string>|null filename (lowercase) => absolute path */
    private ?array $imageIndex = null;

    private const USAGE = [
        'floor-cleaner' => 'Dilute as directed with water before mopping. For tough stains, use undiluted on a damp cloth or mop, then rinse. Test on an inconspicuous area of delicate or polished flooring first. Keep out of reach of children.',
        'phenyl' => 'Dilute with water as directed before mopping floors, drains, or outdoor areas. Do not use undiluted on polished or delicate surfaces. Keep out of reach of children.',
        'room-freshener' => 'Shake well before use. Spray into the air or on fabric from a distance of 20-30cm, away from the face, food, and electrical fittings. Keep out of reach of children.',
        'toilet-bathroom-care' => 'Apply directly onto the surface, leave for 5-10 minutes, then scrub with a brush and rinse thoroughly with water. Wear gloves during use. Keep out of reach of children.',
        'kitchen-dish-care' => 'Apply a small amount onto a wet scrub pad or sponge, clean utensils thoroughly and rinse with water. Keep out of reach of children.',
        'laundry-care' => 'For machine wash, add to the detergent dispenser as per load size. For hand wash, dilute in water and soak clothes for 15-20 minutes before washing. Keep out of reach of children.',
        'specialty-surface-cleaners' => 'Dilute as directed with water. For tough stains, apply undiluted, leave for a few minutes, then scrub and rinse. Test on an inconspicuous area first. Keep out of reach of children.',
        'personal-care' => 'Apply a small amount to palms and rub hands together thoroughly, then rinse with water (hand wash) or allow to air dry (sanitizer). Suitable for frequent use.',
        'feminine-hygiene' => 'Peel and stick onto the inner side of your underwear. Change every 4-6 hours or as needed. Dispose of hygienically after use. For external use only.',
    ];

    public function run(): void
    {
        $sortOrder = 1;

        foreach ($this->catalog() as $item) {
            $category = Category::query()->where('slug', $item['category'])->firstOrFail();
            $slug = Str::slug($item['name']);

            $product = Product::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                    'short_description' => $item['short_description'],
                    'description' => $item['description'],
                    'usage_instructions' => self::USAGE[$item['category']],
                    'is_commercial' => $item['is_commercial'] ?? false,
                    'is_featured' => $item['is_featured'] ?? false,
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                    'meta_title' => "{$item['name']} | Nature Care Products",
                    'meta_description' => $item['short_description'],
                ]
            );

            $this->syncVariants($product, $slug, $item['variants']);
            $this->syncImages($product, $item['images']);

            $sortOrder++;
        }
    }

    /**
     * @param  array<int, array{size_label: string, mrp: int|null, selling: int|null}>  $variants
     */
    private function syncVariants(Product $product, string $productSlug, array $variants): void
    {
        $sort = 1;

        foreach ($variants as $variant) {
            $hasPrice = $variant['mrp'] !== null;
            $mrp = $hasPrice ? $variant['mrp'] : 0;
            $selling = $hasPrice ? ($variant['selling'] ?? $variant['mrp']) : 0;

            $product->variants()->updateOrCreate(
                ['sku' => strtoupper(Str::slug("{$productSlug}-{$variant['size_label']}"))],
                [
                    'size_label' => $variant['size_label'],
                    'mrp' => Money::fromRupees($mrp),
                    'selling_price' => Money::fromRupees($selling),
                    'stock_qty' => $hasPrice ? 100 : 0,
                    'is_active' => $hasPrice,
                    'sort_order' => $sort,
                ]
            );

            $sort++;
        }
    }

    /**
     * @param  array<int, string>  $filenames
     */
    private function syncImages(Product $product, array $filenames): void
    {
        $product->clearMediaCollection(Product::MEDIA_COLLECTION);

        foreach (array_unique($filenames) as $filename) {
            $path = $this->resolveImagePath($filename);

            if ($path === null) {
                $this->command?->warn("Image not found for {$product->name}: {$filename}");

                continue;
            }

            $product->addMedia($path)
                ->preservingOriginal()
                ->toMediaCollection(Product::MEDIA_COLLECTION);
        }
    }

    private function resolveImagePath(string $filename): ?string
    {
        if ($this->imageIndex === null) {
            $this->imageIndex = [];

            foreach (File::allFiles(public_path('images')) as $file) {
                $this->imageIndex[strtolower($file->getFilename())] = $file->getPathname();
            }
        }

        return $this->imageIndex[strtolower($filename)] ?? null;
    }

    /**
     * @return array<int, array{
     *     category: string, name: string, short_description: string, description: string,
     *     is_commercial?: bool, is_featured?: bool,
     *     variants: array<int, array{size_label: string, mrp: int|null, selling?: int|null}>,
     *     images: array<int, string>,
     * }>
     */
    private function catalog(): array
    {
        $floorClaims = 'Removes stains, gives a sparkling shine, cuts through tough dirt and leaves a superior long-lasting fragrance.';
        $floorIngredients = 'Key ingredients: Benzalkonium Chloride, ionic & non-ionic surfactants, Tetra Sodium EDTA, preservatives, colour, water.';

        return [
            // Floor Cleaner
            [
                'category' => 'floor-cleaner',
                'name' => 'Ultra Clean Floor Cleaner – Jasmine',
                'short_description' => 'Jasmine-fragranced floor cleaner that removes tough stains and leaves a sparkling shine.',
                'description' => "{$floorClaims}\n\n{$floorIngredients}",
                'is_featured' => true,
                'variants' => [
                    ['size_label' => '200ml', 'mrp' => null],
                    ['size_label' => '500ml', 'mrp' => 90],
                    ['size_label' => '5L', 'mrp' => null],
                ],
                'images' => [
                    'NatureCare_UltraClean_FloorCleaner_Jasmine_200ml.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Jasmine_500ml.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Jasmine_500ml_Front.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Jasmine_500ml_Back.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Jasmine.jpeg',
                ],
            ],
            [
                'category' => 'floor-cleaner',
                'name' => 'Ultra Clean Floor Cleaner – Lemon',
                'short_description' => 'Lemon-fragranced floor cleaner that removes tough stains and leaves a sparkling shine.',
                'description' => "{$floorClaims}\n\n{$floorIngredients}",
                'is_featured' => true,
                'variants' => [
                    ['size_label' => '500ml', 'mrp' => 90],
                    ['size_label' => '5L', 'mrp' => null],
                ],
                'images' => [
                    'NatureCare_UltraClean_FloorCleaner_Lemon_500ml.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Lemon_500ml_Front.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Lemon_500ml_Back.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Lemon.jpeg',
                ],
            ],
            [
                'category' => 'floor-cleaner',
                'name' => 'Ultra Clean Floor Cleaner – Rose',
                'short_description' => 'Rose-fragranced floor cleaner that removes tough stains and leaves a sparkling shine.',
                'description' => "{$floorClaims}\n\n{$floorIngredients}",
                'is_featured' => true,
                'variants' => [
                    ['size_label' => '200ml', 'mrp' => null],
                    ['size_label' => '500ml', 'mrp' => 90],
                    ['size_label' => '5L', 'mrp' => null],
                ],
                'images' => [
                    'NatureCare_UltraClean_FloorCleaner_Rose_200ml.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Rose_500ml.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Rose_500ml_Front.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Rose_500ml_Back.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Rose.jpeg',
                ],
            ],
            [
                'category' => 'floor-cleaner',
                'name' => 'Ultra Clean Floor Cleaner – Lavender',
                'short_description' => 'Lavender-fragranced floor cleaner that removes tough stains and leaves a sparkling shine.',
                'description' => "{$floorClaims}\n\n{$floorIngredients}",
                'variants' => [
                    ['size_label' => '200ml', 'mrp' => null],
                    ['size_label' => '500ml', 'mrp' => null],
                    ['size_label' => '5L', 'mrp' => null],
                ],
                'images' => [
                    'NatureCare_UltraClean_FloorCleaner_Lavender_200ml.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Lavender_500ml.jpeg',
                    'NatureCare_UltraClean_FloorCleaner_Lavender.jpeg',
                ],
            ],

            // Phenyl
            [
                'category' => 'phenyl',
                'name' => 'Phenyl – Black',
                'short_description' => 'Fresh natural fragrance floor phenyl for daily and heavy-duty cleaning.',
                'description' => 'Fresh natural fragrance floor phenyl. Suitable for floors, drains and outdoor cleaning.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_Phenyl_Black.jpeg'],
            ],
            [
                'category' => 'phenyl',
                'name' => 'Phenyl – Jasmine',
                'short_description' => 'Fresh natural fragrance floor phenyl for daily and heavy-duty cleaning.',
                'description' => 'Fresh natural fragrance floor phenyl. Suitable for floors, drains and outdoor cleaning.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_Phenyl_Jasmine.jpeg'],
            ],
            [
                'category' => 'phenyl',
                'name' => 'Phenyl – Lemon',
                'short_description' => 'Fresh natural fragrance floor phenyl for daily and heavy-duty cleaning.',
                'description' => 'Fresh natural fragrance floor phenyl. Suitable for floors, drains and outdoor cleaning.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_Phenyl_Lemon.jpeg'],
            ],
            [
                'category' => 'phenyl',
                'name' => 'Phenyl – Rose',
                'short_description' => 'Fresh natural fragrance floor phenyl for daily and heavy-duty cleaning.',
                'description' => 'Fresh natural fragrance floor phenyl. Suitable for floors, drains and outdoor cleaning.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_Phenyl_Rose.jpeg'],
            ],
            [
                'category' => 'phenyl',
                'name' => 'Phenyl – Lavender',
                'short_description' => 'Fresh natural fragrance floor phenyl for daily and heavy-duty cleaning.',
                'description' => 'Fresh natural fragrance floor phenyl. Suitable for floors, drains and outdoor cleaning.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_Phenyl_Lavender.jpeg'],
            ],
            [
                'category' => 'phenyl',
                'name' => 'Phenyl Compound Concentrate',
                'short_description' => 'Concentrated phenyl compound — dilute before use for bulk/institutional cleaning.',
                'description' => "Concentrate line — dilute before use.\n\nDesigned for institutional and commercial-scale floor and surface disinfection.",
                'is_commercial' => true,
                'variants' => [['size_label' => 'Concentrate', 'mrp' => null]],
                'images' => ['NatureCare_Phenyl_CompoundConcentrate.jpeg'],
            ],

            // Room Freshener
            [
                'category' => 'room-freshener',
                'name' => 'Room Freshener – Green Apple',
                'short_description' => 'Long-lasting Green Apple fragrance that instantly refreshes any room.',
                'description' => 'Safe for daily use, refreshes air instantly, removes bad odours and leaves a long-lasting fragrance.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_RoomFreshener_GreenApple.jpeg'],
            ],
            [
                'category' => 'room-freshener',
                'name' => 'Room Freshener – Brut',
                'short_description' => 'Long-lasting Brut fragrance that instantly refreshes any room.',
                'description' => 'Safe for daily use, refreshes air instantly, removes bad odours and leaves a long-lasting fragrance.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_RoomFreshener_Brut.jpeg'],
            ],
            [
                'category' => 'room-freshener',
                'name' => 'Room Freshener – Lemon',
                'short_description' => 'Long-lasting Lemon fragrance that instantly refreshes any room.',
                'description' => 'Safe for daily use, refreshes air instantly, removes bad odours and leaves a long-lasting fragrance.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_RoomFreshener_Lemon.jpeg'],
            ],
            [
                'category' => 'room-freshener',
                'name' => 'Room Freshener – Lavender',
                'short_description' => 'Long-lasting Lavender fragrance that instantly refreshes any room.',
                'description' => 'Instantly removes bad odours, long-lasting fragrance, safe for daily use.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_RoomFreshener_Lavender.jpeg'],
            ],
            [
                'category' => 'room-freshener',
                'name' => 'Room Freshener – Strawberry',
                'short_description' => 'Long-lasting Strawberry fragrance that instantly refreshes any room.',
                'description' => 'Safe for daily use, refreshes air instantly, removes bad odours and leaves a long-lasting fragrance.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_RoomFreshener_Strawberry.jpeg'],
            ],

            // Toilet & Bathroom Care
            [
                'category' => 'toilet-bathroom-care',
                'name' => 'Power Clean Toilet Cleaner',
                'short_description' => 'Kills 99.9% germs & bacteria and removes tough stains and scale.',
                'description' => "Kills 99.9% germs & bacteria, removes tough stains & scale, removes bad odour and is suitable for all toilet bowls. Does not affect septic tanks.\n\nKey ingredients: Hydrochloric acid, non-ionic surfactant, Acid Blue, water.",
                'is_featured' => true,
                'variants' => [
                    ['size_label' => '200ml', 'mrp' => null],
                    ['size_label' => '500ml', 'mrp' => 85],
                    ['size_label' => '5L', 'mrp' => null],
                ],
                'images' => [
                    'NatureCare_PowerClean_ToiletCleaner_200ml.jpeg',
                    'NatureCare_PowerClean_ToiletCleaner_500ml.jpeg',
                    'NatureCare_PowerClean_ToiletCleaner_500ml_Front.jpeg',
                    'NatureCare_PowerClean_ToiletCleaner_500ml_Back.jpeg',
                    'NatureCare_PowerClean_ToiletCleaner.jpeg',
                ],
            ],
            [
                'category' => 'toilet-bathroom-care',
                'name' => 'Bathroom Cleaner',
                'short_description' => 'Removes tough stains, dirt, limescale and water marks with a fresh long-lasting fragrance.',
                'description' => 'Removes tough stains & dirt, kills germs & bacteria, removes limescale & water marks and leaves a fresh long-lasting fragrance.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_BathroomCleaner.jpeg'],
            ],

            // Kitchen & Dish Care
            [
                'category' => 'kitchen-dish-care',
                'name' => 'Clean Wash Dish Washing Gel – Lemon',
                'short_description' => 'Cuts through tough oil and grease while staying gentle on hands.',
                'description' => 'Removes hard tough oil & grease, kills germs, removes odour, is soft on hands and gives an instant cleaning effect.',
                'is_featured' => true,
                'variants' => [
                    ['size_label' => '230ml', 'mrp' => 55],
                    ['size_label' => '5L', 'mrp' => null],
                ],
                'images' => [
                    'NatureCare_CleanWash_DishWashingGel_230ml.jpeg',
                    'NatureCare_CleanWash_DishWashingGel_230ml_Front.jpeg',
                    'NatureCare_CleanWash_DishWashingGel_230ml_Back.jpeg',
                    'NatureCare_CleanWash_DishWashingGel.jpeg',
                ],
            ],

            // Laundry Care
            [
                'category' => 'laundry-care',
                'name' => 'Pure Wash Detergent Liquid',
                'short_description' => 'Front & top load detergent liquid that removes tough stains and keeps clothes fresh.',
                'description' => "Suitable for front & top load machines as well as hand wash. Removes tough stains easily, keeps clothes fresh & bright and is gentle on fabrics.\n\nKey ingredients: Optical brighteners, quick stain remover.",
                'is_featured' => true,
                'variants' => [
                    ['size_label' => '500ml', 'mrp' => null],
                    ['size_label' => '1kg', 'mrp' => 225],
                    ['size_label' => '1L (Buy 1 Get 1)', 'mrp' => 450, 'selling' => 380],
                    ['size_label' => '5L', 'mrp' => null],
                ],
                'images' => [
                    'NatureCare_PureWash_DetergentLiquid_500ml.jpeg',
                    'NatureCare_PureWash_DetergentLiquid_1kg_Back.jpeg',
                    'NatureCare_PureWash_DetergentLiquid_1L_Offer.jpeg',
                    'NatureCare_PureWash_DetergentLiquid.jpeg',
                ],
            ],
            [
                'category' => 'laundry-care',
                'name' => 'Fabric Conditioner',
                'short_description' => 'Softens fabric and leaves a long-lasting floral fragrance.',
                'description' => 'Softens fabric and leaves a long-lasting floral fragrance. Suitable for machine and hand wash.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_FabricConditioner.jpeg'],
            ],
            [
                'category' => 'laundry-care',
                'name' => 'Fabric Whitener',
                'short_description' => 'Enhances fabric whiteness and removes yellowing without damaging fabric.',
                'description' => 'Fresh clean finish, enhances fabric whiteness, removes yellowing & dullness. Suitable for hand & machine wash.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_FabricWhitener.jpeg'],
            ],

            // Specialty Surface Cleaners
            [
                'category' => 'specialty-surface-cleaners',
                'name' => 'Tiles Cleaner',
                'short_description' => 'Deep-cleans tile surfaces and grout, restoring natural shine.',
                'description' => 'Removes tough stains & dirt, restores shine, leaves a pleasant fresh fragrance. Suitable for daily & heavy cleaning.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_TilesCleaner.jpeg'],
            ],
            [
                'category' => 'specialty-surface-cleaners',
                'name' => 'Glass Cleaner',
                'short_description' => 'Streak-free shine for glass, mirrors and windows.',
                'description' => 'Streak-free shine, removes grease, safe for home & office use.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_GlassCleaner.jpeg'],
            ],
            [
                'category' => 'specialty-surface-cleaners',
                'name' => 'Swimming Pool Cleaner',
                'short_description' => 'Fast-acting chlorine formula that keeps pool water crystal clear.',
                'description' => "Kills bacteria, viruses & algae, keeps pool water crystal clear. Fast-acting chlorine formula suitable for all pools.\n\nKey ingredient: Sodium Hypochlorite.",
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_SwimmingPoolCleaner.jpeg'],
            ],
            [
                'category' => 'specialty-surface-cleaners',
                'name' => 'Multipurpose Liquid',
                'short_description' => 'One liquid for floors, kitchen counters and household surfaces.',
                'description' => 'Removes dirt, grease & stains with a fresh long-lasting fragrance. Suitable for multiple surfaces, home & commercial use.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_MultipurposeLiquid.jpeg'],
            ],
            [
                'category' => 'specialty-surface-cleaners',
                'name' => 'Soap Oil',
                'short_description' => 'Concentrated cleaning oil with high cleaning efficiency.',
                'description' => 'High cleaning efficiency with stable & consistent quality. Ideal for commercial and institutional use.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_SoapOil.jpeg'],
            ],

            // Personal Care
            [
                'category' => 'personal-care',
                'name' => 'Hand Sanitizer',
                'short_description' => 'Kills 99.9% germs without water — quick-dry, non-sticky formula.',
                'description' => 'Kills 99.9% germs, no water required, quick dry formula. Non-sticky & skin friendly.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_HandSanitizer.jpeg'],
            ],
            [
                'category' => 'personal-care',
                'name' => 'Hand Wash',
                'short_description' => 'Rich foaming hand wash that removes dirt and germs.',
                'description' => 'Removes dirt & germs, soft & gentle on skin, rich foaming formula with a fresh & long-lasting fragrance.',
                'is_commercial' => true,
                'variants' => [['size_label' => '5L', 'mrp' => null]],
                'images' => ['NatureCare_HandWash.jpeg'],
            ],

            // Feminine Hygiene
            [
                'category' => 'feminine-hygiene',
                'name' => 'Sanitary Napkins (Anion)',
                'short_description' => 'Superior quality, dioxin-free napkins with an ultra soft cotton layer.',
                'description' => "Superior quality napkins, 100% safe & hygienic, individually wrapped in non-woven packaging.\n\nKey features: Dioxin free, 5-in-1 feature, ultra soft & cotton layer.",
                'variants' => [
                    ['size_label' => '240mm (12 pads)', 'mrp' => null],
                    ['size_label' => '290mm (12 pads)', 'mrp' => null],
                    ['size_label' => '330mm (12 pads)', 'mrp' => null],
                ],
                'images' => [
                    'NatureCare_SanitaryNapkins_01.jpeg',
                    'NatureCare_SanitaryNapkins_02.jpeg',
                ],
            ],
        ];
    }
}
