<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Livewire\Checkout;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutCodTest extends TestCase
{
    use RefreshDatabase;

    protected function seedCartWithVariant(int $stock = 10): ProductVariant
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();
        $variant = ProductVariant::factory()->for($product)->create([
            'stock_qty' => $stock,
            'selling_price' => 20000,
            'mrp' => 20000,
        ]);

        app(CartService::class)->addItem($variant, 2);

        return $variant;
    }

    protected function seedTamilNaduZone(bool $codAvailable = true): ShippingZone
    {
        return ShippingZone::factory()->create([
            'pincode_prefix' => '6',
            'name' => 'Tamil Nadu',
            'shipping_charge' => 4900,
            'cod_available' => $codAvailable,
            'free_shipping_above' => null,
        ]);
    }

    public function test_full_cod_checkout_places_order_and_decrements_stock(): void
    {
        Mail::fake();
        RateLimiter::clear('checkout:127.0.0.1');

        $variant = $this->seedCartWithVariant(10);
        $this->seedTamilNaduZone();

        Livewire::test(Checkout::class)
            ->set('name', 'Ramesh Kumar')
            ->set('mobile', '9876543210')
            ->set('email', 'ramesh@example.com')
            ->call('goToAddress')
            ->assertSet('step', 2)
            ->set('line1', '12 Anna Nagar')
            ->set('city', 'Chennai')
            ->set('pincode', '600001')
            ->call('goToPayment')
            ->assertSet('step', 3)
            ->set('paymentMethod', 'cod')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'contact_name' => 'Ramesh Kumar',
            'payment_method' => PaymentMethod::Cod->value,
            'order_status' => OrderStatus::Placed->value,
        ]);

        $this->assertSame(8, $variant->fresh()->stock_qty);
        $this->assertTrue(app(CartService::class)->current()?->isEmpty() ?? true);
    }

    public function test_cod_is_hidden_when_zone_does_not_support_it(): void
    {
        $this->seedCartWithVariant(10);
        $this->seedTamilNaduZone(codAvailable: false);

        $component = Livewire::test(Checkout::class)
            ->set('name', 'Ramesh Kumar')
            ->set('mobile', '9876543210')
            ->call('goToAddress')
            ->set('line1', '12 Anna Nagar')
            ->set('city', 'Chennai')
            ->set('pincode', '600001')
            ->call('goToPayment');

        $component->assertSet('codAvailable', false);
        $component->assertSet('paymentMethod', 'razorpay');
    }

    public function test_unserviceable_pincode_blocks_progress_to_payment(): void
    {
        $this->seedCartWithVariant(10);
        $this->seedTamilNaduZone();

        Livewire::test(Checkout::class)
            ->set('name', 'Ramesh Kumar')
            ->set('mobile', '9876543210')
            ->call('goToAddress')
            ->set('line1', '12 MG Road')
            ->set('city', 'Delhi')
            ->set('pincode', '110001')
            ->call('goToPayment')
            ->assertSet('step', 2)
            ->assertHasErrors(['pincode']);
    }

    public function test_checkout_is_rate_limited(): void
    {
        Mail::fake();
        RateLimiter::clear('checkout:127.0.0.1');

        $this->seedTamilNaduZone();

        for ($i = 0; $i < 5; $i++) {
            $this->seedCartWithVariant(10);

            Livewire::test(Checkout::class)
                ->set('name', 'Ramesh Kumar')
                ->set('mobile', '9876543210')
                ->call('goToAddress')
                ->set('line1', '12 Anna Nagar')
                ->set('city', 'Chennai')
                ->set('pincode', '600001')
                ->call('goToPayment')
                ->set('paymentMethod', 'cod')
                ->call('placeOrder');
        }

        $this->seedCartWithVariant(10);

        Livewire::test(Checkout::class)
            ->set('name', 'Ramesh Kumar')
            ->set('mobile', '9876543210')
            ->call('goToAddress')
            ->set('line1', '12 Anna Nagar')
            ->set('city', 'Chennai')
            ->set('pincode', '600001')
            ->call('goToPayment')
            ->set('paymentMethod', 'cod')
            ->call('placeOrder')
            ->assertHasErrors(['form']);

        $this->assertSame(5, \App\Models\Order::query()->count());
    }

    public function test_invalid_mobile_number_fails_validation(): void
    {
        $this->seedCartWithVariant(10);

        Livewire::test(Checkout::class)
            ->set('name', 'Ramesh Kumar')
            ->set('mobile', '12345')
            ->call('goToAddress')
            ->assertHasErrors(['mobile']);
    }
}
