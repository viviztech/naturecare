<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $categories = Category::query()->active()->ordered()
            ->with(['activeProducts.media'])
            ->get();

        $featuredProducts = Product::query()
            ->active()
            ->featured()
            ->with(['category', 'media'])
            ->ordered()
            ->limit(8)
            ->get();

        return view('pages.home', compact('categories', 'featuredProducts'));
    }
}
