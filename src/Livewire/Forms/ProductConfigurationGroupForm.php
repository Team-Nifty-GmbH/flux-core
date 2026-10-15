<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\ProductConfigurationGroup\CreateProductConfigurationGroup;
use FluxErp\Actions\ProductConfigurationGroup\DeleteProductConfigurationGroup;
use FluxErp\Actions\ProductConfigurationGroup\UpdateProductConfigurationGroup;
use Livewire\Attributes\Locked;

class ProductConfigurationGroupForm extends FluxForm
{
    public ?string $description = null;

    #[Locked]
    public ?int $id = null;

    public ?int $max_selections = null;

    public ?int $min_selections = null;

    public ?string $name = null;

    public ?string $price_mode_enum = 'surcharge';

    public ?string $selection_type_enum = 'single';

    protected function getActions(): array
    {
        return [
            'create' => CreateProductConfigurationGroup::class,
            'update' => UpdateProductConfigurationGroup::class,
            'delete' => DeleteProductConfigurationGroup::class,
        ];
    }
}
