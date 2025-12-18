<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\TicketType\CreateTicketType;
use FluxErp\Actions\TicketType\DeleteTicketType;
use FluxErp\Actions\TicketType\UpdateTicketType;
use FluxErp\Traits\Livewire\Form\SupportsAutoRender;
use Livewire\Attributes\Locked;

class TicketTypeForm extends FluxForm
{
    use SupportsAutoRender;

    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public function getActions(): array
    {
        return [
            'create' => CreateTicketType::class,
            'update' => UpdateTicketType::class,
            'delete' => DeleteTicketType::class,
        ];
    }

    protected function renderAsModal(): bool
    {
        return true;
    }
}
