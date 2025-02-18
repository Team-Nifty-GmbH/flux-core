<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Industry\CreateIndustry;
use FluxErp\Actions\Industry\DeleteIndustry;
use FluxErp\Actions\Industry\UpdateIndustry;
use Livewire\Attributes\Locked;

class IndustryForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateIndustry::class,
            'update' => UpdateIndustry::class,
            'delete' => DeleteIndustry::class,
        ];
    }
}
