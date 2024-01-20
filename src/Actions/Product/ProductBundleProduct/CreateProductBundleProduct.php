<?php

namespace FluxErp\Actions\Product\ProductBundleProduct;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateProductBundleProductRequest;
use FluxErp\Models\Pivots\ProductBundleProduct;
use FluxErp\Models\Product;
use Illuminate\Validation\Rule;

class CreateProductBundleProduct extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateProductBundleProductRequest())->rules();

        $this->rules['bundle_product_id'] = [
            Rule::unique('product_bundle_product', 'bundle_product_id')
                ->where('product_id', $this->data['product_id'] ?? 0),
        ];
    }

    public static function models(): array
    {
        return [ProductBundleProduct::class];
    }

    public function performAction(): ProductBundleProduct
    {
        $productBundleProduct = new ProductBundleProduct();
        $productBundleProduct->fill($this->data);

        $productBundleProduct->save();

        Product::query()
            ->whereKey($this->data['product_id'])
            ->first()
            ->update([
                'is_bundle' => true,
            ]);

        return $productBundleProduct->refresh();
    }
}