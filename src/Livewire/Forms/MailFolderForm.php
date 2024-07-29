<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\MailFolder\CreateMailFolder;
use FluxErp\Actions\MailFolder\DeleteMailFolder;
use FluxErp\Actions\MailFolder\UpdateMailFolder;
use Livewire\Attributes\Locked;

class MailFolderForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?bool $creates_ticket = false;

    public ?bool $creates_purchase_invoice = false;

    public bool $is_active = true;

    protected function getActions(): array
    {
        return [
            'create' => CreateMailFolder::class,
            'update' => UpdateMailFolder::class,
            'delete' => DeleteMailFolder::class,
        ];
    }
}
