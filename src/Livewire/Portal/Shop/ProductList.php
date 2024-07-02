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
            $builder = app(Product::class)->query();
        } else {
            $builder = app(Product::class)->search($this->search)->toEloquentBuilder();
        }

        $result = $builder
            ->webshop()
            ->when($this->orderBy, function (Builder $query, string $orderBy) {
                $query->orderByDesc($orderBy);
            })
            ->orderByDesc('id')
            ->when($this->category, function (Builder $query, int $category) {
                $query->whereHas('categories', function (Builder $query) use ($category) {
                    $query->where('category_id', $category);
                });
            })
            ->paginate(perPage: 18);

        $result->getCollection()
            ->transform(function (Product $product) {
                $product->append('price');
                $productArray = $product->toArray();
                $productArray['cover_url'] = ($product->coverMedia ?? $product->parent?->coverMedia)
                    ?->getUrl('thumb_280x280') ?? route('icons', ['name' => 'photo', 'variant' => 'outline']);
                $productArray['price'] = $product->price->only([
                    'price',
                    'root_price_flat',
                    'root_discount_percentage',
                ]);

                return $productArray;
            });

        return $result;
    }
}
