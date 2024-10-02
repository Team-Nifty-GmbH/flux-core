<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Order\UpdateOrder;
use Livewire\Attributes\Locked;

class OrderAddressesForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public array $addresses = [];

    protected function getActions(): array
    {
        return [
            'update' => UpdateOrder::class,
        ];
    }
}
