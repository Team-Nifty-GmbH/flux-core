<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Actions\Product\DeleteProduct;
use FluxErp\Actions\Product\UpdateProduct;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Htmlables\TabButton;
use FluxErp\Models\Currency;
use FluxErp\Models\PriceList;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Models\VatRate;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use WireUi\Traits\Actions;

class Product extends Component
{
    use Actions, WithTabs;

    public array $product;

    public ?array $priceLists = null;

    public ?array $productCrossSellings = null;

    public array $additionalColumns = [];

    public ?array $currency = null;

    #[Url]
    public string $tab = 'product.general';

    public function mount(int $id): void
    {
        $product = \FluxErp\Models\Product::query()
            ->whereKey($id)
            ->with([
                'categories:id',
                'tags:id',
                'bundleProducts:id',
                'vatRate:id,rate_percentage',
                'parent',
                'coverMedia',
            ])
            ->withCount('children')
            ->firstOrFail();
        $product->append('avatar_url');

        $parent = $product->parent;
        $this->product = $product->toArray();

        $this->product['parent'] = $parent ? [
            'label' => $parent->getLabel(),
            'url' => $parent->getUrl(),
        ] : null;

        $this->product['categories'] = $product->categories->pluck('id')->toArray();
        $this->product['tags'] = $product->tags->pluck('id')->toArray();
        $this->product['bundle_products'] = $product->bundleProducts->map(function ($bundleProduct) {
            return [
                'count' => $bundleProduct->pivot->count,
                'id' => $bundleProduct->pivot->product_id,
            ];
        });

        $this->currency = Currency::query()
            ->where('is_default', true)
            ->first(['id', 'name', 'symbol', 'iso'])
            ->toArray();

        $this->additionalColumns = $product->getAdditionalColumns()->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.product.product', [
            'vatRates' => $this->vatRates(),
        ]);
    }

    #[Computed]
    public function vatRates(): array
    {
        return VatRate::all(['id', 'name', 'rate_percentage'])->toArray();
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('product.general')->label(__('General')),
            TabButton::make('product.variants')->label(__('Variants'))
                ->when($this->product['children_count'] ?? false),
            TabButton::make('product.prices')->label(__('Prices')),
            TabButton::make('product.stock')->label(__('Stock')),
            TabButton::make('product.media')->label(__('Media')),
            TabButton::make('product.cross-selling')->label(__('Cross Selling')),
        ];
    }

    public function save(): bool
    {
        $this->skipRender();
        $action = ($this->product['id'] ?? false) ? UpdateProduct::class : CreateProduct::class;

        if ($this->priceLists !== null) {
            $this->product['prices'] = collect($this->priceLists)
                ->filter(fn ($priceList) => ($priceList['price_net'] !== null || $priceList['price_gross'] !== null)
                    && $priceList['is_editable']
                )
                ->map(function (array $priceList) {
                    return [
                        'price_list_id' => $priceList['id'],
                        'price' => $priceList['is_net'] ? $priceList['price_net'] : $priceList['price_gross'],
                    ];
                })
                ->toArray();
        }

        if ($this->productCrossSellings !== null) {
            $this->product['product_cross_sellings'] = array_map(function (array $productCrossSelling) {
                $productCrossSelling['products'] = array_map(
                    fn (array $product) => $product['id'],
                    $productCrossSelling['products']
                );

                return $productCrossSelling;
            }, $this->productCrossSellings);
        }

        try {
            $product = $action::make($this->product)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->product['id'] = $product->id;
        $this->notification()->success(__('Product saved successfully.'));

        return true;
    }

    public function getPriceLists(): void
    {
        $priceLists = PriceList::query()
            ->with('parent')
            ->get(['id', 'parent_id', 'name', 'price_list_code', 'is_net', 'is_default']);
        $product = \FluxErp\Models\Product::query()->whereKey($this->product['id'])->first();
        $priceListHelper = PriceHelper::make($product)->useDefault(false);

        $priceLists->map(function (PriceList $priceList) use ($priceListHelper) {
            $price = $priceListHelper
                ->setPriceList($priceList)
                ->price();
            $priceList->price_net = $price
                ?->getNet($this->product['vat_rate']['rate_percentage'] ?? 0);
            $priceList->price_gross = $price
                ?->getGross($this->product['vat_rate']['rate_percentage'] ?? 0);
            $priceList->is_editable = is_null($price) || $price?->price_list_id === $priceList->id;
        });

        $this->priceLists = $priceLists->toArray();

        $this->skipRender();
    }

    public function getProductCrossSellings(): void
    {
        $this->productCrossSellings = ProductCrossSelling::query()
            ->where('product_id', $this->product['id'])
            ->with('products:id,name,product_number')
            ->get()
            ->toArray();

        $this->skipRender();
    }

    public function delete(): false|Redirector
    {
        $this->skipRender();

        try {
            DeleteProduct::make($this->product)
                ->checkPermission()
                ->validate()
                ->execute();

            return redirect()->route('products.products');
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        return false;
    }
}
