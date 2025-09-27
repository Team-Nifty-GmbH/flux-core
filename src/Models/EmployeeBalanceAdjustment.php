<?php

namespace FluxErp\Models;

use FluxErp\Enums\EmployeeBalanceAdjustmentReasonEnum;
use FluxErp\Enums\EmployeeBalanceAdjustmentTypeEnum;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBalanceAdjustment extends FluxModel
{
    use HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => EmployeeBalanceAdjustmentTypeEnum::class,
            'reason' => EmployeeBalanceAdjustmentReasonEnum::class,
            'effective_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
