<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\SoftDeletes;

class AbsenceType extends FluxModel
{
    use HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'is_active' => 'boolean',
        'can_select_substitute' => 'boolean',
        'must_select_substitute' => 'boolean',
        'requires_proof' => 'boolean',
        'requires_reason' => 'boolean',
        'counts_as_work_day' => 'boolean',
        'counts_as_target_hours' => 'boolean',
        'requires_work_day' => 'boolean',
        'is_vacation' => 'boolean',
    ];
}