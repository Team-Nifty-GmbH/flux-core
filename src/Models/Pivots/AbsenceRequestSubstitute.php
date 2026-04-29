<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceRequestSubstitute extends FluxPivot
{
    protected $table = 'absence_request_substitute';

    // Relations
    public function absenceRequest(): BelongsTo
    {
        return $this->belongsTo(AbsenceRequest::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
