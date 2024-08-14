<?php

namespace FluxErp\Services;

use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Actions\Ticket\DeleteTicket;
use FluxErp\Actions\Ticket\ToggleTicketUser;
use FluxErp\Actions\Ticket\UpdateTicket;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class TicketService
{
    public function create(array $data): Ticket
    {
        return CreateTicket::make($data)->validate()->execute();
    }

    public function update(array $data): Model
    {
        return UpdateTicket::make($data)->validate()->execute();
    }

    public function delete(string $id): array
    {
        try {
            DeleteTicket::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'ticket deleted'
        );
    }

    public function toggleUserAssignment(array $data): array
    {
        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            statusMessage: 'user '.(
                ToggleTicketUser::make($data)->validate()->execute()['attached'] ? 'attached' : 'detached'
            )
        );
    }
}
