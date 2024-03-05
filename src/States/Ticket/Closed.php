<?php

namespace FluxErp\States\Ticket;

class Closed extends TicketState
{
    public static $name = 'closed';

    public function color(): string
    {
        return static::$color ?? 'positive';
    }
}
