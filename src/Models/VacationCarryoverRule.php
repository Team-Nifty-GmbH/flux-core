<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\SoftDeletes;

class VacationCarryoverRule extends FluxModel
{
    use HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'effective_year' => 'integer',
        'cutoff_month' => 'integer',
        'cutoff_day' => 'integer',
        'max_carryover_days' => 'integer',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];
}