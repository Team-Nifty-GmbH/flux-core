<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\AddressType\CreateAddressType;
use FluxErp\Actions\AddressType\DeleteAddressType;
use FluxErp\Actions\AddressType\UpdateAddressType;

class AddressTypeForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?int $client_id = null;

    public ?string $name = null;

    public ?string $address_type_code = null;

    public bool $is_locked = false;

    public bool $is_unique = false;

    protected function getActions(): array
    {
        return [
            'create' => CreateAddressType::class,
            'update' => UpdateAddressType::class,
            'delete' => DeleteAddressType::class,
        ];
    }
}
