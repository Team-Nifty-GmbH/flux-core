<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Printer\CreatePrinter;
use FluxErp\Actions\Printer\DeletePrinter;
use FluxErp\Actions\Printer\UpdatePrinter;
use Livewire\Attributes\Locked;

class PrinterForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreatePrinter::class,
            'update' => UpdatePrinter::class,
            'delete' => DeletePrinter::class,
        ];
    }
}
