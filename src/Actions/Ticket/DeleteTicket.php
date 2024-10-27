<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Ticket;
use FluxErp\Rulesets\Ticket\DeleteTicketRuleset;

class DeleteTicket extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteTicketRuleset::class;
    }

    public static function models(): array
    {
        return [Ticket::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(Ticket::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
