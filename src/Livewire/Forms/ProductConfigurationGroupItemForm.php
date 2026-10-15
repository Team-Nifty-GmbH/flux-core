<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\ProductConfigurationGroupItem\CreateProductConfigurationGroupItem;
use FluxErp\Actions\ProductConfigurationGroupItem\DeleteProductConfigurationGroupItem;
use FluxErp\Actions\ProductConfigurationGroupItem\UpdateProductConfigurationGroupItem;
use Livewire\Attributes\Locked;

class ProductConfigurationGroupItemForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public int $max_amount = 1;

    public ?int $product_configuration_group_id = null;

    public ?int $product_id = null;

    public int $sort = 0;

    public null|float|string $surcharge = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateProductConfigurationGroupItem::class,
            'update' => UpdateProductConfigurationGroupItem::class,
            'delete' => DeleteProductConfigurationGroupItem::class,
        ];
    }
}
