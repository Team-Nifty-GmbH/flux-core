<?php

namespace FluxErp\Http\Livewire\Product;

use FluxErp\Http\Requests\CreateProductRequest;
use FluxErp\Http\Requests\UpdateProductRequest;
use FluxErp\Models\Currency;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use FluxErp\Services\ProductService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use WireUi\Traits\Actions;

class Product extends Component
{
    use Actions;

    public array $product;

    public ?array $priceLists = null;

    public ?array $currency = null;

    public array $vatRates = [];

    public string $tab = 'general';

    protected $queryString = [
        'tab' => ['except' => 'general'],
    ];

    public function mount(int $id): void
    {
        $product = \FluxErp\Models\Product::query()
            ->whereKey($id)
            ->with([
                'categories:id',
                'tags:id',
                'bundleProducts:id',
                'vatRate:id,rate_percentage'
            ])
            ->first();

        $this->product = $product->toArray();

        $this->product['categories'] = $product->categories->pluck('id')->toArray();
        $this->product['tags'] = $product->tags->pluck('id')->toArray();
        $this->product['bundle_products'] = $product->bundleProducts->map(function ($bundleProduct) {
            return [
                'count' => $bundleProduct->pivot->count,
                'id' => $bundleProduct->pivot->product_id,
            ];
        });

        $this->vatRates = VatRate::all(['id', 'name', 'rate_percentage'])->toArray();

        $this->currency = Currency::query()
            ->where('is_default', true)
            ->first(['id', 'name', 'symbol', 'iso'])
            ->toArray();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.product.product');
    }

    public function save(): bool
    {
        if ($this->priceLists !== null) {
            $this->product['prices'] = collect($this->priceLists)
                ->filter(fn ($priceList) => $priceList['price_net'] !== null || $priceList['price_gross'] !== null)
                ->map(function(array $priceList) {
                    return [
                        'price_list_id' => $priceList['id'],
                        'price' => $priceList['is_net'] ? $priceList['price_net'] : $priceList['price_gross'],
                    ];
                })
                ->toArray();
        }
        $validator = Validator::make(
            $this->product,
            $this->product['id']
                ? (new UpdateProductRequest())->rules()
                : (new CreateProductRequest())->rules()
        );
        $validated = $validator->validate();

        $service = new ProductService();
        if ($this->product['id']) {
            $service->update($validated);;
        } else {
            $service->create($validated);
        }

        $this->notification()->success(__('Product saved'));
        $this->skipRender();

        return true;
    }

    public function getPriceLists(): void
    {
        $priceLists =  PriceList::all(['id', 'name', 'price_list_code', 'is_net', 'is_default']);
        $productPrices = Price::query()->where('product_id', $this->product['id'])->get();
        $priceLists->map(function($priceList) use ($productPrices) {
            $priceList->price_net = $productPrices->where('price_list_id', $priceList->id)
                ->first()
                ?->getNet($this->product['vat_rate']['rate_percentage']) ?? null;
            $priceList->price_gross = $productPrices->where('price_list_id', $priceList->id)
                ->first()
                ?->getGross($this->product['vat_rate']['rate_percentage']) ?? null;
        });

        $this->priceLists = $priceLists->toArray();

        $this->skipRender();
    }
}
