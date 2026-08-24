<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\WarehouseBin\CreateWarehouseBin;
use FluxErp\Actions\WarehouseBin\DeleteWarehouseBin;
use FluxErp\Actions\WarehouseBin\UpdateWarehouseBin;
use Livewire\Attributes\Locked;

class WarehouseBinForm extends FluxForm
{
    public ?string $code = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public bool $is_storage_location = false;

    public ?string $name = null;

    public ?int $parent_id = null;

    public ?int $sort_order = 0;

    public ?string $warehouse_bin_type_enum = null;

    public ?int $warehouse_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateWarehouseBin::class,
            'update' => UpdateWarehouseBin::class,
            'delete' => DeleteWarehouseBin::class,
        ];
    }
}
