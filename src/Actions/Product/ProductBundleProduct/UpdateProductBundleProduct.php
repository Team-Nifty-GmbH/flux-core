<?php

namespace FluxErp\Actions\Product\ProductBundleProduct;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\ProductBundleProduct;
use FluxErp\Rulesets\Product\ProductBundleProduct\UpdateProductBundleProductRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class UpdateProductBundleProduct extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return UpdateProductBundleProductRuleset::class;
    }

    public static function models(): array
    {
        return [ProductBundleProduct::class];
    }

    public function performAction(): Model
    {
        $productBundleProduct = resolve_static(ProductBundleProduct::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $productBundleProduct->fill($this->data);
        $productBundleProduct->save();

        return $productBundleProduct->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules['bundle_product_id'] = [
            Rule::unique('product_bundle_product', 'bundle_product_id')
                ->where('product_id', $this->data['product_id'] ?? 0)
                ->ignore($this->data['id'] ?? 0),
        ];
    }
}
