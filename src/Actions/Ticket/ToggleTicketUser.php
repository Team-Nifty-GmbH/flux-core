<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Ticket;
use FluxErp\Rulesets\Ticket\ToggleTicketUserRuleset;
use Illuminate\Http\Response;

class ToggleTicketUser extends FluxAction
{
    public static function models(): array
    {
        return [Ticket::class];
    }

    public static function name(): string
    {
        return 'ticket.toggle-user';
    }

    protected static function getSuccessCode(): ?int
    {
        return parent::getSuccessCode() ?? Response::HTTP_OK;
    }

    protected function getRulesets(): string|array
    {
        return ToggleTicketUserRuleset::class;
    }

    public function performAction(): array
    {
        $ticket = resolve_static(Ticket::class, 'query')
            ->whereKey($this->data['ticket_id'])
            ->first();

        return $ticket->users()->toggle($this->data['user_id']);
    }
}
