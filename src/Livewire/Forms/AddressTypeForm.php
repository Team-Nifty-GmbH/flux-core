<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\AddressType\CreateAddressType;
use FluxErp\Actions\AddressType\DeleteAddressType;
use FluxErp\Actions\AddressType\UpdateAddressType;

class AddressTypeForm extends FluxForm
{
    public ?string $address_type_code = null;

    public ?int $client_id = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_locked = false;

    public bool $is_unique = false;

    public ?string $name = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateAddressType::class,
            'update' => UpdateAddressType::class,
            'delete' => DeleteAddressType::class,
        ];
    }
}
