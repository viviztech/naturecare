<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('pages.products.index');
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'media', 'activeVariants']);

        return view('pages.products.show', [
            'product' => $product,
            'relatedProducts' => $product->relatedProducts(),
        ]);
    }
}
