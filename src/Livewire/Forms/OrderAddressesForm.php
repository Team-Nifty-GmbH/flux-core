<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Order\UpdateLockedOrder;
use Livewire\Attributes\Locked;

class OrderAddressesForm extends FluxForm
{
    public array $addresses = [];

    #[Locked]
    public ?int $id = null;

    protected function getActions(): array
    {
        return [
            'update' => UpdateLockedOrder::class,
        ];
    }
}
