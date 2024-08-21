<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\ProductPropertyGroup\CreateProductPropertyGroup;
use FluxErp\Actions\ProductPropertyGroup\DeleteProductPropertyGroup;
use FluxErp\Actions\ProductPropertyGroup\UpdateProductPropertyGroup;
use Livewire\Attributes\Locked;

class ProductPropertyGroupForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public array $product_properties = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateProductPropertyGroup::class,
            'update' => UpdateProductPropertyGroup::class,
            'delete' => DeleteProductPropertyGroup::class,
        ];
    }
}
