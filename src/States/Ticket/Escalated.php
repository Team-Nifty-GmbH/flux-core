<?php

namespace FluxErp\States\Ticket;

class Escalated extends TicketState
{
    public static $name = 'escalated';

    public function color(): string
    {
        return static::$color ?? 'red';
    }
}
