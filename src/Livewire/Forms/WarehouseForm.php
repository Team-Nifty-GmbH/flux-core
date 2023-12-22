<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Warehouse\CreateWarehouse;
use FluxErp\Actions\Warehouse\DeleteWarehouse;
use FluxErp\Actions\Warehouse\UpdateWarehouse;
use Livewire\Attributes\Locked;

class WarehouseForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $address_id = null;

    public ?string $name = null;

    public bool $is_default = false;

    protected function getActions(): array
    {
        return [
            'create' => CreateWarehouse::class,
            'update' => UpdateWarehouse::class,
            'delete' => DeleteWarehouse::class,
        ];
    }
}
