<?php

namespace FluxErp\States\Ticket;

use FluxErp\Models\StateSetting;

class Closed extends TicketState
{
    public static $name = 'closed';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'positive';
    }
}
