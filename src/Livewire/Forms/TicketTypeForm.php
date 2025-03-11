<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\TicketType\CreateTicketType;
use FluxErp\Actions\TicketType\DeleteTicketType;
use FluxErp\Actions\TicketType\UpdateTicketType;
use Livewire\Attributes\Locked;

class TicketTypeForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $model_type = null;

    public ?string $name = null;

    public array $roles = [];

    public function getActions(): array
    {
        return [
            'create' => CreateTicketType::class,
            'update' => UpdateTicketType::class,
            'delete' => DeleteTicketType::class,
        ];
    }
}
