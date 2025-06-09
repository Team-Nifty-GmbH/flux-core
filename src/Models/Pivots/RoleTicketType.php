<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\Role;
use FluxErp\Models\TicketType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleTicketType extends FluxPivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $table = 'role_ticket_type';

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }
}
