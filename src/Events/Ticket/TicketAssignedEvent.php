<?php

namespace FluxErp\Events\Ticket;

use FluxErp\Models\Ticket;
use FluxErp\Support\Event\SubscribableEvent;

class TicketAssignedEvent extends SubscribableEvent
{
    public function __construct(public Ticket $ticket) {}
}
