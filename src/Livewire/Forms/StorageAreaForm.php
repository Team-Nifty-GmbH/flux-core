<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\StorageArea\CreateStorageArea;
use FluxErp\Actions\StorageArea\DeleteStorageArea;
use FluxErp\Actions\StorageArea\UpdateStorageArea;
use Livewire\Attributes\Locked;

class StorageAreaForm extends FluxForm
{
    public ?string $code = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public bool $is_storage_location = false;

    public ?string $name = null;

    public ?int $parent_id = null;

    public ?int $sort_number = 0;

    public ?string $storage_area_type_enum = null;

    public ?int $warehouse_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateStorageArea::class,
            'update' => UpdateStorageArea::class,
            'delete' => DeleteStorageArea::class,
        ];
    }
}
