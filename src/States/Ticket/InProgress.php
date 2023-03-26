<?php

namespace FluxErp\States\Ticket;

use FluxErp\Models\StateSetting;

class InProgress extends TicketState
{
    public static $name = 'in_progress';

    public function color(): string
    {
        return StateSetting::query()->where('model', self::class)->first()?->color ?: 'warning';
    }
}
