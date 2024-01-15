<?php

namespace FluxErp\Actions\Product\ProductBundleProduct;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\ProductBundleProduct;

class DeleteProductBundleProduct extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:product_bundle_product,id',
        ];
    }

    public static function models(): array
    {
        return [ProductBundleProduct::class];
    }

    public function performAction(): ?bool
    {
        $productBundleProduct = ProductBundleProduct::query()
            ->whereKey($this->data['id'])
            ->first();

        $delete = $productBundleProduct->delete();

        if ($delete && $productBundleProduct->product->bundleProducts()->count() === 0) {
            $productBundleProduct->product->update([
                'is_bundle' => false,
            ]);
        }

        return $delete;
    }
}
