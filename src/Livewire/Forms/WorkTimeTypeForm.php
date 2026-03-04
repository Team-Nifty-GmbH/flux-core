<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\WorkTimeType\CreateWorkTimeType;
use FluxErp\Actions\WorkTimeType\DeleteWorkTimeType;
use FluxErp\Actions\WorkTimeType\UpdateWorkTimeType;
use FluxErp\Support\Livewire\Attributes\RenderAs;
use FluxErp\Traits\Livewire\Form\SupportsAutoRender;
use Livewire\Attributes\Locked;

class WorkTimeTypeForm extends FluxForm
{
    use SupportsAutoRender;

    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    #[RenderAs('toggle')]
    public bool $is_billable = true;

    protected function getActions(): array
    {
        return [
            'create' => CreateWorkTimeType::class,
            'update' => UpdateWorkTimeType::class,
            'delete' => DeleteWorkTimeType::class,
        ];
    }

    protected function renderAsModal(): bool
    {
        return true;
    }
}
