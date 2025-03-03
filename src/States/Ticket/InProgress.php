<?php

namespace FluxErp\States\Ticket;

class InProgress extends TicketState
{
    public static $name = 'in_progress';

    public function color(): string
    {
        return static::$color ?? 'amber';
    }
}
