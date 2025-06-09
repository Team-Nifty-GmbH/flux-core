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

    public ?int $media_id = null;

    public ?int $printer_id = null;

    public int $quantity = 1;

    public ?string $size = null;

    public function reset(...$properties): void
    {
        parent::reset($properties);

        $this->printer_id ??= auth()->user()->printers()->where('printer_user.is_default', true)->value('id');
    }

    protected function getActions(): array
    {
        return [
            'create' => CreatePrintJob::class,
            'update' => UpdatePrintJob::class,
            'delete' => DeletePrintJob::class,
        ];
    }
}
