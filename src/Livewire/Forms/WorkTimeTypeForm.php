<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\WorkTimeType\CreateWorkTimeType;
use FluxErp\Actions\WorkTimeType\DeleteWorkTimeType;
use FluxErp\Actions\WorkTimeType\UpdateWorkTimeType;
use Livewire\Attributes\Locked;

class WorkTimeTypeForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public bool $is_billable = true;

    public ?string $name = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateWorkTimeType::class,
            'update' => UpdateWorkTimeType::class,
            'delete' => DeleteWorkTimeType::class,
        ];
    }
}
