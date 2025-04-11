<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\PrinterUser\CreatePrinterUser;
use FluxErp\Actions\PrinterUser\DeletePrinterUser;
use FluxErp\Actions\PrinterUser\UpdatePrinterUser;
use Livewire\Attributes\Locked;

class PrinterUserForm extends FluxForm
{
    public ?string $default_size = null;

    public bool $is_default = false;

    public ?int $pivot_id = null;

    #[Locked]
    public ?int $printer_id = null;

    public function getKey(): string
    {
        return 'pivot_id';
    }

    protected function getActions(): array
    {
        return [
            'create' => CreatePrinterUser::class,
            'update' => UpdatePrinterUser::class,
            'delete' => DeletePrinterUser::class,
        ];
    }
}
