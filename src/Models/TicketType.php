<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasCustomEvents;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use CacheModelQueries, HasAdditionalColumns, HasCustomEvents, HasPackageFactory, HasUserModification, HasUuid,
        LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_ticket_type');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
