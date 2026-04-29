<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkTimeType extends FluxModel
{
    use HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_billable' => 'boolean',
        ];
    }

    // Relations
    public function workTimes(): HasMany
    {
        return $this->hasMany(WorkTime::class);
    }

    // Protected methods
    protected function broadcastToEveryone(): bool
    {
        return true;
    }
}
