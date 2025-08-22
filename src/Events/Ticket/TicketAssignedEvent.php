<?php

namespace FluxErp\Events\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Support\Event\SubscribableEvent;
use Illuminate\Queue\SerializesModels;

class TicketAssignedEvent extends SubscribableEvent
{
    use SerializesModels;

    public function __construct(public Ticket $ticket) {}

    public function eventName(): string
    {
        return '*';
    }
}
