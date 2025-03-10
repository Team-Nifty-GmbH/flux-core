<?php

namespace FluxErp\States\Ticket;

class Done extends TicketState
{
    public static bool $isEndState = true;

    public static $name = 'done';

    public function color(): string
    {
        return static::$color ?? 'emerald';
    }
}
