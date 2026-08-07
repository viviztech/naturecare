<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    #[Url(as: 'category', history: true)]
    public string $categorySlug = '';

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategorySlug(): void
    {
        $this->resetPage();
    }

    public function selectCategory(string $slug): void
    {
        $this->categorySlug = $this->categorySlug === $slug ? '' : $slug;
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()
            ->active()
            ->with(['category', 'media', 'activeVariants'])
            ->when($this->categorySlug, fn ($query) => $query->whereHas(
                'category',
                fn ($q) => $q->where('slug', $this->categorySlug)
            ))
            ->search($this->search)
            ->ordered()
            ->paginate(12);

        return view('livewire.product-catalog', [
            'products' => $products,
            'categories' => Category::query()->active()->ordered()->get(),
        ]);
    }
}
