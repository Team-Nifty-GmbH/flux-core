<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\ContactOrigin\CreateContactOrigin;
use FluxErp\Actions\ContactOrigin\DeleteContactOrigin;
use FluxErp\Actions\ContactOrigin\UpdateContactOrigin;
use Livewire\Attributes\Locked;

class ContactOriginForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?string $name = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateContactOrigin::class,
            'update' => UpdateContactOrigin::class,
            'delete' => DeleteContactOrigin::class,
        ];
    }
}
