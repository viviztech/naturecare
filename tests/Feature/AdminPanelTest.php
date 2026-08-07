<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_admin_panel(): void
    {
        $this->get('/admin/categories')->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_can_view_categories(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/categories')->assertOk();
        $this->get('/admin/categories/create')->assertOk();
    }

    public function test_authenticated_admin_can_view_products(): void
    {
        $this->actingAs(User::factory()->create());

        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        $this->get('/admin/products')->assertOk();
        $this->get('/admin/products/create')->assertOk();
        $this->get("/admin/products/{$product->id}/edit")->assertOk();
    }

    public function test_authenticated_admin_can_view_business_enquiries(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/business-enquiries')->assertOk();
    }

    public function test_authenticated_admin_can_view_contact_enquiries(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/contact-enquiries')->assertOk();
    }

    public function test_authenticated_admin_can_view_settings_page(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/settings')->assertOk();
    }

    public function test_authenticated_admin_can_view_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertOk();
    }
}
