<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Models\Category;
use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class Categories extends Component
{
    public function render(): View
    {
        return view('flux::livewire.portal.shop.categories', [
            'categories' => $this->categories,
        ]);
    }

    public function placeholder(): string
    {
        return <<<'Blade'
            <div class="min-w-96">
                <x-card>
                    @include('flux::livewire.placeholders.horizontal-bar')
                </x-card>
            </div>
        Blade;

    }

    #[Computed(persist: true, seconds: 60 * 60 * 24, cache: true)]
    public function categories(): Collection
    {
        Category::addGlobalScope('children', function ($query) {
            $query->with(['children' => fn (HasMany $query) => $query->whereHas('products', fn ($query) => $query->webshop())
                ->withCount([
                    'children' => fn ($query) => $query->whereHas('products', fn ($query) => $query->webshop()),
                ]),
            ]);
        });

        return app(Category::class)
            ->whereNull('parent_id')
            ->withCount(['children' => fn ($query) => $query->whereHas('products', fn ($query) => $query->webshop())])
            ->whereHas('products', fn ($query) => $query->webshop())
            ->where('model_type', morph_alias(Product::class))
            ->get();
    }
}
