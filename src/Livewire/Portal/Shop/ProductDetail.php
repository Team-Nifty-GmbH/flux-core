<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Livewire\Forms\Portal\ProductForm;
use FluxErp\Models\Media as MediaModel;
use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Models\ProductOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductDetail extends Component
{
    public ProductForm $productForm;

    public array $groups = [];

    #[Url]
    public ?int $variant = null;

    public function mount(Product $product): void
    {
        $product->loadMissing(['children', 'parent']);

        $this->fillProductForm($product);

        if ($this->variant) {
            $variant = resolve_static(Product::class, 'query')
                ->where('parent_id', $product->id)
                ->whereKey($this->variant)
                ->with('productOptions:id,product_option_group_id')
                ->sole();
            $this->groups = $variant->productOptions->pluck('id', 'product_option_group_id')->toArray();

            $this->fillProductForm($variant);
        }
    }

    public function render(): View
    {
        return view('flux::livewire.portal.shop.product-detail');
    }

    public function selectOption(ProductOption $option): void
    {
        $this->groups[$option->product_option_group_id] = $option->id;

        // check if exactly one product exists that has all selected options
        try {
            $product = resolve_static(Product::class, 'query')
                ->where('parent_id', $this->productForm->parent_id ?? $this->productForm->id)
                ->whereHas('productOptions', function (Builder $query) {
                    return $query
                        ->select('product_product_option.product_id')
                        ->whereIntegerInRaw('product_options.id', array_values($this->groups))
                        ->groupBy('product_product_option.product_id')
                        ->havingRaw('COUNT(`product_options`.`id`) = ?', [count(array_values($this->groups))]);
                })
                ->sole();
        } catch (MultipleRecordsFoundException) {
            $this->skipRender();

            return;
        }

        $this->fillProductForm($product);
        $this->variant = $product->id;
    }

    public function downloadMedia(array|int $media, ?string $collectionName = null): void
    {
        $media = resolve_static(MediaModel::class, 'query')
            ->whereIntegerInRaw('id', Arr::wrap($media))
            ->get();

        count($media) > 1
            ? $this->redirectRoute(
                'portal.media.download-multiple',
                [
                    'ids' => $media->pluck('id')->implode(','),
                    'filename' => $this->productForm->name . ($collectionName ? ' - ' . $collectionName : ''),
                ]
            )
            : $this->redirectRoute(
                'portal.media',
                [
                    'media' => $media->first()->id,
                    'filename' => $media->first()->file_name,
                ]
            );
    }

    protected function fillProductForm(Product $product): void
    {
        $product->load([
            'bundleProducts' => fn (BelongsToMany $query) => $query->webshop(),
            'productCrossSellings:id,product_id,name',
            'productCrossSellings.products' => fn (BelongsToMany $query) => $query->webshop()->withCount('children'),
            'parent.productCrossSellings.products' => fn (BelongsToMany $query) => $query->webshop()
                ->withCount('children'),
            'meta' => fn (MorphMany $query) => $query->with('additionalColumn:id,label')
                ->whereHas(
                    'additionalColumn',
                    fn ($query) => $query->where('is_frontend_visible', true)
                ),
        ]);
        $product->append('price');

        $productCrossSellings = $product->productCrossSellings->isEmpty()
            ? $product->parent?->productCrossSellings
            : $product->productCrossSellings;

        $product->setRelation(
            'productCrossSellings',
            $productCrossSellings
                ?->filter(fn (ProductCrossSelling $productCrossSelling) => ! $productCrossSelling->products->isEmpty())
                ->each(fn (ProductCrossSelling $productCrossSelling) => $productCrossSelling->products
                    ->transform(function (Product $product) {
                        $product->append('price');
                        $productArray = $product->toArray();

                        $productArray['cover_url'] = $product->coverMedia?->getUrl('thumb_280x280') ?? null;
                        $productForm = clone $this->productForm;
                        $productForm->reset();
                        $productForm->fill($productArray);

                        return $productForm->toArray();
                    }
                    )
                )
                ->values()
        );

        $this->productForm->reset(
            'buy_price',
            'root_price_flat',
            'root_discount_percentage',
            'bundle_products'
        );
        $this->productForm->fill(array_merge($this->productForm->toArray(), $product->toArray()));
        $this->productForm->meta = $product->meta
            ->pluck('value', 'additionalColumn.label')
            ->filter(fn ($value) => ! empty($value))
            ->toArray();

        $this->productForm->cover_url = ($product->coverMedia ?? $product->parent?->coverMedia)
            ?->getUrl() ?? route('icons', ['name' => 'photo']);

        $this->productForm->media = $product
            ->media()
            ->where('collection_name', 'images')
            ->whereNot('id', $product->cover_media_id)
            ->get()
            ->merge($product->parent?->media()->where('collection_name', 'images')->get() ?? [])
            ->map(fn (Media $media) => $media->getUrl('thumb_400x400'))
            ->prepend($this->productForm->cover_url)
            ->toArray();

        $product->media()
            ->get()
            ->merge($product->parent?->media()->get() ?? [])
            ->each(function (Media $media) {
                $this->productForm->additionalMedia[$media->collection_name][$media->id] = [
                    'id' => $media->id,
                    'name' => $media->name,
                    'url' => $media->getUrl(),
                ];
            });

        if ($childCount = $product->children->count()) {
            $this->productForm->children_count = $childCount;
            $this->productForm->productOptionGroups = $product->getChildProductOptions()->toArray();
        }

        $this->productForm->bundle_products = [];
        resolve_static(
            Product::class,
            'addGlobalScope',
            [
                'scope' => 'bundleProducts',
                'implementation' => fn (Builder $query) => $query->with([
                    'bundleProducts' => fn (BelongsToMany $query) => $query->withPivot('count'),
                    'media',
                ]),
            ]
        );

        $this->getBundleData($product->bundleProducts()->get());
    }

    protected function getBundleData(Collection $products): void
    {
        foreach ($products as $product) {
            $this->productForm->bundle_products[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'count' => bcadd(
                    data_get($this->productForm->bundle_products, $product->id . '.count', 0),
                    $product->pivot->count
                ),
            ];

            foreach ($product->media as $media) {
                /** @var Media $media */
                $this->productForm->additionalMedia[$media->collection_name][$media->id] = [
                    'id' => $media->id,
                    'name' => $media->name,
                    'url' => $media->getUrl(),
                ];
            }

            if ($product->bundleProducts->isNotEmpty()) {
                $this->getBundleData($product->bundleProducts);
            }
        }
    }
}
