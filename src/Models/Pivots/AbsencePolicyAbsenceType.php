<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\AbsencePolicy;
use FluxErp\Models\AbsenceType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsencePolicyAbsenceType extends FluxPivot
{
    protected $table = 'absence_policy_absence_type';

    public function absencePolicy(): BelongsTo
    {
        return $this->belongsTo(AbsencePolicy::class);
    }

    public function absenceType(): BelongsTo
    {
        return $this->belongsTo(AbsenceType::class);
    }
}
