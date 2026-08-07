<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Products\RelationManagers\ProductVariantsRelationManager;
use App\Filament\Resources\ShippingZones\Pages\EditShippingZone;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MoneyFieldRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipping_zone_edit_form_loads_without_error(): void
    {
        $this->actingAs(User::factory()->create());

        $zone = ShippingZone::factory()->create([
            'shipping_charge' => Money::fromRupees(60),
            'free_shipping_above' => Money::fromRupees(999),
        ]);

        Livewire::test(EditShippingZone::class, ['record' => $zone->getKey()])
            ->assertFormSet([
                'shipping_charge' => 60.0,
                'free_shipping_above' => 999.0,
            ])
            ->assertOk();

        $this->get("/admin/shipping-zones/{$zone->getKey()}/edit")->assertOk();
    }

    public function test_shipping_zone_edit_form_can_save(): void
    {
        $this->actingAs(User::factory()->create());

        $zone = ShippingZone::factory()->create([
            'shipping_charge' => Money::fromRupees(60),
        ]);

        Livewire::test(EditShippingZone::class, ['record' => $zone->getKey()])
            ->fillForm(['shipping_charge' => 75])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(7500, $zone->refresh()->getRawOriginal('shipping_charge'));
    }

    public function test_product_variants_relation_manager_edit_form_loads_without_error(): void
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create([
            'mrp' => Money::fromRupees(199),
            'selling_price' => Money::fromRupees(179),
        ]);

        Livewire::test(ProductVariantsRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => \App\Filament\Resources\Products\Pages\EditProduct::class,
        ])
            ->mountTableAction('edit', $variant)
            ->assertTableActionDataSet([
                'mrp' => 199.0,
                'selling_price' => 179.0,
            ]);
    }
}
