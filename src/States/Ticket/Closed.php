<?php

namespace FluxErp\States\Ticket;

class Closed extends TicketState
{
    public static bool $isEndState = true;

    public static $name = 'closed';

    public function color(): string
    {
        return static::$color ?? 'emerald';
    }
}
