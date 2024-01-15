<?php

namespace FluxErp\Actions\Product\ProductBundleProduct;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateProductBundleProductRequest;
use FluxErp\Models\Pivots\ProductBundleProduct;
use Illuminate\Database\Eloquent\Model;

class UpdateProductBundleProduct extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateProductBundleProductRequest())->rules();

        // combination of product_id and bundle_product_id must be unique, ignore $this->data['id']
        $this->rules['bundle_product_id'] = [
            'unique:product_bundle_product,bundle_product_id,' . $this->data['id'] . ',id,product_id,' . $this->data['product_id'],
        ];
    }

    public static function models(): array
    {
        return [ProductBundleProduct::class];
    }

    public function performAction(): Model
    {
        $productBundleProduct = ProductBundleProduct::query()
            ->whereKey($this->data['id'])
            ->first();

        if (! $this->data['bundle_product_id'] ?? true) {
            unset($this->data['bundle_product_id']);
        }

        if (! $this->data['count'] ?? true) {
            unset($this->data['count']);
        }

        $productBundleProduct->fill($this->data);
        $productBundleProduct->save();

        return $productBundleProduct->withoutRelations()->fresh();
    }
}
