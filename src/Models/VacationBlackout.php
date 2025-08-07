<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\VacationBlackoutRole;
use FluxErp\Models\Pivots\VacationBlackoutUser;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VacationBlackout extends FluxModel
{
    use HasClientAssignment, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'vacation_blackout_role')
            ->using(VacationBlackoutRole::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vacation_blackout_user')
            ->using(VacationBlackoutUser::class);
    }
}