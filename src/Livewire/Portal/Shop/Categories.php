<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Models\Category;
use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
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
    public function categories()
    {
        Category::addGlobalScope('children', function ($query) {
            $query->with(['children' => function (HasMany $query) {
                $query->whereHas('products', function ($query) {
                    $query->webshop();
                })->withCount(['children' => fn ($query) => $query->whereHas('products', function ($query) {
                    $query->webshop();
                })]);
            }]);
        });

        return app(Category::class)
            ->whereNull('parent_id')
            ->withCount(['children' => fn ($query) => $query->whereHas('products', function ($query) {
                $query->webshop();
            })])
            ->whereHas('products', function ($query) {
                $query->webshop();
            })
            ->where('model_type', Relation::getMorphAlias(Product::class))
            ->get();
    }

    public function loadChildren(Category $category)
    {
        $category->load('children');

        return $category->children?->toArray();
    }
}
