<?php

namespace FluxErp\Actions\Product\ProductBundleProduct;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\ProductBundleProduct;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\ProductBundleProduct\CreateProductBundleProductRuleset;
use Illuminate\Validation\Rule;

class CreateProductBundleProduct extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateProductBundleProductRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ProductBundleProduct::class];
    }

    public function performAction(): ProductBundleProduct
    {
        $productBundleProduct = app(ProductBundleProduct::class, ['attributes' => $this->data]);
        $productBundleProduct->save();

        app(Product::class)->query()
            ->whereKey($this->data['product_id'])
            ->first()
            ->update([
                'is_bundle' => true,
            ]);

        return $productBundleProduct->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules['bundle_product_id'] = [
            Rule::unique('product_bundle_product', 'bundle_product_id')
                ->where('product_id', $this->data['product_id'] ?? 0),
        ];
    }
}
