<?php

namespace FluxErp\States\Ticket;

use FluxErp\Models\StateSetting;

class Done extends TicketState
{
    public static $name = 'done';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'positive';
    }
}
