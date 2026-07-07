<?php

namespace FluxErp\Actions\Product\Variant;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\Variant\CreateVariantsRuleset;
use FluxErp\Support\VariantInheritance\PivotInheritanceSync;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CreateVariants extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateVariantsRuleset::class;
    }

    public function performAction(): Collection
    {
        $parentProduct = resolve_static(Product::class, 'query')
            ->whereKey($this->data['parent_id'])
            ->with(['tenants:id', 'categories:id', 'ownPrices:id,price_list_id,price', 'tags:id'])
            ->first();

        $product = array_merge($parentProduct->toArray(), $this->data);
        unset(
            $product['uuid'],
            $product['id'],
            $product['cover_media_id'],
            $product['parent_id'],
            $product['product_options'],
            $product['product_number'],
            $product['ean'],
            $product['is_bundle'],
            $product['own_prices'],
        );
        $product['parent_id'] = $parentProduct->id;
        $product['tenants'] = $parentProduct->tenants->pluck('id')->toArray();
        // Tags have no is_inherited concept (no such column on the taggables table), so
        // they are always plainly copied as owned, regardless of the inheritance toggle.
        $product['tags'] = $parentProduct->tags?->pluck('id')->toArray();

        $inheritanceEnabled = app(Product::class)->inheritanceEnabled();

        if ($inheritanceEnabled) {
            // Categories/prices are not seeded here — they are materialized onto the new
            // variant as real is_inherited=true copies via the existing parent -> children
            // propagation helpers below, right after the variant exists as a child.
            unset($product['categories'], $product['prices']);
        } else {
            $product['categories'] = $parentProduct->categories?->pluck('id')->toArray();
            $product['prices'] = $parentProduct->ownPrices
                ->map(fn ($price) => [
                    'id' => $price->getKey(),
                    'price_list_id' => $price->price_list_id,
                    'price' => $price->price,
                ])
                ->toArray();
        }

        $createdAny = false;

        foreach (data_get($this->data, 'product_options') as $variantCreate) {
            if ($this->variantExists($variantCreate)) {
                continue;
            }

            $product['product_options'] = $variantCreate;

            CreateProduct::make(
                array_merge(
                    $product,
                    [
                        'name' => resolve_static(
                            Product::class,
                            'calculateVariantName',
                            [
                                'productOptions' => $variantCreate,
                                'parentName' => data_get($product, 'name'),
                            ]
                        ),
                    ]
                )
            )
                ->checkPermission()
                ->validate()
                ->execute();

            $createdAny = true;
        }

        if ($inheritanceEnabled && $createdAny) {
            // Categories/suppliers/productProperties: reuse the same propagation helper
            // UpdateProduct uses after a parent's own pivots change.
            PivotInheritanceSync::propagateToChildren($parentProduct);

            // Prices propagate via Price::booted()'s `saved` hook, keyed off the parent's
            // children — re-saving (a no-op write, since nothing changed) fires that hook
            // so the newly created variant receives inherited price copies too.
            $parentProduct->ownPrices()->get()->each(fn ($price) => $price->save());
        }

        return $parentProduct->children()->get();
    }

    protected function variantExists(array $configuration): bool
    {
        return resolve_static(Product::class, 'query')
            ->withTrashed()
            ->where('parent_id', data_get($this->data, 'parent_id'))
            ->whereHas('productOptions', function (Builder $query) use ($configuration) {
                return $query
                    ->select('product_product_option.product_id')
                    ->whereIntegerInRaw('product_options.id', $configuration)
                    ->groupBy('product_product_option.product_id')
                    ->havingRaw('COUNT(`product_options`.`id`) = ?', [count($configuration)]);
            })
            ->exists();
    }
}
