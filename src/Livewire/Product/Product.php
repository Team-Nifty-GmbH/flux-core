<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Models\Contact;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product as ProductModel;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Models\VatRate;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

class Product extends Component
{
    use Actions, WithTabs;

    public ProductForm $product;

    public ?array $priceLists = null;

    public ?array $productCrossSellings = null;

    public array $additionalColumns = [];

    public ?array $currency = null;

    #[Url]
    public string $tab = 'product.general';

    public function mount(int $id): void
    {
        $product = app(ProductModel::class)->query()
            ->whereKey($id)
            ->with([
                'categories:id',
                'tags:id',
                'bundleProducts:id',
                'vatRate:id,rate_percentage',
                'parent',
                'coverMedia',
                'clients:id',
            ])
            ->withCount('children')
            ->firstOrFail();
        $product->append('avatar_url');

        $this->product->fill($product);

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
        return app(VatRate::class)->all(['id', 'name', 'rate_percentage'])->toArray();
    }

    #[Renderless]
    public function addTag(string $name): void
    {
        try {
            $tag = CreateTag::make([
                'name' => $name,
                'type' => app(ProductModel::class)->getMorphClass(),
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->product->tags[] = $tag->id;
        $this->js(<<<'JS'
            edit = true;
        JS);
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('product.general')->label(__('General')),
            TabButton::make('product.variant-list')
                ->label(__('Variants'))
                ->isLivewireComponent()
                ->wireModel('product')
                ->when(fn () => ! $this->product->parent_id && ! $this->product->is_bundle),
            TabButton::make('product.bundle-list')
                ->label(__('Bundle'))
                ->isLivewireComponent()
                ->wireModel('product')
                ->when(fn () => ! $this->product->children_count),
            TabButton::make('product.prices')->label(__('Prices')),
            TabButton::make('product.warehouse-list')
                ->label(__('Stock'))
                ->isLivewireComponent()
                ->wireModel('product'),
            TabButton::make('product.media')->label(__('Media')),
            TabButton::make('product.cross-selling')->label(__('Cross Selling')),
            TabButton::make('product.activities')
                ->label(__('Activities'))
                ->isLivewireComponent()
                ->wireModel('product'),
        ];
    }

    public function save(): bool
    {
        if ($this->priceLists !== null) {
            $this->product->prices = collect($this->priceLists)
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
            $this->product->product_cross_sellings = array_map(function (array $productCrossSelling) {
                $productCrossSelling['products'] = array_map(
                    fn (array $product) => $product['id'],
                    $productCrossSelling['products']
                );

                return $productCrossSelling;
            }, $this->productCrossSellings);
        }

        try {
            $this->product->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__('Product saved successfully.'));

        return true;
    }

    #[Renderless]
    public function getPriceLists(): void
    {
        $product = app(ProductModel::class)->query()->whereKey($this->product->id)->first();
        $priceListHelper = PriceHelper::make($product)->useDefault(false);

        $priceLists = app(PriceList::class)->query()
            ->with('parent')
            ->get(['id', 'parent_id', 'name', 'price_list_code', 'is_net', 'is_default'])
            ->map(function (PriceList $priceList) use ($priceListHelper) {
                $price = $priceListHelper
                    ->setPriceList($priceList)
                    ->price();

                return [
                    'id' => $priceList->id,
                    'price_id' => $price?->id,
                    'price_net' => $price
                        ?->getNet(data_get($this->product->vat_rate, 'rate_percentage', 0)),
                    'price_gross' => $price
                        ?->getGross(data_get($this->product->vat_rate, 'rate_percentage', 0)),
                    'parent' => $priceList->parent?->toArray(),
                    'name' => $priceList->name,
                    'is_net' => $priceList->is_net,
                    'is_default' => $priceList->is_default,
                    'is_editable' => ! is_null(data_get($price, 'id')) || ! is_null($price->parent),
                ];
            });

        $this->priceLists = $priceLists->toArray();
    }

    #[Renderless]
    public function getProductCrossSellings(): void
    {
        $this->productCrossSellings = app(ProductCrossSelling::class)->query()
            ->where('product_id', $this->product->id)
            ->with('products:id,name,product_number')
            ->get()
            ->each(function (ProductCrossSelling $productCrossSelling) {
                $productCrossSelling->products
                    ->each(fn ($product) => $product->append('avatar_url'));
            })
            ->toArray();
    }

    #[Renderless]
    public function delete(): bool
    {
        try {
            $this->product->delete();

            $this->redirect(route('products.products'), true);

            return true;
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }

        return false;
    }

    #[Renderless]
    public function addSupplier(Contact $contact): void
    {
        if (in_array($contact->id, array_column($this->product->suppliers, 'contact_id'))) {
            return;
        }

        $this->product->suppliers[] = [
            'contact_id' => $contact->id,
            'customer_number' => $contact->customer_number,
            'manufacturer_product_number' => null,
            'purchase_price' => null,
            'main_address' => [
                'name' => $contact->mainAddress->name,
            ],
        ];

        $this->product->suppliers = array_values($this->product->suppliers);
    }
}
