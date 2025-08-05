<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Warehouse\CreateWarehouse;
use FluxErp\Actions\Warehouse\DeleteWarehouse;
use FluxErp\Actions\Warehouse\UpdateWarehouse;
use Livewire\Attributes\Locked;

class WarehouseForm extends FluxForm
{
    public ?int $address_id = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_default = false;

    public ?string $name = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateWarehouse::class,
            'update' => UpdateWarehouse::class,
            'delete' => DeleteWarehouse::class,
        ];
    }
}
