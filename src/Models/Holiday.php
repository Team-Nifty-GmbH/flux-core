<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\SoftDeletes;

class Holiday extends FluxModel
{
    use HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'date' => 'date',
        'month' => 'integer',
        'day' => 'integer',
        'is_recurring' => 'boolean',
        'effective_from' => 'integer',
        'effective_until' => 'integer',
        'is_active' => 'boolean',
    ];
    
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}