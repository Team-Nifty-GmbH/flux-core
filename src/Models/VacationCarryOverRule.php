<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasDefault;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacationCarryOverRule extends FluxModel
{
    use HasDefault, HasUserModification, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'effective_year' => 'integer',
            'cutoff_month' => 'integer',
            'cutoff_day' => 'integer',
            'max_carryover_days' => 'integer',
            'expiry_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
