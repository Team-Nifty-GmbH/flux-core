<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\MailFolder\CreateMailFolder;
use FluxErp\Actions\MailFolder\DeleteMailFolder;
use FluxErp\Actions\MailFolder\UpdateMailFolder;
use Livewire\Attributes\Locked;

class MailFolderForm extends FluxForm
{
    public ?bool $can_create_purchase_invoice = false;

    public ?bool $can_create_ticket = false;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?string $name = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateMailFolder::class,
            'update' => UpdateMailFolder::class,
            'delete' => DeleteMailFolder::class,
        ];
    }
}
