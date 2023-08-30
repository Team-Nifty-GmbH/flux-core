<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasCustomEvents;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasAdditionalColumns, HasCustomEvents, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public array $translatable = [
        'name',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
