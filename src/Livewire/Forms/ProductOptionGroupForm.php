<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\ProductOptionGroup\CreateProductOptionGroup;
use FluxErp\Actions\ProductOptionGroup\DeleteProductOptionGroup;
use FluxErp\Actions\ProductOptionGroup\UpdateProductOptionGroup;
use Livewire\Attributes\Locked;

class ProductOptionGroupForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public array $product_options = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateProductOptionGroup::class,
            'update' => UpdateProductOptionGroup::class,
            'delete' => DeleteProductOptionGroup::class,
        ];
    }
}
