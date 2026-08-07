<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $sitemap = Sitemap::create()
            ->add(Url::create(route('home'))->setPriority(1.0))
            ->add(Url::create(route('products.index'))->setPriority(0.9))
            ->add(Url::create(route('partner.index'))->setPriority(0.8))
            ->add(Url::create(route('about'))->setPriority(0.5))
            ->add(Url::create(route('contact'))->setPriority(0.5));

        Product::query()
            ->active()
            ->select(['slug', 'updated_at'])
            ->each(function (Product $product) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('products.show', $product))
                        ->setLastModificationDate($product->updated_at)
                        ->setPriority(0.7)
                );
            });

        return response($sitemap->render(), 200, ['Content-Type' => 'application/xml']);
    }
}
