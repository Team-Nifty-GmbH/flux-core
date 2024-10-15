<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Order\UpdateLockedOrder;
use Livewire\Attributes\Locked;

class OrderAddressesForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public array $addresses = [];

    protected function getActions(): array
    {
        return [
            'update' => UpdateLockedOrder::class,
        ];
    }
}
