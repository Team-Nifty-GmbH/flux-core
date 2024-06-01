<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Unit\CreateUnit;
use FluxErp\Actions\Unit\DeleteUnit;
use FluxErp\Actions\Unit\UpdateUnit;
use Livewire\Attributes\Locked;

class UnitForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $abbreviation = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateUnit::class,
            'update' => UpdateUnit::class,
            'delete' => DeleteUnit::class,
        ];
    }
}
