<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketUser extends FluxPivot
{
    use HasPackageFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = ['ticket_id', 'user_id'];

    protected $table = 'ticket_user';

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
