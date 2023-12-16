<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\WorkTimeType\CreateWorkTimeType;
use FluxErp\Actions\WorkTimeType\UpdateWorkTimeType;
use Livewire\Attributes\Locked;
use Livewire\Form;

class WorkTimeTypeForm extends Form
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public bool $is_billable = true;

    public function save(): void
    {
        $action = $this->id ? UpdateWorkTimeType::make($this->toArray()) : CreateWorkTimeType::make($this->toArray());

        $response = $action->validate()->execute();

        $this->fill($response);
    }
}
