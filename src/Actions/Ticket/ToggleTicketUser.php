<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\ToggleTicketUserAssignmentRequest;
use FluxErp\Models\Ticket;

class ToggleTicketUser extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new ToggleTicketUserAssignmentRequest())->rules();
    }

    public static function name(): string
    {
        return 'ticket.toggle-user';
    }

    public static function models(): array
    {
        return [Ticket::class];
    }

    public function execute(): array
    {
        $ticket = Ticket::query()
            ->whereKey($this->data['ticket_id'])
            ->first();

        return $ticket->users()->toggle($this->data['user_id']);
    }
}
