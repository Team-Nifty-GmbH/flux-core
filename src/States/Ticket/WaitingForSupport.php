<?php

namespace FluxErp\States\Ticket;

use FluxErp\Models\StateSetting;

class WaitingForSupport extends TicketState
{
    public static $name = 'waiting_for_support';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'warning';
    }
}
