<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Actions\Ticket\DeleteTicket;
use FluxErp\Actions\Ticket\UpdateTicket;
use FluxErp\Models\Ticket;
use Livewire\Attributes\Locked;

class TicketForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $authenticatable_type = null;

    public ?int $authenticatable_id = null;

    public ?string $model_type = null;

    public ?int $model_id = null;

    public ?int $ticket_type_id = null;

    public ?string $ticket_number = null;

    public ?string $title = null;

    public ?string $description = null;

    public ?string $state = null;

    public ?string $created_at = null;

    public ?string $created_by = null;

    public ?string $updated_at = null;

    public ?string $updated_by = null;

    public array $users = [];

    public array $authenticatable = [];

    public array $ticket_type = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateTicket::class,
            'update' => UpdateTicket::class,
            'delete' => DeleteTicket::class,
        ];
    }

    public function fill($values): void
    {
        if ($values instanceof Ticket) {
            $values->loadMissing([
                'authenticatable',
                'ticketType:id,name',
                'users:id',
            ]);
            data_set($values, 'authenticatable.avatar_url', $values->authenticatable?->getAvatarUrl());
            data_set($values, 'authenticatable.avatar_url', $values->authenticatable?->getAvatarUrl());
            data_set($values, 'authenticatable.name', $values->authenticatable?->getLabel());

            $values = $values->toArray();
            data_set($values, 'users', array_column($values['users'], 'id'));
        }

        parent::fill($values);
    }
}
