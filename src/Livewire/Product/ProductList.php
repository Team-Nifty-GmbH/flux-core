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
use FluxErp\Models\Language;
use FluxErp\Models\PriceList;
use FluxErp\Models\VatRate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class ProductList extends BaseProductList
{
    public ?string $cacheKey = 'product.product-list';

    public bool $isSelectable = true;

    public array $priceLists = [];

    public ProductForm $product;

    public ProductPricesUpdateForm $productPricesUpdate;

    public array $productTypes = [];

    public array $vatRates = [];

    public ?int $languageId;

    public array $languages = [];

    protected ?string $includeBefore = 'flux::livewire.product.product-list';

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

        $this->languageId = Session::get('selectedLanguageId')
            ?? resolve_static(Language::class, 'default')?->id;
        $this->languages = resolve_static(Language::class, 'query')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    protected function getTableActions(): array
    {
        return [
            new HtmlString(
                Blade::render(
                    '<x-select.styled
                        required
                        x-model="$wire.languageId"
                        x-on:select="$wire.localize()"
                        select="label:name|value:id"
                        :options="$languages"
                    />',
                    ['languages' => $this->languages]
                )
            ),
            DataTableButton::make()
                ->color('indigo')
                ->text(__('New'))
                ->icon('plus')
                ->when(fn () => resolve_static(CreateProduct::class, 'canPerformAction', [false]))
                ->xOnClick(<<<'JS'
                    $wire.new().then(() => {$modalOpen('create-product-modal');});
                JS),
        ];
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Add to cart'))
                ->icon('shopping-cart')
                ->when(resolve_static(CreateCartItem::class, 'canPerformAction', [false]))
                ->wireClick('addSelectedToCart; showSelectedActions = false;'),
            DataTableButton::make()
                ->text(__('Update prices'))
                ->color('amber')
                ->when(resolve_static(ProductPricesUpdate::class, 'canPerformAction', [false]))
                ->xOnClick(<<<'JS'
                    $modalOpen('update-prices-modal');
                JS),
        ];
    }

    public function localize(): void
    {
        Session::put('selectedLanguageId', $this->languageId);

        $this->loadData();
    }

    #[Renderless]
    public function addSelectedToCart(): void
    {
        $this->dispatch(
            'cart:add',
            $this->getSelectedModelsQuery()
                ->whereDoesntHave('children')
                ->pluck('id')
        )
            ->to('cart.cart');
        $this->reset('selected');
    }

    public function getPriceLists(): array
    {
        return data_get($this->priceLists, '0', []);
    }

    #[Renderless]
    public function new(): void
    {
        $this->product->reset();

        $this->product->client_id = Client::default()?->getKey();
        $this->product->product_type = data_get(ProductType::getDefault(), 'type');
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

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'selectablePriceLists' => resolve_static(PriceList::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'vatRates' => resolve_static(VatRate::class, 'query')
                    ->get(['id', 'name', 'rate_percentage'])
                    ->toArray(),
                'roundingMethods' => RoundingMethodEnum::valuesLocalized(),
                'roundingModes' => [
                    [
                        'label' => __('Round'),
                        'value' => 'round',
                    ],
                    [
                        'label' => __('Round up'),
                        'value' => 'ceil',
                    ],
                    [
                        'label' => __('Round down'),
                        'value' => 'floor',
                    ],
                ],
            ]
        );
    }

    protected function itemToArray($item): array
    {
        return parent::itemToArray($item->localize());
    }
}
