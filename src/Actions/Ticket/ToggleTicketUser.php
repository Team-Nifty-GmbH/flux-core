<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Ticket;
use FluxErp\Rulesets\Ticket\ToggleTicketUserRuleset;

class ToggleTicketUser extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(ToggleTicketUserRuleset::class, 'getRules');
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
        $ticket = app(Ticket::class)->query()
            ->whereKey($this->data['ticket_id'])
            ->first();

        return $ticket->users()->toggle($this->data['user_id']);
    }
}
