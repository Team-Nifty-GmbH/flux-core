<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Models\Product;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Lazy]
class ProductList extends Component
{
    use WithPagination;

    public ?string $search = null;

    public ?string $orderBy = 'is_highlight';

    #[Url]
    public ?int $category = null;

    public function render(): View
    {
        return view('flux::livewire.portal.shop.product-list', [
            'products' => $this->getProducts(),
        ]);
    }

    public function updatedSearch(): void
    {
        $this->category = null;
        $this->setPage(1);
    }

    protected function getProducts(): LengthAwarePaginator
    {
        if (! $this->search) {
            $builder = resolve_static(Product::class, 'query');
        } else {
            $builder = resolve_static(Product::class, 'search', ['query' => $this->search])
                ->toEloquentBuilder();
        }

        $result = $builder
            ->webshop()
            ->when(! $this->search, fn (Builder $query) => $query->whereNull('parent_id'))
            ->when($this->orderBy, function (Builder $query, string $orderBy) {
                $query->orderByDesc($orderBy);
            })
            ->orderByDesc('id')
            ->when($this->category, function (Builder $query, int $category) {
                $query->whereHas('categories', function (Builder $query) use ($category) {
                    $query->where('category_id', $category);
                });
            })
            ->withCount('children')
            ->paginate(perPage: 18);

        $result->getCollection()
            ->transform(function (Product $product) {
                $product->append('price');
                $productArray = $product->toArray();
                $productArray['cover_url'] = ($product->coverMedia ?? $product->parent?->coverMedia)
                    ?->getUrl('thumb_280x280') ?? route('icons', ['name' => 'photo', 'variant' => 'outline']);

                if (auth()->user()->can(route_to_permission('portal.checkout'))) {
                    $productArray['price'] = $product->price->only([
                        'price',
                        'root_price_flat',
                        'root_discount_percentage',
                    ]);
                } else {
                    $productArray['price'] = [];
                }

                return $productArray;
            });

        return $result;
    }
}
