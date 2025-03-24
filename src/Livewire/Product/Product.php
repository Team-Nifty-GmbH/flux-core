<?php

namespace FluxErp\Livewire\Product;

use Exception;
use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Enums\PropertyTypeEnum;
use FluxErp\Facades\ProductType;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\Pivots\ProductBundleProduct;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product as ProductModel;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Models\VatRate;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportPageComponents\PageComponentConfig;
use Livewire\Features\SupportPageComponents\SupportPageComponents;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Product extends Component
{
    use Actions, WithTabs;

    public array $additionalColumns = [];

    public ?array $currency = null;

    public array $displayedProductProperties = [];

    public ?int $languageId;

    public array $languages = [];

    public ?array $priceLists = null;

    public ProductForm $product;

    public ?array $productCrossSellings = null;

    public array $productProperties = [];

    public array $selectedProductProperties = [];

    #[Url]
    public string $tab = 'product.general';

    protected string $view = 'flux::livewire.product.product';

    public function __invoke(): Response
    {
        $html = null;

        $layoutConfig = SupportPageComponents::interceptTheRenderOfTheComponentAndRetreiveTheLayoutConfiguration(
            function () use (&$html): void {
                $params = SupportPageComponents::gatherMountMethodParamsFromRouteParameters($this);

                $productType = resolve_static(ProductModel::class, 'query')
                    ->where($params)
                    ->value('product_type');

                $html = app('livewire')->mount(
                    data_get(ProductType::get($productType) ?? ProductType::getDefault(), 'class') ?? static::class,
                    $params
                );
            }
        );

        $layoutConfig = $layoutConfig ?: new PageComponentConfig();

        $layoutConfig->normalizeViewNameAndParamsForBladeComponents();

        $response = response(SupportPageComponents::renderContentsIntoLayout($html, $layoutConfig));

        if (is_callable($layoutConfig->response)) {
            call_user_func($layoutConfig->response, $response);
        }

        return $response;
    }

    public function mount(int $id): void
    {
        $product = resolve_static(ProductModel::class, 'query')
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
        $this->product->product_properties = Arr::keyBy($this->product->product_properties, 'id');

        $this->languageId = Session::get('selectedLanguageId')
            ?? resolve_static(Language::class, 'default')?->getKey();
        $this->languages = resolve_static(Language::class, 'query')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->additionalColumns = $product->getAdditionalColumns()->toArray();
        $this->recalculateDisplayedProductProperties();
    }

    public function render(): View|Factory|Application
    {
        return view($this->viewName, [
            'vatRates' => $this->vatRates(),
        ]);
    }

    #[Renderless]
    public function addProductProperties(): bool
    {
        $this->selectedProductProperties = array_filter($this->selectedProductProperties);

        if (! $this->selectedProductProperties) {
            $this->product->product_properties = [];

            return true;
        }

        $added = [];
        $keep = [];
        foreach (array_keys($this->selectedProductProperties) as $id) {
            if (! in_array($id, array_column($this->product->product_properties, 'id'))) {
                $added[] = data_get(
                    $this->productProperties,
                    $id,
                    array_merge(
                        resolve_static(ProductProperty::class, 'query')
                            ->whereKey($id)
                            ->with('productPropertyGroup:id,name')
                            ->first(['id', 'product_property_group_id', 'name', 'property_type_enum'])
                            ->toArray(),
                        ['value' => null]
                    )
                );
            } else {
                $keep[] = $id;
            }
        }

        $this->product->product_properties = Arr::keyBy(
            array_merge(
                array_intersect_key($this->product->product_properties, array_flip($keep)),
                array_filter($added)
            ),
            'id'
        );

        $this->recalculateDisplayedProductProperties();

        return true;
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

    #[Renderless]
    public function delete(): bool
    {
        try {
            $this->product->delete();

            $this->redirect(route('products.products'), true);

            return true;
        } catch (Exception $e) {
            exception_to_notifications($e, $this);
        }

        return false;
    }

    #[Renderless]
    public function getPriceLists(): void
    {
        $product = resolve_static(ProductModel::class, 'query')
            ->whereKey($this->product->id)
            ->first();
        $priceListHelper = PriceHelper::make($product)->useDefault(false);

        $priceLists = resolve_static(PriceList::class, 'query')
            ->with('parent')
            ->get([
                'id',
                'parent_id',
                'name',
                'price_list_code',
                'rounding_method_enum',
                'rounding_precision',
                'rounding_number',
                'rounding_mode',
                'is_net',
                'is_default',
                'is_purchase',
            ])
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
                    'is_purchase' => $priceList->is_purchase,
                    'is_editable' => ! is_null(data_get($price, 'id')) || ! is_null($price?->parent) || is_null($price),
                ];
            });

        $this->priceLists = $priceLists->toArray();
    }

    #[Renderless]
    public function getProductCrossSellings(): void
    {
        $this->productCrossSellings = resolve_static(ProductCrossSelling::class, 'query')
            ->where('product_id', $this->product->id)
            ->with('products:id,name,product_number')
            ->get()
            ->each(function (ProductCrossSelling $productCrossSelling): void {
                $productCrossSelling->products
                    ->each(fn ($product) => $product->append('avatar_url'));
            })
            ->toArray();
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('product.general')->text(__('General')),
            TabButton::make('product.variant-list')
                ->text(__('Variants'))
                ->isLivewireComponent()
                ->wireModel('product')
                ->when(fn () => ! $this->product->parent_id && ! $this->product->is_bundle),
            TabButton::make('product.bundle-list')
                ->text(__('Bundle'))
                ->isLivewireComponent()
                ->wireModel('product')
                ->when(fn () => ! $this->product->children_count),
            TabButton::make('product.prices')->text(__('Prices')),
            TabButton::make('product.warehouse-list')
                ->text(__('Stock'))
                ->isLivewireComponent()
                ->wireModel('product'),
            TabButton::make('product.media')->text(__('Media')),
            TabButton::make('product.cross-selling')->text(__('Cross Selling')),
            TabButton::make('product.activities')
                ->text(__('Activities'))
                ->isLivewireComponent()
                ->wireModel('product.id'),
        ];
    }

    #[Renderless]
    public function loadProductProperties(ProductPropertyGroup $propertyGroup): void
    {
        $this->productProperties = $propertyGroup
            ->productProperties()
            ->select(['id', 'product_property_group_id', 'name', 'property_type_enum'])
            ->get()
            ->map(fn ($productProperty) => array_merge(
                $productProperty->toArray(),
                [
                    'value' => null,
                    'product_property_group' => [
                        'id' => $propertyGroup->id,
                        'name' => $propertyGroup->name,
                    ],
                ]
            ))
            ->keyBy('id')
            ->toArray();
    }

    public function localize(): void
    {
        Session::put('selectedLanguageId', $this->languageId);

        $this->product->fill(
            resolve_static(ProductModel::class, 'query')
                ->whereKey($this->product->id)
                ->first()
        );
    }

    public function resetProduct(): void
    {
        $product = resolve_static(ProductModel::class, 'query')
            ->whereKey($this->product->id)
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

        $this->product->reset();
        $this->product->fill($product);
        $this->product->product_properties = Arr::keyBy($this->product->product_properties, 'id');

        $this->recalculateDisplayedProductProperties();
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

        if ($this->displayedProductProperties) {
            $productProperties = Arr::flatten(
                array_filter(data_get($this->displayedProductProperties, '*.' . PropertyTypeEnum::Text->value)),
                1
            );

            foreach ($productProperties as $productProperty) {
                data_set(
                    $this->product->product_properties,
                    $productProperty['id'] . '.value',
                    $productProperty['value']
                );
            }
        }

        if ($this->product->is_bundle) {
            $this->product->bundle_products = resolve_static(ProductBundleProduct::class, 'query')
                ->where('product_id', $this->product->id)
                ->get(['id', 'bundle_product_id', 'count'])
                ->map(fn ($item) => ['id' => $item->bundle_product_id, 'count' => $item->count])
                ->toArray();
        }

        try {
            $this->product->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()->success(__(':model saved', ['model' => __('Product')]))->send();

        return true;
    }

    #[Renderless]
    public function showProductPropertiesModal(): void
    {
        $this->productProperties = [];
        $this->selectedProductProperties = array_fill_keys(
            array_column($this->product->product_properties, 'id'),
            true
        );

        $this->js(<<<'JS'
            $modalOpen('edit-product-properties-modal');
        JS);
    }

    #[Computed]
    public function vatRates(): array
    {
        return app(VatRate::class)->all(['id', 'name', 'rate_percentage'])->toArray();
    }

    #[Computed(persist: true)]
    public function viewName()
    {
        $productType = resolve_static(ProductModel::class, 'query')
            ->whereKey($this->product->id)
            ->value('product_type');

        return data_get(ProductType::get($productType) ?? ProductType::getDefault(), 'view') ?? $this->view;
    }

    protected function recalculateDisplayedProductProperties(): void
    {
        $this->displayedProductProperties = [];
        foreach ($this->product->product_properties as $property) {
            $this->displayedProductProperties[
                data_get($property, 'product_property_group.name')
            ][
                data_get($property, 'property_type_enum')
            ][] = [
                'id' => $property['id'],
                'name' => $property['name'],
                'value' => $property['value'],
            ];
        }

        ksort($this->displayedProductProperties);
        array_walk($this->displayedProductProperties, function (&$propertyTypes): void {
            ksort($propertyTypes);
            array_walk($propertyTypes, function (&$properties): void {
                $properties = Arr::sort($properties, ['name']);
            });
        });
    }
}
