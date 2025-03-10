<?php

namespace FluxErp\States\Ticket;

class WaitingForCustomer extends TicketState
{
    public static $name = 'waiting_for_customer';

    public function color(): string
    {
        return static::$color ?? 'gray';
    }
}
