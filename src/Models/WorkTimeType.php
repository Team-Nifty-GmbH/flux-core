<?php

namespace FluxErp\Models;

use FluxErp\Traits\CacheModelQueries;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkTimeType extends FluxModel
{
    use CacheModelQueries, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_billable' => 'boolean',
        ];
    }

    protected function broadcastToEveryone(): bool
    {
        return true;
    }

    public function workTimes(): HasMany
    {
        return $this->hasMany(WorkTime::class);
    }
}
