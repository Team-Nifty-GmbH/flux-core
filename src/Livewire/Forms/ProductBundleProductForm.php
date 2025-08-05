<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Product\ProductBundleProduct\CreateProductBundleProduct;
use FluxErp\Actions\Product\ProductBundleProduct\DeleteProductBundleProduct;
use FluxErp\Actions\Product\ProductBundleProduct\UpdateProductBundleProduct;
use Livewire\Attributes\Locked;

class ProductBundleProductForm extends FluxForm
{
    public ?int $bundle_product_id = null;

    public ?float $count = null;

    #[Locked]
    public ?int $id = null;

    #[Locked]
    public ?int $product_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateProductBundleProduct::class,
            'update' => UpdateProductBundleProduct::class,
            'delete' => DeleteProductBundleProduct::class,
        ];
    }
}
