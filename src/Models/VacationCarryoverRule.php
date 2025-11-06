<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasDefault;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacationCarryoverRule extends FluxModel
{
    use HasDefault, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
