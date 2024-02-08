<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;

class ToggleTicketUserAssignmentRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'ticket_id' => [
                'required',
                'integer',
                new ModelExists(Ticket::class),
            ],
            'user_id' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
        ];
    }
}
