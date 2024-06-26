<?php

namespace FluxErp\Livewire\Portal\Shop;

use FluxErp\Livewire\Forms\Portal\ProductForm;
use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Models\ProductOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\View\View;
use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Psl\Str\is_empty;

class ProductDetail extends Component
{
    public ProductForm $productForm;

    public array $groups = [];

    public function mount(Product $product): void
    {
        $product->loadMissing(['children', 'parent']);

        $this->fillProductForm($product);
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
            $product = app(Product::class)
                ->query()
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
    }

    protected function fillProductForm(Product $product): void
    {
        $product->load([
            'productCrossSellings:id,product_id,name',
            'productCrossSellings.products' => fn (BelongsToMany $query) => $query->webshop(),
            'meta' => fn (MorphMany $query) => $query->with('additionalColumn:id,label')
                ->whereHas(
                    'additionalColumn',
                    fn ($query) => $query->where('is_frontend_visible', true)
                ),
        ]);
        $product->append('price');

        $product->setRelation(
            'productCrossSellings',
            $product->productCrossSellings
                ->filter(fn (ProductCrossSelling $productCrossSelling) => ! $productCrossSelling->products->isEmpty())
                ->each(fn (ProductCrossSelling $productCrossSelling) => $productCrossSelling->products->transform(function (Product $product) {
                    $product->append('price');
                    $productArray = $product->toArray();

                    $productArray['cover_url'] = $product->coverMedia?->getUrl('thumb_280x280') ?? null;
                    $productForm = clone $this->productForm;
                    $productForm->reset();
                    $productForm->fill($productArray);

                    return $productForm->toArray();
                }))
                ->values()
        );

        $this->productForm->reset('buy_price', 'root_price_flat', 'root_discount_percentage');
        $this->productForm->fill(array_merge($this->productForm->toArray(), $product->toArray()));
        $this->productForm->meta = $product->meta
            ->pluck('value', 'additionalColumn.label')
            ->filter(fn ($value) => ! is_empty($value))
            ->toArray();

        $this->productForm->cover_url = ($product->coverMedia ?? $product->parent?->coverMedia)
            ?->getUrl() ?? route('icons', ['name' => 'photo']);

        $this->productForm->media = $product
            ->media()
            ->where('collection_name', 'images')
            ->whereNot('id', $product->cover_media_id)
            ->get()
            ->map(fn (Media $media) => $media->getUrl('thumb_400x400'))
            ->prepend($this->productForm->cover_url)
            ->toArray();

        if ($childCount = $product->children->count()) {
            $this->productForm->children_count = $childCount;
            $this->productForm->productOptionGroups = $product->getChildProductOptions()->toArray();
        }

        $this->dispatch('folder-tree:loadModel', ['modelType' => Product::class, 'modelId' => $product->id]);
    }
}
