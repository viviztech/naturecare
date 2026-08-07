<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        $this->get(route('home'))->assertOk();
    }

    public function test_products_index_loads(): void
    {
        $this->get(route('products.index'))->assertOk();
    }

    public function test_product_detail_page_loads(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        $this->get(route('products.show', $product))->assertOk();
    }

    public function test_inactive_product_returns_404(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create(['is_active' => false]);

        $this->get(route('products.show', $product))->assertNotFound();
    }

    public function test_partner_page_loads(): void
    {
        $this->get(route('partner.index'))->assertOk();
    }

    public function test_partner_thank_you_page_loads(): void
    {
        $this->get(route('partner.thank-you'))->assertOk();
    }

    public function test_about_page_loads(): void
    {
        $this->get(route('about'))->assertOk();
    }

    public function test_contact_page_loads(): void
    {
        $this->get(route('contact'))->assertOk();
    }

    public function test_sitemap_loads(): void
    {
        $response = $this->get(route('sitemap'))->assertOk();

        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }
}
