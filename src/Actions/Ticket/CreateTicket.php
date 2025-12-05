<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Events\Ticket\TicketAssignedEvent;
use FluxErp\Models\Client;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Rulesets\Ticket\CreateTicketRuleset;
use FluxErp\Traits\Model\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class CreateTicket extends FluxAction
{
    public static function models(): array
    {
        return [Ticket::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateTicketRuleset::class;
    }

    public function performAction(): Ticket
    {
        $users = Arr::pull($this->data, 'users');

        $ticket = app(Ticket::class, ['attributes' => $this->data]);

        $ticket->getSerialNumber(
            'ticket_number',
            Auth::user()?->client_id ?? resolve_static(Client::class, 'default')?->getKey()
        );

        $ticket->save();

        if (in_array(Notifiable::class, class_uses_recursive($ticket->authenticatable))) {
            $ticket->authenticatable->subscribeNotificationChannel($ticket->broadcastChannel());
        }

        if ($users) {
            if ($ticket->authenticatable->getMorphClass() === morph_alias(User::class)) {
                $users = array_filter($users, fn (int $user) => $user !== $ticket->authenticatable_id);
            }

            $ticket->users()->attach($users);
            event(TicketAssignedEvent::make($ticket)
                ->subscribeChannel($users)
            );
        }

        return $ticket->refresh();
    }
}
