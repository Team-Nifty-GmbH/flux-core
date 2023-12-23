<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Currency\CreateCurrency;
use FluxErp\Actions\Currency\DeleteCurrency;
use FluxErp\Actions\Currency\UpdateCurrency;
use Livewire\Attributes\Locked;

class CurrencyForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $iso = null;

    public ?string $symbol = null;

    public bool $is_default = false;

    protected function getActions(): array
    {
        return [
            'create' => CreateCurrency::class,
            'update' => UpdateCurrency::class,
            'delete' => DeleteCurrency::class,
        ];
    }
}
