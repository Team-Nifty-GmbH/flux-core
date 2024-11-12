<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Ticket;
use FluxErp\Rulesets\Ticket\ToggleTicketUserRuleset;

class ToggleTicketUser extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return ToggleTicketUserRuleset::class;
    }

    public static function name(): string
    {
        return 'ticket.toggle-user';
    }

    public static function models(): array
    {
        return [Ticket::class];
    }

    public function performAction(): array
    {
        $ticket = resolve_static(Ticket::class, 'query')
            ->whereKey($this->data['ticket_id'])
            ->first();

        return $ticket->users()->toggle($this->data['user_id']);
    }
}
