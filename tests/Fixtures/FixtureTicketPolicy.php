<?php

namespace FluxErp\Tests\Fixtures;

use FluxErp\Models\Ticket;
use FluxErp\Models\User;

class FixtureTicketPolicy
{
    /**
     * @var array<int, int>
     */
    public static array $allowedIds = [];

    public function view(User $user, Ticket $ticket): bool
    {
        return in_array($ticket->getKey(), static::$allowedIds, true);
    }
}
