<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Actions\CartItem\CreateCartItem;
use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Actions\Product\ProductPricesUpdate;
use FluxErp\Enums\RoundingMethodEnum;
use FluxErp\Facades\ProductType;
use FluxErp\Livewire\DataTables\ProductList as BaseProductList;
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Livewire\Forms\ProductPricesUpdateForm;
use FluxErp\Models\Client;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ProductList extends BaseProductList
{
    protected ?string $includeBefore = 'flux::livewire.product.product-list';

    public ?string $cacheKey = 'product.product-list';

    public ProductForm $product;

    public ProductPricesUpdateForm $productPricesUpdate;

    public array $vatRates = [];

    public array $priceLists = [];

    public array $productTypes = [];

    public bool $isSelectable = true;

    public function mount(): void
    {
        parent::mount();

        $this->vatRates = app(VatRate::class)->all(['id', 'name', 'rate_percentage'])->toArray();
        $priceList = PriceList::default()?->toArray() ?? [];
        $priceList['is_editable'] = true;

        $this->priceLists = [$priceList];

        $this->productTypes = ProductType::all()
            ->keys()
            ->map(fn ($key) => [
                'value' => $key,
                'label' => __(Str::headline($key)),
            ])
            ->toArray();
    }

    #[Renderless]
    public function addSelectedToCart(): void
    {
        $this->dispatch(
            'cart:add',
            $this->getSelectedModelsQuery()->whereDoesntHave('children')->get(['id'])
        )
            ->to('cart.cart');
        $this->reset('selected');
    }

    #[Renderless]
    public function new(): void
    {
        $this->product->reset();

        $this->product->client_id = Client::default()?->id;
        $this->product->product_type = data_get(ProductType::getDefault(), 'type');
    }

    public function getPriceLists(): array
    {
        return data_get($this->priceLists, '0', []);
    }

    #[Renderless]
    public function save(): bool
    {
        $this->product->prices = [
            [
                'price_list_id' => data_get($this->priceLists, '0.id'),
                'price' => data_get($this->priceLists, '0.is_net')
                    ? data_get($this->priceLists, '0.price_net')
                    : data_get($this->priceLists, '0.price_gross'),
            ],
        ];

        try {
            $this->product->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->redirect(route('products.id', $this->product->id), true);

        return true;
    }

    #[Renderless]
    public function updatePrices(): bool
    {
        try {
            ProductPricesUpdate::make(array_merge(
                $this->productPricesUpdate->toArray(),
                [
                    'products' => $this->getSelectedModelsQuery()->pluck('id')->toArray(),
                ]
            ))
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->reset('selected');
        $this->productPricesUpdate->reset();
        $this->loadData();

        return true;
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Add to cart'))
                ->icon('shopping-cart')
                ->when(resolve_static(CreateCartItem::class, 'canPerformAction', [false]))
                ->wireClick('addSelectedToCart; showSelectedActions = false;'),
            DataTableButton::make()
                ->label(__('Update prices'))
                ->color('warning')
                ->when(resolve_static(ProductPricesUpdate::class, 'canPerformAction', [false]))
                ->xOnClick(<<<'JS'
                    $openModal('update-prices');
                JS),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('New'))
                ->icon('plus')
                ->when(fn () => resolve_static(CreateProduct::class, 'canPerformAction', [false]))
                ->xOnClick(<<<'JS'
                    $wire.new().then(() => {$openModal('create-product');});
                JS),
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'vatRates' => resolve_static(VatRate::class, 'query')
                    ->get(['id', 'name', 'rate_percentage'])
                    ->toArray(),
                'roundingMethods' => RoundingMethodEnum::valuesLocalized(),
                'roundingModes' => [
                    'round' => __('Round'),
                    'ceil' => __('Round up'),
                    'floor' => __('Round down'),
                ],
            ]
        );
    }
}
