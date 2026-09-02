<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\AbsencePolicy;
use FluxErp\Models\AbsenceType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsencePolicyAbsenceType extends FluxPivot
{
    protected $table = 'absence_policy_absence_type';

    // Relations
    /**
     * @return BelongsTo<AbsencePolicy, $this>
     */
    public function absencePolicy(): BelongsTo
    {
        return $this->belongsTo(AbsencePolicy::class);
    }

    /**
     * @return BelongsTo<AbsenceType, $this>
     */
    public function absenceType(): BelongsTo
    {
        return $this->belongsTo(AbsenceType::class);
    }
}
