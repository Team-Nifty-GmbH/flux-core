<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\SerialNumber\CreateSerialNumber;
use FluxErp\Actions\SerialNumber\DeleteSerialNumber;
use FluxErp\Actions\SerialNumber\UpdateSerialNumber;
use Livewire\Attributes\Locked;

class SerialNumberForm extends FluxForm
{
    public ?array $addresses = [];

    #[Locked]
    public ?int $id = null;

    public ?array $product = [];

    public ?string $serial_number = null;

    public ?string $supplier_serial_number = null;

    public bool $use_supplier_serial_number = false;

    protected function getActions(): array
    {
        return [
            'create' => CreateSerialNumber::class,
            'update' => UpdateSerialNumber::class,
            'delete' => DeleteSerialNumber::class,
        ];
    }
}
