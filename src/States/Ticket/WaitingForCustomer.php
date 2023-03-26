<?php

namespace FluxErp\States\Ticket;

use FluxErp\Models\StateSetting;

class WaitingForCustomer extends TicketState
{
    public static $name = 'waiting_for_customer';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'secondary';
    }
}
