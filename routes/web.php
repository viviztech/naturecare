<?php

use App\Http\Controllers\Admin\OrderInvoiceController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderSuccessController;
use App\Http\Controllers\OrderTrackController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/partner', [PartnerController::class, 'index'])->name('partner.index');
Route::get('/partner/thank-you', [PartnerController::class, 'thankYou'])->name('partner.thank-you');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/track', [OrderTrackController::class, 'index'])->name('order.track');
Route::get('/orders/{order:order_number}/success', [OrderSuccessController::class, 'show'])
    ->middleware('signed')
    ->name('orders.success');

Route::post('/webhooks/razorpay', RazorpayWebhookController::class)->name('webhooks.razorpay');

// Admin-only GST invoice download, linked to from the Orders Filament
// resource. A plain route (not a Filament/Livewire action) so the browser
// gets a real Content-Disposition file download rather than an AJAX
// response. Guarded by the same 'web' auth guard Filament's default panel
// uses, so only logged-in admin users can reach it.
Route::middleware('auth')->get('/admin/orders/{order:order_number}/invoice', OrderInvoiceController::class)
    ->name('admin.orders.invoice');
