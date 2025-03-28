<?php

namespace FluxErp\Actions\Product\Variant;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\Variant\CreateVariantsRuleset;
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
            ->with(['clients:id', 'categories:id', 'prices:id,price_list_id,price', 'tags:id'])
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
        );
        $product['parent_id'] = $parentProduct->id;
        $product['clients'] = $parentProduct->clients->pluck('id')->toArray();
        $product['categories'] = $parentProduct->categories?->pluck('id')->toArray();
        $product['tags'] = $parentProduct->tags?->pluck('id')->toArray();

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
        }

        return $parentProduct->children()->get();
    }

    protected function variantExists(array $configuration): bool
    {
        return resolve_static(Product::class, 'query')
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
