<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Actions\Ticket\DeleteTicket;
use FluxErp\Actions\Ticket\UpdateTicket;
use FluxErp\Models\Ticket;
use Livewire\Attributes\Locked;

class TicketForm extends FluxForm
{
    public array $authenticatable = [];

    public ?int $authenticatable_id = null;

    public ?string $authenticatable_type = null;

    public ?string $created_at = null;

    public ?string $created_by = null;

    public ?string $description = null;

    #[Locked]
    public ?int $id = null;

    public ?int $model_id = null;

    public ?string $model_type = null;

    public ?string $state = null;

    public ?string $ticket_number = null;

    public ?array $ticket_type = null;

    public ?int $ticket_type_id = null;

    public ?string $title = null;

    public ?string $updated_at = null;

    public ?string $updated_by = null;

    public array $users = [];

    public function fill($values): void
    {
        if ($values instanceof Ticket) {
            $values->loadMissing([
                'authenticatable',
                'ticketType:id,name',
                'users:id',
            ]);
            $model = $values;

            $values = $values->toArray();
            data_set($values, 'authenticatable.avatar_url', $model->authenticatable?->getAvatarUrl());
            data_set($values, 'authenticatable.avatar_url', $model->authenticatable?->getAvatarUrl());
            data_set($values, 'authenticatable.name', $model->authenticatable?->getLabel());
            data_set($values, 'users', array_column($values['users'], 'id'));
        }

        parent::fill($values);
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateTicket::class,
            'update' => UpdateTicket::class,
            'delete' => DeleteTicket::class,
        ];
    }
}
