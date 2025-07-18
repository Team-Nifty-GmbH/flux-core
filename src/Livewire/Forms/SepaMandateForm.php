<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\SepaMandate\CreateSepaMandate;
use FluxErp\Actions\SepaMandate\DeleteSepaMandate;
use FluxErp\Actions\SepaMandate\UpdateSepaMandate;
use Livewire\Attributes\Locked;

class SepaMandateForm extends FluxForm
{
    #[Locked]
    public ?int $client_id = null;

    public ?int $contact_bank_connection_id = null;

    #[Locked]
    public ?int $contact_id = null;

    #[Locked]
    public ?int $id = null;

    public ?string $sepa_mandate_type_enum = null;

    public ?string $signed_date = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateSepaMandate::class,
            'update' => UpdateSepaMandate::class,
            'delete' => DeleteSepaMandate::class,
        ];
    }
}
