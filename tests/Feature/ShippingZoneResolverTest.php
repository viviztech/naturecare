<?php

namespace Tests\Feature;

use App\Models\ShippingZone;
use App\Services\ShippingZoneResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingZoneResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_pincode_matching_a_zone_prefix(): void
    {
        ShippingZone::factory()->create(['pincode_prefix' => '6', 'name' => 'Tamil Nadu']);

        $zone = app(ShippingZoneResolver::class)->resolve('600001');

        $this->assertNotNull($zone);
        $this->assertSame('Tamil Nadu', $zone->name);
    }

    public function test_longest_matching_prefix_wins_over_broader_prefix(): void
    {
        ShippingZone::factory()->create(['pincode_prefix' => '6', 'name' => 'Tamil Nadu (broad)', 'shipping_charge' => 9900]);
        ShippingZone::factory()->create(['pincode_prefix' => '600', 'name' => 'Chennai (specific)', 'shipping_charge' => 2900]);

        $zone = app(ShippingZoneResolver::class)->resolve('600028');

        $this->assertSame('Chennai (specific)', $zone->name);
    }

    public function test_returns_null_for_unserviceable_pincode(): void
    {
        ShippingZone::factory()->create(['pincode_prefix' => '6']);

        $zone = app(ShippingZoneResolver::class)->resolve('110001');

        $this->assertNull($zone);
    }

    public function test_ignores_inactive_zones(): void
    {
        ShippingZone::factory()->create(['pincode_prefix' => '6', 'is_active' => false]);

        $zone = app(ShippingZoneResolver::class)->resolve('600001');

        $this->assertNull($zone);
    }

    public function test_is_serviceable_helper(): void
    {
        ShippingZone::factory()->create(['pincode_prefix' => '6']);

        $resolver = app(ShippingZoneResolver::class);

        $this->assertTrue($resolver->isServiceable('600001'));
        $this->assertFalse($resolver->isServiceable('700001'));
    }
}
