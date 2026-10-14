<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class TicketUser extends FluxPivot
{
    protected $table = 'ticket_user';

    // Relations
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function broadcastOn(string $event): array
    {
        return array_merge(
            Arr::wrap(parent::broadcastOn($event)),
            $this->userBroadcastChannels([$this->user_id])
        );
    }
}
