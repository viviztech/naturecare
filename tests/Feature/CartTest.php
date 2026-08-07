<?php

namespace Tests\Feature;

use App\Livewire\AddToCart;
use App\Livewire\CartPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function makeVariant(array $overrides = []): ProductVariant
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        return ProductVariant::factory()->for($product)->create($overrides);
    }

    public function test_adding_a_variant_to_cart_creates_cart_and_item(): void
    {
        $variant = $this->makeVariant(['stock_qty' => 10]);

        Livewire::test(AddToCart::class, ['product' => $variant->product])
            ->set('variantId', $variant->id)
            ->call('addToCart')
            ->assertHasNoErrors();

        $this->assertSame(1, app(CartService::class)->count());
    }

    public function test_adding_the_same_variant_twice_increments_quantity_not_duplicates_row(): void
    {
        $variant = $this->makeVariant(['stock_qty' => 10]);

        $component = Livewire::test(AddToCart::class, ['product' => $variant->product])
            ->set('variantId', $variant->id)
            ->call('addToCart');

        $component->call('addToCart');

        $cart = app(CartService::class)->current();

        $this->assertSame(1, $cart->items()->count());
        $this->assertSame(2, $cart->items()->first()->qty);
    }

    public function test_cannot_add_out_of_stock_variant(): void
    {
        $variant = $this->makeVariant(['stock_qty' => 0]);

        Livewire::test(AddToCart::class, ['product' => $variant->product])
            ->set('variantId', $variant->id)
            ->call('addToCart')
            ->assertHasErrors(['variant']);

        $this->assertSame(0, app(CartService::class)->count());
    }

    public function test_cart_page_updates_quantity(): void
    {
        $variant = $this->makeVariant(['stock_qty' => 10]);
        app(CartService::class)->addItem($variant, 1);
        $itemId = app(CartService::class)->current()->items->first()->id;

        Livewire::test(CartPage::class)
            ->call('updateQty', $itemId, 3);

        $this->assertSame(3, app(CartService::class)->current()->items->first()->qty);
    }

    public function test_cart_page_removes_item(): void
    {
        $variant = $this->makeVariant(['stock_qty' => 10]);
        app(CartService::class)->addItem($variant, 1);
        $itemId = app(CartService::class)->current()->items->first()->id;

        Livewire::test(CartPage::class)
            ->call('removeItem', $itemId);

        $this->assertTrue(app(CartService::class)->current()->isEmpty());
    }

    public function test_recalculate_sums_line_totals_into_subtotal_and_total(): void
    {
        $variantA = $this->makeVariant(['stock_qty' => 10, 'selling_price' => 10000]);
        $variantB = $this->makeVariant(['stock_qty' => 10, 'selling_price' => 5000]);

        $cartService = app(CartService::class);
        $cartService->addItem($variantA, 2); // 20000
        $cartService->addItem($variantB, 1); // 5000

        $cart = $cartService->current();

        $this->assertSame(25000, $cart->subtotal->paise());
        $this->assertSame(25000, $cart->total->paise());
    }
}
