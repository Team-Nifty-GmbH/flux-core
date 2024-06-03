<?php

namespace FluxErp\Actions\Product\Variant;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Rulesets\Product\Variant\CreateVariantsRuleset;
use Illuminate\Database\Eloquent\Collection;

class CreateVariants extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateVariantsRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Product::class];
    }

    public function performAction(): Collection
    {
        $parentProduct = app(Product::class)->query()
            ->whereKey($this->data['product_id'])
            ->with('clients:id')
            ->first();
        $product = $parentProduct->toArray();
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

        foreach (data_get($this->data, 'product_options') as $variantCreate) {
            $product['product_options'] = $variantCreate;
            $product['name'] = $parentProduct->name . ' - '
                . implode(
                    ' ',
                    app(ProductOption::class)->query()
                        ->whereIntegerInRaw('id', $variantCreate)
                        ->pluck('name')
                        ->toArray()
                );

            CreateProduct::make($product)
                ->checkPermission()
                ->validate()
                ->execute();
        }

        return $parentProduct->variants()->get();
    }
}
