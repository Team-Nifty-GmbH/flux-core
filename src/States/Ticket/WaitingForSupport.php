<?php

namespace FluxErp\States\Ticket;

class WaitingForSupport extends TicketState
{
    public static $name = 'waiting_for_support';

    public function color(): string
    {
        return static::$color ?? 'amber';
    }
}
