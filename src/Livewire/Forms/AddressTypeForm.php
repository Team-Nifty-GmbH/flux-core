<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\AddressType\CreateAddressType;
use FluxErp\Actions\AddressType\DeleteAddressType;
use FluxErp\Actions\AddressType\UpdateAddressType;
use FluxErp\Models\AddressType;
use Livewire\Attributes\Locked;

class AddressTypeForm extends FluxForm
{
    public ?string $address_type_code = null;

    #[Locked]
    public ?int $id = null;

    public bool $is_locked = false;

    public bool $is_unique = false;

    public ?string $name = null;

    public array $tenants = [];

    public function fill($values): void
    {
        if ($values instanceof AddressType) {
            $values->loadMissing(['tenants:id']);

            $values = $values->toArray();
            $values['tenants'] = array_column($values['tenants'] ?? [], 'id');
        }

        parent::fill($values);
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateAddressType::class,
            'update' => UpdateAddressType::class,
            'delete' => DeleteAddressType::class,
        ];
    }
}
