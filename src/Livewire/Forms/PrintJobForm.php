<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\PrintJob\CreatePrintJob;
use FluxErp\Actions\PrintJob\DeletePrintJob;
use FluxErp\Actions\PrintJob\UpdatePrintJob;
use Livewire\Attributes\Locked;

class PrintJobForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreatePrintJob::class,
            'update' => UpdatePrintJob::class,
            'delete' => DeletePrintJob::class,
        ];
    }
}
