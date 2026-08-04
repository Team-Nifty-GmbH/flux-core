<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Price\DeletePrice;
use FluxErp\Actions\ProductCrossSelling\CreateProductCrossSelling;
use FluxErp\Actions\ProductCrossSelling\DeleteProductCrossSelling;
use FluxErp\Actions\ProductCrossSelling\UpdateProductCrossSelling;
use FluxErp\Enums\BundleTypeEnum;
use FluxErp\Helpers\Helper;
use FluxErp\Models\Media;
use FluxErp\Models\Product;
use FluxErp\Models\Tag;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\Product\UpdateProductRuleset;
use FluxErp\Support\VariantInheritance\PivotInheritanceSync;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateProduct extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateProductRuleset::class;
    }

    public function performAction(): Model
    {
        $productOptions = Arr::pull($this->data, 'product_options');
        $productCrossSellings = Arr::pull($this->data, 'product_cross_sellings');
        $tenants = Arr::pull($this->data, 'tenants');

        $productProperties = Arr::pull($this->data, 'product_properties');
        $bundleProducts = Arr::pull($this->data, 'bundle_products', false);
        $prices = Arr::pull($this->data, 'prices', false);
        $suppliers = Arr::pull($this->data, 'suppliers');
        $tags = Arr::pull($this->data, 'tags');

        $product = resolve_static(Product::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $product->fill($this->data);

        if ($product->isDirty('is_bundle') && ! $product->is_bundle) {
            $product->bundleProducts()->detach();
        }

        $product->save();

        // A variant editing its own relation entry must take ownership (is_inherited = false),
        // otherwise the edit would either get overwritten by the next parent propagation or
        // sit alongside a duplicate inherited copy. Parents never own an inherited copy of
        // their own, so this is a no-op for them.
        $takesOwnership = $product->isVariant() && $product->inheritanceEnabled();

        if (! is_null($tags)) {
            $product->syncTags(resolve_static(Tag::class, 'query')->whereKey($tags)->get());
        }

        if (! is_null($productOptions)) {
            $product->productOptions()->sync($productOptions);
        }

        if (! is_null($productProperties)) {
            resolve_static(PivotInheritanceSync::class, 'syncOwned', [
                'relation' => $product->productProperties(),
                'desired' => Arr::mapWithKeys(
                    $productProperties,
                    fn (array $item) => [$item['id'] => ['value' => $item['value']]]
                ),
                'takesOwnership' => $takesOwnership,
            ]);
        }

        if (! is_null($suppliers)) {
            // Key by contact_id instead of passing the raw payload list straight to sync():
            // sync() keys unkeyed array items by their list index, so it would compare those
            // indexes against the real contact_ids already attached and detach everything.
            resolve_static(PivotInheritanceSync::class, 'syncOwned', [
                'relation' => $product->suppliers(),
                'desired' => collect($suppliers)
                    ->mapWithKeys(fn (array $item) => [$item['contact_id'] => Arr::except($item, 'contact_id')])
                    ->all(),
                'takesOwnership' => $takesOwnership,
            ]);
        }

        resolve_static(PivotInheritanceSync::class, 'propagateToChildren', ['parent' => $product]);

        if ($tenants) {
            $product->tenants()->sync($tenants);
        }

        if ($prices) {
            $priceCollection = collect($prices)->keyBy('price_list_id');
            $product->prices
                ?->each(function ($price) use ($priceCollection, $takesOwnership): void {
                    if ($priceCollection->has($price->price_list_id)) {
                        $data = $priceCollection->get($price->price_list_id);

                        if ($takesOwnership) {
                            $data['is_inherited'] = false;
                        }

                        $price->update($data);
                        $priceCollection->forget($price->price_list_id);
                    } elseif (! $takesOwnership || ! $price->is_inherited) {
                        // Inherited copies absent from the payload are managed by parent
                        // propagation and the reset actions, not by payload omission.
                        $price->delete();
                    }
                });

            $priceCollection->each(function ($item) use ($product, $takesOwnership): void {
                if ($takesOwnership) {
                    $item['is_inherited'] = false;
                }

                $product->prices()->create($item);
            });
        }

        if ($product->is_bundle && $bundleProducts) {
            $product->bundleProducts()
                ->sync(
                    collect($bundleProducts)
                        ->unique('id')
                        ->mapWithKeys(fn ($item) => [$item['id'] => ['count' => $item['count']]])
                        ->toArray()
                );
        }

        if ($product->is_bundle && $product->bundle_type_enum === BundleTypeEnum::Group) {
            // when the bundle type is group we need to remove all prices form the product as the price
            // is calculated by the prices of the group items
            foreach ($product->prices()->get('id') as $price) {
                DeletePrice::make([
                    'id' => $price->getKey(),
                ])
                    ->validate()
                    ->execute();
            }
        }

        if (! is_null($productCrossSellings)) {
            Helper::updateRelatedRecords(
                model: $product,
                related: $productCrossSellings,
                relation: 'productCrossSellings',
                foreignKey: 'product_id',
                createAction: CreateProductCrossSelling::class,
                updateAction: UpdateProductCrossSelling::class,
                deleteAction: DeleteProductCrossSelling::class
            );
        }

        return $product->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        if ($this->getData('is_bundle') === false) {
            $this->data['bundle_type_enum'] = null;
        }

        $this->rules['cover_media_id'][] = (new ModelExists(Media::class))
            ->where('model_type', app(Product::class)->getMorphClass())
            ->where('model_id', $this->data['id'] ?? null);
        $this->rules['product_number'] = [
            Rule::unique('products', 'product_number')
                ->whereNull('deleted_at')
                ->ignore(data_get($this->data, 'id')),
        ];
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (array_key_exists('is_variant_parent', $this->data)
            && ! $this->getData('is_variant_parent')
            && resolve_static(Product::class, 'query')
                ->whereKey($this->getData('id'))
                ->whereHas('children', fn (Builder $query) => $query->where('is_active', true))
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'is_variant_parent' => ['Cannot demote to standalone: active variants still exist.'],
            ])
                ->errorBag('updateProduct');
        }

        if ($this->data['parent_id'] ?? false) {
            $product = resolve_static(Product::class, 'query')
                ->whereKey($this->data['id'])
                ->first();

            if (Helper::checkCycle(Product::class, $product, $this->data['parent_id'])) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Cycle detected'],
                ])->errorBag('updateProduct');
            }

            if ($product?->children()->exists()) {
                throw ValidationException::withMessages([
                    'parent_id' => [
                        'A product with existing variants cannot itself become a variant.',
                    ],
                ])
                    ->errorBag('updateProduct');
            }
        }
    }
}
