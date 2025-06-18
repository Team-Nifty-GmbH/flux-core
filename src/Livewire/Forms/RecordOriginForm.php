<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\RecordOrigin\CreateRecordOrigin;
use FluxErp\Actions\RecordOrigin\DeleteRecordOrigin;
use FluxErp\Actions\RecordOrigin\UpdateRecordOrigin;
use Livewire\Attributes\Locked;

class RecordOriginForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?string $model_type = null;

    public ?string $name = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateRecordOrigin::class,
            'update' => UpdateRecordOrigin::class,
            'delete' => DeleteRecordOrigin::class,
        ];
    }
}
