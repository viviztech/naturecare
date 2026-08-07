<?php

namespace Tests\Feature;

use App\Enums\CouponType;
use App\Livewire\CartPage;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CouponApplyTest extends TestCase
{
    use RefreshDatabase;

    protected function seedCartWithVariant(int $sellingPricePaise = 20000): ProductVariant
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();
        $variant = ProductVariant::factory()->for($product)->create([
            'stock_qty' => 10,
            'selling_price' => $sellingPricePaise,
        ]);

        app(CartService::class)->addItem($variant, 1);

        return $variant;
    }

    public function test_flat_coupon_applies_fixed_discount(): void
    {
        $this->seedCartWithVariant(20000);

        $coupon = Coupon::factory()->create([
            'code' => 'FLAT50',
            'type' => CouponType::Flat,
            'value' => 5000,
        ]);

        Livewire::test(CartPage::class)
            ->set('couponCode', 'FLAT50')
            ->call('applyCoupon')
            ->assertSet('couponSuccess', true);

        $cart = app(CartService::class)->current();

        $this->assertSame($coupon->id, $cart->coupon_id);
        $this->assertSame(5000, $cart->discount_total->paise());
        $this->assertSame(15000, $cart->total->paise());
    }

    public function test_percent_coupon_respects_max_discount_cap(): void
    {
        $this->seedCartWithVariant(20000);

        Coupon::factory()->create([
            'code' => 'PERCENT50',
            'type' => CouponType::Percent,
            'value' => 50, // would be 10000 uncapped
            'max_discount_amount' => 3000,
        ]);

        Livewire::test(CartPage::class)
            ->set('couponCode', 'PERCENT50')
            ->call('applyCoupon');

        $cart = app(CartService::class)->current();

        $this->assertSame(3000, $cart->discount_total->paise());
    }

    public function test_coupon_below_minimum_cart_value_is_rejected(): void
    {
        $this->seedCartWithVariant(2000);

        Coupon::factory()->create([
            'code' => 'BIGSPEND',
            'min_cart_value' => 10000,
        ]);

        Livewire::test(CartPage::class)
            ->set('couponCode', 'BIGSPEND')
            ->call('applyCoupon')
            ->assertSet('couponSuccess', false);

        $this->assertNull(app(CartService::class)->current()->coupon_id);
    }

    public function test_expired_coupon_is_rejected(): void
    {
        $this->seedCartWithVariant(20000);

        Coupon::factory()->expired()->create(['code' => 'OLDCODE']);

        Livewire::test(CartPage::class)
            ->set('couponCode', 'OLDCODE')
            ->call('applyCoupon')
            ->assertSet('couponSuccess', false);
    }

    public function test_nonexistent_coupon_code_is_rejected(): void
    {
        $this->seedCartWithVariant(20000);

        Livewire::test(CartPage::class)
            ->set('couponCode', 'DOESNOTEXIST')
            ->call('applyCoupon')
            ->assertSet('couponSuccess', false);
    }

    public function test_removing_coupon_clears_discount(): void
    {
        $this->seedCartWithVariant(20000);

        Coupon::factory()->create(['code' => 'FLAT50', 'type' => CouponType::Flat, 'value' => 5000]);

        Livewire::test(CartPage::class)
            ->set('couponCode', 'FLAT50')
            ->call('applyCoupon')
            ->call('removeCoupon');

        $cart = app(CartService::class)->current();

        $this->assertNull($cart->coupon_id);
        $this->assertSame(0, $cart->discount_total->paise());
    }
}
