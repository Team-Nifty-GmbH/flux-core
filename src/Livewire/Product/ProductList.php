<?php

namespace FluxErp\Livewire\Product;

use FluxErp\Actions\CartItem\CreateCartItem;
use FluxErp\Livewire\DataTables\ProductList as BaseProductList;
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Models\Client;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ProductList extends BaseProductList
{
    protected string $view = 'flux::livewire.product.product-list';

    public ?string $cacheKey = 'product.product-list';

    public ProductForm $product;

    public array $vatRates = [];

    public array $priceLists = [];

    public bool $isSelectable = true;

    public function mount(): void
    {
        parent::mount();

        $this->vatRates = app(VatRate::class)->all(['id', 'name', 'rate_percentage'])->toArray();
        $priceList = resolve_static(PriceList::class, 'default')?->toArray() ?? [];
        $priceList['is_editable'] = true;

        $this->priceLists = [$priceList];
    }

    public function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Add to cart'))
                ->icon('shopping-cart')
                ->when(resolve_static(CreateCartItem::class, 'canPerformAction', [false]))
                ->wireClick('addSelectedToCart; showSelectedActions = false;'),
        ];
    }

    #[Renderless]
    public function addSelectedToCart(): void
    {
        $this->dispatch('cart:add', $this->selected)->to('cart');
        $this->reset('selected');
    }

    #[Renderless]
    public function new(): void
    {
        $this->product->reset();

        $this->product->client_id = resolve_static(Client::class, 'default')?->id;
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('New'))
                ->icon('plus')
                ->attributes([
                    'x-on:click' => "\$wire.new().then(() => {\$openModal('create-product');})",
                ]),
        ];
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'vatRates' => app(VatRate::class)->query()
                    ->get(['id', 'name', 'rate_percentage'])
                    ->toArray(),
            ]
        );
    }

    public function getPriceLists(): array
    {
        return data_get($this->priceLists, '0', []);
    }

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
}
